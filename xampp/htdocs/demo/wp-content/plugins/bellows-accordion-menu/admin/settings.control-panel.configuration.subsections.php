<?php

function bellows_get_configuration_subsections( $config_id ){
	return apply_filters( 'bellows_settings_subsections' , 
		array(
			'integration' => array(
				'title' => __( 'Integration' , 'bellows' ),
			),	
			'basic' => array(
				'title' => __( 'Basic Configuration' , 'bellows' ),
			),			
			'mobile' => array(
				'title' => __( 'Responsive &amp; Mobile' , 'bellows' ),
			),			
			'layout'	=> array(
				'title'	=> __( 'Layout &amp; Position' , 'bellows' ),
			),
			// 'images'	=> array(
			// 	'title'	=> __( 'Images' , 'bellows' ),
			// ),
			'markup'	=> array(
				'title'	=> __( 'Markup Structure' , 'bellows' ),
			),
			'font'	=> array(
				'title'	=> __( 'Font' , 'bellows' ),
			),
			// 'styles'	=> array(
			// 	'title'	=> __( 'Style Customizations' , 'bellows' ),
			// ),
		),
		$config_id 
	);
}