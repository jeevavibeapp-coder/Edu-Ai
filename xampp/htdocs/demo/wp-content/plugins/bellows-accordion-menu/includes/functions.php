<?php

require_once( BELLOWS_DIR . 'includes/asset.loader.php' );
require_once( BELLOWS_DIR . 'includes/skins.php' );
require_once( BELLOWS_DIR . 'includes/widget.php' );

function bellows_get_nav_menu_args( $config_id , $args = array() ){

	// Config ID should be pre-validated

	$args['container']			= bellows_op( 'container_tag' , $config_id );
	$args['container_class']	= 'bellows bellows-nojs';
	$args['menu_class']			= 'bellows-nav';
	$args['items_wrap']			= '<'.BELLOWS_GROUP_TAG.' id="%1$s" class="%2$s" data-bellows-config="'.$config_id.'">%3$s</'.BELLOWS_GROUP_TAG.'>';
	$args['walker']				= new BellowsWalker;

	$args['bellows_config'] 	= $config_id;

//TODO: ALLOW ID TO BE OVERRIDDEN, HANDLE TAXONOMY TYPE - OR MAYBE DONT AUTO GENERATE ID AT ALL

	//Get the menu ID and theme location
	$nav_menu_id = 0;
	$theme_location = 0;
	if( isset( $args['menu'] ) && $args['menu'] ){
		$nav_menu_id = $args['menu'];
	}
	else if( isset( $args['theme_location'] ) && $args['theme_location'] ){
		$theme_location = $args['theme_location'];
		_BELLOWS()->count_theme_location( $theme_location );
		if( $theme_location && has_nav_menu( $theme_location ) ){
			$menus = get_nav_menu_locations();
			$nav_menu_id = $menus[$theme_location];
		}
	}
	//Make sure nav menu ID is a string so that it can be used as part of the ID
	if( is_object( $nav_menu_id ) ){
		if( isset( $nav_menu_id->term_id ) ){
			$nav_menu_id = $nav_menu_id->term_id;
		}
		else{
			$nav_menu_id = '_bad_id_';
		}
	}
	$args['__bellows_menu_id'] = $nav_menu_id;
	_BELLOWS()->count_menu_instance( $nav_menu_id );

	//ID (Config ID is pre-validated and pre-escaped)
	$args['container_id']		= 'bellows-'.$config_id.'-'.sanitize_key( $nav_menu_id );
	if( $theme_location ){
		$args['container_id'].='-'.sanitize_key( $theme_location );
		$theme_location_count = _BELLOWS()->get_theme_location_count( $theme_location );
		if( $theme_location_count > 1 ){
			$args['container_id'].= '-'.$theme_location_count;
		}
	}
	else{
		$menu_instance_count = _BELLOWS()->get_menu_instance_count( $nav_menu_id );
		if( $menu_instance_count > 1 ){
			$args['container_id'].= '-'.$menu_instance_count;
		}
	}

	//Config
	$args['container_class']	.= ' bellows-'.$config_id; // Already escaped

	//Source
	$args['container_class']	.= ' bellows-source-'.$args['bellows_source'];

	//Alignment
	$args['container_class']	.= ' bellows-align-' . bellows_op( 'menu_align' , $config_id );

	//Skin
	$args['container_class']	.= ' bellows-skin-' . bellows_op( 'skin' , $config_id );

	//Tree
	$args['container_class']	.= ' bellows-type-' . bellows_op( 'menu_type', $config_id );

	// Expand Current
	$args['container_class']	.= bellows_op( 'current_expansion' , $config_id ) === 'on' ? ' bellows-expand-current' : '';

	//Mobile collapse
	if( bellows_op( 'mobile_collapse', $config_id ) === 'on' ){
		$args['container_class'].= ' bellows-mobile-collapse';
	}

	return $args;
}

function bellows_menu_toggle_default( $menu_id, $config_id, $args ){

	if( bellows_op( 'mobile_collapse', $config_id ) !== 'on' ) return '';

	$text = bellows_op( 'toggle_text', $config_id );
	// bellp( $args );
	$localized = __( 'Menu', 'bellows' );
	switch( $text ){
		case '':
			$text = $localized;
			break;
		case ' ':
			$text = '';
			break;
		case '{menu_name}':
			if( isset( $args['__bellows_menu_id'] ) ){
				$nav_menu_ob = wp_get_nav_menu_object( $args['__bellows_menu_id'] );
				$text = $nav_menu_ob ? $nav_menu_ob->name : '';
			}
			else{
				$text = $localized;
			}
			break;
		default:
			$text = do_shortcode( $text );
			break;
	}

	$text = '<span class="bellows-menu-toggle-text">'.apply_filters( 'bellow_menu_toggle_default_text', $text, $menu_id, $args ).'</span>';


	$icon = '<i class="fa fa-bars"></i>';

	$content = $icon. ' ' .$text;

	$skin = bellows_op( 'skin' , $config_id );
	$btn_class = "bellows-menu-toggle-skin-$skin";
	return bellows_menu_toggle( $menu_id, $content, $btn_class, $args );
}

function bellows_get_dynamic_post_parent( $dpp ){
	global $post;

	if( $post && $post->ID ){
		switch( $dpp ){
			// Current item
			case -1:
				return $post->ID;
				break;
			// Current Parent
			case -2:
				//Parent item
				//If this is a top level item, just use this ID
				if( $post->post_parent == 0 ) return $post->ID;
				else return $post->post_parent;
				break;
			// Current Root
			case -3:
				//Root item
				//If this is a top level item, just use this ID
				if( $post->post_parent == 0 ){
					return $post->ID;
				}
				//Otherwise, find the root
				else{
					$ancestors = get_post_ancestors( $post );
					return $ancestors ? $ancestors[count($ancestors) - 1] : $post->ID;
				}
				break;

			default:
				//invalid
		}

		// This would prevent the post parent from ever being set to 0, which would return all top level items
		// if( $dpp == 0 ){
		// 	return $post->ID;
		// }

	}
}


/* Translation files */
add_action( 'plugins_loaded' , 'bellows_load_textdomain' );
function bellows_load_textdomain(){
	$domain = 'bellows';
	load_plugin_textdomain( $domain , false , BELLOWS_BASEDIR.'/languages' );

	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
}



/** Admin Notices **/
function bellows_user_is_admin(){
	return current_user_can( 'manage_options' );
}

function bellows_admin_notice( $content , $echo = true ){
	//$showtips = false;

	if( bellows_op( 'admin_notices' , 'general' ) == 'on' ){
		if( bellows_user_is_admin() ){
			$notice = '<div class="bellows-admin-notice"><i class="bellows-admin-notice-icon fa fa-lightbulb-o"></i>'.$content.'</div>';

			if( $echo ) echo $notice;
			return $notice;
		}
	}

}


add_filter( 'plugin_action_links_'.BELLOWS_BASENAME , 'bellows_action_links' );
function bellows_action_links( $links ) {
	$links[] = '<a href="'. admin_url( 'themes.php?page=bellows-settings' ) .'">Control Panel</a>';
	$links[] = '<a target="_blank" href="'.BELLOWS_KB_URL.'">Knowledgebase</a>';
	return $links;
}


function bellows_get_support_url(){
	return _BELLOWS()->get_support_url();
}




function bellows_get_menu_item_data( $item_id ){
	$meta = get_post_meta( $item_id , BELLOWS_MENU_ITEM_META_KEY , true );

	//Add URL for image
	if( !empty( $meta['item_image'] ) ){
		$src = wp_get_attachment_image_src( $meta['item_image'] );
		if( $src ){
			$meta['item_image_url'] = $src[0];
			$meta['item_image_edit'] = get_edit_post_link( $meta['item_image'], 'raw' );
		}
	}

	return $meta;
}


add_filter( 'wp_nav_menu_args' , 'bellows_force_prefilter' , 1 , 1 );
function bellows_force_prefilter($args){

	if( bellows_op( 'force_override_theme_filters' , 'general' ) != 'off' ){
		if( isset( $args['bellows_config'] ) ){
			$args['bellows_args'] = $args;
		}
	}
	return $args;
}

add_filter( 'wp_nav_menu_args' , 'bellows_force_refilter' , 999999 , 1 );
function bellows_force_refilter($args){
	if( bellows_op( 'force_override_theme_filters' , 'general' ) != 'off' ){
		if( isset( $args['bellows_args'] ) ){
			$args = $args['bellows_args'];
		}
	}
	return $args;
}


function bellows_output_responsive_toggle($config_id, $args){
	// Should the toggle and menu be printed
	$print = isset( $args['echo'] ) ? $args['echo'] : true;

	// Get the toggle based on config settings
	$toggle = bellows_menu_toggle_default( $args['container_id'], $config_id, $args );

	// If we're printing, print the toggle.
	if( $print ){
		echo $toggle;
	}

	return $toggle;
}


/**
 * Validates that the passed config ID matches an existing configuration, 
 * after escaping unsafe characters that may have been passed
 * 
 * If the config is invalid, returns the default configuration
 * 
 */
function bellows_validate_config_id( $config_id, $default = 'main' ){

	// Escape the string to defend against XSS attacks
	$config_id = esc_attr( $config_id );

	// If this is the main ID, we don't actually need to query the DB, as this always exists
	if( $config_id === 'main' ){
		return $config_id;
	}

	// Find the valid configurations
	$valid_configs = bellows_get_menu_configurations();

	// If the ID matches a valid configuration, we're good to go
	if( in_array( $config_id, $valid_configs ) ){
		return $config_id;
	}

	// If not, return a valid config (main by default)
	return $default;
}


function bellp( $d ){
	echo '<pre>';
	print_r( $d );
	echo '</pre>';
}