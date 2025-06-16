<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit;

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
    protected function _register_controls() {
        $this->start_controls_section(
            'section_menu',
            [ 'label' => __( 'Menu Structure', 'iw-mega-menu' ) ]
        );
        $this->add_control(
            'menu_items', [
                'label' => __( 'Menu Items', 'iw-mega-menu' ),
                'type' => Controls_Manager::REPEATER,
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
        // Normal state controls already above
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
    protected function render() {
        $settings = $this->get_settings_for_display();
        $global_sidebar_template_id = !empty($settings['sidebar_template_id']) ? $settings['sidebar_template_id'] : false;
        $menu_id = 'iw-mega-menu-' . $this->get_id();
        $items = isset($settings['menu_items']) ? $settings['menu_items'] : [];
        // Build menu tree from flat array (robust for 3 levels)
        $tree = [];
        $lookup = [];
        foreach ($items as $idx => $item) {
            $item['children'] = [];
            $lookup[$idx] = $item;
        }
        // Attach third-level to second-level, and second-level to top-level
        foreach ($lookup as $idx => &$item) {
            if ($item['item_type'] === 'third' && isset($lookup[$item['parent_id']]) && $lookup[$item['parent_id']]['item_type'] === 'second') {
                $lookup[$item['parent_id']]['children'][$idx] = &$item;
            }
        }
        foreach ($lookup as $idx => &$item) {
            if ($item['item_type'] === 'second' && isset($lookup[$item['parent_id']]) && $lookup[$item['parent_id']]['item_type'] === 'top') {
                $lookup[$item['parent_id']]['children'][$idx] = &$item;
            }
        }
        foreach ($lookup as $idx => &$item) {
            if ($item['item_type'] === 'top' || $item['parent_id'] == 0) {
                $tree[$idx] = &$item;
            }
        }
        unset($item);
        echo '<nav class="iw-mega-menu__nav" id="' . esc_attr($menu_id) . '">';
        echo '<ul class="iw-mega-menu__top-level">';
        foreach ($tree as $i => $top) {
            $has_dropdown = !empty($top['children']);
            $url = isset($top['item_link']['url']) ? $top['item_link']['url'] : '';
            echo '<li class="iw-mega-menu__top-item">';
            if ($url) {
                // Render as <a> if URL is set
                echo '<a class="iw-mega-menu__top-link" href="' . esc_url($url) . '"' . ($has_dropdown ? ' aria-haspopup="true" aria-expanded="false" data-dropdown-index="' . $i . '"' : '') . '>';
                echo '<span class="iw-mega-menu__link-content">';
                echo esc_html($top['item_label']);
                if (!empty($top['item_badge'])) {
                    echo '<span class="iw-mega-menu__badge">' . esc_html($top['item_badge']) . '</span>';
                }
                echo '</span>';
                echo '</a>';
            } else {
                // Render as <button> if no URL
                echo '<button class="iw-mega-menu__top-link" type="button"' . ($has_dropdown ? ' aria-haspopup="true" aria-expanded="false" data-dropdown-index="' . $i . '"' : '') . '>';
                echo '<span class="iw-mega-menu__link-content">';
                echo esc_html($top['item_label']);
                if (!empty($top['item_badge'])) {
                    echo '<span class="iw-mega-menu__badge">' . esc_html($top['item_badge']) . '</span>';
                }
                echo '</span>';
                echo '</button>';
            }
            echo '</li>';
        }
        echo '</ul>';
        // Render all dropdowns as direct children of nav
        foreach ($tree as $i => $top) {
            if (empty($top['children'])) continue;
            $dropdown_id = $menu_id . '-dropdown-' . $i;
            $sidebar_template_id = !empty($top['sidebar_template_id']) ? $top['sidebar_template_id'] : $global_sidebar_template_id;
            $has_sidebar = !empty($sidebar_template_id);
            $dropdown_class = 'iw-mega-menu__dropdown';
            if ($has_sidebar) {
                $dropdown_class .= ' iw-mega-menu__dropdown--with-sidebar';
            } else {
                $dropdown_class .= ' iw-mega-menu__dropdown--no-sidebar';
            }
            echo '<div class="' . esc_attr($dropdown_class) . '" id="' . esc_attr($dropdown_id) . '" data-dropdown-index="' . $i . '" tabindex="-1" hidden>';
            // Main content
            echo '<div class="iw-mega-menu__dropdown-main">';
            // Segment second-level links
            $non_accordion = [];
            $accordion = [];
            foreach ($top['children'] as $j => $second) {
                if ($second['is_accordion'] === 'yes') {
                    $accordion[$j] = $second;
                } else {
                    $non_accordion[$j] = $second;
                }
            }
            // Two-column layout for non-accordion links (max 4 per column)
            $non_accordion_links = array_values($non_accordion);
            $columns = [[], []];
            foreach ($non_accordion_links as $idx => $link) {
                $col = floor($idx / 4); // 0 or 1
                if ($col < 2) {
                    $columns[$col][] = $link;
                }
            }
            if (count($non_accordion_links) > 0) {
                echo '<div class="iw-mega-menu__second-list">';
                for ($c = 0; $c < 2; $c++) {
                    echo '<div class="iw-mega-menu__second-col">';
                    foreach ($columns[$c] as $k => $second) {
                        // Label with icon in-line
                        echo '<a class="iw-mega-menu__section-label" href="' . esc_url($second['item_link']['url']) . '">';
                        echo '<span class="iw-mega-menu__link-content">';
                        if (!empty($second['item_icon']['value'])) {
                            $icon_class = !empty($second['item_icon_class']) ? esc_attr($second['item_icon_class']) : 'icon-acc-purple';
                            echo '<span class="iw-mega-menu__icon ' . $icon_class . '">';
                            \Elementor\Icons_Manager::render_icon($second['item_icon'], [ 'aria-hidden' => 'true' ]);
                            echo '</span>';
                        }
                        echo esc_html($second['item_label']);
                        if (!empty($second['item_badge'])) {
                            echo '<span class="iw-mega-menu__badge">' . esc_html($second['item_badge']) . '</span>';
                        }
                        echo '</span>';
                        if (!empty($second['item_description'])) {
                            echo '<span class="iw-mega-menu__description">' . esc_html($second['item_description']) . '</span>';
                        }
                        echo '</a>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
            // Accordion links below
            foreach ($accordion as $j => $second) {
                $is_accordion = true;
                $section_id = $dropdown_id . '-section-' . $j;
                echo '<div class="iw-mega-menu__section iw-mega-menu__accordion">';
                // Icon
                if (!empty($second['item_icon']['value'])) {
                    $icon_class = !empty($second['item_icon_class']) ? esc_attr($second['item_icon_class']) : 'icon-acc-purple';
                    echo '<span class="iw-mega-menu__icon ' . $icon_class . '">';
                    \Elementor\Icons_Manager::render_icon($second['item_icon'], [ 'aria-hidden' => 'true' ]);
                    echo '</span>';
                }
                // Accordion label with icon in-line
                echo '<button class="iw-mega-menu__section-label iw-mega-menu__accordion-toggle" type="button" aria-controls="' . esc_attr($section_id) . '" aria-expanded="false">';
                echo '<span class="iw-mega-menu__link-content">';
                if (!empty($second['item_icon']['value'])) {
                    $icon_class = !empty($second['item_icon_class']) ? esc_attr($second['item_icon_class']) : 'icon-acc-purple';
                    echo '<span class="iw-mega-menu__icon ' . $icon_class . '">';
                    \Elementor\Icons_Manager::render_icon($second['item_icon'], [ 'aria-hidden' => 'true' ]);
                    echo '</span>';
                }
                echo esc_html($second['item_label']);
                if (!empty($second['item_badge'])) {
                    echo '<span class="iw-mega-menu__badge">' . esc_html($second['item_badge']) . '</span>';
                }
                if (!empty($second['item_description'])) {
                    echo '<span class="iw-mega-menu__description">' . esc_html($second['item_description']) . '</span>';
                }
                echo ' <span class="iw-mega-menu__caret" aria-hidden="true"><i class="fa-regular fa-square-caret-down"></i></span></button>';
                // Third-level links (accordion content)
                echo '<div class="iw-mega-menu__section-content" id="' . esc_attr($section_id) . '" hidden>';
                $third_links = array_values($second['children']);
                $columns = [[], [], []];
                foreach ($third_links as $idx => $link) {
                    $col = floor($idx / 2); // 0, 1, 2
                    if ($col < 3) {
                        $columns[$col][] = $link;
                    }
                }
                echo '<div class="iw-mega-menu__third-list">';
                for ($c = 0; $c < 3; $c++) {
                    echo '<div class="iw-mega-menu__third-col">';
                    foreach ($columns[$c] as $third) {
                        echo '<a class="iw-mega-menu__third-link" href="' . esc_url($third['item_link']['url']) . '">';
                        echo '<span class="iw-mega-menu__link-content">';
                        echo esc_html($third['item_label']);
                        if (!empty($third['item_badge'])) {
                            echo '<span class="iw-mega-menu__badge">' . esc_html($third['item_badge']) . '</span>';
                        }
                        echo '</span>';
                        echo '</a>';
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            // Sidebar (only if present)
            if ($has_sidebar) {
                echo '<aside class="iw-mega-menu-sidebar">';
                echo $this->get_sidebar_template_content($sidebar_template_id);
                echo '</aside>';
            }
            echo '</div>';
            // Mouse buffer for dropdown persistence
            echo '<div class="iw-mega-menu__dropdown-buffer" data-dropdown-index="' . $i . '"></div>';
        }
        echo '</nav>';
        // Hamburger toggle and mobile menu panel
        echo '<button class="iw-mega-menu__mobile-toggle" aria-label="Open menu" style="display:none;">&#9776;</button>';
        echo '<nav class="iw-mega-menu__mobile-panel" hidden tabindex="-1">';
        echo '<div class="iw-mega-menu__mobile-topbar">';
        echo '<button class="iw-mega-menu__mobile-close" aria-label="Close menu">&times;</button>';
        echo '</div>';
        echo '<ul class="iw-mega-menu__mobile-list">';
        foreach ($tree as $i => $top) {
            echo '<li class="iw-mega-menu__mobile-item">';
            $has_children = !empty($top['children']);
            $is_accordion = false;
            echo '<button class="iw-mega-menu__mobile-link" type="button" data-mobile-index="' . $i . '"';
            if ($has_children) echo ' aria-expanded="false" aria-controls="mobile-sublist-' . $i . '"';
            echo '>' . esc_html($top['item_label']);
            if (!empty($top['item_badge'])) {
                echo '<span class="iw-mega-menu__badge">' . esc_html($top['item_badge']) . '</span>';
            }
            if ($has_children) echo '<span class="iw-mega-menu__mobile-caret"><i class="fa-regular fa-square-caret-down"></i></span>';
            echo '</button>';
            if ($has_children) {
                echo '<ul class="iw-mega-menu__mobile-sublist" id="mobile-sublist-' . $i . '" hidden>';
                foreach ($top['children'] as $j => $second) {
                    $has_third = !empty($second['children']);
                    $is_accordion = $second['is_accordion'] === 'yes';
                    echo '<li class="iw-mega-menu__mobile-item">';
                    echo '<button class="iw-mega-menu__mobile-link" type="button" data-mobile-index="' . $i . '-' . $j . '"';
                    if ($has_third) echo ' aria-expanded="false" aria-controls="mobile-sublist-' . $i . '-' . $j . '"';
                    echo '>' . esc_html($second['item_label']);
                    if (!empty($second['item_badge'])) {
                        echo '<span class="iw-mega-menu__badge">' . esc_html($second['item_badge']) . '</span>';
                    }
                    if ($has_third) echo '<span class="iw-mega-menu__mobile-caret"><i class="fa-regular fa-square-caret-down"></i></span>';
                    echo '</button>';
                    if ($has_third) {
                        echo '<ul class="iw-mega-menu__mobile-sublist" id="mobile-sublist-' . $i . '-' . $j . '" hidden>';
                        foreach ($second['children'] as $third) {
                            echo '<li class="iw-mega-menu__mobile-item">';
                            echo '<a class="iw-mega-menu__mobile-link" href="' . esc_url($third['item_link']['url']) . '">' . esc_html($third['item_label']);
                            if (!empty($third['item_badge'])) {
                                echo '<span class="iw-mega-menu__badge">' . esc_html($third['item_badge']) . '</span>';
                            }
                            echo '</a>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
            }
            echo '</li>';
        }
        echo '</ul>';
        echo '</nav>';
        // JS for dropdown and accordion
        ?>
        <script>
        (function(){
            var nav = document.getElementById('<?php echo esc_js($menu_id); ?>');
            if (!nav) return;
            var topItems = nav.querySelectorAll('.iw-mega-menu__top-item');
            var dropdowns = nav.querySelectorAll('.iw-mega-menu__dropdown');
            var buffers = nav.querySelectorAll('.iw-mega-menu__dropdown-buffer');
            // Hide all buffers on page load
            buffers.forEach(function(b){ b.hidden = true; });
            function hideAllDropdowns() {
                dropdowns.forEach(function(dd){ dd.hidden = true; });
                buffers.forEach(function(b){ b.hidden = true; });
                nav.querySelectorAll('.iw-mega-menu__top-link[aria-expanded]')
                  .forEach(function(btn){ btn.setAttribute('aria-expanded', 'false'); });
            }
            
            // Add event listeners to top-level items
            topItems.forEach(function(item){
                var btn = item.querySelector('.iw-mega-menu__top-link');
                if (!btn) return;
                var idx = btn.getAttribute('data-dropdown-index');
                if (idx === null) return;
                var dropdown = nav.querySelector('.iw-mega-menu__dropdown[data-dropdown-index="' + idx + '"]');
                var buffer = nav.querySelector('.iw-mega-menu__dropdown-buffer[data-dropdown-index="' + idx + '"]');
                var open = false;
                var timeout;
                var showDropdown = function() {
                    clearTimeout(timeout);
                    hideAllDropdowns();
                    if (dropdown) {
                        dropdown.hidden = false;
                        dropdown.style.opacity = '1';
                        dropdown.style.transform = 'translateY(0)';
                        dropdown.style.pointerEvents = 'auto';
                    }
                    if (buffer) buffer.hidden = false;
                    btn.setAttribute('aria-expanded', 'true');
                    open = true;
                };
                var hideDropdown = function() {
                    timeout = setTimeout(function() {
                        if (dropdown) {
                            dropdown.hidden = true;
                            dropdown.style.opacity = '0';
                            dropdown.style.transform = 'translateY(-16px)';
                            dropdown.style.pointerEvents = 'none';
                        }
                        if (buffer) buffer.hidden = true;
                        btn.setAttribute('aria-expanded', 'false');
                        open = false;
                    }, 120);
                };
                btn.addEventListener('mouseenter', showDropdown);
                btn.addEventListener('focus', showDropdown);
                item.addEventListener('mouseleave', hideDropdown);
                btn.addEventListener('blur', function(e){
                    if (!nav.contains(e.relatedTarget)) hideDropdown();
                });
                if (dropdown) {
                    dropdown.addEventListener('mouseenter', function(){ clearTimeout(timeout); });
                    dropdown.addEventListener('mouseleave', hideDropdown);
                }
                if (buffer) {
                    buffer.addEventListener('mouseenter', function(){ clearTimeout(timeout); showDropdown(); });
                    buffer.addEventListener('mouseleave', hideDropdown);
                }
            });
            // Hide all dropdowns on nav mouseleave
            nav.addEventListener('mouseleave', hideAllDropdowns);
            // Accordion logic
            nav.querySelectorAll('.iw-mega-menu__accordion-toggle').forEach(function(toggle){
                toggle.addEventListener('click', function(){
                    var expanded = toggle.getAttribute('aria-expanded') === 'true';
                    var content = nav.querySelector('#' + toggle.getAttribute('aria-controls'));
                    if (!content) return;
                    var caret = toggle.querySelector('.iw-mega-menu__caret i');
                    if (expanded) {
                        toggle.setAttribute('aria-expanded', 'false');
                        content.hidden = true;
                        if (caret) {
                            caret.className = 'fa-regular fa-square-caret-down';
                        }
                    } else {
                        toggle.setAttribute('aria-expanded', 'true');
                        content.hidden = false;
                        if (caret) {
                            caret.className = 'fa-solid fa-square-caret-up';
                        }
                    }
                });
            });
            // Mobile menu logic
            var mobileToggle = document.querySelector('.iw-mega-menu__mobile-toggle');
            var mobilePanel = document.querySelector('.iw-mega-menu__mobile-panel');
            var mobileClose = document.querySelector('.iw-mega-menu__mobile-close');
            if (mobileToggle && mobilePanel) {
                function openMobileMenu() {
                    mobilePanel.hidden = false;
                    document.body.style.overflow = 'hidden';
                    mobilePanel.focus();
                }
                function closeMobileMenu() {
                    mobilePanel.hidden = true;
                    document.body.style.overflow = '';
                }
                mobileToggle.addEventListener('click', openMobileMenu);
                if (mobileClose) mobileClose.addEventListener('click', closeMobileMenu);
                // Close on Escape
                mobilePanel.addEventListener('keydown', function(e){
                    if (e.key === 'Escape') closeMobileMenu();
                });
                // Collapsible logic
                mobilePanel.querySelectorAll('.iw-mega-menu__mobile-link[aria-controls]').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        var expanded = btn.getAttribute('aria-expanded') === 'true';
                        var sublist = mobilePanel.querySelector('#' + btn.getAttribute('aria-controls'));
                        if (!sublist) return;
                        if (expanded) {
                            btn.setAttribute('aria-expanded', 'false');
                            sublist.hidden = true;
                            btn.querySelector('.iw-mega-menu__mobile-caret i').className = 'fa-regular fa-square-caret-down';
                        } else {
                            btn.setAttribute('aria-expanded', 'true');
                            sublist.hidden = false;
                            btn.querySelector('.iw-mega-menu__mobile-caret i').className = 'fa-solid fa-square-caret-up';
                        }
                    });
                });
            }
            // Show/hide hamburger based on screen size
            function updateMobileToggle() {
                if (window.innerWidth <= 767) {
                    if (mobileToggle) mobileToggle.style.display = 'flex';
                } else {
                    if (mobileToggle) mobileToggle.style.display = 'none';
                    if (mobilePanel) mobilePanel.hidden = true;
                }
            }
            window.addEventListener('resize', updateMobileToggle);
            updateMobileToggle();
        })();
        </script>
        <?php
    }
    protected function content_template() {
        ?>
        <#
        var menu_id = 'iw-mega-menu-' + view.getID();
        var items = settings.menu_items || [];
        var defaultIconColor = '#5a3fa0';
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
            // Always apply a default color class if none is specified
            var iconClass = icon_class ? ' ' + icon_class : ' icon-acc-purple';
            if (iconHTML && iconHTML.value) {
                return '<span class="iw-mega-menu__icon' + iconClass + '">' + iconHTML.value + '</span>';
            }
            return '';
        }
        #>
        <div class="iw-mega-menu-wrapper">
            <nav class="iw-mega-menu__nav" id="{{ menu_id }}">
                <ul class="iw-mega-menu__top-level">
                    <# _.each(tree, function(top, i) { #>
                        <li class="iw-mega-menu__top-item">
                            <# var has_dropdown = top.children && top.children.length; #>
                            <# if (top.item_link && top.item_link.url) { #>
                                <a class="iw-mega-menu__top-link" href="{{ top.item_link.url }}" <# if (has_dropdown) { #> aria-haspopup="true" aria-expanded="false" data-dropdown-index="{{ i }}"<# } #>>
                                    <span class="iw-mega-menu__link-content">
                                        {{{ top.item_label }}}
                                        <# if (top.item_badge) { #>
                                            <span class="iw-mega-menu__badge">{{{ top.item_badge }}}</span>
                                        <# } #>
                                    </span>
                                </a>
                            <# } else { #>
                                <button class="iw-mega-menu__top-link" type="button" <# if (has_dropdown) { #> aria-haspopup="true" aria-expanded="false" data-dropdown-index="{{ i }}"<# } #>>
                                    <span class="iw-mega-menu__link-content">
                                        {{{ top.item_label }}}
                                        <# if (top.item_badge) { #>
                                            <span class="iw-mega-menu__badge">{{{ top.item_badge }}}</span>
                                        <# } #>
                                    </span>
                                </button>
                            <# } #>
                        </li>
                    <# }); #>
                </ul>
                <# _.each(tree, function(top, i) { if (!top.children || !top.children.length) return; var dropdown_id = menu_id + '-dropdown-' + i; #>
                    <# var has_sidebar = top.sidebar_template_id && top.sidebar_template_id !== ''; #>
                    <div class="iw-mega-menu__dropdown <# if (has_sidebar) { #>iw-mega-menu__dropdown--with-sidebar<# } else { #>iw-mega-menu__dropdown--no-sidebar<# } #>" id="{{ dropdown_id }}" data-dropdown-index="{{ i }}" tabindex="-1" hidden>
                        <div class="iw-mega-menu__dropdown-main">
                            <# var non_accordion = [], accordion = []; _.each(top.children, function(second) { if (second.is_accordion === 'yes') { accordion.push(second); } else { non_accordion.push(second); } }); #>
                            <# if (non_accordion.length) { #>
                                <div class="iw-mega-menu__second-list">
                                    <# var columns = [[], []]; _.each(non_accordion, function(link, idx) { var col = Math.floor(idx / 4); if (col < 2) columns[col].push(link); }); #>
                                    <# for (var c = 0; c < 2; c++) { #>
                                        <div class="iw-mega-menu__second-col">
                                            <# _.each(columns[c], function(second) { #>
                                                <a class="iw-mega-menu__section-label" href="{{ second.item_link.url }}">
                                                    {{{ renderIcon(second.item_icon, second.item_icon_class) }}}
                                                    {{{ second.item_label }}}
                                                    <# if (second.item_badge) { #>
                                                        <span class="iw-mega-menu__badge">{{{ second.item_badge }}}</span>
                                                    <# } #>
                                                </a>
                                            <# }); #>
                                        </div>
                                    <# } #>
                                </div>
                            <# } #>
                            <# _.each(accordion, function(second, j) { var section_id = dropdown_id + '-section-' + j; #>
                                <div class="iw-mega-menu__section iw-mega-menu__accordion">
                                    <# if (second.item_icon && second.item_icon.value) { #>
                                        {{{ renderIcon(second.item_icon, second.item_icon_class) }}}
                                    <# } #>
                                    <button class="iw-mega-menu__section-label iw-mega-menu__accordion-toggle" type="button" aria-controls="{{ section_id }}" aria-expanded="false">
                                        <span class="iw-mega-menu__link-content">
                                            <# if (second.item_icon && second.item_icon.value) { #>
                                                {{{ renderIcon(second.item_icon, second.item_icon_class) }}}
                                            <# } #>
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
                                        <# var third_links = second.children || []; var third_columns = [[], [], []]; _.each(third_links, function(link, idx) { var col = Math.floor(idx / 2); if (col < 3) third_columns[col].push(link); }); #>
                                        <div class="iw-mega-menu__third-list">
                                            <# for (var c = 0; c < 3; c++) { #>
                                                <div class="iw-mega-menu__third-col">
                                                    <# _.each(third_columns[c], function(third) { #>
                                                        <a class="iw-mega-menu__third-link" href="{{ third.item_link.url }}">
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
                            <aside class="iw-mega-menu-sidebar">Sidebar Preview</aside>
                        <# } #>
                    </div>
                <# }); #>
            </nav>
        </div>
        <script>
        (function($){
            // Only run this logic in the Elementor editor
            if (typeof elementorFrontend === 'undefined' || typeof elementorFrontend.hooks === 'undefined') return;

            function logMenuDiagnostics($scope) {
                var $wrapper = $scope.find('.iw-mega-menu-wrapper');
                var $nav = $wrapper.find('.iw-mega-menu__nav');
                var $dropdowns = $nav.find('.iw-mega-menu__dropdown');
                console.log('[IW MegaMenu] Init diagnostics:');
                console.log('  $wrapper.length:', $wrapper.length);
                console.log('  $nav.length:', $nav.length);
                console.log('  $dropdowns.length:', $dropdowns.length);
                $dropdowns.each(function(i, el) {
                    var $el = $(el);
                    var computed = window.getComputedStyle(el);
                    console.log('    Dropdown #' + i + ': hidden attr =', el.hasAttribute('hidden'), ', display =', computed.display, ', visibility =', computed.visibility, ', opacity =', computed.opacity);
                });
            }

            function forceShowDropdown($dropdown, idx) {
                $dropdown.removeAttr('hidden');
                $dropdown.css({ display: 'block', opacity: 1, visibility: 'visible' });
                console.log('[IW MegaMenu] Forcibly showed dropdown', idx);
            }
            function forceHideDropdown($dropdown, idx) {
                $dropdown.attr('hidden', true);
                $dropdown.css({ display: 'none', opacity: 0 });
                console.log('[IW MegaMenu] Forcibly hid dropdown', idx);
            }

            function initMenu($scope) {
                var $wrapper = $scope.find('.iw-mega-menu-wrapper');
                var $nav = $wrapper.find('.iw-mega-menu__nav');
                if (!$nav.length || !$nav.find('.iw-mega-menu__top-item').length) {
                    console.log('[IW MegaMenu] Menu not found in scope:', $scope);
                    return;
                }
                var $topItems = $nav.find('.iw-mega-menu__top-item');
                var $dropdowns = $nav.find('.iw-mega-menu__dropdown');
                var $accordions = $nav.find('.iw-mega-menu__accordion');
                // Dropdowns
                $topItems.each(function() {
                    var $item = $(this);
                    var $btn = $item.find('.iw-mega-menu__top-link');
                    var idx = $btn.data('dropdown-index');
                    if (idx === undefined) return;
                    var $dropdown = $nav.find('.iw-mega-menu__dropdown[data-dropdown-index="' + idx + '"]');
                    $btn.off('mouseenter focus').on('mouseenter focus', function(){
                        $dropdowns.each(function(i, el) { forceHideDropdown($(el), i); });
                        forceShowDropdown($dropdown, idx);
                        $btn.attr('aria-expanded', 'true');
                        console.log('[IW MegaMenu] Show dropdown', idx);
                    });
                    $item.off('mouseleave').on('mouseleave', function(){
                        forceHideDropdown($dropdown, idx);
                        $btn.attr('aria-expanded', 'false');
                        console.log('[IW MegaMenu] Hide dropdown', idx);
                    });
                    $btn.off('blur').on('blur', function(e){
                        if (!$item.has(e.relatedTarget).length) {
                            forceHideDropdown($dropdown, idx);
                            $btn.attr('aria-expanded', 'false');
                            console.log('[IW MegaMenu] Blur/hide dropdown', idx);
                        }
                    });
                });
                // Accordions
                $accordions.each(function() {
                    var $section = $(this);
                    var $btn = $section.find('.iw-mega-menu__accordion-toggle');
                    var $content = $section.find('.iw-mega-menu__section-content');
                    $btn.off('click').on('click', function(){
                        var isOpen = $btn.attr('aria-expanded') === 'true';
                        if (isOpen) {
                            $content.hide();
                            $btn.attr('aria-expanded', 'false');
                            $btn.find('.iw-mega-menu__caret i').removeClass('fa-solid fa-square-caret-up').addClass('fa-regular fa-square-caret-down');
                            console.log('[IW MegaMenu] Accordion closed');
                        } else {
                            $content.show();
                            $btn.attr('aria-expanded', 'true');
                            $btn.find('.iw-mega-menu__caret i').removeClass('fa-regular fa-square-caret-down').addClass('fa-solid fa-square-caret-up');
                            console.log('[IW MegaMenu] Accordion opened');
                        }
                    });
                });
                logMenuDiagnostics($scope);
            }

            // Elementor frontend/element_ready hook for this widget
            elementorFrontend.hooks.addAction('frontend/element_ready/iw-mega-menu.default', function($scope){
                console.log('[IW MegaMenu] element_ready triggered');
                initMenu($scope);
            });
        })(jQuery);
        </script>
        <?php
    }
    private function get_elementor_templates() {
        $templates = [];
        $posts = get_posts([
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
        ]);
        if ( $posts ) {
            foreach ( $posts as $post ) {
                $templates[ $post->ID ] = $post->post_title;
            }
        }
        return $templates;
    }
    private function get_sidebar_template_content( $template_id ) {
        if ( ! $template_id ) return '';
        return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
    }
} 