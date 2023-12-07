<?php

function bellows_get_configuration_fields( $config_id ){

	$settings = array(

		//Integration
		10 => array(
			'name'	=> 'header_integration',
			'label'	=> __( 'Integration' , 'bellows' ),
			//'desc'	=> __( '', 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'integration',
		),

		20 => array(
			'name'	=> 'php',
			'label'	=> __( 'Integration Code' , 'bellows' ),
			'desc'	=> array( 'func' => 'bellows_integration_code_ui' , 'args' => $config_id ), //$integration_code,
			'type'	=> 'func_html',
			'group'	=> 'integration'
		),


		//Basic

		50 => array(
			'name'	=> 'header_basic',
			'label'	=> __( 'Basic Configuration' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'basic',
		),


		60 => array(
			'name'	=> 'skin',
			'label'	=> __( 'Skin' , 'bellows' ),
			'type'	=> 'select',
			'desc' => __( 'The skin applies colors and other styles to the menu.  If you disable the skin, you should provide your own custom skin.' , 'bellows' ),
			//'options'	=> array(),
			'options' => 'bellows_get_skin_ops',
			'default' => 'blue-material',
			'group'	=> 'basic',
			'customizer' => true,
			'customizer_section' => 'general',
		),


		70 => array(
			'name'	=> 'folding',
			'label'	=> __( 'Accordion Folding' , 'bellows' ),
			'type'	=> 'radio_advanced',
			'desc'	=> __( 'Determine whether only one submenu can be open at a time.' , 'bellows' ),
			'options'=> array(
				'group' => array(
					'multiple'	=> array(
						'name' => __( 'Multiple' , 'bellows' ),
						'desc' => __( 'Multiple submenus can be expanded at once' , 'bellows' ),
					),
					'single'	=> array(
						'name' => __( 'Single', 'bellows' ),
						'desc' => __( 'Only one submenu can be expanded at once', 'bellows' ),
					),
				)
			),
			'default'=> 'multiple',
			'group'	=> 'basic',

			'customizer'	=> true,
			'customizer_section' => 'general',
		),

		80 => array(
			'name'	=> 'current_expansion',
			'label'	=> __( 'Expand Current Submenu' , 'bellows' ),
			'type'	=> 'checkbox',
			'desc'	=> __( 'Automatically expand the submenu of the current menu item' , 'bellows' ),
			'default'=> 'off',
			'group'	=> 'basic',

			'customizer'	=> true,
			'customizer_section' => 'general',
		),

		100 => array(
			'name'	=> 'current_associated_term',
			'label'	=> __( 'Current Associated Term Highlighting' , 'bellows' ),
			'type'	=> 'checkbox',
			'desc'	=> __( 'Term items within the menu will be highlighted as current when they match the current post\'s associated terms.  Mainly affects auto-populated menus.' , 'bellows' ),
			'default'=> 'on',
			'group'	=> 'basic',

			'customizer'	=> true,
			'customizer_section' => 'general',
		),



		// MOBILE

		150 => array(
			'name' => 'header_mobile',
			'label' => __( 'Responsive &amp; Mobile', 'bellows' ),
			'type' => 'header',
			'group' => 'mobile',
		),

		160 => array(
			'name' => 'mobile_collapse',
			'label' => __( 'Collapse menu on mobile', 'bellows' ),
			'desc' => __( 'Below the mobile breakpoint, display a toggle to reveal the menu' , 'bellows' ),
			'type' => 'checkbox',
			'default' => 'off',
			'group' => 'mobile',
		),
		165 => array(
			'name' => 'toggle_text',
			'label' => __( 'Toggle text', 'bellows' ),
			'desc' => __( 'Text to include in the toggle.  Use a single space for none.  Use {menu_name} for the menu name.  Defaults to translatable Menu string' , 'bellows' ),
			'type' => 'text',
			'default' => '',
			'group' => 'mobile',
		),


		//LAYOUT

		200 => array(
			'name'	=> 'header_layout',
			'label'	=> __( 'Layout &amp; Position' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'layout',
		),

		210 => array(
			'name'	=> 'menu_align',
			'label'	=> __( 'Menu Alignment' , 'bellows' ),
			'type'	=> 'radio',
			'desc'	=> __( 'Generally it is best to place your menu inside a grid column in your layout and leave this at Full Width, so that the menu will respond to your layout container.  If you choose any other option (Left, Right, Center), you will likely want to set a width below as well.' , 'bellows' ),
			'options'=> array(
				'full'		=> __( 'Full Width' , 'bellows' ),
				'left'		=> __( 'Left', 'bellows' ),
				'right'	 	=> __( 'Right', 'bellows' ),
				'center'	=> __( 'Center (width required)' , 'bellows' ),
			),
			'default'=> 'full',
			'group'	=> 'layout',

			'customizer'	=> true,
			'customizer_section' => 'general',

		),

		220 => array(
			'name'	=> 'menu_width',
			'label'	=> __( 'Menu Width' , 'bellows' ),
			'type'	=> 'text',
			'default'=> '',
			'desc'	=> __( 'A width should be set if you choose a Left, Right, or Center Menu Alignment.  Otherwise your menu may change width when open and closed.  To use the Center alignment, a width is required.  Remember, the menu can never be larger than its container, no matter how big you make this value.' , 'bellows' ),
			'group'	=> 'layout',
			'custom_style' => 'menu_width',
			'customizer'	=> true,
			'customizer_section' => 'general',
		),




		//MARKUP

		400 => array(
			'name'	=> 'header_html',
			'label'	=> __( 'Markup Structure' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'markup',
		),

		410 => array(
			'name'	=> 'container_tag',
			'label'	=> __( 'Container Tag' , 'bellows' ),
			'type'	=> 'radio',
			'desc'	=> __( 'You can make the wrapper of the menu a nav or div element.' , 'bellows' ),
			'options'=> array(
				'nav'		=> '&lt;nav&gt;',
				'div'		=> '&lt;div&gt;',
			),
			'default'=> 'nav',
			'group'	=> 'markup',

			'customizer'	=> true,
			'customizer_section' => 'general',

		),



		//Font
		500 => array(
			'name'	=> 'header_font',
			'label'	=> __( 'Font' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'font',
		),
		510	=> array(
			'name'	=> 'font_family',
			'label'	=> __( 'Font Family' , 'bellows' ),
			'desc'	=> __( 'A font family stack, e.g. "Helvetica", "Roboto", sans-serif' , 'bellows' ),
			'type'	=> 'text',
			'group'	=> 'font',

			'customizer' => true,
			'customizer_section' => 'font',
			'custom_style'	=> 'font_family'
		),


	);

	return apply_filters( 'bellows_configuration_fields' , $settings , $config_id );
}
