<?php
/**
 * IW Mega Menu Block
 *
 * A server-rendered block for the block editor and block themes (e.g. a
 * header template part).
 *
 * @package IWMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class IW_Mega_Menu_Block {

	/**
	 * Initialize the block.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Register the block and its editor script.
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'iw-mega-menu-block-editor',
			IW_MEGA_MENU_URL . 'blocks/mega-menu/index.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-api-fetch', 'wp-i18n' ],
			IW_MEGA_MENU_VERSION,
			true
		);

		register_block_type( IW_MEGA_MENU_PATH . 'blocks/mega-menu', [
			'render_callback' => [ $this, 'render_block' ],
		] );
	}

	/**
	 * Render the block on the front end and in the editor preview.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$menu_id = isset( $attributes['menuId'] ) ? absint( $attributes['menuId'] ) : 0;
		$html    = $menu_id
			? IW_Mega_Menu_Renderer::render_menu( $menu_id, [ 'label' => $attributes['label'] ?? '' ] )
			: '';

		if ( '' === $html ) {
			return '';
		}

		return sprintf( '<div %s>%s</div>', get_block_wrapper_attributes(), $html );
	}
}
