/**
 * Admin JavaScript for WP Product Verification
 */

(function($) {
    'use strict';

    /**
     * Admin functionality
     */
    const WPVAdmin = {
        /**
         * Initialize
         * @returns {void}
         */
        init() {
            this.handleFormValidation();
            this.handleDeleteConfirmation();
            this.setupPrefixAutoFormat();
        },

        /**
         * Handle form validation
         * @returns {void}
         */
        handleFormValidation() {
            $('form[action*="wpv_"]').on('submit', function(e) {
                const $form = $(this);
                const $requiredFields = $form.find('[required]');
                let isValid = true;

                $requiredFields.each(function() {
                    const $field = $(this);
                    if (!$field.val().trim()) {
                        isValid = false;
                        $field.css('border-color', '#d63638');

                        // Remove error styling when user types
                        $field.one('input change', function() {
                            $(this).css('border-color', '');
                        });
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        },

        /**
         * Handle delete confirmation
         * @returns {void}
         */
        handleDeleteConfirmation() {
            $('.button-link-delete').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        },

        /**
         * Setup prefix auto-format
         * @returns {void}
         */
        setupPrefixAutoFormat() {
            $('input[name="product_prefix"], input[name="serial_number"]').on('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
            });
        }
    };

    /**
     * Settings Page functionality
     */
    const WPVSettings = {
        /**
         * Initialize
         * @returns {void}
         */
        init() {
            this.updateFormatPreview();
        },

        /**
         * Update format preview
         * @returns {void}
         */
        updateFormatPreview() {
            const $lengthInput = $('#serial_length');
            const $separatorInput = $('#separator');
            const $intervalInput = $('#separator_interval');
            const $preview = $('#wpv-format-example');

            if ($preview.length === 0) {
                return;
            }

            const updatePreview = () => {
                const length = parseInt($lengthInput.val()) || 16;
                const separator = $separatorInput.val() || '-';
                const interval = parseInt($intervalInput.val()) || 4;
                const prefix = 'ABCD';

                const remaining = Math.max(0, length - prefix.length);
                let sample = prefix + 'X'.repeat(remaining);

                if (separator && interval > 0) {
                    const parts = sample.match(new RegExp('.{1,' + interval + '}', 'g'));
                    if (parts) {
                        sample = parts.join(separator);
                    }
                }

                $preview.text(sample);
            };

            $lengthInput.on('input change', updatePreview);
            $separatorInput.on('input change', updatePreview);
            $intervalInput.on('input change', updatePreview);
        }
    };

    /**
     * Product Management functionality
     */
    const WPVProducts = {
        mediaUploader: null,

        /**
         * Initialize
         * @returns {void}
         */
        init() {
            this.handleAddProductToggle();
            this.handleCancelAddProduct();
            this.handleLogoUpload();
        },

        /**
         * Handle add product form toggle
         * @returns {void}
         */
        handleAddProductToggle() {
            $('.wpv-add-product-btn').on('click', function(e) {
                e.preventDefault();
                $('#wpv-add-product-form').slideToggle();
            });
        },

        /**
         * Handle cancel add product
         * @returns {void}
         */
        handleCancelAddProduct() {
            $('.wpv-cancel-add-product').on('click', function(e) {
                e.preventDefault();
                $('#wpv-add-product-form').slideUp();
            });
        },

        /**
         * Handle logo upload
         * @returns {void}
         */
        handleLogoUpload() {
            const self = this;

            // Upload logo button
            $(document).on('click', '.wpv-upload-logo-btn', function(e) {
                e.preventDefault();

                const $button = $(this);
                const $container = $button.closest('.wpv-logo-upload-container');
                const $preview = $container.find('.wpv-logo-preview');
                const $input = $container.find('input[type="hidden"]');
                const $removeBtn = $container.find('.wpv-remove-logo-btn');

                // If the media frame already exists, reopen it
                if (self.mediaUploader) {
                    self.mediaUploader.open();
                    return;
                }

                // Create the media frame
                self.mediaUploader = wp.media({
                    title: 'Select Product Logo',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });

                // When an image is selected, run a callback
                self.mediaUploader.on('select', function() {
                    const attachment = self.mediaUploader.state().get('selection').first().toJSON();

                    // Set the hidden input value
                    $input.val(attachment.id);

                    // Display the image preview
                    $preview.html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;">');

                    // Show remove button
                    $removeBtn.show();
                });

                // Open the uploader dialog
                self.mediaUploader.open();
            });

            // Remove logo button
            $(document).on('click', '.wpv-remove-logo-btn', function(e) {
                e.preventDefault();

                const $button = $(this);
                const $container = $button.closest('.wpv-logo-upload-container');
                const $preview = $container.find('.wpv-logo-preview');
                const $input = $container.find('input[type="hidden"]');

                // Clear the hidden input
                $input.val('');

                // Clear the preview
                $preview.html('');

                // Hide remove button
                $button.hide();
            });
        }
    };

    /**
     * Serial Management functionality
     */
    const WPVSerials = {
        /**
         * Initialize
         * @returns {void}
         */
        init() {
            this.handleAddSerialToggle();
            this.handleGenerateToggle();
            this.handleCancelAddSerial();
            this.handleCancelGenerateSerials();
            this.handleBulkActions();
        },

        /**
         * Handle add serial form toggle
         * @returns {void}
         */
        handleAddSerialToggle() {
            $('.wpv-add-serial-btn').on('click', function(e) {
                e.preventDefault();
                $('#wpv-add-serial-form').slideToggle();
                $('#wpv-generate-serials-form').slideUp();
            });
        },

        /**
         * Handle generate serials form toggle
         * @returns {void}
         */
        handleGenerateToggle() {
            $('.wpv-generate-serials-btn').on('click', function(e) {
                e.preventDefault();
                $('#wpv-generate-serials-form').slideToggle();
                $('#wpv-add-serial-form').slideUp();
            });
        },

        /**
         * Handle cancel add serial
         * @returns {void}
         */
        handleCancelAddSerial() {
            $('.wpv-cancel-add-serial').on('click', function(e) {
                e.preventDefault();
                $('#wpv-add-serial-form').slideUp();
            });
        },

        /**
         * Handle cancel generate serials
         * @returns {void}
         */
        handleCancelGenerateSerials() {
            $('.wpv-cancel-generate-serials').on('click', function(e) {
                e.preventDefault();
                $('#wpv-generate-serials-form').slideUp();
            });
        },

        /**
         * Handle bulk actions
         * @returns {void}
         */
        handleBulkActions() {
            // Select all checkboxes
            $('#wpv-select-all').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.wpv-serial-checkbox').prop('checked', isChecked);
            });

            // Update select all checkbox based on individual checkboxes
            $('.wpv-serial-checkbox').on('change', function() {
                const totalCheckboxes = $('.wpv-serial-checkbox').length;
                const checkedCheckboxes = $('.wpv-serial-checkbox:checked').length;
                $('#wpv-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            });

            // Bulk form submission
            $('#wpv-bulk-serials-form').on('submit', function(e) {
                const bulkAction = $('#wpv-bulk-action').val();
                const checkedCount = $('.wpv-serial-checkbox:checked').length;

                if (!bulkAction) {
                    e.preventDefault();
                    alert('Please select a bulk action.');
                    return false;
                }

                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select serial numbers to delete.');
                    return false;
                }

                if (bulkAction === 'delete') {
                    const confirmMessage = checkedCount === 1
                        ? 'Are you sure you want to delete 1 serial number?'
                        : 'Are you sure you want to delete ' + checkedCount + ' serial numbers?';

                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        }
    };

    /**
     * CSV Import functionality
     */
    const WPVImport = {
        /**
         * Initialize
         * @returns {void}
         */
        init() {
            this.handleFileValidation();
        },

        /**
         * Handle CSV file validation
         * @returns {void}
         */
        handleFileValidation() {
            $('input[type="file"][accept=".csv"]').on('change', function() {
                const file = this.files[0];

                if (file) {
                    const fileName = file.name;
                    const fileExt = fileName.split('.').pop().toLowerCase();

                    if (fileExt !== 'csv') {
                        alert('Please select a valid CSV file.');
                        $(this).val('');
                        return;
                    }

                    // Check file size (max 10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    if (file.size > maxSize) {
                        alert('File size exceeds 10MB. Please choose a smaller file.');
                        $(this).val('');
                        return;
                    }
                }
            });
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        WPVAdmin.init();

        // Initialize page-specific functionality based on current page
        const currentPage = new URLSearchParams(window.location.search).get('page');

        switch (currentPage) {
            case 'wp-product-verification-settings':
                WPVSettings.init();
                break;
            case 'wp-product-verification-products':
                WPVProducts.init();
                break;
            case 'wp-product-verification-serials':
                WPVSerials.init();
                break;
            case 'wp-product-verification-import':
                WPVImport.init();
                break;
        }
    });

})(jQuery);
