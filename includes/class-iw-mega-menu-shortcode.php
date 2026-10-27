<?php
/**
 * IW Mega Menu Shortcode
 *
 * [iw_mega_menu id="123" label="Main Navigation"] — for the classic editor,
 * text widgets and other page builders.
 *
 * @package IWMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class IW_Mega_Menu_Shortcode {

	/**
	 * Initialize the shortcode.
	 */
	public function init() {
		add_shortcode( 'iw_mega_menu', [ $this, 'render_shortcode' ] );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts( [
			'id'    => 0,
			'label' => '',
		], $atts, 'iw_mega_menu' );

		return IW_Mega_Menu_Renderer::render_menu( $atts['id'], [ 'label' => $atts['label'] ] );
	}
}
