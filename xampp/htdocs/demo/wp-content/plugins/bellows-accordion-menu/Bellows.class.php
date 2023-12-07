<?php

if ( !class_exists( 'Bellows' ) ) :

final class Bellows {
	/** Singleton *************************************************************/

	private static $instance;
	private static $settings_api;
	private static $skins;
	private static $settings_defaults;
	private static $registered_icons;
	private static $current_config_id = 'bellows-main';

	private static $theme_location_counts = array();
	private static $menu_instance_counts = array();

	private static $settings_fields = false;

	private static $support_url;

	private static $item_styles;

	public static function instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new Bellows;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->activation_check();
		}
		return self::$instance;
	}

	/**
	 * Setup plugin constants
	 *
	 * @since 1.0
	 * @access private
	 * @uses plugin_dir_path() To generate plugin path
	 * @uses plugin_dir_url() To generate plugin url
	 */
	private function setup_constants() {
		// Plugin version

		if( ! defined( 'BELLOWS_PRO' ) )
			define( 'BELLOWS_PRO', false );

		if( ! defined( 'BELLOWS_MENU_ITEM_META_KEY' ) )
			define( 'BELLOWS_MENU_ITEM_META_KEY' , '_bellows_settings' );

		if( ! defined( 'BELLOWS_EXTENDED' ) )
			define( 'BELLOWS_EXTENDED', false );

		if( ! defined( 'BELLOWS_GROUP_TAG' ) )
			define( 'BELLOWS_GROUP_TAG' , 'ul' );

		if( ! defined( 'BELLOWS_ITEM_TAG' ) )
			define( 'BELLOWS_ITEM_TAG' , 'li' );



		define( 'BELLOWS_MENU_CONFIGURATIONS' , 'bellows_configurations' );

		define( 'BELLOWS_SKIN_GENERATOR_STYLES' , '_bellows_skin_generator_styles' );		//Key for Skin Gen Styles Array
		define( 'BELLOWS_MENU_STYLES' , '_bellows_menu_styles' );							//Key for Menu Styles Array
		define( 'BELLOWS_MENU_ITEM_STYLES' , '_bellows_menu_item_styles' );				//Key for Item Styles Array

		define( 'BELLOWS_MENU_ITEM_WIDGET_AREAS' , '_bellows_menu_item_widget_areas' );

		define( 'BELLOWS_GENERATED_STYLE_TRANSIENT' , '_bellows_generated_styles' );
		if( ! defined( 'BELLOWS_GENERATED_STYLE_TRANSIENT_EXPIRATION' ) )
			define( 'BELLOWS_GENERATED_STYLE_TRANSIENT_EXPIRATION' , 30 * DAY_IN_SECONDS );


		//URLS
		define( 'BELLOWS_SUPPORT_URL' , 'http://sevenspark.com/help' );
		define( 'BELLOWS_KB_URL' , 'http://sevenspark.com/docs/bellows' );
		define( 'BELLOWS_PRO_URL' , 'http://getbellows.com' );
		

		define( 'BELLOWS_PREFIX' , 'bellows_' );
	}

	private function includes() {
		
		require_once BELLOWS_DIR . 'includes/BellowsWalker.class.php';
		// //require_once BELLOWS_DIR . 'includes/icons.php';
		require_once BELLOWS_DIR . 'includes/functions.php';
		require_once BELLOWS_DIR . 'includes/bellows.api.php';
		require_once BELLOWS_DIR . 'customizer/customizer.php';
		

		require_once BELLOWS_DIR . 'admin/admin.php';

		if( BELLOWS_PRO ) require_once BELLOWS_DIR . 'pro/bellows.pro.php';

		// if( BELLOWS_PRO ) require_once BELLOWS_DIR . 'pro/bellows.pro.php';

	}

	private function activation_check(){

		if( BELLOWS_PRO ){
			$last_activated = get_option( 'bellows_pro_version' , '0' );
			if( !version_compare( $last_activated , BELLOWS_VERSION , '=' ) ){
				do_action( 'bellows_update' );
				update_option( 'bellows_pro_version' , BELLOWS_VERSION );
			}
		}
	}

	public function settings_api(){
		if( self::$settings_api == null ){
			self::$settings_api = new Bellows_Settings_API();
		}
		return self::$settings_api;
	}

	public function get_current_config_id(){
		return self::$current_config_id;
	}

	public function set_current_config_id( $instance_id ){
		return self::$current_config_id = $instance_id;
	}

	public function count_theme_location( $theme_location ){
		if( !isset( self::$theme_location_counts[$theme_location] ) ){
			self::$theme_location_counts[$theme_location] = 0;
		}
		self::$theme_location_counts[$theme_location]++;
	}
	public function get_theme_location_count( $theme_location ){
		return isset( self::$theme_location_counts[$theme_location] ) ? self::$theme_location_counts[$theme_location] : 0;
	}
	public function count_menu_instance( $menu_id ){
		if( !isset( self::$menu_instance_counts[$menu_id] ) ){
			self::$menu_instance_counts[$menu_id] = 0;
		}
		self::$menu_instance_counts[$menu_id]++;
	}
	public function get_menu_instance_count( $menu_id ){
		return isset( self::$menu_instance_counts[$menu_id] ) ? self::$menu_instance_counts[$menu_id] : 0;
	}


	public function get_skins(){
		return self::$skins;
	}
	public function register_skin( $id , $title , $src ){
		if( self::$skins == null ){
			self::$skins = array();
		}
		self::$skins[$id] = array(
			'title'	=> $title,
			'src'	=> $src,
		);

		wp_register_style( 'bellows-'.$id , $src , false , BELLOWS_VERSION );
	}


	function get_settings_fields(){
		return self::$settings_fields;
	}
	function set_settings_fields( $fields ){
		self::$settings_fields = $fields;
	}

	public function set_defaults( $fields ){

		if( self::$settings_defaults == null ) self::$settings_defaults = array();

		foreach( $fields as $section_id => $ops ){

			self::$settings_defaults[$section_id] = array();

			foreach( $ops as $op ){
				self::$settings_defaults[$section_id][$op['name']] = isset( $op['default'] ) ? $op['default'] : '';
			}
		}

		//bellp( $this->settings_defaults );

	}

	function get_defaults( $section = null ){
		if( self::$settings_defaults == null ) self::set_defaults( bellows_get_settings_fields() );

		if( $section != null && isset( self::$settings_defaults[$section] ) ) return self::$settings_defaults[$section];
		
		return self::$settings_defaults;
	}

	function get_default( $option , $section ){

		if( self::$settings_defaults == null ) self::set_defaults( bellows_get_settings_fields() );

		$default = '';

		//echo "[[$section|$option]]  ";
		if( isset( self::$settings_defaults[$section] ) && isset( self::$settings_defaults[$section][$option] ) ){
			$default = self::$settings_defaults[$section][$option];
		}
		return $default;
	}

	function register_icons( $group , $iconmap ){
		if( !is_array( self::$registered_icons ) ) self::$registered_icons = array();
		self::$registered_icons[$group] = $iconmap;
	}
	function degister_icons( $group ){
		if( is_array( self::$registered_icons ) && isset( self::$registered_icons[$group] ) ){
			unset( self::$registered_icons[$group] );
		}
	}
	function get_registered_icons(){ //$group = '' ){
		return self::$registered_icons;
	}


	static function is_mobile(){
		if( self::$is_mobile === null ){
			self::$is_mobile = apply_filters( 'bellows_is_mobile' , wp_is_mobile() );
		}
		return self::$is_mobile;
	}
	function display_now(){

		if( self::$display_now === null ){

			$display = true;

			//Mobile only and this isn't mobile
			if( bellows_op( 'mobile_only' , 'general' ) == 'on' && !self::is_mobile() ){
				$display = false;
			}

			self::$display_now = apply_filters( 'bellows_display_now' , $display );
		}

		return self::$display_now;	

	}



	function set_item_style( $item_id , $selector , $property_map ){
		//Get all stored menu item styles
		$item_styles = _BELLOWS()->get_item_styles( $item_id );

		//Initialize new array if this menu item doesn't have any rules yet
		if( !isset( self::$item_styles[$item_id] ) ){
			self::$item_styles[$item_id] = array();
		}

		if( $selector ){
			//Initialize new array if this selector doesn't exist yet
			if( !isset( self::$item_styles[$item_id][$selector] ) ){
				self::$item_styles[$item_id][$selector] = array();
			}

			if( is_array( $property_map ) ){
				//Add to the $properties array
				foreach( $property_map as $property => $value ){
					if( $value == '' ){
						unset( self::$item_styles[$item_id][$selector][$property] );
					}
					else self::$item_styles[$item_id][$selector][$property] = $value;
				}
			}
		}

	}
	function get_item_styles( $reset_id = false ){
		if( !is_array( self::$item_styles ) ){
			self::$item_styles = get_option( BELLOWS_MENU_ITEM_STYLES , array() );
			if( $reset_id ){
				//reset the item's styles so we can re-save from scratch
				unset( self::$item_styles[$reset_id] );
			}
		}
		return self::$item_styles;
	}
	function update_item_styles(){
		if( is_array( self::$item_styles ) ){

			//Clear out empty arrays
			foreach( self::$item_styles as $item_id => $styles ){
				if( !is_array( $styles ) || empty( $styles ) ){
					unset( self::$item_styles[$item_id] );
				}
			}

			update_option( BELLOWS_MENU_ITEM_STYLES , self::$item_styles );
		}
		self::$item_styles = null;	//reset so we'll need to grab it again
	}




	function get_support_url(){

		if( self::$support_url ){
			return self::$support_url;
		}

		$url = BELLOWS_SUPPORT_URL;

		$data = array();


		$data['src']			= 'bellows_pro_plugin';
		$data['product_id']		= 11;

		//Site Data
		$data['site_url'] 		= get_site_url();
		$data['version']		= BELLOWS_VERSION;
		$data['timezone']		= get_option('timezone_string');

		//Theme Data
		$theme = wp_get_theme();

		$data['theme']			= $theme->get( 'Name' );
		$data['theme_link']		= '<a target="_blank" href="'.$theme->get( 'ThemeURI' ).'">'. $theme->get( 'Name' ). ' v'.$theme->get( 'Version' ).' by ' . $theme->get( 'Author' ).'</a>';
		$data['theme_slug']		= isset( $theme->stylesheet ) ? $theme->stylesheet : '';
		$data['theme_parent']	= $theme->get( 'Template' );

		//User Data
		$current_user = wp_get_current_user();
		if( $current_user ){
			if( $current_user->user_firstname ){
				$data['first_name']		= $current_user->user_firstname;
			}
			if( $current_user->user_firstname ){
				$data['last_name']		= $current_user->user_lastname;
			}
			if( $current_user ){
				$data['email']			= $current_user->user_email;
			}
		}
		//$data['email']			= get_bloginfo( 'admin_email' );


		//License Data
		$license_code = bellows_op( 'license_code' , 'updates' , '' );
		if( $license_code ){
			$data['license_code']	= $license_code;
		}

		$query = http_build_query( $data );

		$support_url = "$url?$query";
		self::$support_url = $support_url;

		return $support_url;
	}

}
else: //if( defined( 'BELLOWS_PRO' ) && BELLOWS_PRO ):

	function deactivate_bellows() {
		if ( is_plugin_active('bellows-accordion-menu/bellows-accordion-menu.php') ) {
			deactivate_plugins('bellows-accordion-menu/bellows-accordion-menu.php');    
		}
	}
	add_action( 'admin_init', 'deactivate_bellows' );

	//or
	function bellows_duplicate_warning(){
		echo '<div class="notice notice-info is-dismissable"><p><strong>Attempting to disable Bellows [Lite]</strong>.  Please be sure that the free version of Bellows has been disabled in order to use Bellows Pro.  Your settings will be automatically inherited.</p></div>';
	}
	add_action( 'admin_notices' , 'bellows_duplicate_warning' );

endif; // End if class_exists check

if( !function_exists( '_BELLOWS' ) ){
	function _BELLOWS() {
		return Bellows::instance();
	}
	_BELLOWS();
}
