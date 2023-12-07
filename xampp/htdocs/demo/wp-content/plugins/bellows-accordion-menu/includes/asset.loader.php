<?php 

function bellows_load_assets(){
	$assets = BELLOWS_URL . 'assets/';

	if( SCRIPT_DEBUG ){
		wp_enqueue_style( 'bellows' , $assets.'css/bellows.css' , false , BELLOWS_VERSION );
	}
	else{
		wp_enqueue_style( 'bellows' , $assets.'css/bellows.min.css' , false , BELLOWS_VERSION );
	}

	//Font Awesome
	if( bellows_op( 'load_fontawesome' , 'general' ) == 'on' ){
		wp_enqueue_style( 'bellows-font-awesome' , $assets.'css/fontawesome/css/font-awesome.min.css' , false , BELLOWS_VERSION );
	}


	bellows_enqueue_skins();
	

	wp_enqueue_script( 'jquery' );
	if( SCRIPT_DEBUG ){
		wp_enqueue_script( 'bellows' , $assets.'js/bellows.js' , array( 'jquery' ) , BELLOWS_VERSION , true );
	}
	else{
		wp_enqueue_script( 'bellows' , $assets.'js/bellows.min.js' , array( 'jquery' ) , BELLOWS_VERSION , true );
	}



	$config_data = array();

	$configs = bellows_get_menu_configurations( true );
	foreach( $configs as $config_id ){
		$config_data[$config_id] = array(
			'folding'	=> bellows_op( 'folding' , $config_id ),
			'current'	=> bellows_op( 'current_expansion' , $config_id ),
			'slide_speed'=> bellows_op( 'slide_speed', $config_id, 400 ),
		);
	}

	wp_localize_script( 'bellows' , 'bellows_data' , array( 
		'config'	=> $config_data,
		'v'			=> BELLOWS_VERSION,
	) );


}
add_action( 'wp_enqueue_scripts' , 'bellows_load_assets' , 101 );



function bellows_inject_custom_css(){
	echo '<style id="bellows-custom-generated-css">';
	//echo bellows_generate_custom_styles();
	echo bellows_get_custom_styles();
	//global $is_IE;
	echo "\n</style>";
}
add_action( 'wp_head' , 'bellows_inject_custom_css' );