<?php
/**
 * IW Mega Menu Renderer
 *
 * Builds the menu markup for every placement — the block, the shortcode, the
 * template tag and the optional Elementor widget — so none of them depend on a
 * page builder.
 *
 * @package IWMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class IW_Mega_Menu_Renderer {

	/**
	 * Menus rendered on this request, for unique element IDs.
	 *
	 * @var int
	 */
	private static $instance_count = 0;

	/**
	 * Sidebar nesting depth. A sidebar pattern that itself contains a mega menu
	 * would otherwise render forever.
	 *
	 * @var int
	 */
	private static $sidebar_depth = 0;

	/**
	 * Load a saved menu's items and settings.
	 *
	 * @param int $menu_id Mega menu post ID.
	 * @return array|null [ 'items' => array, 'settings' => array ], or null if missing.
	 */
	public static function get_menu_data( $menu_id ) {
		$post = get_post( absint( $menu_id ) );
		if ( ! $post || 'iw_mega_menu' !== $post->post_type ) {
			return null;
		}

		$raw  = get_post_meta( $post->ID, '_iw_mega_menu_data', true );
		$data = is_string( $raw ) && '' !== $raw ? json_decode( $raw, true ) : null;
		if ( ! is_array( $data ) ) {
			return null;
		}

		return [
			'items'    => isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : [],
			'settings' => isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : [],
		];
	}

	/**
	 * Render a saved menu by ID.
	 *
	 * @param int   $menu_id Mega menu post ID.
	 * @param array $args    See render().
	 * @return string Markup, or '' if the menu is missing or empty.
	 */
	public static function render_menu( $menu_id, $args = [] ) {
		$data = self::get_menu_data( $menu_id );
		if ( ! $data ) {
			return '';
		}

		$args = is_array( $args ) ? $args : [];
		if ( ! empty( $data['settings']['global_sidebar_template_id'] ) ) {
			$args['sidebar_id'] = $data['settings']['global_sidebar_template_id'];
		}

		return self::render( $data['items'], $args );
	}

	/**
	 * Convert the legacy Elementor repeater (flat list, parent_id = index) into
	 * the nested tree render() expects.
	 *
	 * @param array $items Repeater rows.
	 * @return array
	 */
	public static function build_legacy_tree( $items ) {
		$items  = is_array( $items ) ? $items : [];
		$lookup = [];
		$tree   = [];

		foreach ( $items as $idx => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$item['children']  = [];
			$item['item_type'] = $item['item_type'] ?? '';
			$item['parent_id'] = $item['parent_id'] ?? '';
			$lookup[ $idx ]    = $item;
		}

		// Attach third-level to second-level, and second-level to top-level.
		foreach ( $lookup as $idx => &$item ) {
			if ( $item['item_type'] === 'third' && isset( $lookup[ $item['parent_id'] ] ) && $lookup[ $item['parent_id'] ]['item_type'] === 'second' ) {
				$lookup[ $item['parent_id'] ]['children'][ $idx ] = &$item;
			}
		}
		foreach ( $lookup as $idx => &$item ) {
			if ( $item['item_type'] === 'second' && isset( $lookup[ $item['parent_id'] ] ) && $lookup[ $item['parent_id'] ]['item_type'] === 'top' ) {
				$lookup[ $item['parent_id'] ]['children'][ $idx ] = &$item;
			}
		}
		foreach ( $lookup as $idx => &$item ) {
			if ( $item['item_type'] === 'top' || (int) $item['parent_id'] === 0 ) {
				$tree[ $idx ] = &$item;
			}
		}
		unset( $item );

		return $tree;
	}

	/**
	 * Render a menu tree.
	 *
	 * @param array $tree Nested items (top → second → third).
	 * @param array $args {
	 *     @type string     $uid        Suffix for element IDs. Generated when empty.
	 *     @type string     $label      Accessible name for the navigation.
	 *     @type int|string $sidebar_id Sidebar shown for top items that don't set their own.
	 * }
	 * @return string
	 */
	public static function render( $tree, $args = [] ) {
		$tree = is_array( $tree ) ? array_filter( $tree, 'is_array' ) : [];
		if ( empty( $tree ) ) {
			return '';
		}

		$args = wp_parse_args( $args, [
			'uid'        => '',
			'label'      => '',
			'sidebar_id' => '',
		] );

		$uid   = '' !== (string) $args['uid'] ? sanitize_html_class( (string) $args['uid'] ) : 'menu-' . ( ++self::$instance_count );
		$label = '' !== (string) $args['label'] ? (string) $args['label'] : __( 'Main Navigation', 'iw-mega-menu' );

		$menu_id         = 'iw-mega-menu-' . $uid;
		$mobile_panel_id = 'iw-mobile-panel-' . $uid;
		$global_sidebar  = $args['sidebar_id'];

		self::enqueue_assets();

		ob_start();
		?>
		<div class="iw-mega-menu-wrapper">
			<nav class="iw-mega-menu__nav" id="<?php echo esc_attr( $menu_id ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
				<ul class="iw-mega-menu__top-level">
					<?php foreach ( $tree as $i => $top ) :
						$children            = self::children_of( $top );
						$dropdown_id         = $menu_id . '-dropdown-' . $i;
						$sidebar_template_id = ! empty( $top['sidebar_template_id'] ) ? $top['sidebar_template_id'] : $global_sidebar;
						$has_sidebar         = ! empty( $sidebar_template_id );
						$dropdown_class      = 'iw-mega-menu__dropdown ' . ( $has_sidebar ? 'iw-mega-menu__dropdown--with-sidebar' : 'iw-mega-menu__dropdown--no-sidebar' );
					?>
						<li class="iw-mega-menu__top-item">
							<?php if ( $children ) : ?>
								<button class="iw-mega-menu__top-link" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="<?php echo esc_attr( $dropdown_id ); ?>" data-dropdown-index="<?php echo esc_attr( $i ); ?>">
									<?php echo self::item_content( $top ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in item_content(). ?>
									<?php echo self::item_description( $top ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</button>

								<div class="<?php echo esc_attr( $dropdown_class ); ?>" id="<?php echo esc_attr( $dropdown_id ); ?>" data-dropdown-index="<?php echo esc_attr( $i ); ?>" tabindex="-1" hidden>
									<div class="iw-mega-menu__dropdown-main">
										<?php
										$non_accordion = [];
										$accordion     = [];
										foreach ( $children as $j => $second ) {
											if ( isset( $second['is_accordion'] ) && 'yes' === $second['is_accordion'] ) {
												$accordion[ $j ] = $second;
											} else {
												$non_accordion[] = $second;
											}
										}

										// Two columns of up to four section links each.
										$columns = array_pad( array_slice( array_chunk( $non_accordion, 4 ), 0, 2 ), 2, [] );
										?>
										<?php if ( $non_accordion ) : ?>
											<div class="iw-mega-menu__second-list">
												<?php foreach ( $columns as $column ) : ?>
													<div class="iw-mega-menu__second-col">
														<?php foreach ( $column as $second ) :
															$second_url = self::item_url( $second );
														?>
															<?php if ( $second_url ) : ?>
																<a class="iw-mega-menu__section-label" href="<?php echo esc_url( $second_url ); ?>">
															<?php else : ?>
																<div class="iw-mega-menu__section-label" role="heading" aria-level="3">
															<?php endif; ?>
																<?php echo self::item_content( $second ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
																<?php echo self::item_description( $second ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
															<?php echo $second_url ? '</a>' : '</div>'; ?>
														<?php endforeach; ?>
													</div>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>

										<?php foreach ( $accordion as $j => $second ) :
											$section_id = $dropdown_id . '-section-' . $j;
											// Three columns of up to two links each.
											$third_columns = array_pad( array_slice( array_chunk( self::children_of( $second ), 2 ), 0, 3 ), 3, [] );
										?>
											<div class="iw-mega-menu__section iw-mega-menu__accordion">
												<button class="iw-mega-menu__section-label iw-mega-menu__accordion-toggle" type="button" aria-controls="<?php echo esc_attr( $section_id ); ?>" aria-expanded="false">
													<?php echo self::item_content( $second ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
													<?php echo self::item_description( $second ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
													<span class="iw-mega-menu__caret" aria-hidden="true"></span>
												</button>

												<div class="iw-mega-menu__section-content" id="<?php echo esc_attr( $section_id ); ?>" hidden>
													<div class="iw-mega-menu__third-list">
														<?php foreach ( $third_columns as $column ) : ?>
															<div class="iw-mega-menu__third-col">
																<?php foreach ( $column as $third ) : ?>
																	<a class="iw-mega-menu__third-link" href="<?php echo esc_url( self::item_url( $third, '#' ) ); ?>">
																		<?php echo self::item_content( $third, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
																	</a>
																<?php endforeach; ?>
															</div>
														<?php endforeach; ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>

									<?php if ( $has_sidebar ) : ?>
										<aside class="iw-mega-menu-sidebar">
											<?php echo self::render_sidebar( $sidebar_template_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- rendered block/template content. ?>
										</aside>
									<?php endif; ?>
								</div>

								<div class="iw-mega-menu__dropdown-buffer" data-dropdown-index="<?php echo esc_attr( $i ); ?>" aria-hidden="true" hidden></div>
							<?php else :
								$url = self::item_url( $top );
							?>
								<?php if ( $url ) : ?>
									<a class="iw-mega-menu__top-link" href="<?php echo esc_url( $url ); ?>">
								<?php else : ?>
									<button class="iw-mega-menu__top-link" type="button">
								<?php endif; ?>
									<?php echo self::item_content( $top ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php echo self::item_description( $top ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<?php echo $url ? '</a>' : '</button>'; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<button class="iw-mega-menu__mobile-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $mobile_panel_id ); ?>">
				<span class="iw-mega-menu__mobile-button-text"><?php esc_html_e( 'Menu', 'iw-mega-menu' ); ?></span>
				<span class="iw-mega-menu__mobile-button-icon" aria-hidden="true"></span>
			</button>

			<nav class="iw-mega-menu__mobile-panel" id="<?php echo esc_attr( $mobile_panel_id ); ?>" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'iw-mega-menu' ); ?>" hidden tabindex="-1">
				<div class="iw-mega-menu__mobile-topbar">
					<button class="iw-mega-menu__mobile-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'iw-mega-menu' ); ?>">
						<span class="iw-mega-menu__mobile-button-text"><?php esc_html_e( 'Menu', 'iw-mega-menu' ); ?></span>
						<span class="iw-mega-menu__mobile-button-icon" aria-hidden="true"></span>
					</button>
				</div>
				<ul class="iw-mega-menu__mobile-list">
					<?php foreach ( $tree as $i => $top ) :
						$children       = self::children_of( $top );
						$top_sublist_id = 'mobile-sublist-' . $uid . '-' . $i;
					?>
						<li class="iw-mega-menu__mobile-item">
							<?php if ( $children ) : ?>
								<button class="iw-mega-menu__mobile-link" type="button" data-mobile-index="<?php echo esc_attr( $i ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $top_sublist_id ); ?>">
									<?php echo self::item_content( $top, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<span class="iw-mega-menu__mobile-caret" aria-hidden="true"></span>
								</button>

								<ul class="iw-mega-menu__mobile-sublist" id="<?php echo esc_attr( $top_sublist_id ); ?>" hidden>
									<?php foreach ( $children as $j => $second ) :
										$third_items       = self::children_of( $second );
										$second_sublist_id = $top_sublist_id . '-' . $j;
									?>
										<li class="iw-mega-menu__mobile-item">
											<?php if ( $third_items ) : ?>
												<button class="iw-mega-menu__mobile-link" type="button" data-mobile-index="<?php echo esc_attr( $i . '-' . $j ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $second_sublist_id ); ?>">
													<?php echo self::item_content( $second, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
													<span class="iw-mega-menu__mobile-caret" aria-hidden="true"></span>
												</button>

												<ul class="iw-mega-menu__mobile-sublist" id="<?php echo esc_attr( $second_sublist_id ); ?>" hidden>
													<?php foreach ( $third_items as $third ) : ?>
														<li class="iw-mega-menu__mobile-item">
															<a class="iw-mega-menu__mobile-link" href="<?php echo esc_url( self::item_url( $third, '#' ) ); ?>">
																<?php echo self::item_content( $third, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
															</a>
														</li>
													<?php endforeach; ?>
												</ul>
											<?php else : ?>
												<a class="iw-mega-menu__mobile-link" href="<?php echo esc_url( self::item_url( $second, '#' ) ); ?>">
													<?php echo self::item_content( $second, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
												</a>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<a class="iw-mega-menu__mobile-link" href="<?php echo esc_url( self::item_url( $top, '#' ) ); ?>">
									<?php echo self::item_content( $top, false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php if ( ! empty( $global_sidebar ) ) : ?>
					<div class="iw-mega-menu__mobile-sidebar">
						<?php echo self::render_sidebar( $global_sidebar ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<?php endif; ?>
			</nav>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Content a sidebar can show: patterns always, Elementor templates when
	 * Elementor is active.
	 *
	 * @return array[] Each [ 'id' => int, 'title' => string, 'type' => string ].
	 */
	public static function get_sidebar_sources() {
		$types = [ 'wp_block' => __( 'Pattern', 'iw-mega-menu' ) ];
		if ( self::elementor_active() ) {
			$types['elementor_library'] = __( 'Elementor', 'iw-mega-menu' );
		}

		$sources = [];
		foreach ( $types as $post_type => $type_label ) {
			$ids = get_posts( [
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			] );
			foreach ( $ids as $id ) {
				$title     = get_the_title( $id );
				$sources[] = [
					'id'    => (int) $id,
					'title' => '' !== $title ? $title : sprintf( '#%d', $id ),
					'type'  => $type_label,
				];
			}
		}

		return $sources;
	}

	/**
	 * Render a sidebar's content.
	 *
	 * @param int|string $template_id A pattern (wp_block) or Elementor template ID.
	 * @return string
	 */
	public static function render_sidebar( $template_id ) {
		$template_id = absint( $template_id );
		if ( ! $template_id || self::$sidebar_depth > 0 ) {
			return '';
		}

		$post = get_post( $template_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return '';
		}

		self::$sidebar_depth++;
		$html = '';
		if ( 'wp_block' === $post->post_type ) {
			// Through core's own Pattern block, so embeds and shortcodes behave as anywhere else.
			$html = do_blocks( '<!-- wp:block {"ref":' . $template_id . '} /-->' );
		} elseif ( 'elementor_library' === $post->post_type && self::elementor_active() ) {
			$html = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
		}
		self::$sidebar_depth--;

		/**
		 * Filter a mega menu sidebar's rendered content.
		 *
		 * @param string  $html Rendered content.
		 * @param WP_Post $post The sidebar's source post.
		 */
		return (string) apply_filters( 'iw_mega_menu_sidebar_content', $html, $post );
	}

	/**
	 * Whether Elementor is loaded.
	 *
	 * @return bool
	 */
	public static function elementor_active() {
		return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Enqueue the front-end stylesheet and script.
	 */
	public static function enqueue_assets() {
		wp_enqueue_style( 'iw-mega-menu' );
		wp_enqueue_script( 'iw-mega-menu' );
	}

	/**
	 * An item's valid child items.
	 *
	 * @param array $item Menu item.
	 * @return array
	 */
	private static function children_of( $item ) {
		return isset( $item['children'] ) && is_array( $item['children'] )
			? array_filter( $item['children'], 'is_array' )
			: [];
	}

	/**
	 * An item's link URL.
	 *
	 * @param array  $item     Menu item.
	 * @param string $fallback Returned when the item has no URL.
	 * @return string
	 */
	private static function item_url( $item, $fallback = '' ) {
		return isset( $item['item_link']['url'] ) && is_string( $item['item_link']['url'] ) && '' !== $item['item_link']['url']
			? $item['item_link']['url']
			: $fallback;
	}

	/**
	 * Icon, label and badge for an item.
	 *
	 * @param array $item      Menu item.
	 * @param bool  $show_icon Whether to include the item's icon.
	 * @return string
	 */
	private static function item_content( $item, $show_icon = true ) {
		$html = '<span class="iw-mega-menu__link-content">';
		if ( $show_icon ) {
			$html .= self::render_icon( $item['item_icon'] ?? null, $item['item_icon_class'] ?? '' );
		}
		$html .= '<span class="iw-mega-menu__label">' . esc_html( $item['item_label'] ?? '' ) . '</span>';
		if ( ! empty( $item['item_badge'] ) ) {
			$html .= '<span class="iw-mega-menu__badge">' . esc_html( $item['item_badge'] ) . '</span>';
		}
		return $html . '</span>';
	}

	/**
	 * An item's description line.
	 *
	 * @param array $item Menu item.
	 * @return string
	 */
	private static function item_description( $item ) {
		return ! empty( $item['item_description'] )
			? '<span class="iw-mega-menu__description">' . esc_html( $item['item_description'] ) . '</span>'
			: '';
	}

	/**
	 * Render an item icon.
	 *
	 * Builder icons are Font Awesome 6 class strings and are rendered directly.
	 * Icons from the legacy Elementor repeater (other icon libraries, uploaded
	 * SVGs) go through Elementor's Icons_Manager when Elementor is active.
	 *
	 * @param array|null $icon        [ 'value' => string|array, 'library' => string ].
	 * @param string     $color_class One of the icon-acc-* color classes.
	 * @return string
	 */
	private static function render_icon( $icon, $color_class ) {
		if ( ! is_array( $icon ) || empty( $icon['value'] ) ) {
			return '';
		}

		$value = $icon['value'];
		$inner = '';

		if ( is_string( $value ) && preg_match( '/^fa-(solid|regular|brands)\b/', $value ) ) {
			$inner = self::font_awesome_icon( $value );
		} elseif ( self::elementor_active() && class_exists( '\Elementor\Icons_Manager' ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
			$inner = ob_get_clean();
		} elseif ( is_array( $value ) && ! empty( $value['url'] ) ) {
			$inner = '<img src="' . esc_url( $value['url'] ) . '" alt="" />';
		} elseif ( is_string( $value ) ) {
			$inner = self::font_awesome_icon( $value );
		}

		if ( '' === $inner ) {
			return '';
		}

		$color_class = '' !== (string) $color_class ? (string) $color_class : 'icon-acc-purple';

		return '<span class="iw-mega-menu__icon ' . esc_attr( $color_class ) . '" aria-hidden="true">' . $inner . '</span>';
	}

	/**
	 * A Font Awesome <i>, loading Font Awesome on first use.
	 *
	 * @param string $classes Icon classes, e.g. "fa-solid fa-house".
	 * @return string
	 */
	private static function font_awesome_icon( $classes ) {
		wp_enqueue_style( 'iw-mega-menu-font-awesome' );
		return '<i class="' . esc_attr( $classes ) . '" aria-hidden="true"></i>';
	}
}
