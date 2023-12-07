<?php


function bellows_get_custom_styles(){

	$styles = get_transient( BELLOWS_GENERATED_STYLE_TRANSIENT );

	//No valid transient - regenerate
	if( $styles === false ){
		$styles = bellows_generate_custom_styles();
		set_transient( BELLOWS_GENERATED_STYLE_TRANSIENT , $styles , BELLOWS_GENERATED_STYLE_TRANSIENT_EXPIRATION );
		$styles.= "\n/* Status: Regenerated */\n";
	}
	//Valid transient, good to go
	else{
		$styles.= "\n/* Status: Loaded from Transient */\n";
	}	

	return $styles;
}

add_action( 'bellows_after_menu_item_save' , 'bellows_reset_generated_styles' , 10 , 1 );
function bellows_reset_generated_styles( $menu_item_id = 0 ){
	delete_transient( BELLOWS_GENERATED_STYLE_TRANSIENT );
}

/**
 * Build the custom CSS from the various arrays of CSS property/values
 * @return [type] [description]
 */
function bellows_generate_custom_styles(){

	$styles = array();

	//Responsive Styles
	// $responsive_styles = bellows_custom_responsive_styles();
	// if( $responsive_styles ){
	// 	$responsive_styles = "\n/** Bellows Responsive Styles (Breakpoint Setting) **/\n".$responsive_styles;
	// 	$styles[10] = $responsive_styles;	
	// }


	//Menu Styles
	global $wp_customize;
	if( !isset( $wp_customize ) ){
		$menu_styles = bellows_generate_all_menu_styles();
		if( $menu_styles ){
			$menu_styles = "\n/** Bellows Custom Menu Styles (Customizer) **/\n".$menu_styles;
			$styles[20] = $menu_styles;
		}
	}


	//Menu Item Styles
	$item_styles = bellows_generate_item_styles();
	if( $item_styles ){
		$item_styles = "\n/** Bellows Custom Menu Item Styles (Menu Item Settings) **/\n" . $item_styles;
	}
	$styles[30] = $item_styles;	
	
	


	//Custom Styles
	$custom_styles = bellows_op( 'custom_tweaks' , 'general' );
	if( $custom_styles ){
		$custom_styles = "\n/** Bellows Custom Tweaks (General Settings) **/\n".$custom_styles;
		$styles[50] = $custom_styles;
	}

	//Custom Styles - Mobile
	// $custom_styles_mobile = bellows_op( 'custom_tweaks_mobile' , 'general' );
	// if( $custom_styles_mobile ){
	// 	$max_width = bellows_op( 'responsive_breakpoint' , 'general' );
	// 	if( !$max_width ) $max_width = 959;
	// 	if( is_numeric( $max_width ) ) $max_width.='px';
	// 	$custom_styles_mobile = 
	// 		"\n/** Bellows Custom Tweaks - Mobile **/\n".
	// 		"@media screen and (max-width:".$max_width."){\n".
	// 			$custom_styles_mobile.
	// 		"\n}";
	// 	$styles[60] = $custom_styles_mobile;
	// }


	//Custom Styles - Desktop
	// $custom_styles_desktop = bellows_op( 'custom_tweaks_desktop' , 'general' );
	// if( $custom_styles_desktop ){
	// 	$min_width = bellows_op( 'responsive_breakpoint' , 'general' );
	// 	if( !$min_width ) $min_width = 960;
	// 	else{ $min_width = $min_width + 1; }

	// 	if( is_numeric( $min_width ) ) $min_width.='px';
	// 	$custom_styles_desktop = 
	// 		"\n/** Bellows Custom Tweaks - Desktop **/\n".
	// 		"@media screen and (min-width:".$min_width."){\n".
	// 			$custom_styles_desktop.
	// 		"\n}";
	// 	$styles[100] = $custom_styles_desktop;
	// }
		

	$styles = apply_filters( 'bellows_custom_styles' , $styles );

	return implode( "\n" , $styles );
}




function bellows_generate_all_menu_styles( $menu_styles = false ){

	$styles = '';

	if( !$menu_styles ){
		$menu_styles = get_option( BELLOWS_MENU_STYLES , array() );
	}

	foreach( $menu_styles as $menu_id => $rules ){
		
		if( empty( $rules ) ) continue;

		$styles.= "/* $menu_id */\n";

		//Normal
		bellows_process_menu_rules( $rules , false , $styles );

		//Responsive
		// if( isset( $rules['_responsive'] ) ){
		// 	$breakpoint = bellows_op( 'responsive_breakpoint' , 'general' );
		// 	if( !$breakpoint ) $breakpoint = 959;
		// 	if( is_numeric( $breakpoint ) ) $breakpoint.= 'px';

		// 	$styles.= "/* $menu_id - responsive */\n";
		// 	$styles.= "@media screen and (max-width:$breakpoint){\n";
		// 		bellows_process_menu_rules( $rules , '_responsive' , $styles );
		// 	$styles.= "}\n";
		// }
		
	}

	return $styles;

}

function bellows_process_menu_rules( $rules , $flag, &$styles ){

	if( $flag ){
		if( isset( $rules[$flag] ) ){
			$rules = $rules[$flag];
		}
		else return;
	}

	foreach( $rules as $selector => $property_map ){

		if( $selector == '_responsive' ) continue;

		$styles.= "$selector { ";
		foreach( $property_map as $property => $value ){

			if( is_array( $value ) ){
				//Multiple instances of this property  (for example, when using browser prefix gradients)
				foreach( $value as $v ){
					$styles.= "$property:$v; ";
				}
			}
			else{
				$styles.= "$property:$value; ";
			}
		}
		$styles.= "}\n";
	}

	//return $styles;
}


/**
 * Call bellows_save_menu_styles() for each menu instance
 */
add_action( 'bellows_settings_panel_updated' , 'bellows_save_all_menu_styles' );
add_action( 'customize_save_after' , 'bellows_save_all_menu_styles' );
function bellows_save_all_menu_styles(){

	bellows_save_menu_styles( 'main' );

	if( function_exists( 'bellows_get_menu_configurations' ) ){
		$configs = bellows_get_menu_configurations();
		foreach( $configs as $config_id ){
			bellows_save_menu_styles( $config_id );
		}
	}

	bellows_reset_generated_styles();	//clears transient

	if( BELLOWS_PRO ) add_settings_error( 'menu' , 'menu-styles' , 'Custom menu styles updated.' , 'updated' );

}


/**
 * For each field, checks to see if it has a custom style callback.
 * If callback exists, runs it to generate the style and adds that 
 * style to the master array of styles.  Then saves all styles back
 * to the DB in an array format 
 * ($config_id => $selector => $property => $value )
 * 
 * @param  string  $menu_id The ID of the menu instance to save
 * @param  boolean $fields  An optional set of fields to use.  
 *                          Uses all registered settings by default
 */
function bellows_save_menu_styles( $config_id , $fields = false ){

	$config_key = BELLOWS_PREFIX . $config_id;

	if( !$fields ){
		$all_fields = bellows_get_settings_fields();
		$fields = $all_fields[$config_key];
	}

	$menu_styles = array();

	/*
	if( !isset( $menu_styles[$menu_id] ) ){
		$menu_styles[$menu_id] = array();
	}
	*/

	foreach( $fields as $field ){

		if( isset( $field['custom_style'] ) ){
			$callback = 'bellows_get_menu_style_'. $field['custom_style'];
			if( function_exists( $callback ) ){
				$callback( $field , $config_id , $menu_styles );
			}
		}

	}
	
	$all_styles = get_option( BELLOWS_MENU_STYLES , array() );
	$all_styles[$config_id] = $menu_styles;

	update_option( BELLOWS_MENU_STYLES , $all_styles );

}


function bellows_delete_menu_styles( $config_id ){
	$all_styles = get_option( BELLOWS_MENU_STYLES , array() );
	unset( $all_styles[$config_id] );
	update_option( BELLOWS_MENU_STYLES , $all_styles );
	bellows_reset_generated_styles();	//clear transient
}