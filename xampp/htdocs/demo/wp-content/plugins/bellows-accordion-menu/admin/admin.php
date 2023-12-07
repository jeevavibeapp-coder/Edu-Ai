<?php

require_once( BELLOWS_DIR.'admin/settings-api.class.php' );


require_once( BELLOWS_DIR.'admin/settings.control-panel.configuration.fields.php' );
require_once( BELLOWS_DIR.'admin/settings.control-panel.configuration.subsections.php' );
require_once( BELLOWS_DIR.'admin/settings.control-panel.general.php' );
require_once( BELLOWS_DIR.'admin/settings.control-panel.integration.php' );
require_once( BELLOWS_DIR.'admin/settings.control-panel.php' );



function bellows_get_menu_configurations( $main = false ){
	$configs = get_option( BELLOWS_MENU_CONFIGURATIONS , array() );

	if( $main ){
		$configs[] = 'main';
	}

	return $configs;
}
