# WP Product Verification

A comprehensive WordPress plugin for product serial number verification with Elementor integration.

## Features

### Core Functionality
- **Product Management**: Create and manage multiple products with unique prefixes
- **Serial Number Management**: Add, generate, and track serial numbers for each product
- **Verification System**: Allow customers to verify product authenticity via serial numbers
- **Verification Limits**: Set maximum verification counts per serial number
- **Email Notifications**: Automatic notifications when serials reach max verifications

### Configuration Options
- **Max Verifications**: Set the maximum number of times a serial can be verified
- **Serial Format**: Customize serial number length, separator character, and interval
- **Prefix Management**: Assign unique prefixes to each product (e.g., ABCD-1234-5678-9012)
- **Email Settings**: Configure multiple notification recipients

### Import & Export
- **CSV Import**: Bulk import serial numbers with batch processing for large files (10,000+ serials)
- **Sample CSV**: Download sample CSV file with proper formatting
- **Progress Tracking**: Real-time progress bar during import

### Elementor Integration
- **Custom Widget**: Drag-and-drop verification form widget
- **Full Customization**: Control colors, typography, spacing, and layout
- **Responsive Design**: Mobile-friendly verification forms
- **Live Preview**: See changes in real-time in Elementor editor

### Admin Dashboard
- **Statistics**: Overview of products, serials, and verifications
- **Recent Activity**: Track recent verification attempts
- **Product Overview**: View all products and their serial numbers
- **Verification Logs**: Complete audit trail with IP addresses and timestamps

## Installation

1. Upload the plugin files to `/wp-content/plugins/wp-product-verification/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'Product Verification' in the admin menu
4. Configure your settings under Settings tab

## Usage

### 1. Configure Settings

Go to **Product Verification > Settings** and configure:

- **Maximum Verifications**: Number of times each serial can be verified (default: 1)
- **Serial Length**: Total character count including prefix and separators (default: 16)
- **Separator**: Character to separate serial blocks (default: -)
- **Separator Interval**: Characters between each separator (default: 4)
- **Notification Emails**: Email addresses for notifications (one per line or comma-separated)

### 2. Create Products

Go to **Product Verification > Products**:

1. Click "Add New"
2. Enter product name
3. Set a unique prefix (e.g., "ABCD" for Product A)
4. Add optional description
5. Click "Add Product"

### 3. Add Serial Numbers

#### Manual Entry
1. Go to **Product Verification > Serial Numbers**
2. Click "Add New"
3. Select product
4. Enter serial number
5. Set max verifications
6. Click "Add Serial"

#### Generate Serials
1. Go to **Product Verification > Serial Numbers**
2. Click "Generate Serials"
3. Select product
4. Enter quantity (max 1000 per batch)
5. Click "Generate Serials"

#### CSV Import
1. Go to **Product Verification > Import Serials**
2. Download the sample CSV file
3. Prepare your CSV with one serial per row
4. Select product
5. Upload CSV file
6. Wait for batch processing to complete

### 4. Add Verification Form to Page

#### Using Elementor:
1. Edit any page with Elementor
2. Search for "Product Verification Form" widget
3. Drag it onto your page
4. Customize the heading, description, placeholder, and button text
5. Style the form using the Style tab
6. Publish your page

#### Using Shortcode (if needed):
The Elementor widget is the primary method, but you can also add the form programmatically.

### 5. Customer Verification Process

1. Customer visits your verification page
2. Enters their serial number
3. Clicks "Verify"
4. Receives instant feedback on validity
5. Sees product information and remaining verifications

## File Structure

```
wp-product-verification/
├── wp-product-verification.php          # Main plugin file
├── includes/
│   ├── Admin/
│   │   ├── Menu.php                     # Admin menu registration
│   │   ├── Settings.php                 # Settings management
│   │   ├── ProductManager.php           # Product CRUD operations
│   │   ├── SerialManager.php            # Serial number management
│   │   ├── CSVImporter.php              # CSV import with batch processing
│   │   └── Views/
│   │       ├── dashboard.php            # Dashboard template
│   │       ├── products.php             # Products list/edit template
│   │       ├── serials.php              # Serials list template
│   │       ├── import.php               # Import page template
│   │       └── settings.php             # Settings page template
│   ├── Ajax/
│   │   └── VerificationHandler.php      # AJAX verification handler
│   ├── Database/
│   │   └── Schema.php                   # Database table creation
│   ├── Email/
│   │   └── NotificationManager.php      # Email notification system
│   └── Elementor/
│       └── VerificationWidget.php       # Elementor widget
├── assets/
│   ├── css/
│   │   ├── admin.css                    # Admin styles
│   │   └── frontend.css                 # Frontend styles
│   └── js/
│       ├── admin.js                     # Admin JavaScript
│       └── frontend.js                  # Frontend AJAX handler
└── README.md                            # Documentation
```

## Database Tables

### wpv_products
- `id`: Product ID
- `name`: Product name
- `prefix`: Serial number prefix
- `description`: Product description
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### wpv_serials
- `id`: Serial ID
- `product_id`: Associated product
- `serial_number`: Serial number
- `verification_count`: Current verification count
- `max_verifications`: Maximum allowed verifications
- `status`: Serial status (active/inactive)
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### wpv_verification_logs
- `id`: Log ID
- `serial_id`: Associated serial
- `product_id`: Associated product
- `ip_address`: User IP address
- `user_agent`: Browser user agent
- `verified_at`: Verification timestamp

## Type Safety

This plugin follows strict type hints throughout the codebase:

- **Return Types**: All methods declare return types (`: void`, `: string`, `: int`, etc.)
- **Parameter Types**: All parameters have type declarations (`string $name`, `int $id`, etc.)
- **Property Types**: Class properties use type declarations
- **Null Safety**: Nullable types use proper `?Type` syntax
- **Array Types**: Uses PHPDoc `@param` and `@return` annotations for array types

Example:
```php
public function get_product(int $id): ?object {
    global $wpdb;
    $table = $wpdb->prefix . 'wpv_products';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
}
```

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Elementor (optional, for widget functionality)

## Frequently Asked Questions

### Can I customize the verification form design?
Yes! If using Elementor, you can fully customize colors, typography, spacing, borders, and more through the widget settings.

### How many serial numbers can I import at once?
The CSV importer uses batch processing, so you can import tens of thousands of serial numbers. The system processes them in batches of 500 to prevent timeouts.

### Can I have different verification limits for different products?
Yes, each serial number can have its own max verification count, or you can use the default from settings.

### What happens when a serial reaches max verifications?
The serial will no longer accept verification attempts, and configured email addresses will receive a notification.

### Can I export serial numbers?
Currently, the plugin focuses on import and management. You can view and manage serials through the admin interface.

### Is the plugin translation-ready?
Yes, all strings use WordPress translation functions and the text domain 'wp-product-verification'.

## Support

For support, please visit the plugin repository or contact the developer.

## Changelog

### 1.0.0
- Initial release
- Product management
- Serial number management
- CSV import with batch processing
- Elementor widget integration
- Email notifications
- Admin dashboard with statistics
- Verification logging

## License

GPL v2 or later

## Credits

Developed with proper PHP type hints and WordPress coding standards.
