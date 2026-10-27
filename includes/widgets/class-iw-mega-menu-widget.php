<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * IW Mega Menu Elementor Widget
 *
 * Optional Elementor adapter: loaded only when Elementor is active. Markup comes
 * from IW_Mega_Menu_Renderer, shared with the block, shortcode and template tag.
 * Backward-compatible with existing layouts, including legacy repeater menus.
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
                        // ACC brand palette. Retired teal/blue/red classes still render, mapped to brand colors in CSS.
                        'options' => [
                            'icon-acc-purple' => __( 'Purple', 'iw-mega-menu' ),
                            'icon-acc-purple-dark' => __( 'Dark Purple', 'iw-mega-menu' ),
                            'icon-acc-orange' => __( 'Orange', 'iw-mega-menu' ),
                            'icon-acc-yellow' => __( 'Yellow', 'iw-mega-menu' ),
                            'icon-acc-lime' => __( 'Lime', 'iw-mega-menu' ),
                            'icon-acc-black' => __( 'Black', 'iw-mega-menu' ),
                            'icon-acc-grey' => __( 'Dark Grey', 'iw-mega-menu' ),
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
                        'options' => $this->get_sidebar_options(),
                        'default' => '',
                        'condition' => [ 'item_type' => 'top' ],
                        'description' => __( 'Select a pattern or saved Elementor template to display in the sidebar for this menu card.', 'iw-mega-menu' ),
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
                'options' => $this->get_sidebar_options(),
                'default' => '',
                'description' => __( 'Select a pattern or saved Elementor template to display in the sidebar.', 'iw-mega-menu' ),
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
                'default' => '#4D1979',
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
        $settings   = $this->get_settings_for_display();
        $sidebar_id = ! empty( $settings['sidebar_template_id'] ) ? $settings['sidebar_template_id'] : '';

        // Widgets saved before v2.0.0 have no menu_source, so treat them as legacy repeaters.
        $menu_source = ! empty( $settings['menu_source'] ) ? $settings['menu_source'] : 'legacy_repeater';

        $tree = [];
        if ( 'saved_menu' === $menu_source && ! empty( $settings['saved_menu_id'] ) ) {
            $saved = IW_Mega_Menu_Renderer::get_menu_data( $settings['saved_menu_id'] );
            if ( $saved ) {
                $tree = $saved['items'];
                if ( ! empty( $saved['settings']['global_sidebar_template_id'] ) ) {
                    $sidebar_id = $saved['settings']['global_sidebar_template_id'];
                }
            }
        }

        if ( empty( $tree ) ) {
            $tree = IW_Mega_Menu_Renderer::build_legacy_tree( $settings['menu_items'] ?? [] );
        }

        echo IW_Mega_Menu_Renderer::render( $tree, [ // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by the renderer.
            'uid'        => $this->get_id(),
            'sidebar_id' => $sidebar_id,
        ] );
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
     * Get sidebar choices — patterns and Elementor templates (cached)
     *
     * @return array Post ID => "Type: Title"
     */
    private function get_sidebar_options() {
        static $options = null;
        if ( null !== $options ) {
            return $options;
        }

        $options = [ '' => __( 'None', 'iw-mega-menu' ) ];
        foreach ( IW_Mega_Menu_Renderer::get_sidebar_sources() as $source ) {
            $options[ $source['id'] ] = $source['type'] . ': ' . $source['title'];
        }
        return $options;
    }
}
