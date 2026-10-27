/**
 * IW Mega Menu - Frontend JavaScript Handler
 *
 * Provides accessible keyboard navigation, mouse buffer handling, touch support,
 * mobile modal dialog drawer with focus trapping, and accordion toggling.
 * Works wherever the menu is rendered (block, shortcode, template tag) and with
 * the Elementor widget's frontend lifecycle. Open/closed visuals are driven by
 * the hidden and aria-expanded attributes in CSS.
 *
 * @package IW_Mega_Menu
 */

(function($) {
    'use strict';

    /**
     * IWMegaMenu Class
     *
     * @param {jQuery} $scope Widget container element.
     */
    var IWMegaMenu = function($scope) {
        var self = this;
        self.$scope = $scope;
        self.$nav = $scope.find('.iw-mega-menu__nav');

        if (!self.$nav.length) {
            return;
        }

        self.initDesktopMenu();
        self.initAccordions();
        self.initMobileMenu();
    };

    /**
     * Initialize Desktop Mega Menu Navigation
     */
    IWMegaMenu.prototype.initDesktopMenu = function() {
        var self = this;
        var $topItems = self.$nav.find('.iw-mega-menu__top-item');
        var $dropdowns = self.$nav.find('.iw-mega-menu__dropdown');
        var $buffers = self.$nav.find('.iw-mega-menu__dropdown-buffer');

        // Hide all dropdown buffers initially
        $buffers.prop('hidden', true);

        function hideAllDropdowns() {
            $dropdowns.prop('hidden', true).removeClass('is-active');
            $buffers.prop('hidden', true);
            self.$nav.find('.iw-mega-menu__top-link[aria-expanded="true"]')
                     .attr('aria-expanded', 'false');
        }

        function showDropdown($item, $btn, $dropdown, $buffer) {
            hideAllDropdowns();
            if ($dropdown.length) {
                $dropdown.prop('hidden', false).addClass('is-active');
            }
            if ($buffer && $buffer.length) {
                $buffer.prop('hidden', false);
            }
            $btn.attr('aria-expanded', 'true');
        }

        $topItems.each(function() {
            var $item = $(this);
            var $btn = $item.find('.iw-mega-menu__top-link');
            var $dropdown = $item.find('.iw-mega-menu__dropdown');
            var $buffer = $item.find('.iw-mega-menu__dropdown-buffer');
            var hideTimeout;

            if (!$dropdown.length) {
                return;
            }

            var scheduleHide = function() {
                hideTimeout = setTimeout(function() {
                    if ($dropdown.length) {
                        $dropdown.prop('hidden', true).removeClass('is-active');
                    }
                    if ($buffer && $buffer.length) {
                        $buffer.prop('hidden', true);
                    }
                    $btn.attr('aria-expanded', 'false');
                }, 150);
            };

            var cancelHide = function() {
                clearTimeout(hideTimeout);
            };

            // Mouse hover interactions
            $btn.on('mouseenter', function() {
                cancelHide();
                showDropdown($item, $btn, $dropdown, $buffer);
            });

            $item.on('mouseleave', function() {
                scheduleHide();
            });

            if ($dropdown.length) {
                $dropdown.on('mouseenter', cancelHide);
                $dropdown.on('mouseleave', scheduleHide);
            }

            if ($buffer && $buffer.length) {
                $buffer.on('mouseenter', function() {
                    cancelHide();
                    showDropdown($item, $btn, $dropdown, $buffer);
                });
                $buffer.on('mouseleave', scheduleHide);
            }

            // Keyboard Focus interactions on Top Button
            $btn.on('focus', function() {
                cancelHide();
                showDropdown($item, $btn, $dropdown, $buffer);
            });

            $btn.on('blur', function(e) {
                var relatedTarget = e.relatedTarget;
                if (!relatedTarget || (!$item[0].contains(relatedTarget) && !self.$nav[0].contains(relatedTarget))) {
                    scheduleHide();
                }
            });

            // Touch device support (tablets / touch laptops)
            $btn.on('touchstart', function(e) {
                var isExpanded = $btn.attr('aria-expanded') === 'true';
                if (!isExpanded) {
                    e.preventDefault();
                    cancelHide();
                    showDropdown($item, $btn, $dropdown, $buffer);
                }
            });

            // Keyboard accessibility within dropdown (Escape & Tab boundary handling)
            $item.on('keydown', function(e) {
                // Escape key closes dropdown and returns focus to top-level trigger
                if (e.key === 'Escape' || e.keyCode === 27) {
                    if ($btn.attr('aria-expanded') === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        hideAllDropdowns();
                        $btn.trigger('focus');
                    }
                }
            });

            // Focus management inside dropdown
            $dropdown.on('focusout', function(e) {
                var relatedTarget = e.relatedTarget;
                if (!relatedTarget || !$item[0].contains(relatedTarget)) {
                    scheduleHide();
                }
            });
        });

        // Close all dropdowns if user clicks outside nav
        $(document).on('click.iwMegaMenu', function(e) {
            if (!self.$nav.is(e.target) && self.$nav.has(e.target).length === 0) {
                hideAllDropdowns();
            }
        });
    };

    /**
     * Initialize Desktop & Mobile Accordions
     */
    IWMegaMenu.prototype.initAccordions = function() {
        var self = this;
        var $accordions = self.$scope.find('.iw-mega-menu__accordion-toggle');

        $accordions.each(function() {
            var $toggle = $(this);
            var controlsId = $toggle.attr('aria-controls');
            if (!controlsId) {
                return;
            }

            var $content = self.$scope.find('#' + controlsId);
            if (!$content.length) {
                return;
            }

            // The plus/close icon turns via CSS on aria-expanded.
            $toggle.off('click.iwAccordion').on('click.iwAccordion', function(e) {
                e.preventDefault();
                var isExpanded = $toggle.attr('aria-expanded') === 'true';
                $toggle.attr('aria-expanded', isExpanded ? 'false' : 'true');
                $content.prop('hidden', isExpanded);
            });
        });
    };

    /**
     * Initialize Mobile Drawer Modal & Focus Trapping
     */
    IWMegaMenu.prototype.initMobileMenu = function() {
        var self = this;
        var $mobileToggle = self.$scope.find('.iw-mega-menu__mobile-toggle');
        var $mobilePanel = self.$scope.find('.iw-mega-menu__mobile-panel');
        var $mobileClose = self.$scope.find('.iw-mega-menu__mobile-close');

        if (!$mobileToggle.length || !$mobilePanel.length) {
            return;
        }

        // Generate unique IDs if missing for aria-controls linkage
        var panelId = $mobilePanel.attr('id') || 'iw-mobile-panel-' + Math.random().toString(36).substr(2, 9);
        $mobilePanel.attr('id', panelId);
        $mobileToggle.attr('aria-controls', panelId).attr('aria-expanded', 'false');

        // Focus trap helper
        var focusableElementsSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

        function openMobileMenu() {
            $mobileToggle.attr('aria-expanded', 'true');
            $mobilePanel.prop('hidden', false).addClass('is-open');
            $('html, body').addClass('iw-mega-menu-mobile-open');

            // Move focus to close button or first focusable element
            setTimeout(function() {
                if ($mobileClose.length && $mobileClose.is(':visible')) {
                    $mobileClose.trigger('focus');
                } else {
                    var $firstFocusable = $mobilePanel.find(focusableElementsSelector).first();
                    if ($firstFocusable.length) {
                        $firstFocusable.trigger('focus');
                    }
                }
            }, 50);
        }

        function closeMobileMenu() {
            $mobileToggle.attr('aria-expanded', 'false');
            $mobilePanel.prop('hidden', true).removeClass('is-open');
            $('html, body').removeClass('iw-mega-menu-mobile-open');

            // Return focus to hamburger toggle button
            setTimeout(function() {
                $mobileToggle.trigger('focus');
            }, 50);
        }

        $mobileToggle.off('click.iwMobileOpen').on('click.iwMobileOpen', function(e) {
            e.preventDefault();
            var isOpen = $mobileToggle.attr('aria-expanded') === 'true';
            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        if ($mobileClose.length) {
            $mobileClose.off('click.iwMobileClose').on('click.iwMobileClose', function(e) {
                e.preventDefault();
                closeMobileMenu();
            });
        }

        // Trap focus inside mobile panel & handle Escape key
        $mobilePanel.off('keydown.iwFocusTrap').on('keydown.iwFocusTrap', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                e.preventDefault();
                closeMobileMenu();
                return;
            }

            if (e.key === 'Tab' || e.keyCode === 9) {
                var $focusable = $mobilePanel.find(focusableElementsSelector).filter(':visible');
                if (!$focusable.length) {
                    e.preventDefault();
                    return;
                }

                var $first = $focusable.first();
                var $last = $focusable.last();

                if (e.shiftKey) {
                    // Shift + Tab
                    if ($(document.activeElement).is($first) || !$.contains($mobilePanel[0], document.activeElement)) {
                        e.preventDefault();
                        $last.trigger('focus');
                    }
                } else {
                    // Tab
                    if ($(document.activeElement).is($last)) {
                        e.preventDefault();
                        $first.trigger('focus');
                    }
                }
            }
        });

        // Mobile collapsible sublists
        $mobilePanel.find('.iw-mega-menu__mobile-link[aria-controls]').each(function() {
            var $btn = $(this);
            var sublistId = $btn.attr('aria-controls');
            if (!sublistId) return;

            var $sublist = $mobilePanel.find('#' + sublistId);
            if (!$sublist.length) return;

            $btn.off('click.iwMobileSub').on('click.iwMobileSub', function(e) {
                e.preventDefault();
                var isExpanded = $btn.attr('aria-expanded') === 'true';
                $btn.attr('aria-expanded', isExpanded ? 'false' : 'true');
                $sublist.prop('hidden', isExpanded);
            });
        });
    };

    /**
     * Initialize every menu wrapper in a context, once each.
     *
     * Each .iw-mega-menu-wrapper holds a whole menu (nav, mobile toggle and
     * panel), so menus from blocks, shortcodes and Elementor widgets can share
     * a page.
     *
     * @param {Element|jQuery} context
     */
    function initMenus(context) {
        $(context).find('.iw-mega-menu-wrapper').addBack('.iw-mega-menu-wrapper').each(function() {
            var $wrapper = $(this);
            if ($wrapper.data('iwMegaMenu')) {
                return;
            }
            $wrapper.data('iwMegaMenu', new IWMegaMenu($wrapper));
        });
    }

    // Elementor re-renders widgets in the editor; initialize each new copy.
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/iw-mega-menu.default', function($scope) {
            initMenus($scope);
        });
    });

    $(function() {
        initMenus(document);
    });

})(jQuery);

