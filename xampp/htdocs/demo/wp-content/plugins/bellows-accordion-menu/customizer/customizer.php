<?php

require_once( BELLOWS_DIR.'customizer/customizer.styles.generator.php' );
require_once( BELLOWS_DIR.'customizer/customizer.styles.manager.php' );
require_once( BELLOWS_DIR.'customizer/customizer.styles.menu-item.php' );

function bellows_register_customizers( $wp_customize ){

	require_once( BELLOWS_DIR.'customizer/customizer.controls.php' );

	$configs = bellows_get_menu_configurations( true );
	foreach( $configs as $config_id ){
		bellows_register_customizer( $config_id , $config_id , $wp_customize );
		//bellows_register_theme_customizer( $instance.'_responsive' , $instance , $wp_customize );
	}

}
add_action( 'customize_register', 'bellows_register_customizers' );

function bellows_register_customizer( $config_id , $config_id_root , $wp_customize ) {

	$config_tag = BELLOWS_PREFIX.$config_id;
	$prefixed_config_id_root = BELLOWS_PREFIX.$config_id_root;

	$section_id = $panel_id = 'bellows_config_'.$config_id; //.$variation_string;

	$wp_customize->add_panel( $panel_id, array(
		'title'			=> __( 'Bellows', 'bellows' ) . ' ['.$config_id.']',
		'priority'		=> 35,
	) );

	$wp_customize->add_section( $panel_id.'_general', array(
		'title'		=> __( 'General', 'bellows' ),
		'priority'	=> 10,
		'panel'		=> $panel_id,
	) );

	$wp_customize->add_section( $panel_id.'_top_level', array(
		'title'		=> __( 'Top Level Styles', 'bellows' ),
		'priority'	=> 20,
		'panel'		=> $panel_id,
	) );


	$wp_customize->add_section( $panel_id.'_submenu', array(
		'title'		=> __( 'Submenu Styles', 'bellows' ),
		'priority'	=> 30,
		'panel'		=> $panel_id,
	) );

	$wp_customize->add_section( $panel_id.'_font', array(
		'title'		=> __( 'Fonts', 'bellows' ),
		'priority'	=> 40,
		'panel'		=> $panel_id,
	) );

	// $wp_customize->add_section( $panel_id.'_markup', array(
	// 	'title'		=> __( 'Markup', 'bellows' ),
	// 	'priority'	=> 10,
	// 	'panel'		=> $panel_id,
	// ) );



	$setting_op = $config_tag;
	$all_fields = bellows_get_settings_fields();
	$fields = $all_fields[$prefixed_config_id_root];

	//bellp( $fields );

	$priority = 0;

	foreach( $fields as $field ){

		$priority+= 10;

		if( isset( $field['customizer'] ) && $field['customizer'] ){
			$setting_id = $setting_op.'['.$field['name'].']';

			$default = isset( $field['default'] ) ? $field['default'] : '';

			$setting_args = array(
				'default'		=> $default,
				'type'			=> 'option',
			);

			if( $field['type'] == 'checkbox' ){
				// $default = $default == 'on' ? true : false;
				$setting_args['sanitize_callback'] = 'bellows_sanitize_checkbox';
				$setting_args['sanitize_js_callback'] = 'bellows_sanitize_js_checkbox';
			}

			$wp_customize->add_setting(
				$setting_id,
				$setting_args
			);

			$field_section_id = $section_id;
			if( isset( $field['customizer_section'] ) ){
				$field_section_id = $panel_id.'_'.$field['customizer_section'];	//bellows_config_{config_id}_{section}
			}

			$args = array(
				'label'			=> $field['label'],
				'section'		=> $field_section_id,
				'settings'		=> $setting_id,
				'priority'		=> $priority,
			);

			if( isset( $field['desc'] ) ){
				$args['description'] = $field['desc'];
			}

			switch( $field['type'] ){

				case 'text':

					$args['type'] = 'text';
					$wp_customize->add_control(
						$setting_id,
						$args
					);
					break;

				case 'checkbox':

					$args['type'] = 'checkbox';
					$wp_customize->add_control(
						$setting_id,
						$args
					);
					break;

				case 'select':

					$args['type'] = 'select';
					$ops = $field['options'];
					if( !is_array( $ops ) && function_exists( $ops ) ){
						$ops = $ops();
					}
					$args['choices'] = $ops;
					$wp_customize->add_control(
						$setting_id,
						$args
					);
					break;

				case 'radio':

					$args['type'] = 'radio';
					$args['choices'] = $field['options'];

					if( isset( $field['customizer_control'] ) && $field['customizer_control'] == 'radio_html' ){
						$wp_customize->add_control( 
							new WP_Customize_Control_Bellows_Radio_HTML(
								$wp_customize,
								$setting_id,
								$args
							)
						);
					}
					else{
						$wp_customize->add_control(
							$setting_id,
							$args
						);
					}
					break;



				case 'color':
					
					$wp_customize->add_control(
						new WP_Customize_Color_Control(
							$wp_customize,
							$setting_id,
							$args
						)
					);
					break;
			}

		}
	}

	/*
	$wp_customize->add_section( $panel_id.'_general', array(
		'title'		=> __( 'General', 'bellows' ),
		'priority'	=> 5,
		'panel'		=> $panel_id,
	) );

	$wp_customize->add_setting(
		'test',
		array(
			'default'     	=> 'hi',
			'type'			=> 'option',
		)
	);

	$args = array(
		'label'			=> 'TEST' , // $field['label'],
		'section'		=> $panel_id.'_general', //$field_section_id,
		'settings'		=> 'test', //$setting_id,
		'priority'		=> 10, //$priority,
	);
	$args['type'] = 'text';
	$wp_customize->add_control(
		'test',
		$args
	);
	*/

}




function bellows_customizer_assets(){
	wp_enqueue_style( 'bellows-font-awesome' , BELLOWS_URL.'assets/css/fontawesome/css/font-awesome.min.css' );
}
add_action( 'customize_controls_enqueue_scripts' , 'bellows_customizer_assets' );




function bellows_customizer_css() {

	//echo bellows_generate_custom_styles();

	global $wp_customize;
	if ( isset( $wp_customize ) ):
	?>
	<style type="text/css">
		<?php 
			echo bellows_generate_all_menu_preview_styles();
		?>
	</style>
	<?php endif;
}
add_action( 'wp_head', 'bellows_customizer_css' );


function bellows_generate_all_menu_preview_styles(){

	$all_styles = array();

	//$all_styles['main'] = bellows_generate_menu_preview_styles( 'main' );

	$configs = bellows_get_menu_configurations( true );
	foreach( $configs as $config_id ){
		$all_styles[$config_id] = bellows_generate_menu_preview_styles( $config_id );
	}

	return bellows_generate_all_menu_styles( $all_styles );

}

function bellows_generate_menu_preview_styles( $config_id , $fields = false ){

	$menu_key = BELLOWS_PREFIX . $config_id;

	if( !$fields ){
		$all_fields = bellows_get_settings_fields();
		$fields = $all_fields[$menu_key];
	}

	$menu_styles = array();

	foreach( $fields as $field ){

		if( isset( $field['custom_style'] ) ){
			$callback = 'bellows_get_menu_style_'. $field['custom_style'];

			if( function_exists( $callback ) ){
				$callback( $field , $config_id , $menu_styles );
			}
		}

	}

	return $menu_styles;

}


// Converts '1' => 'on' and '0' => 'off' when saving to DB
function bellows_sanitize_checkbox( $checked ) {
  return ( ( isset( $checked ) && true == $checked ) ? 'on' : 'off' );
}
// Converts 'on' => 1 and 'off' => 0 when sending values to Customizer JS
function bellows_sanitize_js_checkbox( $value, $setting ){
	return $value === 'on' ? 1 : 0;  // integers critical!
}