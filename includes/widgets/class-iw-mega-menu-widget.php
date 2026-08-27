<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * IW Mega Menu Elementor Widget
 *
 * Provides a responsive, 3-level accessible mega menu navigation widget.
 * Fully compatible with Elementor 3.x and backward-compatible with existing layouts.
 */
class IW_Mega_Menu_Widget extends Widget_Base {

    public function get_name() {
        return 'iw-mega-menu';
    }

    public function get_title() {
        return __( 'IW Mega Menu', 'iw-mega-menu' );
    }

    public function get_icon() {
        return 'eicon-menu-bar';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_script_depends() {
        return [ 'iw-mega-menu' ];
    }

    public function get_style_depends() {
        return [ 'iw-mega-menu' ];
    }

    /**
     * Backward compatibility for older Elementor versions
     */
    protected function _register_controls() {
        $this->register_controls();
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        $this->start_controls_section(
            'section_menu',
            [ 'label' => __( 'Menu Structure', 'iw-mega-menu' ) ]
        );

        $this->add_control(
            'menu_source',
            [
                'label' => __( 'Menu Source', 'iw-mega-menu' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'saved_menu',
                'options' => [
                    'saved_menu' => __( 'Saved Menu (Builder)', 'iw-mega-menu' ),
                    'legacy_repeater' => __( 'Legacy Repeater', 'iw-mega-menu' ),
                ],
                'description' => __( 'Choose "Saved Menu" to use the new IW Mega Menu Builder.', 'iw-mega-menu' ),
            ]
        );

        $this->add_control(
            'saved_menu_id',
            [
                'label' => __( 'Select Saved Menu', 'iw-mega-menu' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_saved_menus(),
                'default' => '',
                'condition' => [
                    'menu_source' => 'saved_menu',
                ],
            ]
        );

        $this->add_control(
            'menu_items', [
                'label' => __( 'Menu Items', 'iw-mega-menu' ),
                'type' => Controls_Manager::REPEATER,
                // Widgets saved before v2.0.0 have no stored menu_source, so Elementor
                // applies the 'saved_menu' default and a plain condition would hide
                // their existing items in the editor panel. Showing the repeater
                // whenever no saved menu is selected covers every pre-2.0.0 instance.
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'menu_source',
                            'operator' => '===',
                            'value' => 'legacy_repeater',
                        ],
                        [
                            'name' => 'saved_menu_id',
                            'operator' => '==',
                            'value' => '',
                        ],
                    ],
                ],
                'fields' => [
                    [
                        'name' => 'item_label',
                        'label' => __( 'Label', 'iw-mega-menu' ),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                    ],
                    [
                        'name' => 'item_description',
                        'label' => __( 'Description', 'iw-mega-menu' ),
                        'type' => Controls_Manager::TEXTAREA,
                        'default' => '',
                        'description' => __( 'Optional short description to display beneath the menu item label.', 'iw-mega-menu' ),
                        'condition' => [ 'item_type' => 'second' ],
                    ],
                    [
                        'name' => 'item_link',
                        'label' => __( 'Link', 'iw-mega-menu' ),
                        'type' => Controls_Manager::URL,
                        'default' => [ 'url' => '' ],
                    ],
                    [
                        'name' => 'item_icon',
                        'label' => __( 'Icon', 'iw-mega-menu' ),
                        'type' => Controls_Manager::ICONS,
                    ],
                    [
                        'name' => 'item_icon_class',
                        'label' => __( 'Icon Color', 'iw-mega-menu' ),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                            'icon-acc-purple' => __( 'Purple', 'iw-mega-menu' ),
                            'icon-acc-purple-dark' => __( 'Purple (Dark)', 'iw-mega-menu' ),
                            'icon-acc-teal' => __( 'Teal', 'iw-mega-menu' ),
                            'icon-acc-blue-dark' => __( 'Blue (Dark)', 'iw-mega-menu' ),
                            'icon-acc-blue-light' => __( 'Blue (Light)', 'iw-mega-menu' ),
                            'icon-acc-lime' => __( 'Lime', 'iw-mega-menu' ),
                            'icon-acc-yellow' => __( 'Yellow', 'iw-mega-menu' ),
                            'icon-acc-orange' => __( 'Orange', 'iw-mega-menu' ),
                            'icon-acc-red' => __( 'Red', 'iw-mega-menu' ),
                            'icon-acc-black' => __( 'Black', 'iw-mega-menu' ),
                            'icon-acc-grey' => __( 'Grey', 'iw-mega-menu' ),
                        ],
                        'default' => 'icon-acc-purple',
                    ],
                    [
                        'name' => 'item_badge',
                        'label' => __( 'Badge', 'iw-mega-menu' ),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'description' => __( 'Optional badge text to display next to the menu item.', 'iw-mega-menu' ),
                    ],
                    [
                        'name' => 'item_type',
                        'label' => __( 'Type', 'iw-mega-menu' ),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                            'top' => __( 'Top-Level', 'iw-mega-menu' ),
                            'second' => __( 'Second-Level', 'iw-mega-menu' ),
                            'third' => __( 'Third-Level', 'iw-mega-menu' ),
                        ],
                        'default' => 'top',
                    ],
                    [
                        'name' => 'parent_id',
                        'label' => __( 'Parent Item', 'iw-mega-menu' ),
                        'type' => Controls_Manager::NUMBER,
                        'default' => 0,
                        'description' => __( 'Set the parent item index (0 for top-level, or the index of the parent item for sub-items).', 'iw-mega-menu' ),
                    ],
                    [
                        'name' => 'sidebar_template_id',
                        'label' => __( 'Sidebar Template (Top-Level Only)', 'iw-mega-menu' ),
                        'type' => Controls_Manager::SELECT,
                        'options' => $this->get_elementor_templates(),
                        'default' => '',
                        'condition' => [ 'item_type' => 'top' ],
                        'description' => __( 'Select a saved Elementor template to display in the sidebar for this menu card.', 'iw-mega-menu' ),
                    ],
                    [
                        'name' => 'is_accordion',
                        'label' => __( 'Accordion (Second-Level Only)', 'iw-mega-menu' ),
                        'type' => Controls_Manager::SWITCHER,
                        'default' => '',
                        'condition' => [ 'item_type' => 'second' ],
                    ],
                ],
                'title_field' => '{{ item_label }}',
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_sidebar',
            [ 'label' => __( 'Sidebar', 'iw-mega-menu' ) ]
        );
        $this->add_control(
            'sidebar_template_id', [
                'label' => __( 'Sidebar Template', 'iw-mega-menu' ),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_elementor_templates(),
                'default' => '',
                'description' => __( 'Select a saved Elementor template to display in the sidebar.', 'iw-mega-menu' ),
            ]
        );
        $this->end_controls_section();

        // Top-Level Style Controls
        $this->start_controls_section(
            'section_style_top',
            [ 'label' => __( 'Top-Level Style', 'iw-mega-menu' ), 'tab' => Controls_Manager::TAB_STYLE ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'top_typography',
                'selector' => '{{WRAPPER}} .iw-mega-menu__top-link',
            ]
        );
        $this->add_control(
            'top_color', [
                'label' => __( 'Text Color', 'iw-mega-menu' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__top-link' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'top_bg', [
                'label' => __( 'Background', 'iw-mega-menu' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__top-link' => 'background: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'top_border',
                'selector' => '{{WRAPPER}} .iw-mega-menu__top-link',
            ]
        );
        $this->add_responsive_control(
            'top_padding', [
                'label' => __( 'Padding', 'iw-mega-menu' ),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__top-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'top_gap', [
                'label' => __( 'Gap Between Items', 'iw-mega-menu' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', '%', 'vw', 'vh' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 60 ],
                    'em' => [ 'min' => 0, 'max' => 5 ],
                    'rem' => [ 'min' => 0, 'max' => 5 ],
                    '%' => [ 'min' => 0, 'max' => 100 ],
                    'vw' => [ 'min' => 0, 'max' => 10 ],
                    'vh' => [ 'min' => 0, 'max' => 10 ],
                ],
                'default' => [ 'size' => 1, 'unit' => 'rem' ],
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__top-item:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_top_states');
        $this->start_controls_tab('tab_top_normal', [ 'label' => __('Normal', 'iw-mega-menu') ]);
        $this->end_controls_tab();

        $this->start_controls_tab('tab_top_hover', [ 'label' => __('Hover', 'iw-mega-menu') ]);
        $this->add_control('top_color_hover', [
            'label' => __('Text Color', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__top-link:hover, {{WRAPPER}} .iw-mega-menu__top-link:focus' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('top_bg_hover', [
            'label' => __('Background', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__top-link:hover, {{WRAPPER}} .iw-mega-menu__top-link:focus' => 'background: {{VALUE}};',
            ],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab('tab_top_active', [ 'label' => __('Active', 'iw-mega-menu') ]);
        $this->add_control('top_color_active', [
            'label' => __('Text Color', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__top-link[aria-expanded="true"]' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('top_bg_active', [
            'label' => __('Background', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__top-link[aria-expanded="true"]' => 'background: {{VALUE}};',
            ],
        ]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        // Second-Level Style Controls
        $this->start_controls_section(
            'section_style_second',
            [ 'label' => __( 'Second-Level Style', 'iw-mega-menu' ), 'tab' => Controls_Manager::TAB_STYLE ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'second_typography',
                'selector' => '{{WRAPPER}} .iw-mega-menu__section-label',
            ]
        );
        $this->add_control(
            'second_color', [
                'label' => __( 'Text Color', 'iw-mega-menu' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__section-label' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'second_bg', [
                'label' => __( 'Background', 'iw-mega-menu' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__section-label' => 'background: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'second_border',
                'selector' => '{{WRAPPER}} .iw-mega-menu__section-label',
            ]
        );
        $this->add_responsive_control(
            'second_padding', [
                'label' => __( 'Padding', 'iw-mega-menu' ),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__section-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'second_gap', [
                'label' => __( 'Gap Between Items', 'iw-mega-menu' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__section:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_second_states');
        $this->start_controls_tab('tab_second_normal', [ 'label' => __('Normal', 'iw-mega-menu') ]);
        $this->end_controls_tab();

        $this->start_controls_tab('tab_second_hover', [ 'label' => __('Hover', 'iw-mega-menu') ]);
        $this->add_control('second_color_hover', [
            'label' => __('Text Color', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__section-label:hover, {{WRAPPER}} .iw-mega-menu__section-label:focus' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('second_bg_hover', [
            'label' => __('Background', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__section-label:hover, {{WRAPPER}} .iw-mega-menu__section-label:focus' => 'background: {{VALUE}};',
            ],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab('tab_second_active', [ 'label' => __('Active', 'iw-mega-menu') ]);
        $this->add_control('second_color_active', [
            'label' => __('Text Color', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__section-label[aria-expanded="true"]' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('second_bg_active', [
            'label' => __('Background', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__section-label[aria-expanded="true"]' => 'background: {{VALUE}};',
            ],
        ]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        // Third-Level Style Controls
        $this->start_controls_section(
            'section_style_third',
            [ 'label' => __( 'Third-Level Style', 'iw-mega-menu' ), 'tab' => Controls_Manager::TAB_STYLE ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'third_typography',
                'selector' => '{{WRAPPER}} .iw-mega-menu__third-link',
            ]
        );
        $this->add_control(
            'third_color', [
                'label' => __( 'Text Color', 'iw-mega-menu' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__third-link' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'third_bg', [
                'label' => __( 'Background', 'iw-mega-menu' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__third-link' => 'background: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'third_border',
                'selector' => '{{WRAPPER}} .iw-mega-menu__third-link',
            ]
        );
        $this->add_responsive_control(
            'third_padding', [
                'label' => __( 'Padding', 'iw-mega-menu' ),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__third-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'third_gap', [
                'label' => __( 'Gap Between Items', 'iw-mega-menu' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__third-link:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_third_states');
        $this->start_controls_tab('tab_third_normal', [ 'label' => __('Normal', 'iw-mega-menu') ]);
        $this->end_controls_tab();

        $this->start_controls_tab('tab_third_hover', [ 'label' => __('Hover', 'iw-mega-menu') ]);
        $this->add_control('third_color_hover', [
            'label' => __('Text Color', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__third-link:hover, {{WRAPPER}} .iw-mega-menu__third-link:focus' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('third_bg_hover', [
            'label' => __('Background', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__third-link:hover, {{WRAPPER}} .iw-mega-menu__third-link:focus' => 'background: {{VALUE}};',
            ],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab('tab_third_active', [ 'label' => __('Active', 'iw-mega-menu') ]);
        $this->add_control('third_color_active', [
            'label' => __('Text Color', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__third-link[aria-current="page"]' => 'color: {{VALUE}};',
            ],
        ]);
        $this->add_control('third_bg_active', [
            'label' => __('Background', 'iw-mega-menu'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .iw-mega-menu__third-link[aria-current="page"]' => 'background: {{VALUE}};',
            ],
        ]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        // Icon Style Controls
        $this->start_controls_section(
            'section_style_icon',
            [ 'label' => __( 'Icon Style', 'iw-mega-menu' ), 'tab' => Controls_Manager::TAB_STYLE ]
        );
        $this->add_control(
            'icon_color',
            [
                'label' => __( 'Icon Color', 'iw-mega-menu' ),
                'type' => Controls_Manager::COLOR,
                'default' => '#5a3fa0',
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .iw-mega-menu__icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .iw-mega-menu__icon i' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'icon_size',
            [
                'label' => __( 'Icon Size', 'iw-mega-menu' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px' => [ 'min' => 6, 'max' => 300 ],
                    'em' => [ 'min' => 0.5, 'max' => 20 ],
                    'rem' => [ 'min' => 0.5, 'max' => 20 ],
                ],
                'default' => [ 'size' => 1.2, 'unit' => 'rem' ],
                'selectors' => [
                    '{{WRAPPER}} .iw-mega-menu__icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .iw-mega-menu__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .iw-mega-menu__icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Render widget on frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $global_sidebar_template_id = ! empty( $settings['sidebar_template_id'] ) ? $settings['sidebar_template_id'] : false;
        $menu_id = 'iw-mega-menu-' . esc_attr( $this->get_id() );
        $mobile_panel_id = 'iw-mobile-panel-' . esc_attr( $this->get_id() );
        
        $tree = [];
        $menu_source = array_key_exists( 'menu_source', $settings ) && ! empty( $settings['menu_source'] )
            ? $settings['menu_source']
            : 'legacy_repeater';

        if ( $menu_source === 'saved_menu' && ! empty( $settings['saved_menu_id'] ) ) {
            $saved_menu = get_post( absint( $settings['saved_menu_id'] ) );
            $saved_data = $saved_menu && 'iw_mega_menu' === $saved_menu->post_type
                ? get_post_meta( $saved_menu->ID, '_iw_mega_menu_data', true )
                : '';
            if ( is_string( $saved_data ) && $saved_data ) {
                $decoded = json_decode( $saved_data, true );
                if ( is_array( $decoded ) && isset( $decoded['items'] ) && is_array( $decoded['items'] ) ) {
                    $tree = $decoded['items'];
                }
                if ( is_array( $decoded ) && ! empty( $decoded['settings']['global_sidebar_template_id'] ) ) {
                    $global_sidebar_template_id = $decoded['settings']['global_sidebar_template_id'];
                }
            }
        }

        if ( empty( $tree ) ) {
            $items = isset( $settings['menu_items'] ) && is_array( $settings['menu_items'] ) ? $settings['menu_items'] : [];

            // Build menu tree from flat array (3 levels)
            $lookup = [];
            foreach ( $items as $idx => $item ) {
                $item['children'] = [];
                $lookup[ $idx ] = $item;
            }

            // Attach third-level to second-level, and second-level to top-level
            foreach ( $lookup as $idx => &$item ) {
                if ( $item['item_type'] === 'third' && isset( $lookup[ $item['parent_id'] ] ) && $lookup[ $item['parent_id'] ]['item_type'] === 'second' ) {
                    $lookup[ $item['parent_id'] ]['children'][ $idx ] = &$item;
                }
            }
            foreach ( $lookup as $idx => &$item ) {
                if ( $item['item_type'] === 'second' && isset( $lookup[ $item['parent_id'] ] ) && $lookup[ $item['parent_id'] ]['item_type'] === 'top' ) {
                    $lookup[ $item['parent_id'] ]['children'][ $idx ] = &$item;
                }
            }
            foreach ( $lookup as $idx => &$item ) {
                if ( $item['item_type'] === 'top' || (int) $item['parent_id'] === 0 ) {
                    $tree[ $idx ] = &$item;
                }
            }
            unset( $item );
        }

        ?>
        <div class="iw-mega-menu-wrapper">
            <nav class="iw-mega-menu__nav" id="<?php echo esc_attr( $menu_id ); ?>" aria-label="<?php echo esc_attr__( 'Main Navigation', 'iw-mega-menu' ); ?>">
                <ul class="iw-mega-menu__top-level">
                    <?php foreach ( $tree as $i => $top ) : 
                        $has_dropdown = ! empty( $top['children'] );
                        $url = ! empty( $top['item_link']['url'] ) ? $top['item_link']['url'] : '';
                        $dropdown_id = $menu_id . '-dropdown-' . esc_attr( $i );
                        $sidebar_template_id = ! empty( $top['sidebar_template_id'] ) ? $top['sidebar_template_id'] : $global_sidebar_template_id;
                        $has_sidebar = ! empty( $sidebar_template_id );
                        $dropdown_class = 'iw-mega-menu__dropdown ' . ( $has_sidebar ? 'iw-mega-menu__dropdown--with-sidebar' : 'iw-mega-menu__dropdown--no-sidebar' );
                    ?>
                        <li class="iw-mega-menu__top-item">
                            <?php if ( $has_dropdown ) : ?>
                                <button class="iw-mega-menu__top-link" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="<?php echo esc_attr( $dropdown_id ); ?>" data-dropdown-index="<?php echo esc_attr( $i ); ?>">
                                    <span class="iw-mega-menu__link-content">
                                        <?php if ( ! empty( $top['item_icon']['value'] ) ) :
                                            $top_icon_class = ! empty( $top['item_icon_class'] ) ? esc_attr( $top['item_icon_class'] ) : 'icon-acc-purple';
                                        ?>
                                            <span class="iw-mega-menu__icon <?php echo $top_icon_class; ?>" aria-hidden="true">
                                                <?php \Elementor\Icons_Manager::render_icon( $top['item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php echo esc_html( $top['item_label'] ); ?>
                                        <?php if ( ! empty( $top['item_badge'] ) ) : ?>
                                            <span class="iw-mega-menu__badge"><?php echo esc_html( $top['item_badge'] ); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if ( ! empty( $top['item_description'] ) ) : ?>
                                        <span class="iw-mega-menu__description"><?php echo esc_html( $top['item_description'] ); ?></span>
                                    <?php endif; ?>
                                </button>

                                <div class="<?php echo esc_attr( $dropdown_class ); ?>" id="<?php echo esc_attr( $dropdown_id ); ?>" data-dropdown-index="<?php echo esc_attr( $i ); ?>" tabindex="-1" hidden>
                                    <div class="iw-mega-menu__dropdown-main">
                                        <?php
                                        $non_accordion = [];
                                        $accordion = [];
                                        foreach ( $top['children'] as $j => $second ) {
                                            if ( isset( $second['is_accordion'] ) && $second['is_accordion'] === 'yes' ) {
                                                $accordion[ $j ] = $second;
                                            } else {
                                                $non_accordion[ $j ] = $second;
                                            }
                                        }

                                        // Two-column layout for non-accordion links (max 4 per column)
                                        $non_accordion_links = array_values( $non_accordion );
                                        $columns = [ [], [] ];
                                        foreach ( $non_accordion_links as $idx => $link ) {
                                            $col = (int) floor( $idx / 4 );
                                            if ( $col < 2 ) {
                                                $columns[ $col ][] = $link;
                                            }
                                        }

                                        if ( count( $non_accordion_links ) > 0 ) : ?>
                                            <div class="iw-mega-menu__second-list">
                                                <?php for ( $c = 0; $c < 2; $c++ ) : ?>
                                                    <div class="iw-mega-menu__second-col">
                                                        <?php foreach ( $columns[ $c ] as $k => $second ) : 
                                                            $second_url = ! empty( $second['item_link']['url'] ) ? $second['item_link']['url'] : '';
                                                        ?>
                                                            <?php if ( $second_url ) : ?>
                                                                <a class="iw-mega-menu__section-label" href="<?php echo esc_url( $second_url ); ?>">
                                                            <?php else : ?>
                                                                <div class="iw-mega-menu__section-label" role="heading" aria-level="3">
                                                            <?php endif; ?>

                                                                <span class="iw-mega-menu__link-content">
                                                                    <?php if ( ! empty( $second['item_icon']['value'] ) ) : 
                                                                        $icon_class = ! empty( $second['item_icon_class'] ) ? esc_attr( $second['item_icon_class'] ) : 'icon-acc-purple';
                                                                    ?>
                                                                        <span class="iw-mega-menu__icon <?php echo $icon_class; ?>" aria-hidden="true">
                                                                            <?php \Elementor\Icons_Manager::render_icon( $second['item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                    <?php echo esc_html( $second['item_label'] ); ?>
                                                                    <?php if ( ! empty( $second['item_badge'] ) ) : ?>
                                                                        <span class="iw-mega-menu__badge"><?php echo esc_html( $second['item_badge'] ); ?></span>
                                                                    <?php endif; ?>
                                                                </span>
                                                                <?php if ( ! empty( $second['item_description'] ) ) : ?>
                                                                    <span class="iw-mega-menu__description"><?php echo esc_html( $second['item_description'] ); ?></span>
                                                                <?php endif; ?>

                                                            <?php if ( $second_url ) : ?>
                                                                </a>
                                                            <?php else : ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php // Accordion sections below
                                        foreach ( $accordion as $j => $second ) : 
                                            $section_id = $dropdown_id . '-section-' . esc_attr( $j );
                                        ?>
                                            <div class="iw-mega-menu__section iw-mega-menu__accordion">
                                                <button class="iw-mega-menu__section-label iw-mega-menu__accordion-toggle" type="button" aria-controls="<?php echo esc_attr( $section_id ); ?>" aria-expanded="false">
                                                    <span class="iw-mega-menu__link-content">
                                                        <?php if ( ! empty( $second['item_icon']['value'] ) ) : 
                                                            $icon_class = ! empty( $second['item_icon_class'] ) ? esc_attr( $second['item_icon_class'] ) : 'icon-acc-purple';
                                                        ?>
                                                            <span class="iw-mega-menu__icon <?php echo $icon_class; ?>" aria-hidden="true">
                                                                <?php \Elementor\Icons_Manager::render_icon( $second['item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php echo esc_html( $second['item_label'] ); ?>
                                                        <?php if ( ! empty( $second['item_badge'] ) ) : ?>
                                                            <span class="iw-mega-menu__badge"><?php echo esc_html( $second['item_badge'] ); ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <?php if ( ! empty( $second['item_description'] ) ) : ?>
                                                        <span class="iw-mega-menu__description"><?php echo esc_html( $second['item_description'] ); ?></span>
                                                    <?php endif; ?>
                                                    <span class="iw-mega-menu__caret" aria-hidden="true"><i class="fa-regular fa-square-caret-down"></i></span>
                                                </button>

                                                <div class="iw-mega-menu__section-content" id="<?php echo esc_attr( $section_id ); ?>" hidden>
                                                    <?php
                                                    $third_links = isset( $second['children'] ) && is_array( $second['children'] ) ? array_values( $second['children'] ) : [];
                                                    $columns = [ [], [], [] ];
                                                    foreach ( $third_links as $idx => $link ) {
                                                        $col = (int) floor( $idx / 2 );
                                                        if ( $col < 3 ) {
                                                            $columns[ $col ][] = $link;
                                                        }
                                                    }
                                                    ?>
                                                    <div class="iw-mega-menu__third-list">
                                                        <?php for ( $c = 0; $c < 3; $c++ ) : ?>
                                                            <div class="iw-mega-menu__third-col">
                                                                <?php foreach ( $columns[ $c ] as $third ) : 
                                                                    $third_url = ! empty( $third['item_link']['url'] ) ? $third['item_link']['url'] : '#';
                                                                ?>
                                                                    <a class="iw-mega-menu__third-link" href="<?php echo esc_url( $third_url ); ?>">
                                                                        <span class="iw-mega-menu__link-content">
                                                                            <?php echo esc_html( $third['item_label'] ); ?>
                                                                            <?php if ( ! empty( $third['item_badge'] ) ) : ?>
                                                                                <span class="iw-mega-menu__badge"><?php echo esc_html( $third['item_badge'] ); ?></span>
                                                                            <?php endif; ?>
                                                                        </span>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if ( $has_sidebar ) : ?>
                                        <aside class="iw-mega-menu-sidebar">
                                            <?php echo $this->get_sidebar_template_content( $sidebar_template_id ); ?>
                                        </aside>
                                    <?php endif; ?>
                                </div>

                                <div class="iw-mega-menu__dropdown-buffer" data-dropdown-index="<?php echo esc_attr( $i ); ?>" aria-hidden="true" hidden></div>
                            <?php else : ?>
                                <?php
                                $top_icon_html = '';
                                if ( ! empty( $top['item_icon']['value'] ) ) {
                                    $top_icon_class = ! empty( $top['item_icon_class'] ) ? esc_attr( $top['item_icon_class'] ) : 'icon-acc-purple';
                                    ob_start();
                                    ?>
                                    <span class="iw-mega-menu__icon <?php echo $top_icon_class; ?>" aria-hidden="true">
                                        <?php \Elementor\Icons_Manager::render_icon( $top['item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                    </span>
                                    <?php
                                    $top_icon_html = ob_get_clean();
                                }
                                ?>
                                <?php if ( $url ) : ?>
                                    <a class="iw-mega-menu__top-link" href="<?php echo esc_url( $url ); ?>">
                                        <span class="iw-mega-menu__link-content">
                                            <?php echo $top_icon_html; ?>
                                            <?php echo esc_html( $top['item_label'] ); ?>
                                            <?php if ( ! empty( $top['item_badge'] ) ) : ?>
                                                <span class="iw-mega-menu__badge"><?php echo esc_html( $top['item_badge'] ); ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <?php if ( ! empty( $top['item_description'] ) ) : ?>
                                            <span class="iw-mega-menu__description"><?php echo esc_html( $top['item_description'] ); ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php else : ?>
                                    <button class="iw-mega-menu__top-link" type="button">
                                        <span class="iw-mega-menu__link-content">
                                            <?php echo $top_icon_html; ?>
                                            <?php echo esc_html( $top['item_label'] ); ?>
                                            <?php if ( ! empty( $top['item_badge'] ) ) : ?>
                                                <span class="iw-mega-menu__badge"><?php echo esc_html( $top['item_badge'] ); ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <?php if ( ! empty( $top['item_description'] ) ) : ?>
                                            <span class="iw-mega-menu__description"><?php echo esc_html( $top['item_description'] ); ?></span>
                                        <?php endif; ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Accessible Mobile Toggle Button -->
            <button class="iw-mega-menu__mobile-toggle" type="button" aria-label="<?php echo esc_attr__( 'Open menu', 'iw-mega-menu' ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $mobile_panel_id ); ?>">&#9776;</button>

            <!-- Accessible Mobile Modal Panel Drawer -->
            <nav class="iw-mega-menu__mobile-panel" id="<?php echo esc_attr( $mobile_panel_id ); ?>" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Mobile Navigation', 'iw-mega-menu' ); ?>" hidden tabindex="-1">
                <div class="iw-mega-menu__mobile-topbar">
                    <button class="iw-mega-menu__mobile-close" type="button" aria-label="<?php echo esc_attr__( 'Close menu', 'iw-mega-menu' ); ?>">&times;</button>
                </div>
                <ul class="iw-mega-menu__mobile-list">
                    <?php foreach ( $tree as $i => $top ) : 
                        $has_children = ! empty( $top['children'] );
                        $top_sublist_id = 'mobile-sublist-' . esc_attr( $this->get_id() ) . '-' . esc_attr( $i );
                        $url = ! empty( $top['item_link']['url'] ) ? $top['item_link']['url'] : '';
                    ?>
                        <li class="iw-mega-menu__mobile-item">
                            <?php if ( $has_children ) : ?>
                                <button class="iw-mega-menu__mobile-link" type="button" data-mobile-index="<?php echo esc_attr( $i ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $top_sublist_id ); ?>">
                                    <span>
                                        <?php echo esc_html( $top['item_label'] ); ?>
                                        <?php if ( ! empty( $top['item_badge'] ) ) : ?>
                                            <span class="iw-mega-menu__badge"><?php echo esc_html( $top['item_badge'] ); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="iw-mega-menu__mobile-caret" aria-hidden="true"><i class="fa-regular fa-square-caret-down"></i></span>
                                </button>

                                <ul class="iw-mega-menu__mobile-sublist" id="<?php echo esc_attr( $top_sublist_id ); ?>" hidden>
                                    <?php foreach ( $top['children'] as $j => $second ) : 
                                        $has_third = ! empty( $second['children'] );
                                        $second_sublist_id = $top_sublist_id . '-' . esc_attr( $j );
                                        $second_url = ! empty( $second['item_link']['url'] ) ? $second['item_link']['url'] : '';
                                    ?>
                                        <li class="iw-mega-menu__mobile-item">
                                            <?php if ( $has_third ) : ?>
                                                <button class="iw-mega-menu__mobile-link" type="button" data-mobile-index="<?php echo esc_attr( $i . '-' . $j ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $second_sublist_id ); ?>">
                                                    <span>
                                                        <?php echo esc_html( $second['item_label'] ); ?>
                                                        <?php if ( ! empty( $second['item_badge'] ) ) : ?>
                                                            <span class="iw-mega-menu__badge"><?php echo esc_html( $second['item_badge'] ); ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="iw-mega-menu__mobile-caret" aria-hidden="true"><i class="fa-regular fa-square-caret-down"></i></span>
                                                </button>

                                                <ul class="iw-mega-menu__mobile-sublist" id="<?php echo esc_attr( $second_sublist_id ); ?>" hidden>
                                                    <?php foreach ( $second['children'] as $third ) : 
                                                        $third_url = ! empty( $third['item_link']['url'] ) ? $third['item_link']['url'] : '#';
                                                    ?>
                                                        <li class="iw-mega-menu__mobile-item">
                                                            <a class="iw-mega-menu__mobile-link" href="<?php echo esc_url( $third_url ); ?>">
                                                                <span>
                                                                    <?php echo esc_html( $third['item_label'] ); ?>
                                                                    <?php if ( ! empty( $third['item_badge'] ) ) : ?>
                                                                        <span class="iw-mega-menu__badge"><?php echo esc_html( $third['item_badge'] ); ?></span>
                                                                    <?php endif; ?>
                                                                </span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else : ?>
                                                <a class="iw-mega-menu__mobile-link" href="<?php echo esc_url( $second_url ? $second_url : '#' ); ?>">
                                                    <span>
                                                        <?php echo esc_html( $second['item_label'] ); ?>
                                                        <?php if ( ! empty( $second['item_badge'] ) ) : ?>
                                                            <span class="iw-mega-menu__badge"><?php echo esc_html( $second['item_badge'] ); ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                </a>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <a class="iw-mega-menu__mobile-link" href="<?php echo esc_url( $url ? $url : '#' ); ?>">
                                    <span>
                                        <?php echo esc_html( $top['item_label'] ); ?>
                                        <?php if ( ! empty( $top['item_badge'] ) ) : ?>
                                            <span class="iw-mega-menu__badge"><?php echo esc_html( $top['item_badge'] ); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ( ! empty( $global_sidebar_template_id ) ) : ?>
                    <div class="iw-mega-menu__mobile-sidebar">
                        <?php echo $this->get_sidebar_template_content( $global_sidebar_template_id ); ?>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
        <?php
    }

    /**
     * Marionette / Underscore JS live preview template for Elementor editor
     */
    protected function content_template() {
        ?>
        <#
        var menu_id = 'iw-mega-menu-' + view.getID();
        var mobile_panel_id = 'iw-mobile-panel-' + view.getID();
        var items = settings.menu_items || [];
        var global_sidebar_id = settings.sidebar_template_id || '';

        // Build lookup and tree
        var lookup = {}, tree = {};
        _.each(items, function(item, idx) {
            item.children = [];
            lookup[idx] = item;
        });

        // Attach third-level to second-level, and second-level to top-level
        _.each(lookup, function(item, idx) {
            if (item.item_type === 'third' && lookup[item.parent_id] && lookup[item.parent_id].item_type === 'second') {
                lookup[item.parent_id].children.push(item);
            }
        });
        _.each(lookup, function(item, idx) {
            if (item.item_type === 'second' && lookup[item.parent_id] && lookup[item.parent_id].item_type === 'top') {
                lookup[item.parent_id].children.push(item);
            }
        });
        _.each(lookup, function(item, idx) {
            if (item.item_type === 'top' || item.parent_id == 0) {
                tree[idx] = item;
            }
        });

        function renderIcon(icon, icon_class) {
            if (!icon || !icon.value) return '';
            var iconHTML = elementor.helpers.renderIcon(view, icon, { 'aria-hidden': true }, 'i', 'object');
            var iconClass = icon_class ? ' ' + icon_class : ' icon-acc-purple';
            if (iconHTML && iconHTML.value) {
                return '<span class="iw-mega-menu__icon' + iconClass + '" aria-hidden="true">' + iconHTML.value + '</span>';
            }
            return '';
        }
        #>
        <div class="iw-mega-menu-wrapper">
            <nav class="iw-mega-menu__nav" id="{{ menu_id }}" aria-label="<?php esc_attr_e( 'Main Navigation', 'iw-mega-menu' ); ?>">
                <ul class="iw-mega-menu__top-level">
                    <# _.each(tree, function(top, i) { 
                        var has_dropdown = top.children && top.children.length;
                        var dropdown_id = menu_id + '-dropdown-' + i;
                        var sidebar_id = top.sidebar_template_id || global_sidebar_id;
                        var has_sidebar = sidebar_id && sidebar_id !== '';
                    #>
                        <li class="iw-mega-menu__top-item">
                            <# if (has_dropdown) { #>
                                <button class="iw-mega-menu__top-link" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="{{ dropdown_id }}" data-dropdown-index="{{ i }}">
                                    <span class="iw-mega-menu__link-content">
                                        {{{ renderIcon(top.item_icon, top.item_icon_class) }}}
                                        {{{ top.item_label }}}
                                        <# if (top.item_badge) { #>
                                            <span class="iw-mega-menu__badge">{{{ top.item_badge }}}</span>
                                        <# } #>
                                    </span>
                                    <# if (top.item_description) { #>
                                        <span class="iw-mega-menu__description">{{{ top.item_description }}}</span>
                                    <# } #>
                                </button>

                                <div class="iw-mega-menu__dropdown <# if (has_sidebar) { #>iw-mega-menu__dropdown--with-sidebar<# } else { #>iw-mega-menu__dropdown--no-sidebar<# } #>" id="{{ dropdown_id }}" data-dropdown-index="{{ i }}" tabindex="-1" hidden>
                                    <div class="iw-mega-menu__dropdown-main">
                                        <# 
                                        var non_accordion = [], accordion = []; 
                                        _.each(top.children, function(second) { 
                                            if (second.is_accordion === 'yes') { 
                                                accordion.push(second); 
                                            } else { 
                                                non_accordion.push(second); 
                                            } 
                                        }); 
                                        #>
                                        <# if (non_accordion.length) { #>
                                            <div class="iw-mega-menu__second-list">
                                                <# 
                                                var columns = [[], []]; 
                                                _.each(non_accordion, function(link, idx) { 
                                                    var col = Math.floor(idx / 4); 
                                                    if (col < 2) columns[col].push(link); 
                                                }); 
                                                #>
                                                <# for (var c = 0; c < 2; c++) { #>
                                                    <div class="iw-mega-menu__second-col">
                                                        <# _.each(columns[c], function(second) { #>
                                                            <# if (second.item_link && second.item_link.url) { #>
                                                                <a class="iw-mega-menu__section-label" href="{{ second.item_link.url }}">
                                                            <# } else { #>
                                                                <div class="iw-mega-menu__section-label" role="heading" aria-level="3">
                                                            <# } #>
                                                                    <span class="iw-mega-menu__link-content">
                                                                        {{{ renderIcon(second.item_icon, second.item_icon_class) }}}
                                                                        {{{ second.item_label }}}
                                                                        <# if (second.item_badge) { #>
                                                                            <span class="iw-mega-menu__badge">{{{ second.item_badge }}}</span>
                                                                        <# } #>
                                                                    </span>
                                                                    <# if (second.item_description) { #>
                                                                        <span class="iw-mega-menu__description">{{{ second.item_description }}}</span>
                                                                    <# } #>
                                                            <# if (second.item_link && second.item_link.url) { #>
                                                                </a>
                                                            <# } else { #>
                                                                </div>
                                                            <# } #>
                                                        <# }); #>
                                                    </div>
                                                <# } #>
                                            </div>
                                        <# } #>

                                        <# _.each(accordion, function(second, j) { 
                                            var section_id = dropdown_id + '-section-' + j; 
                                        #>
                                            <div class="iw-mega-menu__section iw-mega-menu__accordion">
                                                <button class="iw-mega-menu__section-label iw-mega-menu__accordion-toggle" type="button" aria-controls="{{ section_id }}" aria-expanded="false">
                                                    <span class="iw-mega-menu__link-content">
                                                        {{{ renderIcon(second.item_icon, second.item_icon_class) }}}
                                                        {{{ second.item_label }}}
                                                        <# if (second.item_badge) { #>
                                                            <span class="iw-mega-menu__badge">{{{ second.item_badge }}}</span>
                                                        <# } #>
                                                    </span>
                                                    <# if (second.item_description) { #>
                                                        <span class="iw-mega-menu__description">{{{ second.item_description }}}</span>
                                                    <# } #>
                                                    <span class="iw-mega-menu__caret" aria-hidden="true"><i class="fa-regular fa-square-caret-down"></i></span>
                                                </button>
                                                <div class="iw-mega-menu__section-content" id="{{ section_id }}" hidden>
                                                    <# 
                                                    var third_links = second.children || []; 
                                                    var third_columns = [[], [], []]; 
                                                    _.each(third_links, function(link, idx) { 
                                                        var col = Math.floor(idx / 2); 
                                                        if (col < 3) third_columns[col].push(link); 
                                                    }); 
                                                    #>
                                                    <div class="iw-mega-menu__third-list">
                                                        <# for (var c = 0; c < 3; c++) { #>
                                                            <div class="iw-mega-menu__third-col">
                                                                <# _.each(third_columns[c], function(third) { #>
                                                                    <a class="iw-mega-menu__third-link" href="{{ third.item_link ? third.item_link.url : '#' }}">
                                                                        <span class="iw-mega-menu__link-content">
                                                                            {{{ third.item_label }}}
                                                                            <# if (third.item_badge) { #>
                                                                                <span class="iw-mega-menu__badge">{{{ third.item_badge }}}</span>
                                                                            <# } #>
                                                                        </span>
                                                                    </a>
                                                                <# }); #>
                                                            </div>
                                                        <# } #>
                                                    </div>
                                                </div>
                                            </div>
                                        <# }); #>
                                    </div>
                                    <# if (has_sidebar) { #>
                                        <aside class="iw-mega-menu-sidebar"><?php esc_html_e( 'Sidebar Preview', 'iw-mega-menu' ); ?></aside>
                                    <# } #>
                                </div>
                                <div class="iw-mega-menu__dropdown-buffer" data-dropdown-index="{{ i }}" aria-hidden="true" hidden></div>
                            <# } else { #>
                                <# if (top.item_link && top.item_link.url) { #>
                                    <a class="iw-mega-menu__top-link" href="{{ top.item_link.url }}">
                                        <span class="iw-mega-menu__link-content">
                                            {{{ renderIcon(top.item_icon, top.item_icon_class) }}}
                                            {{{ top.item_label }}}
                                            <# if (top.item_badge) { #>
                                                <span class="iw-mega-menu__badge">{{{ top.item_badge }}}</span>
                                            <# } #>
                                        </span>
                                        <# if (top.item_description) { #>
                                            <span class="iw-mega-menu__description">{{{ top.item_description }}}</span>
                                        <# } #>
                                    </a>
                                <# } else { #>
                                    <button class="iw-mega-menu__top-link" type="button">
                                        <span class="iw-mega-menu__link-content">
                                            {{{ renderIcon(top.item_icon, top.item_icon_class) }}}
                                            {{{ top.item_label }}}
                                            <# if (top.item_badge) { #>
                                                <span class="iw-mega-menu__badge">{{{ top.item_badge }}}</span>
                                            <# } #>
                                        </span>
                                        <# if (top.item_description) { #>
                                            <span class="iw-mega-menu__description">{{{ top.item_description }}}</span>
                                        <# } #>
                                    </button>
                                <# } #>
                            <# } #>
                        </li>
                    <# }); #>
                </ul>
            </nav>
        </div>
        <?php
    }

    /**
     * Get saved IW Mega Menus for dropdown
     *
     * @return array Menu ID => Title
     */
    private function get_saved_menus() {
        $menus = [ '' => __( 'Select a Menu', 'iw-mega-menu' ) ];
        $posts = get_posts([
            'post_type'      => 'iw_mega_menu',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $post ) {
                $menus[ $post->ID ] = $post->post_title;
            }
        }
        return $menus;
    }

    /**
     * Get saved Elementor templates for sidebar selection (cached)
     *
     * @return array Template ID => Title
     */
    private function get_elementor_templates() {
        static $templates = null;
        if ( null !== $templates ) {
            return $templates;
        }

        $templates = [ '' => __( 'None', 'iw-mega-menu' ) ];
        $posts = get_posts([
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ]);

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $post_id ) {
                $templates[ $post_id ] = get_the_title( $post_id );
            }
        }
        return $templates;
    }

    /**
     * Render Elementor template content
     *
     * @param int $template_id
     * @return string
     */
    private function get_sidebar_template_content( $template_id ) {
        if ( ! $template_id ) return '';
        return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
    }
}