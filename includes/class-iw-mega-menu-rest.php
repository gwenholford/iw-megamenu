<?php
/**
 * IW Mega Menu REST API
 *
 * @package IWMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class IW_Mega_Menu_REST {

	/**
	 * Initialize REST endpoints.
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Check if user has permission.
	 */
	public function check_permission() {
		return current_user_can( 'edit_theme_options' );
	}

	/**
	 * Register REST API Endpoints.
	 */
	public function register_endpoints() {
		$namespace = 'iw-mega-menu/v1';

		register_rest_route( $namespace, '/menus', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_menus' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_menu' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		] );

		register_rest_route( $namespace, '/menus/(?P<id>\d+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_menu' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'id' => [
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						}
					],
				],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE, // Using POST to handle complex JSON well
				'callback'            => [ $this, 'update_menu' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_menu' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		] );

		register_rest_route( $namespace, '/templates', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_elementor_templates' ],
			'permission_callback' => [ $this, 'check_permission' ],
		] );

		register_rest_route( $namespace, '/wp-menus', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_wp_menus' ],
			'permission_callback' => [ $this, 'check_permission' ],
		] );

		register_rest_route( $namespace, '/wp-menus/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_wp_menu_tree' ],
			'permission_callback' => [ $this, 'check_permission' ],
		] );
	}

	public function get_menus( $request ) {
		$posts = get_posts( [
			'post_type'      => 'iw_mega_menu',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		] );

		$data = [];
		foreach ( $posts as $post ) {
			$menu_data = get_post_meta( $post->ID, '_iw_mega_menu_data', true );
			$menu_data = is_string( $menu_data ) ? json_decode( $menu_data, true ) : [];
			$data[] = [
				'id'         => $post->ID,
				'title'      => $post->post_title,
				'date'       => $post->post_modified,
				'item_count' => is_array( $menu_data['items'] ?? null ) ? $this->count_items( $menu_data['items'] ) : 0,
			];
		}
		return rest_ensure_response( $data );
	}

	private function count_items( $items ) {
		$count = 0;
		foreach ( $items as $item ) {
			$count++;
			if ( is_array( $item['children'] ?? null ) ) {
				$count += $this->count_items( $item['children'] );
			}
		}
		return $count;
	}

	public function get_menu( $request ) {
		$post_id = $request['id'];
		$post    = get_post( $post_id );

		if ( ! $post || $post->post_type !== 'iw_mega_menu' ) {
			return new WP_Error( 'not_found', 'Menu not found', [ 'status' => 404 ] );
		}

		$menu_data = get_post_meta( $post_id, '_iw_mega_menu_data', true );

		return rest_ensure_response( [
			'id'    => $post_id,
			'title' => $post->post_title,
			'data'  => $menu_data ? json_decode( $menu_data, true ) : [ 'items' => [], 'settings' => [] ],
		] );
	}

	public function create_menu( $request ) {
		$title = $request->get_param( 'title' );
		$data  = $request->get_param( 'data' );

		if ( empty( $title ) ) {
			return new WP_Error( 'missing_title', 'Title is required', [ 'status' => 400 ] );
		}

		$post_id = wp_insert_post( [
			'post_title'  => sanitize_text_field( $title ),
			'post_type'   => 'iw_mega_menu',
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( $data ) {
			update_post_meta( $post_id, '_iw_mega_menu_data', wp_slash( wp_json_encode( $data ) ) );
		}

		return rest_ensure_response( [
			'id'      => $post_id,
			'message' => 'Menu created',
		] );
	}

	public function update_menu( $request ) {
		$post_id = $request['id'];
		$title   = $request->get_param( 'title' );
		$data    = $request->get_param( 'data' );

		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'iw_mega_menu' ) {
			return new WP_Error( 'not_found', 'Menu not found', [ 'status' => 404 ] );
		}

		if ( ! empty( $title ) ) {
			wp_update_post( [
				'ID'         => $post_id,
				'post_title' => sanitize_text_field( $title ),
			] );
		}

		if ( isset( $data ) ) {
			update_post_meta( $post_id, '_iw_mega_menu_data', wp_slash( wp_json_encode( $data ) ) );
		}

		return rest_ensure_response( [
			'id'      => $post_id,
			'message' => 'Menu updated',
		] );
	}

	public function delete_menu( $request ) {
		$post_id = $request['id'];
		$post    = get_post( $post_id );

		if ( ! $post || $post->post_type !== 'iw_mega_menu' ) {
			return new WP_Error( 'not_found', 'Menu not found', [ 'status' => 404 ] );
		}

		wp_delete_post( $post_id, true );

		return rest_ensure_response( [
			'id'      => $post_id,
			'message' => 'Menu deleted',
		] );
	}

	public function get_elementor_templates( $request ) {
		$templates = get_posts( [
			'post_type'      => 'elementor_library',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		] );

		$data = [];
		foreach ( $templates as $template ) {
			$data[] = [
				'id'    => $template->ID,
				'title' => $template->post_title,
			];
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Get all registered WordPress nav menus.
	 */
	public function get_wp_menus( $request ) {
		$menus = wp_get_nav_menus();
		$data  = [];
		foreach ( $menus as $menu ) {
			$data[] = [
				'id'    => $menu->term_id,
				'name'  => $menu->name,
				'count' => $menu->count,
			];
		}
		return rest_ensure_response( $data );
	}

	/**
	 * Get a WordPress nav menu's items as a tree compatible with the builder.
	 */
	public function get_wp_menu_tree( $request ) {
		$menu_id = (int) $request['id'];
		$items   = wp_get_nav_menu_items( $menu_id );

		if ( ! $items ) {
			return new WP_Error( 'not_found', 'Menu not found or empty', [ 'status' => 404 ] );
		}

		// Build a flat lookup keyed by menu item ID
		$flat = [];
		foreach ( $items as $item ) {
			$flat[ $item->ID ] = [
				'id'                  => 'wp_' . $item->ID,
				'item_label'          => $item->title,
				'item_link'           => [ 'url' => $item->url ],
				'item_description'    => $item->description ?: '',
				'item_badge'          => '',
				'item_icon'           => [ 'value' => '', 'library' => '' ],
				'item_icon_class'     => 'icon-acc-purple',
				'item_type'           => 'top', // will be overridden below
				'is_accordion'        => 'no',
				'sidebar_template_id' => '',
				'children'            => [],
				'_parent'             => (int) $item->menu_item_parent,
			];
		}

		// Build tree and assign depth-based types
		$tree = [];
		foreach ( $flat as $id => &$node ) {
			$parent_id = $node['_parent'];
			unset( $node['_parent'] );

			if ( $parent_id && isset( $flat[ $parent_id ] ) ) {
				// Determine depth by checking if parent is already a child
				$grandparent_type = $flat[ $parent_id ]['item_type'];
				if ( $grandparent_type === 'top' ) {
					$node['item_type'] = 'second';
				} else {
					$node['item_type'] = 'third';
				}
				$flat[ $parent_id ]['children'][] = &$node;
			} else {
				$node['item_type'] = 'top';
				$tree[] = &$node;
			}
		}
		unset( $node );

		return rest_ensure_response( [ 'items' => $tree, 'settings' => [] ] );
	}
}
