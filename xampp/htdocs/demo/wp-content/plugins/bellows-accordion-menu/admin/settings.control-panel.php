<?php

/**
 * Register the plugin page
 */
function bellows_admin_menu() {
	add_submenu_page(
		'themes.php',
		'Bellows Settings',
		'Bellows Menu',
		BELLOWS_ADMIN_CAP,
		'bellows-settings',
		'bellows_control_panel' //'bellows_settings_panel'
	);
}
add_action( 'admin_menu', 'bellows_admin_menu' );


function bellows_control_panel(){
	bellows_settings_panel();
}

function bellows_settings_panel(){

	if( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ){
		do_action( 'bellows_settings_panel_updated' );
	}

	$settings_api = _BELLOWS()->settings_api();

	?>
	<div class="wrap ssmenu-wrap">

	<?php settings_errors(); ?>

	<div class="bellows-settings-links">
		<?php do_action( 'bellows_settings_before_title' ); ?>
	</div>


	<h1><strong>Bellows</strong> Control Panel <span class="ssmenu-version"><?php echo BELLOWS_VERSION; ?></span></h1>

	<?php 

	do_action( 'bellows_settings_before' );

	$settings_api->show_navigation();
	$settings_api->show_forms();
	?>

	</div>

	<?php
}

function bellows_admin_panel_assets( $hook ){

	if( $hook == 'appearance_page_bellows-settings' ){
		wp_enqueue_script( 'bellows-control-panel' , BELLOWS_URL . 'admin/assets/js/admin.control-panel.js' , array( 'jquery' ) , BELLOWS_VERSION , true );
		wp_enqueue_style( 'bellows-settings-styles' , BELLOWS_URL.'admin/assets/css/admin.control-panel.css' );
		wp_enqueue_style( 'bellows-font-awesome' , BELLOWS_URL.'assets/css/fontawesome/css/font-awesome.min.css' );

		// wp_localize_script( 'bellows-control-panel' , 'bellows_control_panel' , array( 
		// 	'load_google_cse'	=> bellows_op( 'load_google_cse' , 'general' ),
		// ) );

		bellows_load_assets();
		if( BELLOWS_PRO ) bellows_pro_load_assets();
	}
}
add_action( 'admin_enqueue_scripts' , 'bellows_admin_panel_assets' );



/**
 * Registers settings section and fields
 */
function bellows_admin_init() {

	$prefix = BELLOWS_PREFIX;
 
 	$sections = bellows_get_settings_sections();
 	$fields = bellows_get_settings_fields();

 	//set up defaults so they are accessible
	_BELLOWS()->set_defaults( $fields );

	
	$settings_api = _BELLOWS()->settings_api();

	//set sections and fields
	$settings_api->set_sections( $sections );
	$settings_api->set_fields( $fields );

	//initialize them
	$settings_api->admin_init();

}
add_action( 'admin_init', 'bellows_admin_init' );



/**
 * Settings
 **/
function bellows_get_settings_sections(){

	$prefix = BELLOWS_PREFIX;

	$sections = array(

		array(
			'id' => $prefix.'main',
			'title' => __( 'Main Configuration', 'bellows' ),
			'sub_sections'	=> bellows_get_configuration_subsections( 'main' ),
		),

	);

	$sections = apply_filters( 'bellows_settings_panel_sections' , $sections );

	return $sections;

}

function bellows_get_settings_fields(){
	$prefix = BELLOWS_PREFIX;
	$settings_fields = _BELLOWS()->get_settings_fields();
	if( $settings_fields ) return $settings_fields;

	$config_id = 'main';

	$fields = array(
		$prefix.$config_id => bellows_get_configuration_fields( $config_id )
	);

	$fields = apply_filters( 'bellows_settings_panel_fields' , $fields );

	foreach( $fields as $section_id => $section_fields ){
		ksort( $fields[$section_id] );
		$fields[$section_id] = array_values( $fields[$section_id] );
	}

	_BELLOWS()->set_settings_fields( $fields );

	return $fields;
}



/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 * @return mixed
 */
function bellows_op( $option, $section, $default = null ) {
 
	$options = get_option( BELLOWS_PREFIX.$section , array() );		//cached by WP

	//Value from settings
	if ( isset( $options[$option] ) ) {
		$val = $options[$option];
	}
	//Default Fallback
	else{
		//No default passed
		if( $default === null ){
			$val = _BELLOWS()->get_default( $option, BELLOWS_PREFIX.$section );
		}
		//Use passed default
		else{
			$val = $default;
		}
	}

	$val = apply_filters( 'bellows_op' , $val , $option , $section );

	return $val;
}
function bellows_get_configuration_options( $config_id ){
	$defaults = _BELLOWS()->get_defaults( BELLOWS_PREFIX.$config_id );
	$options = get_option( BELLOWS_PREFIX.$config_id , $defaults );
	if( !is_array( $options ) || count( $options ) == 0 ) return $defaults;
	return $options;
}



function bellows_allow_html( $str ){
	return $str;
}



function bellows_settings_links(){

	$qs = 'quick-start';
	if( !BELLOWS_PRO ) $qs.='/lite';

	//if( bellows_is_pro() ) echo '<a class="button button-quickstart" href="#"><i class="fa fa-bolt"></i> QuickStart</a> ';
	echo '<a target="_blank" class="button button-secondary" href="'.BELLOWS_KB_URL.'/'.$qs.'"><i class="fa fa-bolt"></i> Quick Start</a> ';
	echo '<a target="_blank" class="button button-primary" href="'.BELLOWS_KB_URL.'"><i class="fa fa-book"></i> Knowledgebase</a> ';
	//echo '<a target="_blank" class="button button-tertiary" href="'.BELLOWS_VIDEOS_URL.'"><i class="fa fa-video-camera"></i> Video Tutorials</a> ';
}
add_action( 'bellows_settings_before_title' , 'bellows_settings_links' );




function bellows_pro_link(){
	?>
	<div class="bellows_pro_button_container">
		<a target="_blank" href="http://goo.gl/PaueKZ" class="bellows_pro_button"><i class="fa fa-rocket"></i> Go Pro</a>
	</div>
	<?php
}
if( !BELLOWS_PRO ) add_action( 'bellows_settings_before' , 'bellows_pro_link' );