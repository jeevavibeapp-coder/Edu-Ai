<?php

function bellows_get_skin_ops(){

	$registered_skins = _BELLOWS()->get_skins();
	if( !is_array( $registered_skins ) ) return array();
	$ops = array();
	foreach( $registered_skins as $id => $skin ){
		$ops[$id] = $skin['title'];
	}
	return $ops;
}
function bellows_register_skin( $id, $title, $path , $classes = '' ){
	_BELLOWS()->register_skin( $id , $title , $path , $classes );
}

add_action( 'init' , 'bellows_register_skins' );
function bellows_register_skins(){
	$main = BELLOWS_URL . 'assets/css/skins/';
	bellows_register_skin( 'none' , 'None (Disable)' , '' );
	bellows_register_skin( 'vanilla' 	   , 'Vanilla' 		 , $main.'vanilla.css' );
	bellows_register_skin( 'blue-material' , 'Blue Material' , $main.'blue-material.css' );
	bellows_register_skin( 'grey-material' , 'Grey Material' , $main.'grey-material.css' );
}


function bellows_enqueue_skin( $skin ){
	wp_enqueue_style( 'bellows-'.$skin );
}

function bellows_enqueue_skins(){
	//Load Required Skins
	$configs = bellows_get_menu_configurations( true );
	foreach( $configs as $config ){
		$skin = bellows_op( 'skin' , $config );
		bellows_enqueue_skin( $skin );
	}
}