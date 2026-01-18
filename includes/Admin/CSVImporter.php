<?php
/**
 * CSV Import functionality
 *
 * @package WPProductVerification\Admin
 */

namespace WPProductVerification\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CSVImporter class for handling CSV imports
 */
class CSVImporter {
    /**
     * @var int Batch size for processing
     */
    private const BATCH_SIZE = 500;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_post_wpv_import_csv', [$this, 'import_csv']);
        add_action('admin_post_wpv_download_sample', [$this, 'download_sample']);
        add_action('wp_ajax_wpv_process_import_batch', [$this, 'process_import_batch']);
    }

    /**
     * Handle CSV import
     *
     * @return void
     */
    public function import_csv(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-product-verification'));
        }

        check_admin_referer('wpv_import_csv', 'wpv_import_nonce');

        $product_id = absint($_POST['product_id'] ?? 0);

        if ($product_id === 0) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-import',
                'error' => 'missing_product',
            ], admin_url('admin.php')));
            exit;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-import',
                'error' => 'upload_error',
            ], admin_url('admin.php')));
            exit;
        }

        $file_path = $_FILES['csv_file']['tmp_name'];
        $file_extension = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));

        if ($file_extension !== 'csv') {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-import',
                'error' => 'invalid_file',
            ], admin_url('admin.php')));
            exit;
        }

        // Read CSV file
        $serials = $this->parse_csv_file($file_path);

        if (empty($serials)) {
            wp_redirect(add_query_arg([
                'page' => 'wp-product-verification-import',
                'error' => 'empty_file',
            ], admin_url('admin.php')));
            exit;
        }

        // Store import data in transient for batch processing
        $import_id = uniqid('wpv_import_', true);
        set_transient('wpv_import_' . $import_id, [
            'product_id' => $product_id,
            'serials' => $serials,
            'total' => count($serials),
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
        ], HOUR_IN_SECONDS);

        wp_redirect(add_query_arg([
            'page' => 'wp-product-verification-import',
            'import_id' => $import_id,
            'processing' => 'true',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Process import batch via AJAX
     *
     * @return void
     */
    public function process_import_batch(): void {
        check_ajax_referer('wpv_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        $import_id = sanitize_text_field($_POST['import_id'] ?? '');
        $import_data = get_transient('wpv_import_' . $import_id);

        if ($import_data === false) {
            wp_send_json_error(['message' => 'Import session expired']);
            return;
        }

        $max_verifications = absint($_POST['max_verifications'] ?? Settings::get_setting('max_verifications', 1));
        $batch_start = (int) $import_data['processed'];
        $batch_end = min($batch_start + self::BATCH_SIZE, $import_data['total']);

        $success = 0;
        $failed = 0;

        for ($i = $batch_start; $i < $batch_end; $i++) {
            $serial = $import_data['serials'][$i];
            if (SerialManager::add_serial_to_db($import_data['product_id'], $serial, $max_verifications)) {
                $success++;
            } else {
                $failed++;
            }
        }

        $import_data['processed'] = $batch_end;
        $import_data['success'] += $success;
        $import_data['failed'] += $failed;

        set_transient('wpv_import_' . $import_id, $import_data, HOUR_IN_SECONDS);

        $is_complete = $import_data['processed'] >= $import_data['total'];

        if ($is_complete) {
            delete_transient('wpv_import_' . $import_id);
        }

        wp_send_json_success([
            'processed' => $import_data['processed'],
            'total' => $import_data['total'],
            'success' => $import_data['success'],
            'failed' => $import_data['failed'],
            'is_complete' => $is_complete,
            'percentage' => round(($import_data['processed'] / $import_data['total']) * 100, 2),
        ]);
    }

    /**
     * Parse CSV file
     *
     * @param string $file_path Path to CSV file
     * @return array<int, string> Array of serial numbers
     */
    private function parse_csv_file(string $file_path): array {
        $serials = [];
        $handle = fopen($file_path, 'r');

        if ($handle === false) {
            return $serials;
        }

        // Skip header row
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (isset($row[0]) && !empty(trim($row[0]))) {
                $serials[] = strtoupper(trim($row[0]));
            }
        }

        fclose($handle);

        return array_unique($serials);
    }

    /**
     * Download sample CSV file
     *
     * @return void
     */
    public function download_sample(): void {
        $sample_data = [
            ['serial_number'],
            ['ABCD-1234-5678-9012'],
            ['ABCD-2345-6789-0123'],
            ['ABCD-3456-7890-1234'],
        ];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sample-serials.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        if ($output !== false) {
            foreach ($sample_data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }

        exit;
    }
}
