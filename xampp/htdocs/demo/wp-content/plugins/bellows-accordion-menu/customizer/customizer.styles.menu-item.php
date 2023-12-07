<?php

/***********

MENU ITEM STYLES

***********/

function bellows_generate_item_styles(){
	$item_styles = get_option( BELLOWS_MENU_ITEM_STYLES , array() );
	$styles = '';

	//Each Item
	foreach( $item_styles as $item_id => $rules ){

		if( empty( $rules ) ) continue;

		$spacing = 12;
		$delim = "/* $item_id */";
		$remainder = $spacing - strlen( $delim );
		if( $remainder < 0 ) $remainder = 0;
		$item_styles = $delim . str_repeat( ' ' , $remainder );

		//Each Item Rule
		$k = 0;
		$item_rules = '';
		foreach( $rules as $selector => $property_map ){
			
			//Property map may have empty values
			$props = '';
			foreach( $property_map as $property => $value ){
				if( $value != '' ) $props.= "$property:$value; ";		//
			}
			//If we actually wrote properties, create a style
			if( $props ){
				if( $k > 0 ) $item_rules.= str_repeat( ' ' , $spacing );
				$item_rules.= "$selector { ";
				$item_rules.= $props;
				$item_rules.= "}\n";
			}

			$k++;
		}

		if( $item_rules ) $styles.= $item_styles . $item_rules;
	}
	return $styles;
}


//Delete when menu item is deleted
add_action( 'before_delete_post' , 'bellows_delete_item_custom_styles' );
function bellows_delete_item_custom_styles( $post_id ){

	if( 'nav_menu_item' == get_post_type( $post_id ) ){
		
		//Reset styles for post ID
		$custom_styles = _BELLOWS()->get_item_styles( $post_id );

		//Update DB
		_BELLOWS()->update_item_styles();

		//Force regerenation of styles
		bellows_reset_generated_styles();

		// bellp( _BELLOWS()->get_item_styles() , 2 );
		// die();

		//unset( $custom_styles[$item_id] );
		//update_option( BELLOWS_MENU_ITEM_STYLES , $custom_styles );
	}
}




//Saving is deferred until after all properties have been processed for efficiency
add_action( 'bellows_after_menu_item_save' , 'bellows_update_item_styles' , 10 , 1 );
function bellows_update_item_styles( $menu_item_id ){
	_BELLOWS()->update_item_styles();
}


function bellows_set_item_style( $item_id , $selector , $property_map ){
	_BELLOWS()->set_item_style( $item_id , $selector , $property_map );
}


function bellows_item_save_background_color( $item_id , $setting , $val , &$saved_settings ){
	//up( $setting ); //echo $val; //die();

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id > .bellows-target";

	$property_map = array(
		'background'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_background_color_hover( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id > .bellows-target:hover";

	$property_map = array(
		'background'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_background_color_active( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id.bellows-active > .bellows-target";

	$property_map = array(
		'background'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_background_color_current( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id.bellows-current-menu-item > .bellows-target,".
				".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id.bellows-current-menu-ancestor > .bellows-target";

	$property_map = array(
		'background'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}


function bellows_item_save_font_color( $item_id , $setting , $val , &$saved_settings ){
	//up( $setting ); //echo $val; //die();

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id > .bellows-target";

	$property_map = array(
		'color'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_font_color_hover( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id > .bellows-target:hover";

	$property_map = array(
		'color'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_font_color_active( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id.bellows-active > .bellows-target";

	$property_map = array(
		'color'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_font_color_current( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id.bellows-current-menu-item > .bellows-target,".
				".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id.bellows-current-menu-ancestor > .bellows-target";

	$property_map = array(
		'color'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_font_size( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	if( is_numeric( $val ) ) $val.= 'px';

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id > .bellows-target";

	$property_map = array(
		'font-size'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_font_weight( $item_id , $setting , $val , &$saved_settings ){

	if( !$val ) return;

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id > .bellows-target";

	$property_map = array(
		'font-weight'	=> $val
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}

function bellows_item_save_padding( $item_id , $setting , $val , &$saved_settings ){

	//if( $val == '' ) return;

	if( is_numeric( $val ) ) $val.='px';

	$selector = ".bellows .bellows-nav .bellows-menu-item.bellows-menu-item-$item_id > .bellows-target";

	$property_map = array(
		'padding'	=> $val,
	);

	bellows_set_item_style( $item_id , $selector , $property_map );

}