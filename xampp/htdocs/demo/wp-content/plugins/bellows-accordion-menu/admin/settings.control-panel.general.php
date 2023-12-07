<?php

add_filter( 'bellows_settings_panel_sections' , 'bellows_general_settings_tab' , 80 );
function bellows_general_settings_tab( $sections ){
	$prefix = BELLOWS_PREFIX;
	$section = array(
		'id'	=> $prefix.'general',
		'title'	=> __( 'General Settings' , 'bellows' ),
		'sub_sections'	=> array(

			'custom_css'=> array(
				'title'	=> __( 'Custom CSS' , 'bellows' ),
			),
			'assets'	=> array(
				'title'	=> __( 'Assets' , 'bellows' ),
			),
			'misc'=> array(
				'title'	=> __( 'Miscellaneous' , 'bellows' ),
			),
			// 'script_config'=> array(
			// 	'title'	=> __( 'Script Configuration' , 'bellows' ),
			// ),
			// 'misc'=> array(
			// 	'title'	=> __( 'Miscellaneous' , 'bellows' ),
			// ),
			// 'maintenance'=> array(
			// 	'title'	=> __( 'Maintenance', 'bellows' ),
			// ),

		),
	);

	$section = apply_filters( 'bellows_general_settings_sections' , $section );

	$sections[] = $section;

	return $sections;
}


add_filter( 'bellows_settings_panel_fields' , 'bellows_settings_panel_fields_general' );
function bellows_settings_panel_fields_general( $fields ){


	$fields[BELLOWS_PREFIX.'general'] = array(

		/* Custom Styles */
		10 => array(
			'name'	=> 'header_custom_styles',
			'label'	=> __( 'Custom Styles' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'custom_css',
		),


		20 => array(
			'name'	=> 'custom_tweaks',
			'label'	=> __( 'Custom CSS Tweaks' , 'bellows' ),
			'type'	=> 'textarea',
			'desc'	=> __( 'These styles will be added into the &lt;head&gt; of your site.', 'bellows' ),
			'group'	=> 'custom_css',
			'sanitize_callback' => 'bellows_allow_html',
		),

		/*
		30 => array(
			'name'	=> 'custom_tweaks_mobile',
			'label'	=> __( 'Custom CSS Tweaks - Mobile' , 'bellows' ),
			'desc'	=> __( 'Styles to apply below the responsive breakpoint only.' , 'bellows' ),
			'type'	=> 'textarea',
			'group'	=> 'custom_css',
			'sanitize_callback' => 'bellows_allow_html',
		),

		40 => array(
			'name'	=> 'custom_tweaks_desktop',
			'label'	=> __( 'Custom CSS Tweaks - Desktop' , 'bellows' ),
			'desc'	=> __( 'Styles to apply above the responsive breakpoint only.' , 'bellows' ),
			'type'	=> 'textarea',
			'group'	=> 'custom_css',
			'sanitize_callback' => 'bellows_allow_html',
		),

		*/



		50 => array(
			'name'	=> 'header_assets',
			'label'	=> __( 'Assets' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'assets',
		),

		60 => array(
			'name' 		=> 'load_fontawesome',
			'label' 	=> __( 'Load Font Awesome', 'bellows' ),
			'desc' 		=> __( 'If you are already loading the latest version of Font Awesome 4 elsewhere in your setup, you can disable this.', 'bellows' ),
			'type' 		=> 'checkbox',
			'default' 	=> 'on',
			'group'		=> 'assets',
		),




		/** Script Configuration **/
		/*
		170 => array(
			'name'	=> 'header_script_config',
			'label'	=> __( 'Script Configuration' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'script_config',
		),

		175	=> array(
			'name'	=> 'touch_off_close',
			'label'	=> __( 'Touch-off Close' , 'bellows' ),
			'desc'	=> __( 'Close all submenus when the user clicks or touches off of the menu.  If you disable this, make sure you leave your users with another way to close the submenu.' , 'bellows' ),
			'type'	=> 'checkbox',
			'default'=> 'on',
			'group'	=> 'script_config',
		),

		210 => array(
			'name'	=> 'scrollto_offset',
			'label' => __( 'ScrollTo Offset' , 'bellows' ),
			'desc'	=> __( 'Pixel offset to leave when scrolling.', 'bellows' ),
			'type'	=> 'text',
			'default'=> 50,
			'group'	=> 'script_config',
		),

		215 => array(
			'name'	=> 'scrollto_duration',
			'label' => __( 'ScrollTo Duration' , 'bellows' ),
			'desc'	=> __( 'Duration of the scroll animation in milliseconds.  The actual speed will be determined by the distance that needs to be traveled.  <em>1000</em> is 1 second.', 'bellows' ),
			'type'	=> 'text',
			'default'=> 1000,
			'group'	=> 'script_config',
		),

		217 => array(
			'name'	=> 'collapse_after_scroll',
			'label'	=> __( 'Collapse Menu after Scroll To (Mobile)' , 'bellows' ),
			'desc'	=> __( 'When a ScrollTo-enabled item is clicked on mobile, collapse the menu after the scroll completes' , 'bellows' ),
			'type'	=> 'checkbox',
			'default'=> 'on',
			'group'	=> 'script_config',
		),
		*/













		/** Admin Notices **/



		270 => array(
			'name'	=> 'header_misc',
			'label'	=> __( 'Miscellaneous' , 'bellows' ),
			'type'	=> 'header',
			'group'	=> 'misc',
		),

		280 => array(
			'name' 		=> 'force_override_theme_filters',
			'label' 	=> __( 'Force Override Menu Filters', 'bellows' ),
			'desc' 		=> __( 'Some themes and plugins will override the menu arguments with a filter, which can break the HTML and functionality of the menu.  Enabling this setting will attempt to override those theme filters.', 'bellows' ),
			'type' 		=> 'checkbox',
			'default' 	=> 'on',
			'group'		=> 'misc',
		),


		290 => array(
			'name'	=> 'admin_notices',
			'label'	=> __( 'Show Admin Notices' , 'bellows' ),
			'type'	=> 'checkbox',
			'default'	=> 'on',
			'desc'	=> __( 'Display helpful notices - only to admins', 'bellows' ),
			'group'	=> 'misc',
		),






		// /** MAINTAINENCE **/
		// 330 => array(
		// 	'name'	=> 'header_maintenance',
		// 	'label'	=> __( 'Maintenance' , 'bellows' ),
		// 	'desc'	=> '<i class="fa fa-warning"></i> '. __( 'You should only adjust settings in this section if you are certain of what you are doing.'  , 'bellows' ),
		// 	'type'	=> 'header',
		// 	'group'	=> 'maintenance',
		// ),

		/*
		350 => array(
			'name'	=> 'reset_all',
			'label'	=> __( 'Reset ALL Settings' , 'bellows' ),
			'desc'	=> '<a class="button button-primary" href="'.admin_url('themes.php?page=bellows-settings&do=reset-all-check').'">'.__( 'Reset Settings' , 'bellows' ).'</a><br/><p>'.__( 'Reset ALL Control Panel settings to the factory defaults.', 'bellows' ).'</p>',
			'type'	=> 'html',
			'group'	=> 'maintenance',
		),
		*/


	);


	return $fields;
}
