<?php
/*
Plugin Name: IW Mega Menu
Description: A powerful, Elementor-optimized Mega Menu plugin with multi-level navigation and widget support. Developed for Austin Community College - Instructional Web Projects.
Version: 1.0.1
Author: Austin Community College - Instructional Web Projects
Author URI: https://instruction.austincc.edu
Text Domain: iw-mega-menu
Domain Path: /languages
Requires at least: 5.8
Requires PHP: 7.4
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'IW_MEGA_MENU_VERSION', '1.0.1' );
define( 'IW_MEGA_MENU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IW_MEGA_MENU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function iw_mega_menu_enqueue_styles() {
    wp_enqueue_style( 'iw-mega-menu', IW_MEGA_MENU_PLUGIN_URL . 'iw-megamenu.css', [], IW_MEGA_MENU_VERSION );
}
add_action( 'wp_enqueue_scripts', 'iw_mega_menu_enqueue_styles' );

// Elementor widget registration
add_action('elementor/widgets/widgets_registered', function() {
    // Check Elementor is loaded
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>IW Mega Menu requires Elementor to be installed and activated.</p></div>';
        });
        return;
    }
    require_once IW_MEGA_MENU_PLUGIN_DIR . 'includes/widgets/class-iw-mega-menu-widget.php';
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \IW_Mega_Menu_Widget() );
}); 

add_action('wp_head', function() {
    ?>
    <style>
    @media (max-width: 767px) {
      .iw-mega-menu__nav { display: none !important; }
      .iw-mega-menu__mobile-panel {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: #fff !important;
        z-index: 1200 !important;
        overflow-y: auto !important;
        max-height: 100vh !important;
        padding: 0 0 1.5rem 0 !important;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10) !important;
      }
      .iw-mega-menu__mobile-panel[hidden] {
        display: none !important;
      }
      /* Top bar for close button */
      .iw-mega-menu__mobile-topbar {
        width: 100%;
        background: #2E1A47 !important;
        padding: 0 1.5rem !important;
        min-height: 3.5rem !important;
        display: flex;
        align-items: center;
        justify-content: flex-end;
      }
      .iw-mega-menu__mobile-close {
        background: #2E1A47 !important;
        color: #fff !important;
        border: none !important;
        font-size: 2rem !important;
        cursor: pointer !important;
        width: 2.5rem !important;
        height: 2.5rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
        border-radius: 8px !important;
      }
      .iw-mega-menu__mobile-list {
        list-style: none !important;
        padding: 1.5rem !important;
        margin: 0 !important;
      }
      .iw-mega-menu__mobile-sublist {
        background: #F3F4F5 !important;
        list-style: none !important;
        padding-left: 1.5rem !important;
        margin: 0 !important;
        max-height: 60vh !important;
        overflow-y: auto !important;
        border-radius: 12px !important;
      }
      /* Third-level: nested sublist */
      .iw-mega-menu__mobile-sublist .iw-mega-menu__mobile-sublist {
        padding-left: 3rem !important;
        background: #F3F4F5 !important;
      }
      .iw-mega-menu__mobile-item {
        margin-bottom: 0.5rem !important;
      }
      .iw-mega-menu__mobile-link,
      .iw-mega-menu__mobile-list button,
      .iw-mega-menu__mobile-sublist button {
        background: none !important;
        border: none !important;
        font-size: 1rem !important;
        line-height: 1.6rem !important;
        font-weight: 500 !important;
        color: #3a2670 !important;
        text-decoration: none !important;
        padding: 0.5rem 0 !important;
        width: 100% !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        transition: background 0.2s, color 0.2s !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        outline: none !important;
      }
      .iw-mega-menu__mobile-link:active,
      .iw-mega-menu__mobile-link:focus,
      .iw-mega-menu__mobile-list button:active,
      .iw-mega-menu__mobile-list button:focus,
      .iw-mega-menu__mobile-sublist button:active,
      .iw-mega-menu__mobile-sublist button:focus {
        background: #f5f5fa !important;
        color: #3a2670 !important;
        outline: none !important;
      }
      .iw-mega-menu__mobile-caret {
        margin-left: 0.5rem !important;
        font-size: 1.2rem !important;
        color: #3a2670 !important;
      }
      body.iw-mega-menu-mobile-open,
      html.iw-mega-menu-mobile-open {
        overflow: visible !important;
        position: static !important;
      }
    }
    </style>
    <?php
}); 