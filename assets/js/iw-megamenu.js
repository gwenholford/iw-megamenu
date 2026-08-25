/**
 * IW Mega Menu - Frontend JavaScript Handler
 *
 * Provides accessible keyboard navigation, mouse buffer handling, touch support,
 * mobile modal dialog drawer with focus trapping, and accordion toggling.
 * Fully compatible with Elementor Frontend lifecycle and standalone rendering.
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
            $dropdowns.each(function() {
                var $dd = $(this);
                $dd.prop('hidden', true)
                   .removeClass('is-active')
                   .css({ opacity: 0, transform: 'translateY(-12px)', pointerEvents: 'none' });
            });
            $buffers.prop('hidden', true);
            self.$nav.find('.iw-mega-menu__top-link[aria-expanded="true"]')
                     .attr('aria-expanded', 'false');
        }

        function showDropdown($item, $btn, $dropdown, $buffer) {
            hideAllDropdowns();
            if ($dropdown.length) {
                $dropdown.prop('hidden', false)
                         .addClass('is-active')
                         .css({ opacity: 1, transform: 'translateY(0)', pointerEvents: 'auto' });
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
                        $dropdown.prop('hidden', true)
                                 .removeClass('is-active')
                                 .css({ opacity: 0, transform: 'translateY(-12px)', pointerEvents: 'none' });
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

            $toggle.off('click.iwAccordion').on('click.iwAccordion', function(e) {
                e.preventDefault();
                var isExpanded = $toggle.attr('aria-expanded') === 'true';
                var $caret = $toggle.find('.iw-mega-menu__caret i, .iw-mega-menu__mobile-caret i');

                if (isExpanded) {
                    $toggle.attr('aria-expanded', 'false');
                    $content.prop('hidden', true).slideUp(200);
                    if ($caret.length) {
                        $caret.removeClass('fa-solid fa-square-caret-up fa-caret-up')
                              .addClass('fa-regular fa-square-caret-down');
                    }
                } else {
                    $toggle.attr('aria-expanded', 'true');
                    $content.prop('hidden', false).slideDown(200);
                    if ($caret.length) {
                        $caret.removeClass('fa-regular fa-square-caret-down')
                              .addClass('fa-solid fa-square-caret-up');
                    }
                }
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
                var $caret = $btn.find('.iw-mega-menu__mobile-caret i');

                if (isExpanded) {
                    $btn.attr('aria-expanded', 'false');
                    $sublist.prop('hidden', true).slideUp(200);
                    if ($caret.length) {
                        $caret.removeClass('fa-solid fa-square-caret-up')
                              .addClass('fa-regular fa-square-caret-down');
                    }
                } else {
                    $btn.attr('aria-expanded', 'true');
                    $sublist.prop('hidden', false).slideDown(200);
                    if ($caret.length) {
                        $caret.removeClass('fa-regular fa-square-caret-down')
                              .addClass('fa-solid fa-square-caret-up');
                    }
                }
            });
        });
    };

    /**
     * Hook into Elementor Frontend and Document Ready
     */
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/iw-mega-menu.default', function($scope) {
            new IWMegaMenu($scope);
        });
    });

    $(document).ready(function() {
        // Fallback for non-Elementor or standalone rendering
        if (typeof window.elementorFrontend === 'undefined') {
            $('.iw-mega-menu__nav').each(function() {
                var $scope = $(this).closest('.elementor-widget-iw-mega-menu, .iw-mega-menu-wrapper, body');
                new IWMegaMenu($scope);
            });
        }
    });

})(jQuery);

