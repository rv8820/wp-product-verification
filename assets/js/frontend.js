/**
 * Frontend JavaScript for WP Product Verification
 */

(function($) {
    'use strict';

    /**
     * Verification Form Handler
     */
    class VerificationForm {
        /**
         * Constructor
         * @param {jQuery} $form - Form element
         */
        constructor($form) {
            this.$form = $form;
            this.$input = $form.find('#wpv-serial-input');
            this.$button = $form.find('.wpv-button');
            this.$message = $form.find('#wpv-message');
            this.buttonOriginalText = this.$button.text();

            this.init();
        }

        /**
         * Initialize form
         * @returns {void}
         */
        init() {
            this.$form.on('submit', (e) => this.handleSubmit(e));

            // Auto-format serial number input
            this.$input.on('input', (e) => this.formatInput(e));
        }

        /**
         * Format input as user types
         * @param {Event} e - Input event
         * @returns {void}
         */
        formatInput(e) {
            let value = e.target.value.toUpperCase();
            // Remove any characters that aren't alphanumeric or hyphens
            value = value.replace(/[^A-Z0-9-]/g, '');
            e.target.value = value;
        }

        /**
         * Handle form submission
         * @param {Event} e - Submit event
         * @returns {void}
         */
        handleSubmit(e) {
            e.preventDefault();

            const serialNumber = this.$input.val().trim();

            if (!serialNumber) {
                this.showMessage('Please enter a serial number.', 'error');
                return;
            }

            this.verifySerial(serialNumber);
        }

        /**
         * Verify serial number via AJAX
         * @param {string} serialNumber - Serial number to verify
         * @returns {void}
         */
        verifySerial(serialNumber) {
            // Disable button and show loading state
            this.$button.prop('disabled', true);
            this.$button.html('<span class="wpv-loading"></span> Verifying...');
            this.hideMessage();

            $.ajax({
                url: wpvFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpv_verify_serial',
                    nonce: wpvFrontend.nonce,
                    serial_number: serialNumber
                },
                success: (response) => {
                    if (response.success) {
                        this.handleSuccess(response.data);
                    } else {
                        this.handleError(response.data);
                    }
                },
                error: () => {
                    this.showMessage(
                        'An error occurred while verifying the serial number. Please try again.',
                        'error'
                    );
                },
                complete: () => {
                    // Re-enable button
                    this.$button.prop('disabled', false);
                    this.$button.text(this.buttonOriginalText);
                }
            });
        }

        /**
         * Handle successful verification
         * @param {Object} data - Response data
         * @returns {void}
         */
        handleSuccess(data) {
            let message = data.message;

            if (data.product_name) {
                message += `
                    <div class="wpv-verification-details">
                        <h4>Verification Details</h4>
                        <p><strong>Product:</strong> ${this.escapeHtml(data.product_name)}</p>
                        <p><strong>Verifications:</strong> ${data.verification_count} / ${data.max_verifications}</p>
                        ${data.remaining > 0
                            ? `<p><strong>Remaining:</strong> ${data.remaining}</p>`
                            : '<p style="color: #d9534f;"><strong>Maximum verifications reached</strong></p>'}
                    </div>
                `;
            }

            this.showMessage(message, 'success');
            this.$input.val('');
        }

        /**
         * Handle verification error
         * @param {Object} data - Response data
         * @returns {void}
         */
        handleError(data) {
            this.showMessage(data.message || 'Verification failed.', 'error');
        }

        /**
         * Show message
         * @param {string} message - Message to display
         * @param {string} type - Message type (success, error, info)
         * @returns {void}
         */
        showMessage(message, type) {
            this.$message
                .removeClass('wpv-success wpv-error wpv-info')
                .addClass('wpv-' + type)
                .html(message)
                .slideDown();
        }

        /**
         * Hide message
         * @returns {void}
         */
        hideMessage() {
            this.$message.slideUp();
        }

        /**
         * Escape HTML to prevent XSS
         * @param {string} text - Text to escape
         * @returns {string} Escaped text
         */
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, (m) => map[m]);
        }
    }

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        // Initialize all verification forms on the page
        $('.wpv-verification-form').each(function() {
            new VerificationForm($(this));
        });
    });

})(jQuery);
