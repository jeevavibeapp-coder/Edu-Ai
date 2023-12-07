<?php

/**
 * Plugin Name:       Bellows Accordion Menu
 * Plugin URI:        http://getbellows.com
 * Description:       A flexible and robust WordPress accordion menu plugin
 * Version:           1.4.3
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            SevenSpark
 * Author URI:        https://sevenspark.com
 * Text Domain:       bellows
 * Domain Path:       /languages
 */

if( ! defined( 'WPINC' ) ) die; 		// If this file is called directly, abort.

if( ! defined( 'BELLOWS_VERSION' ) )	define( 'BELLOWS_VERSION', 	'1.4.3' );
if( ! defined( 'BELLOWS_PRO' ) )		define( 'BELLOWS_PRO' , 	false );

if( ! defined( 'BELLOWS_BASENAME' ) )	define( 'BELLOWS_BASENAME',	plugin_basename( __FILE__ ) );
if( ! defined( 'BELLOWS_BASEDIR' ) )	define( 'BELLOWS_BASEDIR',	dirname( plugin_basename(__FILE__) ) );
if( ! defined( 'BELLOWS_FILE' ) )		define( 'BELLOWS_FILE', 	__FILE__ );
if( ! defined( 'BELLOWS_URL' ) )		define( 'BELLOWS_URL', 		plugin_dir_url( __FILE__ ) );
if( ! defined( 'BELLOWS_DIR' ) )		define( 'BELLOWS_DIR', 		plugin_dir_path( __FILE__ ) );
if( ! defined( 'BELLOWS_ADMIN_CAP' ) )  define( 'BELLOWS_ADMIN_CAP', 'manage_options' );

include( 'Bellows.class.php' );	//Let's get the party started
