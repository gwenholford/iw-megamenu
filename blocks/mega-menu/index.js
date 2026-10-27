/**
 * IW Mega Menu block — editor script.
 *
 * Plain ES2017 against the wp.* globals, so the plugin needs no build step.
 * The block is dynamic: the editor shows a server-rendered preview and the
 * saved markup is null.
 *
 * @package IWMegaMenu
 */
( function ( wp ) {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { createElement: el, useEffect, useState } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { Disabled, PanelBody, Placeholder, SelectControl, Spinner, TextControl } = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const { __ } = wp.i18n;

	const BLOCK_NAME = 'iw-mega-menu/mega-menu';

	// Saved menus from the builder's REST API; null while loading.
	function useMenus() {
		const [ menus, setMenus ] = useState( null );
		useEffect( () => {
			wp.apiFetch( { path: '/iw-mega-menu/v1/menus' } )
				.then( ( result ) => setMenus( Array.isArray( result ) ? result : [] ) )
				.catch( () => setMenus( [] ) );
		}, [] );
		return menus;
	}

	function MenuPicker( { menus, value, onChange } ) {
		return el( SelectControl, {
			label: __( 'Menu', 'iw-mega-menu' ),
			value: value ? String( value ) : '',
			options: [ { label: __( '— Select a menu —', 'iw-mega-menu' ), value: '' } ].concat(
				menus.map( ( menu ) => ( { label: menu.title || '#' + menu.id, value: String( menu.id ) } ) )
			),
			onChange: ( next ) => onChange( parseInt( next, 10 ) || 0 ),
			__nextHasNoMarginBottom: true,
		} );
	}

	registerBlockType( BLOCK_NAME, {
		edit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps();
			const menus = useMenus();
			const setMenuId = ( menuId ) => setAttributes( { menuId } );

			let body;
			if ( menus === null ) {
				body = el( Placeholder, { icon: 'menu-alt3', label: __( 'IW Mega Menu', 'iw-mega-menu' ) }, el( Spinner ) );
			} else if ( ! attributes.menuId ) {
				body = el(
					Placeholder,
					{
						icon: 'menu-alt3',
						label: __( 'IW Mega Menu', 'iw-mega-menu' ),
						instructions: menus.length
							? __( 'Choose a menu built in the IW Mega Menu builder.', 'iw-mega-menu' )
							: __( 'No mega menus yet. Build one under IW Mega Menu in the dashboard.', 'iw-mega-menu' ),
					},
					menus.length ? el( MenuPicker, { menus, value: attributes.menuId, onChange: setMenuId } ) : null
				);
			} else {
				// Disabled keeps preview links and buttons from navigating inside the editor.
				body = el( Disabled, null, el( ServerSideRender, { block: BLOCK_NAME, attributes } ) );
			}

			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Menu settings', 'iw-mega-menu' ) },
						menus && menus.length ? el( MenuPicker, { menus, value: attributes.menuId, onChange: setMenuId } ) : null,
						el( TextControl, {
							label: __( 'Accessible label', 'iw-mega-menu' ),
							help: __( 'Names this navigation for screen readers. Defaults to "Main Navigation".', 'iw-mega-menu' ),
							value: attributes.label,
							onChange: ( label ) => setAttributes( { label } ),
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				body
			);
		},
		save: () => null,
	} );
} )( window.wp );
