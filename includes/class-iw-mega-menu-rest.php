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
	 * Listing menus only exposes titles, so anyone who can place the block may do it.
	 */
	public function check_list_permission() {
		return current_user_can( 'edit_posts' );
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
				'permission_callback' => [ $this, 'check_list_permission' ],
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
			'callback'            => [ $this, 'get_sidebar_templates' ],
			'permission_callback' => [ $this, 'check_permission' ],
		] );

		// Builder preview: the real front-end markup for an unsaved tree.
		register_rest_route( $namespace, '/preview', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'render_preview' ],
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

		// Legacy repeater migration: find IW Mega Menu widgets that still store
		// their items inline in Elementor, and convert them to builder trees.
		register_rest_route( $namespace, '/legacy-widgets', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_legacy_widgets' ],
			'permission_callback' => [ $this, 'check_permission' ],
		] );

		register_rest_route( $namespace, '/legacy-widgets/(?P<post_id>\d+)/(?P<widget_id>[A-Za-z0-9_-]+)', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_legacy_widget_tree' ],
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
		$decoded   = is_string( $menu_data ) && '' !== $menu_data ? json_decode( $menu_data, true ) : null;

		return rest_ensure_response( [
			'id'    => $post_id,
			'title' => $post->post_title,
			'data'  => is_array( $decoded ) ? $decoded : [ 'items' => [], 'settings' => [] ],
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

	/**
	 * Render the builder's working copy with the front-end renderer, so the
	 * preview is the same markup the site will output.
	 */
	public function render_preview( $request ) {
		$data     = $request->get_param( 'data' );
		$items    = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : [];
		$settings = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : [];

		return rest_ensure_response( [
			'html' => IW_Mega_Menu_Renderer::render( $items, [
				'uid'        => 'preview',
				'label'      => __( 'Preview Navigation', 'iw-mega-menu' ),
				'sidebar_id' => $settings['global_sidebar_template_id'] ?? '',
				'preview'    => true,
			] ),
		] );
	}

	/**
	 * Content a dropdown sidebar can show: patterns, plus Elementor templates when Elementor is active.
	 */
	public function get_sidebar_templates( $request ) {
		return rest_ensure_response( IW_Mega_Menu_Renderer::get_sidebar_sources() );
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

	/**
	 * List every IW Mega Menu widget that still carries legacy repeater items.
	 */
	public function get_legacy_widgets( $request ) {
		global $wpdb;

		// Broad LIKE first (Elementor's JSON escaping varies); each hit is then
		// verified properly by decoding the data below.
		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT pm.post_id
				   FROM {$wpdb->postmeta} pm
				   INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				  WHERE pm.meta_key = '_elementor_data'
				    AND pm.meta_value LIKE %s
				    AND p.post_type != 'revision'
				    AND p.post_status IN ( 'publish', 'draft', 'private', 'pending' )
				  LIMIT 300",
				'%' . $wpdb->esc_like( 'iw-mega-menu' ) . '%'
			)
		);

		$found = [];
		foreach ( $post_ids as $post_id ) {
			foreach ( $this->find_legacy_widgets_in_post( (int) $post_id ) as $widget ) {
				$items = isset( $widget['settings']['menu_items'] ) && is_array( $widget['settings']['menu_items'] )
					? $widget['settings']['menu_items']
					: [];

				if ( empty( $items ) ) {
					continue; // Already migrated, or never populated.
				}

				$labels = [];
				foreach ( array_slice( $items, 0, 3 ) as $item ) {
					if ( ! empty( $item['item_label'] ) ) {
						$labels[] = $item['item_label'];
					}
				}

				$found[] = [
					'post_id'    => (int) $post_id,
					'post_title' => get_the_title( $post_id ) ? get_the_title( $post_id ) : sprintf( '#%d', $post_id ),
					'post_type'  => get_post_type( $post_id ),
					'widget_id'  => $widget['id'],
					'item_count' => count( $items ),
					'preview'    => implode( ', ', $labels ),
				];
			}
		}

		return rest_ensure_response( $found );
	}

	/**
	 * Convert one legacy widget's repeater items into a builder-shaped tree.
	 */
	public function get_legacy_widget_tree( $request ) {
		$post_id   = (int) $request['post_id'];
		$widget_id = (string) $request['widget_id'];

		foreach ( $this->find_legacy_widgets_in_post( $post_id ) as $widget ) {
			if ( $widget['id'] !== $widget_id ) {
				continue;
			}

			$items = isset( $widget['settings']['menu_items'] ) && is_array( $widget['settings']['menu_items'] )
				? $widget['settings']['menu_items']
				: [];

			return rest_ensure_response( [
				'items'    => $this->convert_legacy_items( $items ),
				'settings' => [],
			] );
		}

		return new WP_Error( 'not_found', 'Legacy widget not found', [ 'status' => 404 ] );
	}

	/**
	 * Decode a post's Elementor data and return every iw-mega-menu widget in it.
	 */
	private function find_legacy_widgets_in_post( $post_id ) {
		$raw = get_post_meta( $post_id, '_elementor_data', true );

		if ( is_string( $raw ) ) {
			$raw = json_decode( $raw, true );
		}

		if ( ! is_array( $raw ) ) {
			return [];
		}

		$out = [];
		$this->collect_legacy_widgets( $raw, $out );

		return $out;
	}

	/**
	 * Walk Elementor's nested element tree collecting our widgets.
	 */
	private function collect_legacy_widgets( $elements, &$out ) {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			if ( isset( $element['widgetType'] ) && 'iw-mega-menu' === $element['widgetType'] ) {
				$out[] = [
					'id'       => isset( $element['id'] ) ? (string) $element['id'] : '',
					'settings' => isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : [],
				];
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$this->collect_legacy_widgets( $element['elements'], $out );
			}
		}
	}

	/**
	 * Flat repeater (parent_id = array index) -> nested builder tree.
	 *
	 * Mirrors the parent/child pairing in IW_Mega_Menu_Widget::render() so an
	 * import reproduces what the widget was rendering. One deliberate difference:
	 * the widget also pushes any item with parent_id 0 into the root list even
	 * when it was already attached to a parent, so such an item renders twice.
	 * Importing that duplicate would be surprising, so an item that finds a
	 * parent is nested there only.
	 */
	private function convert_legacy_items( $items ) {
		$items = array_values( $items );
		$nodes = [];

		foreach ( $items as $idx => $item ) {
			$type = ! empty( $item['item_type'] ) ? $item['item_type'] : 'top';

			$nodes[ $idx ] = [
				'id'                  => 'legacy_' . $idx . '_' . substr( md5( (string) wp_json_encode( $item ) . $idx ), 0, 8 ),
				'item_label'          => isset( $item['item_label'] ) ? (string) $item['item_label'] : '',
				'item_link'           => [ 'url' => isset( $item['item_link']['url'] ) ? (string) $item['item_link']['url'] : '' ],
				'item_description'    => isset( $item['item_description'] ) ? (string) $item['item_description'] : '',
				'item_badge'          => isset( $item['item_badge'] ) ? (string) $item['item_badge'] : '',
				'item_icon'           => [
					'value'   => isset( $item['item_icon']['value'] ) ? $item['item_icon']['value'] : '',
					'library' => isset( $item['item_icon']['library'] ) ? $item['item_icon']['library'] : '',
				],
				'item_icon_class'     => ! empty( $item['item_icon_class'] ) ? $item['item_icon_class'] : 'icon-acc-purple',
				'item_type'           => $type,
				'is_accordion'        => ( isset( $item['is_accordion'] ) && 'yes' === $item['is_accordion'] ) ? 'yes' : 'no',
				'sidebar_template_id' => isset( $item['sidebar_template_id'] ) ? (string) $item['sidebar_template_id'] : '',
				'children'            => [],
			];
		}

		// Pass 1: work out who nests under whom, by index.
		$children_of = [];
		$roots       = [];

		foreach ( $nodes as $idx => $node ) {
			$parent = isset( $items[ $idx ]['parent_id'] ) ? (int) $items[ $idx ]['parent_id'] : 0;
			$type   = $node['item_type'];

			$has_parent = $parent !== $idx && isset( $nodes[ $parent ] );

			if ( $has_parent && 'third' === $type && 'second' === $nodes[ $parent ]['item_type'] ) {
				$children_of[ $parent ][] = $idx;
			} elseif ( $has_parent && 'second' === $type && 'top' === $nodes[ $parent ]['item_type'] ) {
				$children_of[ $parent ][] = $idx;
			} else {
				$roots[] = $idx;
			}
		}

		// Pass 2: materialise the nested structure depth-first.
		$build = function ( $idx ) use ( &$build, &$nodes, $children_of ) {
			$node = $nodes[ $idx ];
			if ( ! empty( $children_of[ $idx ] ) ) {
				foreach ( $children_of[ $idx ] as $child_idx ) {
					$node['children'][] = $build( $child_idx );
				}
			}
			// A section with links only renders them as an accordion, so make that
			// explicit rather than importing links that would silently not display.
			if ( 'second' === $node['item_type'] && ! empty( $node['children'] ) ) {
				$node['is_accordion'] = 'yes';
			}
			return $node;
		};

		$tree = [];
		foreach ( $roots as $idx ) {
			$tree[] = $build( $idx );
		}

		return $tree;
	}
}
