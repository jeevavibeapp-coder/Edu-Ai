<?php 

function bellows_integration_code_ui( $config_id ){
	$integration_code = '<div class="ssmenu-integration-code-wrap">'.bellows_menu_integration_code( array() , $config_id );

	$menu_select = '<h4>Integrate Specific Menu</h4>';
	$loc_select = '<h4>Integrate Specific Theme Location</h4>';

	$menus = wp_get_nav_menus( array('orderby' => 'name') );
	
	if( is_array( $menus ) ){
		foreach( $menus as $menu ){
			$integration_code.= bellows_menu_integration_code( array( 'menu' => $menu->term_id ) , $config_id );
		}

		$menu_select.= '<select class="ssmenu-manual-code-menu-selection">';
		$menu_select.= '<option value="_default">Default</option>';
		foreach( $menus as $menu ){
			$menu_select.= '<option value="'.$menu->term_id.'">'.$menu->name.'</option>';
		}
		$menu_select.= '</select>';

		$menu_select.= '<p class="ssmenu-sub-desc ssmenu-desc-understated">To display a specific menu, select the menu above to generate that code</p>';
	}

	$locs = get_registered_nav_menus();

	if( is_array( $locs ) ){

		foreach( $locs as $loc_id => $loc_name ){
			$integration_code.= bellows_menu_integration_code( array( 'theme_location' => $loc_id ) , $config_id );
		}

		$loc_select.= '<select class="ssmenu-manual-code-menu-selection">';
		$loc_select.= '<option value="_default">None</option>';
		foreach( $locs as $loc_id => $loc_name ){
			$loc_select.= '<option value="'.$loc_id.'">'.$loc_name.'</option>';
		}
		$loc_select.= '</select>';

		$loc_select.= '<p class="ssmenu-sub-desc ssmenu-desc-understated">To display a specific theme locaton, select the theme location above to generate that code</p>';
	}

	$integration_code.= $menu_select . $loc_select;

	$integration_code.='</div>';

	return $integration_code;
}

function bellows_menu_integration_code( $args , $config_id ){


	$shortcode = '<code class="ssmenu-highlight-code">[bellows config_id="'.$config_id.'"';
	$api = '<code class="ssmenu-highlight-code">&lt;?php bellows( \''.$config_id.'\' ';
	if( is_array( $args ) && !empty( $args ) ){
		$api.= ', array( ';
		$k = 0;
		foreach( $args as $key => $val ){
			$shortcode.= ' '.$key.'="'.$val.'"';

			if( $k>0 ) $api.= ",";

			if( !is_numeric( $val ) ) $val = "'$val'";
			$api.= "'$key' => $val ";

			$k++;
		}
		$api.= ') ';
	}
	$shortcode.= ']</code>';
	$api.= '); ?&gt;</code>';

	$code_id = '_default';
	if( isset( $args['theme_location'] ) ) $code_id = $args['theme_location'];
	else if( isset( $args['menu'] ) ) $code_id = $args['menu'];

	$code = 
		'<div class="ssmenu-integration-code ssmenu-integration-code-'.$code_id.'">'.
			'<div class="ssmenu-desc-row">
				<span class="ssmenu-code-snippet-type">PHP</span> '.$api.'
			</div>
			<div class="ssmenu-desc-row">
				<span class="ssmenu-code-snippet-type">Shortcode</span> '.$shortcode.'				
			</div>
			<p class="ssmenu-sub-desc ssmenu-desc-mini" >Click to select, then <strong><em>&#8984;+c</em></strong> or <strong><em>ctrl+c</em></strong> to copy to clipboard</p>
			<p class="ssmenu-sub-desc ssmenu-desc-understated">Pick the appropriate code and add to your theme template or content where you want the menu to appear.</p>
			<p class="ssmenu-sub-desc ssmenu-sub-desc-manualint"><i class="fa fa-arrow-down"></i> Select a <strong>Theme Location</strong> or <strong>Menu</strong> below to generate the proper code above <i class="fa fa-arrow-up"></i></p>'.
		'</div>';

	return $code;
}