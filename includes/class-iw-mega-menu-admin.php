<?php
/**
 * IW Mega Menu Admin Page
 *
 * @package IWMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class IW_Mega_Menu_Admin {

	/**
	 * Initialize Admin.
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_admin_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Register Admin Page.
	 */
	public function register_admin_page() {
		add_menu_page(
			__( 'IW Mega Menu', 'iw-mega-menu' ),
			__( 'IW Mega Menu', 'iw-mega-menu' ),
			'edit_theme_options',
			'iw-mega-menu',
			[ $this, 'render_admin_page' ],
			'dashicons-menu-alt3',
			58
		);
	}

	/**
	 * Enqueue Admin Assets.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( 'toplevel_page_iw-mega-menu' !== $hook_suffix ) {
			return;
		}

		$plugin_dir = plugin_dir_path( dirname( __FILE__ ) );

		// Font Awesome for preview icons
		wp_enqueue_style(
			'font-awesome-6',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
			[],
			'6.5.1'
		);

		// The builder UI uses Dashicons for its controls and Font Awesome for icon previews.
		wp_enqueue_style(
			'iw-admin-builder-css',
			plugins_url( 'assets/admin/css/iw-admin-builder.css', dirname( __FILE__ ) ),
			[ 'dashicons', 'font-awesome-6' ],
			file_exists( $plugin_dir . 'assets/admin/css/iw-admin-builder.css' )
				? filemtime( $plugin_dir . 'assets/admin/css/iw-admin-builder.css' )
				: IW_MEGA_MENU_VERSION
		);

		wp_enqueue_style(
			'iw-mega-menu',
			plugins_url( 'assets/css/iw-megamenu.css', dirname( __FILE__ ) ),
			[],
			file_exists( $plugin_dir . 'assets/css/iw-megamenu.css' )
				? filemtime( $plugin_dir . 'assets/css/iw-megamenu.css' )
				: IW_MEGA_MENU_VERSION
		);

		wp_enqueue_script(
			'iw-admin-builder-js',
			plugins_url( 'assets/admin/js/iw-admin-builder.js', dirname( __FILE__ ) ),
			[ 'wp-api-fetch' ],
			file_exists( $plugin_dir . 'assets/admin/js/iw-admin-builder.js' )
				? filemtime( $plugin_dir . 'assets/admin/js/iw-admin-builder.js' )
				: IW_MEGA_MENU_VERSION,
			true
		);

		wp_localize_script( 'iw-admin-builder-js', 'iwMegaMenuAdminData', [
			'restUrl' => esc_url_raw( rest_url( 'iw-mega-menu/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		] );
	}

	/**
	 * Render Admin Page.
	 */
	public function render_admin_page() {
		?>
		<div class="wrap iw-mega-menu-admin-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'IW Mega Menu Builder', 'iw-mega-menu' ); ?></h1>
			<hr class="wp-header-end">
			<p class="description">
				<?php esc_html_e( 'Build multi-level navigation menus with dropdowns, accordions, and sidebar content, then place the finished menu with the IW Mega Menu block, the [iw_mega_menu] shortcode, the iw_mega_menu() template tag, or the Elementor widget.', 'iw-mega-menu' ); ?>
			</p>
			<div id="iw-mega-menu-builder-root">
				<!-- Vanilla JS app will mount here -->
				<p><?php esc_html_e( 'Loading builder...', 'iw-mega-menu' ); ?></p>
			</div>
		</div>
		<?php
	}
}
