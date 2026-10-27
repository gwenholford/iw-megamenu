<?php
/**
 * IW Mega Menu template tags, for theme developers.
 *
 *     <?php if ( function_exists( 'iw_mega_menu' ) ) { iw_mega_menu( 123 ); } ?>
 *
 * @package IWMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'iw_get_mega_menu' ) ) {
	/**
	 * Return a saved mega menu's markup.
	 *
	 * @param int   $menu_id Mega menu ID (shown in the IW Mega Menu builder).
	 * @param array $args {
	 *     Optional.
	 *
	 *     @type string $label Accessible name for the navigation. Default "Main Navigation".
	 * }
	 * @return string Markup, or '' if the menu doesn't exist.
	 */
	function iw_get_mega_menu( $menu_id, $args = [] ) {
		return IW_Mega_Menu_Renderer::render_menu( $menu_id, $args );
	}
}

if ( ! function_exists( 'iw_mega_menu' ) ) {
	/**
	 * Print a saved mega menu.
	 *
	 * @param int   $menu_id Mega menu ID.
	 * @param array $args    See iw_get_mega_menu().
	 */
	function iw_mega_menu( $menu_id, $args = [] ) {
		echo iw_get_mega_menu( $menu_id, $args ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by the renderer.
	}
}
