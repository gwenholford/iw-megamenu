<?php
/**
 * Plugin Name: IW Mega Menu
 * Description: An accessible, multi-level mega menu for any WordPress site. Build menus in the dashboard, then place them with the IW Mega Menu block, the [iw_mega_menu] shortcode, the iw_mega_menu() template tag, or the optional Elementor widget. Developed for Austin Community College - Instructional Web Projects.
 * Version: 3.0.1
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
define( 'IW_MEGA_MENU_VERSION', '3.0.1' );
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
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Load translation text domain
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // Register front-end assets early so the block's style handle resolves.
        add_action( 'init', [ $this, 'register_assets' ], 5 );

        $this->init_core_modules();

        // Optional Elementor widget, for sites that use Elementor.
        if ( did_action( 'elementor/loaded' ) ) {
            $this->init_elementor();
        } else {
            add_action( 'elementor/loaded', [ $this, 'init_elementor' ] );
        }
    }

    /**
     * Initialize Core Modules
     */
    private function init_core_modules() {
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-renderer.php';
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-cpt.php';
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-rest.php';
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-admin.php';
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-block.php';
        require_once IW_MEGA_MENU_PATH . 'includes/class-iw-mega-menu-shortcode.php';
        require_once IW_MEGA_MENU_PATH . 'includes/template-tags.php';

        $cpt = new IW_Mega_Menu_CPT();
        $cpt->init();

        $rest = new IW_Mega_Menu_REST();
        $rest->init();

        $admin = new IW_Mega_Menu_Admin();
        $admin->init();

        $block = new IW_Mega_Menu_Block();
        $block->init();

        $shortcode = new IW_Mega_Menu_Shortcode();
        $shortcode->init();
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
     * Register CSS and JS assets. The renderer enqueues them when a menu is output.
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

        /**
         * Filter the Font Awesome 6 stylesheet loaded for menu item icons.
         * Return an empty string if the theme already loads Font Awesome 6.
         *
         * @param string $url Stylesheet URL.
         */
        $font_awesome_url = apply_filters(
            'iw_mega_menu_font_awesome_url',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'
        );

        wp_register_style(
            'iw-mega-menu-font-awesome',
            $font_awesome_url ? $font_awesome_url : false,
            [],
            '6.5.1'
        );
    }

    /**
     * Hook into Elementor widget registration
     */
    public function init_elementor() {
        // Modern Elementor 3.5+ widget registration action
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        // Elementor < 3.5.0 only. Newer versions flag any listener on this hook as deprecated.
        if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '<' ) ) {
            add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets_legacy' ] );
        }
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
}

// Instantiate plugin
IW_Mega_Menu::get_instance();
