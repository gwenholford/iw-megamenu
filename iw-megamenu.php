<?php
/**
 * Plugin Name: IW Mega Menu
 * Description: A powerful, accessible, and Elementor-optimized Mega Menu plugin with multi-level navigation and widget support. Developed for Austin Community College - Instructional Web Projects.
 * Version: 2.0.0
 * Author: Austin Community College - Instructional Web Projects
 * Author URI: https://instruction.austincc.edu
 * Plugin URI: https://github.com/gwenholford/iw-megamenu
 * Update URI: false
 * Text Domain: iw-mega-menu
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Elementor tested up to: 3.25
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/*
 * Bail if another copy of this plugin already loaded.
 *
 * WordPress identifies a plugin by "folder/main-file.php", so an upload that
 * lands in a differently-named folder (e.g. iw-megamenu-main/ from a GitHub zip)
 * is registered as a separate plugin and can be activated alongside the original.
 * Without this guard the second copy re-declares IW_Mega_Menu and fatals with
 * "Cannot declare class IW_Mega_Menu, because the name is already in use",
 * taking down every site the plugin is active on. First copy loaded wins.
 */
if ( defined( 'IW_MEGA_MENU_VERSION' ) ) {
    return;
}

// Define Plugin Constants
define( 'IW_MEGA_MENU_VERSION', '2.0.0' );
define( 'IW_MEGA_MENU_FILE', __FILE__ );
define( 'IW_MEGA_MENU_PATH', plugin_dir_path( __FILE__ ) );
define( 'IW_MEGA_MENU_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main IW Mega Menu Plugin Class
 */
final class IW_Mega_Menu {

    /**
     * Singleton instance
     *
     * @var IW_Mega_Menu|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return IW_Mega_Menu
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress and Elementor hooks
     */
    private function init_hooks() {
        // Load translation text domain
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // Register frontend assets
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );

        // Initialize Core v2.0.0 Modules
        $this->init_core_modules();

        // Elementor widget registration
        if ( did_action( 'elementor/loaded' ) ) {
            $this->init_elementor();
        } else {
            add_action( 'elementor/loaded', [ $this, 'init_elementor' ] );
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
        }
    }

    /**
     * Initialize Core v2.0.0 Modules
     */
    private function init_core_modules() {
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-cpt.php';
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-rest.php';
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-admin.php';

        $cpt = new IW_Mega_Menu_CPT();
        $cpt->init();

        $rest = new IW_Mega_Menu_REST();
        $rest->init();

        $admin = new IW_Mega_Menu_Admin();
        $admin->init();
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'iw-mega-menu',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    /**
     * Register CSS and JS assets conditionally
     */
    public function register_assets() {
        $css_file = file_exists( IW_MEGA_MENU_PATH . 'assets/css/iw-megamenu.css' )
            ? IW_MEGA_MENU_URL . 'assets/css/iw-megamenu.css'
            : IW_MEGA_MENU_URL . 'iw-megamenu.css';

        wp_register_style(
            'iw-mega-menu',
            $css_file,
            [],
            IW_MEGA_MENU_VERSION
        );

        wp_register_script(
            'iw-mega-menu',
            IW_MEGA_MENU_URL . 'assets/js/iw-megamenu.js',
            [ 'jquery' ],
            IW_MEGA_MENU_VERSION,
            true
        );
    }

    /**
     * Hook into Elementor widget registration
     */
    public function init_elementor() {
        // Modern Elementor 3.5+ widget registration action
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        // Backward compatibility fallback for older Elementor versions (< 3.5.0)
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets_legacy' ] );
    }

    /**
     * Register widgets for Elementor 3.5+
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public function register_widgets( $widgets_manager ) {
        require_once IW_MEGA_MENU_PATH . 'includes/widgets/class-iw-mega-menu-widget.php';
        $widgets_manager->register( new \IW_Mega_Menu_Widget() );
    }

    /**
     * Register widgets legacy fallback (< 3.5.0)
     */
    public function register_widgets_legacy() {
        if ( did_action( 'elementor/widgets/register' ) ) {
            return;
        }
        require_once IW_MEGA_MENU_PATH . 'includes/widgets/class-iw-mega-menu-widget.php';
        if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::instance()->widgets_manager ) ) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \IW_Mega_Menu_Widget() );
        }
    }

    /**
     * Admin notice if Elementor is not installed or activated
     */
    public function admin_notice_missing_elementor() {
        if ( did_action( 'elementor/loaded' ) ) {
            return;
        }

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $message = sprintf(
            /* translators: 1: Plugin Name, 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'iw-mega-menu' ),
            '<strong>' . esc_html__( 'IW Mega Menu', 'iw-mega-menu' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'iw-mega-menu' ) . '</strong>'
        );

        printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
    }
}

// Instantiate plugin
IW_Mega_Menu::get_instance();