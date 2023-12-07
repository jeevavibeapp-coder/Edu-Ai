<?php

function bellows_get_menu_style_submenu_indent( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id";
	if( $val ){
		if( is_numeric( $val ) ) $val.= 'px';
		$menu_styles[$selector]['--bellows-indent'] = $val;
	}
}

function bellows_get_menu_style_font_family( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id, .bellows.bellows-$config_id .bellows-menu-item, .bellows.bellows-$config_id .bellows-menu-item .bellows-target";
	if( $val ){
		$menu_styles[$selector]['font-family'] = $val;
	}
}



function bellows_get_menu_style_menu_width( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	if( is_numeric( $val ) ) $val.= 'px';
	$selector = ".bellows.bellows-$config_id";
	if( $val ){
		$menu_styles[$selector]['width'] = $val;
	}
	//else unset( $menu_styles[$selector]['width'] );
}

function bellows_get_menu_style_top_level_background_color( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id";
	if( $val ){
		$menu_styles[$selector]['background-color'] = $val;
	}
}

function bellows_get_menu_style_top_level_background_color_hover( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target:hover";
	if( $val ){
		$menu_styles[$selector]['background-color'] = $val;
	}
}

function bellows_get_menu_style_top_level_background_color_active( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-active > .bellows-target";
	if( $val ){
		$menu_styles[$selector]['background-color'] = $val;
	}
}

function bellows_get_menu_style_top_level_background_color_current( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-current-menu-item > .bellows-target, ".
				".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-current-menu-ancestor > .bellows-target";
	if( $val ){
		$menu_styles[$selector]['background-color'] = $val;
	}
}

function bellows_get_menu_style_top_level_font_color( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target, .bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-custom-content";
	if( $val ){
		$menu_styles[$selector]['color'] = $val;
	}
}
function bellows_get_menu_style_top_level_font_color_hover( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target:hover";
	if( $val ){
		$menu_styles[$selector]['color'] = $val;
	}
}
function bellows_get_menu_style_top_level_font_color_active( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-active > .bellows-target";
	if( $val ){
		$menu_styles[$selector]['color'] = $val;
	}
}
function bellows_get_menu_style_top_level_font_color_current( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-current-menu-item > .bellows-target, ".
		 		".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-current-menu-ancestor > .bellows-target";
	if( $val ){
		$menu_styles[$selector]['color'] = $val;
	}
}


function bellows_get_menu_style_top_level_font_size( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target, .bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-subtoggle";
	if( $val ){

		//$sub_toggle_icon_margin = intval( $val );
		if( is_numeric( $val ) ){
			$menu_styles[".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-subtoggle .fa"]['margin-top'] = '-'.ceil(($val*1.3/2)).'px';
			$val.= 'px';
		}
		$menu_styles[$selector]['font-size'] = $val;

	}
}
function bellows_get_menu_style_top_level_font_weight( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target";
	if( $val ){
		$menu_styles[$selector]['font-weight'] = $val;
	}
}

function bellows_get_menu_style_top_level_item_padding( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target, ". 
				".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target.bellows-target-w-image > .bellows-target-text, ".
				".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target.bellows-target-w-image > .bellows-image.bellows-image-padded";
	$toggle_val = '90px';
	if( $val ){
		if( is_numeric( $val ) ){
			$toggle_val = ( $val + 70 ) . 'px';
			$val.= 'px';
		}
		$menu_styles[$selector]['padding'] = $val;

		// Increase edge padding if there is a toggle (right for LTR, left for )
		$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-menu-item-has-children > .bellows-target";
		$menu_styles['body:not(.rtl) '.$selector]['padding-right'] = $toggle_val;
		$menu_styles['body.rtl '.$selector]['padding-left'] = $toggle_val;
	}

}
function bellows_get_menu_style_top_level_item_v_padding( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id";

	if( $val ){
		if( is_numeric( $val ) ){
			$val.= 'px';
		}
		$menu_styles[$selector]['--bellows-item-padding-v'] = $val;
	}

}
function bellows_get_menu_style_top_level_item_h_padding( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id";

	if( $val ){
		if( is_numeric( $val ) ){
			$val.= 'px';
		}
		$menu_styles[$selector]['--bellows-item-padding-h'] = $val;

		// Increase edge padding if there is a toggle (right for LTR, left for )
		// $selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0.bellows-menu-item-has-children > .bellows-target";
		// $menu_styles['body:not(.rtl) '.$selector]['padding-right'] = $toggle_val;
		// $menu_styles['body.rtl '.$selector]['padding-left'] = $toggle_val;
	}

}

function bellows_get_menu_style_top_level_divider_color( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	//$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target";
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-target, ".
				".bellows.bellows-$config_id .bellows-nav .bellows-item-level-0 > .bellows-custom-content";
	if( $val ){
		$menu_styles[$selector]['border-bottom-color'] = $val;
	}
}








function bellows_get_menu_style_submenu_background( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu";
	if( $val ){
		$menu_styles[$selector]['background-color'] = $val;
	}
}

function bellows_get_menu_style_submenu_item_background_hover( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-target:hover";
	if( $val ){
		$menu_styles[$selector]['background-color'] = $val;
	}
}

function bellows_get_menu_style_submenu_item_background_current( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-current-menu-item > .bellows-target, ".
				".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-current-menu-ancestor > .bellows-target";
	if( $val ){
		$menu_styles[$selector]['background-color'] = $val;
	}
}

function bellows_get_menu_style_submenu_item_color( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-target";
	if( $val ){
		$menu_styles[$selector]['color'] = $val;
	}
}

function bellows_get_menu_style_submenu_item_color_hover( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-target:hover";
	if( $val ){
		$menu_styles[$selector]['color'] = $val;
	}
}

function bellows_get_menu_style_submenu_item_color_current( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-current-menu-item > .bellows-target, ".
				".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-current-menu-ancestor > .bellows-target";
	if( $val ){
		$menu_styles[$selector]['color'] = $val;
	}
}

function bellows_get_menu_style_submenu_item_font_size( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-menu-item > .bellows-target, .bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-menu-item > .bellows-subtoggle";
	if( $val ){

		//$sub_toggle_icon_margin = intval( $val );
		if( is_numeric( $val ) ){
			$menu_styles[".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-menu-item > .bellows-subtoggle .fa"]['margin-top'] = '-'.ceil(($val*1.3/2)).'px';
			$val.= 'px';
		}
		$menu_styles[$selector]['font-size'] = $val;

	}
}

function bellows_get_menu_style_submenu_item_padding( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-menu-item > .bellows-target, ". 
				".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-custom-content.bellows-custom-content-padded, ". 
				".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-target.bellows-target-w-image > .bellows-target-text, ". 
				".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-target.bellows-target-w-image > .bellows-image.bellows-image-padded";

	if( $val ){
		if( is_numeric( $val ) ) $val.= 'px';
		$menu_styles[$selector]['padding'] = $val;
	}
}
function bellows_get_menu_style_submenu_item_v_padding( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id";

	if( $val ){
		if( is_numeric( $val ) ) $val.= 'px';
		$menu_styles[$selector]['--bellows-sub-item-padding-v'] = $val;
	}
}
function bellows_get_menu_style_submenu_item_h_padding( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id";

	if( $val ){
		if( is_numeric( $val ) ) $val.= 'px';
		$menu_styles[$selector]['--bellows-sub-item-padding-h'] = $val;
	}
}


function bellows_get_menu_style_submenu_item_divider( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-target";
	if( !$val ){
		$menu_styles[$selector]['border-bottom'] = 'none';
	}
	elseif( bellows_op( 'skin' , $config_id ) == 'none' &&
			bellows_op( 'submenu_item_divider_color' , $config_id ) ){
		$menu_styles[$selector]['border-bottom-style'] = 'solid';
		$menu_styles[$selector]['border-bottom-width'] = '1px';
	}
}


function bellows_get_menu_style_submenu_item_divider_color( $field , $config_id , &$menu_styles ){
	$val = bellows_op( $field['name'] , $config_id );
	$selector = ".bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-target, .bellows.bellows-$config_id .bellows-nav .bellows-submenu .bellows-custom-content";
	if( $val ){
		$menu_styles[$selector]['border-bottom-color'] = $val;
	}
}
