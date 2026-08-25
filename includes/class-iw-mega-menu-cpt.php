<?php
/**
 * IW Mega Menu Custom Post Type
 *
 * @package IWMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class IW_Mega_Menu_CPT {

	/**
	 * Initialize the CPT.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_cpt' ] );
	}

	/**
	 * Register the Custom Post Type.
	 */
	public function register_cpt() {
		$labels = [
			'name'               => _x( 'Mega Menus', 'post type general name', 'iw-megamenu' ),
			'singular_name'      => _x( 'Mega Menu', 'post type singular name', 'iw-megamenu' ),
			'menu_name'          => _x( 'Mega Menus', 'admin menu', 'iw-megamenu' ),
			'name_admin_bar'     => _x( 'Mega Menu', 'add new on admin bar', 'iw-megamenu' ),
			'add_new'            => _x( 'Add New', 'mega menu', 'iw-megamenu' ),
			'add_new_item'       => __( 'Add New Mega Menu', 'iw-megamenu' ),
			'new_item'           => __( 'New Mega Menu', 'iw-megamenu' ),
			'edit_item'          => __( 'Edit Mega Menu', 'iw-megamenu' ),
			'view_item'          => __( 'View Mega Menu', 'iw-megamenu' ),
			'all_items'          => __( 'All Mega Menus', 'iw-megamenu' ),
			'search_items'       => __( 'Search Mega Menus', 'iw-megamenu' ),
			'parent_item_colon'  => __( 'Parent Mega Menus:', 'iw-megamenu' ),
			'not_found'          => __( 'No mega menus found.', 'iw-megamenu' ),
			'not_found_in_trash' => __( 'No mega menus found in Trash.', 'iw-megamenu' )
		];

		$args = [
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false, // Managed by our custom admin page
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => [ 'title', 'author' ], // keep title, maybe author
		];

		register_post_type( 'iw_mega_menu', $args );
	}
}
