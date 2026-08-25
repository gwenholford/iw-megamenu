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
			__( 'IW Mega Menu', 'iw-megamenu' ),
			__( 'IW Mega Menu', 'iw-megamenu' ),
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

		wp_enqueue_style(
			'iw-admin-builder-css',
			plugins_url( 'assets/admin/css/iw-admin-builder.css', dirname( __FILE__ ) ),
			[],
			'2.0.0'
		);

		wp_enqueue_script(
			'iw-admin-builder-js',
			plugins_url( 'assets/admin/js/iw-admin-builder.js', dirname( __FILE__ ) ),
			[ 'wp-api-fetch' ], // vanilla js using wp-api-fetch
			'2.0.0',
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
			<h1 class="wp-heading-inline"><?php esc_html_e( 'IW Mega Menu Builder', 'iw-megamenu' ); ?></h1>
			<hr class="wp-header-end">
			<div id="iw-mega-menu-builder-root">
				<!-- Vanilla JS app will mount here -->
				<p>Loading builder...</p>
			</div>
		</div>
		<?php
	}
}
