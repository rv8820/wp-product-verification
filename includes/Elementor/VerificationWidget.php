<?php
/**
 * Elementor Verification Widget
 *
 * @package WPProductVerification\Elementor
 */

namespace WPProductVerification\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verification Widget for Elementor
 */
class VerificationWidget extends Widget_Base {
    /**
     * Get widget name
     *
     * @return string Widget name
     */
    public function get_name(): string {
        return 'wpv_verification_form';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     */
    public function get_title(): string {
        return __('Product Verification Form', 'wp-product-verification');
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     */
    public function get_icon(): string {
        return 'eicon-lock-user';
    }

    /**
     * Get widget categories
     *
     * @return array<int, string> Widget categories
     */
    public function get_categories(): array {
        return ['general'];
    }

    /**
     * Get widget keywords
     *
     * @return array<int, string> Widget keywords
     */
    public function get_keywords(): array {
        return ['verification', 'serial', 'product', 'validate'];
    }

    /**
     * Register widget controls
     *
     * @return void
     */
    protected function register_controls(): void {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'wp-product-verification'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'heading_text',
            [
                'label' => __('Heading Text', 'wp-product-verification'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Enter Your Serial Number Here', 'wp-product-verification'),
                'placeholder' => __('Enter heading text', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'description_text',
            [
                'label' => __('Description Text', 'wp-product-verification'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'wp-product-verification'),
                'placeholder' => __('Enter description text', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'input_placeholder',
            [
                'label' => __('Input Placeholder', 'wp-product-verification'),
                'type' => Controls_Manager::TEXT,
                'default' => 'XXXX-XXXX-XXXX-XXXX',
                'placeholder' => __('Enter placeholder', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'wp-product-verification'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Verify', 'wp-product-verification'),
                'placeholder' => __('Enter button text', 'wp-product-verification'),
            ]
        );

        $this->end_controls_section();

        // Messages Section
        $this->start_controls_section(
            'messages_section',
            [
                'label' => __('Messages', 'wp-product-verification'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'success_message',
            [
                'label' => __('Success Message', 'wp-product-verification'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Leave empty to use default from settings', 'wp-product-verification'),
                'description' => __('Message shown when verification succeeds', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'error_invalid_message',
            [
                'label' => __('Invalid Serial Message', 'wp-product-verification'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Leave empty to use default from settings', 'wp-product-verification'),
                'description' => __('Message shown when serial is not found', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'error_max_reached_message',
            [
                'label' => __('Max Reached Message', 'wp-product-verification'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Leave empty to use default from settings', 'wp-product-verification'),
                'description' => __('Message shown when verification limit reached', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'error_inactive_message',
            [
                'label' => __('Inactive Serial Message', 'wp-product-verification'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Leave empty to use default from settings', 'wp-product-verification'),
                'description' => __('Message shown when serial is deactivated', 'wp-product-verification'),
            ]
        );

        $this->end_controls_section();

        // Style Section - Heading
        $this->start_controls_section(
            'heading_style_section',
            [
                'label' => __('Heading', 'wp-product-verification'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label' => __('Color', 'wp-product-verification'),
                'type' => Controls_Manager::COLOR,
                'default' => '#5FC1E8',
                'selectors' => [
                    '{{WRAPPER}} .wpv-heading' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .wpv-heading',
            ]
        );

        $this->add_responsive_control(
            'heading_align',
            [
                'label' => __('Alignment', 'wp-product-verification'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'wp-product-verification'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'wp-product-verification'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'wp-product-verification'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .wpv-heading' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Description
        $this->start_controls_section(
            'description_style_section',
            [
                'label' => __('Description', 'wp-product-verification'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __('Color', 'wp-product-verification'),
                'type' => Controls_Manager::COLOR,
                'default' => '#7A7A7A',
                'selectors' => [
                    '{{WRAPPER}} .wpv-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .wpv-description',
            ]
        );

        $this->add_responsive_control(
            'description_align',
            [
                'label' => __('Alignment', 'wp-product-verification'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'wp-product-verification'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'wp-product-verification'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'wp-product-verification'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .wpv-description' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Input
        $this->start_controls_section(
            'input_style_section',
            [
                'label' => __('Input Field', 'wp-product-verification'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'selector' => '{{WRAPPER}} .wpv-input',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'selector' => '{{WRAPPER}} .wpv-input',
            ]
        );

        $this->add_responsive_control(
            'input_border_radius',
            [
                'label' => __('Border Radius', 'wp-product-verification'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpv-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_padding',
            [
                'label' => __('Padding', 'wp-product-verification'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpv-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Button
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Button', 'wp-product-verification'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .wpv-button',
            ]
        );

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => __('Normal', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => __('Text Color', 'wp-product-verification'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFFFFF',
                'selectors' => [
                    '{{WRAPPER}} .wpv-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label' => __('Background Color', 'wp-product-verification'),
                'type' => Controls_Manager::COLOR,
                'default' => '#5CB85C',
                'selectors' => [
                    '{{WRAPPER}} .wpv-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => __('Hover', 'wp-product-verification'),
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => __('Text Color', 'wp-product-verification'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpv-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background',
            [
                'label' => __('Background Color', 'wp-product-verification'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpv-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .wpv-button',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'wp-product-verification'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpv-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'wp-product-verification'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .wpv-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     *
     * @return void
     */
    protected function render(): void {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="wpv-verification-widget">
            <h2 class="wpv-heading"><?php echo esc_html($settings['heading_text']); ?></h2>
            <p class="wpv-description"><?php echo esc_html($settings['description_text']); ?></p>

            <div class="wpv-form-container">
                <form class="wpv-verification-form" id="wpv-verification-form"
                    data-success-message="<?php echo esc_attr($settings['success_message']); ?>"
                    data-error-invalid-message="<?php echo esc_attr($settings['error_invalid_message']); ?>"
                    data-error-max-reached-message="<?php echo esc_attr($settings['error_max_reached_message']); ?>"
                    data-error-inactive-message="<?php echo esc_attr($settings['error_inactive_message']); ?>">
                    <div class="wpv-form-row">
                        <input
                            type="text"
                            class="wpv-input"
                            id="wpv-serial-input"
                            name="serial_number"
                            placeholder="<?php echo esc_attr($settings['input_placeholder']); ?>"
                            required
                        >
                        <button type="submit" class="wpv-button">
                            <?php echo esc_html($settings['button_text']); ?>
                        </button>
                    </div>
                    <div class="wpv-message" id="wpv-message" style="display: none;"></div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     *
     * @return void
     */
    protected function content_template(): void {
        ?>
        <#
        var headingText = settings.heading_text || 'Enter Your Serial Number Here';
        var descriptionText = settings.description_text || 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        var inputPlaceholder = settings.input_placeholder || 'XXXX-XXXX-XXXX-XXXX';
        var buttonText = settings.button_text || 'Verify';
        #>
        <div class="wpv-verification-widget">
            <h2 class="wpv-heading">{{{ headingText }}}</h2>
            <p class="wpv-description">{{{ descriptionText }}}</p>

            <div class="wpv-form-container">
                <form class="wpv-verification-form">
                    <div class="wpv-form-row">
                        <input
                            type="text"
                            class="wpv-input"
                            placeholder="{{{ inputPlaceholder }}}"
                        >
                        <button type="button" class="wpv-button">
                            {{{ buttonText }}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
