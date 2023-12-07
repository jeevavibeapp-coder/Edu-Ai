<?php

/**
 * Create HTML list of nav menu items.
 *
 * @since 3.0.0
 * @uses Walker
 */
class BellowsWalker extends Walker_Nav_Menu {

	public $config_id = 'main';
	public $submenu_toggle_icon_expand = 'chevron-down';
	public $submenu_toggle_icon_collapse = 'chevron-up';

	/*
	 * What the class handles.
	 *
	 * @see Walker::$tree_type
	 * @since 3.0.0
	 * @var string
	 */
	public $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * Database fields to use.
	 *
	 * @see Walker::$db_fields
	 * @since 3.0.0
	 * @todo Decouple this.
	 * @var array
	 */
	public $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

	function __construct() {
		//parent::__construct();
		$this->config_id = _BELLOWS()->get_current_config_id();

		if( BELLOWS_PRO ){
			$this->submenu_toggle_icon_expand = apply_filters( 'bellows_submenu_toggle_icon_expand', bellows_op( 'submenu_toggle_icon_expand' , $this->config_id ), $this->config_id );
			$this->submenu_toggle_icon_collapse = apply_filters( 'bellows_submenu_toggle_icon_collapse' , bellows_op( 'submenu_toggle_icon_collapse' , $this->config_id ), $this->config_id );
		}

	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<".BELLOWS_GROUP_TAG." class=\"bellows-submenu\">\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</".BELLOWS_GROUP_TAG.">\n";
	}



	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

		//Ignore UberMenu Elements
		if( $element->object == 'ubermenu-custom' ){

			$id = $element->ID;
			
			//This is the part of Walker_Nav_Menu:dispay_element that handles printing children
			if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id]) ) {
				foreach ( $children_elements[ $id ] as $child ){
					if ( !isset($newlevel) ) {
						$newlevel = true;
						//start the child delimiter
						$cb_args = array_merge( array(&$output, $depth), $args);
						//call_user_func_array(array($this, 'start_lvl'), $cb_args); // removed, as we don't want the opening UL
					}
					$this->display_element( $child, $children_elements, $max_depth, $depth+1, $args, $output );
				}
				unset( $children_elements[ $id ] );
			}

			return;
		}

		//bellp( $element );
		//if( $element->ID )
		//bellp( $children_elements );
		if( isset( $children_elements[$element->ID] ) ){
			$element->has_sub = 1;
		}
		else{
			$element->has_sub = 0;
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}



	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $item       		  Menu item data object.
	 * @param int      $depth             Depth of menu item. Used for padding.
	 * @param stdClass $args              An object of wp_nav_menu() arguments.
	 * @param int      $id				  Optional. ID of the current menu item. Default 0.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {

		$item_output = '';

		$target_classes = [ 'bellows-target' ];

		$config_id = isset( $args->bellows_config ) ? $args->bellows_config : '';

		//Get menu item settings as $data array
		$data = bellows_get_menu_item_data( $item->ID );
		$data = apply_filters( 'bellows_menu_item_data' , $data , $item );

		//Basic Setup
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;


		//Description setup
		$description = '';
		if( isset( $item->description ) ){
			$description = $item->description;
			if( $description ) $target_classes[] = 'bellows-target-w-desc';
		}

		//Icon
		$icon = isset( $data['icon'] ) ? $data['icon'] : '';
		$icon_classes = apply_filters( 'bellows_icon_custom_class' , $icon , $item->ID , isset( $data['icon_custom_class'] ) ? $data['icon_custom_class'] : '' );
		//if( isset( $data['icon'] ) && $data['icon'] != '' ){
		if( $icon_classes ){
			$classes[] = 'has-icon';
			//$icon = '<i class="bellows-icon '.$data['icon'].'"></i>';
			$icon = '<i class="bellows-icon '.$icon_classes.'"></i>';
		}

		//Disable Link
		$disable_link = isset( $data['disable_link'] ) && ( $data['disable_link'] == 'on' ) ? true : false;

		//Show More
		$data_show_more = '';
		$show_more = isset( $data['show_more'] ) && ( $data['show_more'] == 'on' ) ? true : false;
		if( $show_more ){
			$classes[] = 'show-more-toggle';
			if( isset( $data['show_less_text'] ) && $data['show_less_text'] ){
				$data_show_more = ' data-show-less="'.$data['show_less_text'].'"';
			}
		}


		//Image
		$img = '';
		$img_position = '';
		$img_id = isset( $data['item_image'] ) ? $data['item_image'] : '';

		//Inherit featured image dynamically
		if( isset( $data['inherit_featured_image'] ) && $data['inherit_featured_image'] == 'on' ){
			if( $item->type == 'post_type' ){
				$thumb_id = get_post_thumbnail_id( $item->object_id );
				if( $thumb_id ) $img_id = $thumb_id;
			}
		}

		if( $img_id ){
			$target_classes[] = 'bellows-target-w-image';

			//Image Position
			$img_position = isset( $data['image_position'] ) ? $data['image_position'] : 'before';
			$target_classes[] = 'bellows-target-w-image-'.$img_position;

			$atts = array();
			$img_srcset = $img_sizes = '';

			$atts['class'] = 'bellows-image';
			$img_size = bellows_op( 'image_size' , $config_id ); //'full';									//TODO

			$atts['class'].= ' bellows-image-size-'.$img_size;

			if( isset( $data['image_padding'] ) && $data['image_padding'] == 'on' )
				$atts['class'].= ' bellows-image-padded';

			//Get the image src
			$img_src = wp_get_attachment_image_src( $img_id , $img_size );
			if( function_exists( 'wp_get_attachment_image_srcset' ) ){
				$img_srcset = wp_get_attachment_image_srcset( $img_id , $img_size );
				$img_sizes = wp_get_attachment_image_sizes( $img_id , $img_size );
			}


			$atts['src'] = $img_src[0];
			if( $img_srcset ){
				$atts['srcset'] = $img_srcset;
				if( $img_sizes ) $atts['sizes'] = $img_sizes;
			}

			$img_w = $img_src[1];
			$img_h = $img_src[2];

			//Add 'alt' & 'title'
			$meta = get_post_custom( $img_id );
			$alt = isset( $meta['_wp_attachment_image_alt'] ) ? $meta['_wp_attachment_image_alt'][0] : '';	//Alt field
			$title = '';

			if( $alt == '' ){
				$title = get_the_title( $img_id );
				$alt = $title;
			}
			$atts['alt'] = $alt;

			// if( $this->get_menu_op( 'image_title_attribute' ) == 'on' ){
			// 	if( $title == '' ) $title = get_the_title( $img_id );
			// 	$atts['title'] = $title;
			// }

			//Build attributes string
			$atts = apply_filters( 'bellows_item_image_attributes' , $atts , $item );
			$attributes = '';
			foreach( $atts as $name => $val ){
				$attributes.= $name . '="'. esc_attr( $val ) .'" ';
			}

			$img = "<img $attributes />";

		}



		//Custom Content
		$custom_content = isset( $data['custom_content'] ) ? $data['custom_content'] : '';
		if( $custom_content ) $classes[] = 'has-custom-content';
		$custom_content_only = isset( $data['custom_content_only'] ) && $data['custom_content_only'] == 'on' ? true : false;


		//Widget
		$widget = isset( $data['auto_widget_area'] ) ? $data['auto_widget_area'] : '';
		if( $widget ) $classes[] = 'has-widget';



		//Item Level
		$classes[] = 'item-level-'.$depth;


		//Default Submenu state
		$default_submenu_state = bellows_op( 'default_submenu_state' , $config_id );	//Global state
		if(isset( $data['default_submenu_state'] ) ){									//Check for menu item override
			if( $data['default_submenu_state'] != 'inherit' ){
				$default_submenu_state = $data['default_submenu_state'];
			}
		}
		if( $default_submenu_state == 'open' && $item->has_sub ){
			$classes[] = 'active';
		}


		//Current item classes for terms in menu with associated posts
		//WordPress already determines this, but we want to add current-menu-item classes specifically, to unify
		//Note this does not apply to automatic menus because that type is "auto-term"
		if( $item->type === 'taxonomy' ){
			if( bellows_op( 'current_associated_term' , $config_id ) === 'on' ){
				if( $item->current_item_parent ){
					$classes[] = 'current-menu-item';
				}
				if( $item->current_item_ancestor ){
					$classes[] = 'current-menu-ancestor';
				}
			}
		}




		/**
		 * Filter the CSS class(es) applied to a menu item's list item element.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array  $classes The CSS classes that are applied to the menu item's `<li>` element.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of {@see wp_nav_menu()} arguments.
		 * @param int    $depth   Depth of menu item. Used for padding.
		 */
		// $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		// $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$this->item_classes = apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth );
		$this->prefix_classes( $args );
		//$this->item_classes[] = 'bellows-item-level-'.$depth;
		$class_names = join( ' ', $this->item_classes );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';


		/**
		 * Filter the ID applied to a menu item's list item element.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of {@see wp_nav_menu()} arguments.
		 * @param int    $depth   Depth of menu item. Used for padding.
		 */

		$id_prefix = in_array( $args->bellows_source, array( 'menu', 'section' ) ) ? '' : trim( $args->bellows_source , 's' ).'-';
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $id_prefix . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$data_id = '';
		if( $args->bellows_source == 'posts' ){
			$data_id = ' data-post-id="'.$item->ID.'"';
		}
		else if( $args->bellows_source == 'terms' ){
			$data_id = ' data-term-id="'.$item->ID.'"';
		}

		$output .= $indent . '<'.BELLOWS_ITEM_TAG . $id . $class_names . $data_id . $data_show_more . '>';

		if( !$custom_content_only ){
			//Target atts
			$atts = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
			$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
			$atts['href']   = ! empty( $item->url )        ? $item->url        : '';
			$atts['class']  = implode( ' ' , $target_classes );

			/**
			 * Filter the HTML attributes applied to a menu item's anchor element.
			 *
			 * @since 3.6.0
			 * @since 4.1.0 The `$depth` parameter was added.
			 *
			 * @param array $atts {
			 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
			 *
			 *     @type string $title  Title attribute.
			 *     @type string $target Target attribute.
			 *     @type string $rel    The rel attribute.
			 *     @type string $href   The href attribute.
			 * }
			 * @param object $item  The current menu item.
			 * @param array  $args  An array of {@see wp_nav_menu()} arguments.
			 * @param int    $depth Depth of menu item. Used for padding.
			 */
			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			//Bellows-specific filter
			$atts = apply_filters( 'bellows_link_attributes', $atts, $item, $args, $depth );



			//Disable Link
			$el = 'a';
			if( $disable_link ){
				$el = 'span';
				unset( $atts['href'] );
			}


			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );

					if( $attr === 'href' && ( $custom_url = ( isset( $data['custom_url'] ) ? $data['custom_url'] : '' ) ) ){
						$value = do_shortcode( $custom_url );
					}

					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}



			//Title / Navigation Label
			$title = '';
			if( !isset( $data['disable_text'] ) || $data['disable_text'] == 'off' ){
				/** This filter is documented in wp-includes/post-template.php */
				$title = apply_filters( 'the_title', $item->title, $item->ID );
				$title = do_shortcode( $title );
			}


			//Only print target if there's something inside it
			if( $title || $icon || $img || $description ){
				$item_output .= $args->before;
				$item_output .= '<'.$el.' '. $attributes .'>';

				if( $img_position == 'before' ) $item_output .= $img;

				$titletext = $icon . $title;

				//if( $title && ( $icon || $description || $img ) )
				if( $title ) $titletext = '<span class="bellows-target-title bellows-target-text">'.$titletext.'</span>';
				$item_output .= $args->link_before . $titletext . $args->link_after;

				//Description
				if( $description ){
					$description = do_shortcode( $description );

					$description = '<span class="bellows-target-description bellows-target-text">' . $description . '</span>';

					//TODO: Divider
					//$divider = $this->get_menu_op( 'target_divider' );
					$divider = ' &ndash; ';
					if( $title && $divider ) $description = '<span class="bellows-target-divider">'.$divider.'</span>' . $description;

					$item_output .= $description;
				}

				if( $item->has_sub ) {
					$submenu_toggle_aria_label = apply_filters( 'bellows_submenu_toggle_aria_label', __( 'Toggle Submenu', 'bellows' ), $this->config_id, $item );
					$submenu_toggle_content = apply_filters( 'bellows_submenu_toggle_content', '<i class="bellows-subtoggle-icon-expand fa fa-'.$this->submenu_toggle_icon_expand.'"></i><i class="bellows-subtoggle-icon-collapse fa fa-'.$this->submenu_toggle_icon_collapse.'"></i>', $this->config_id, $item );
					$submenu_toggle = '<button class="bellows-subtoggle" aria-label="'.$submenu_toggle_aria_label.'">'.$submenu_toggle_content.'</button>';

					$item_output.= $submenu_toggle;
				}

				if( $img_position == 'after' ) $item_output .= $img;

				$item_output .= '</'.$el.'>';
			}

		} //end link when custom content only


		//Custom Content
		//$custom_content = isset( $data['custom_content'] ) ? $data['custom_content'] : '';
		if( $custom_content ){
			$pad_custom_content = isset( $data['pad_custom_content'] ) && ( $data['pad_custom_content'] == 'on' ) ? 'bellows-custom-content-padded' : '' ;
			$custom_html = '<div class="bellows-content-block bellows-custom-content '.$pad_custom_content.'">';
			$custom_html.= do_shortcode( $custom_content );
			$custom_html.= '</div>';

			$item_output .= $custom_html;
		}


		if( $widget ){
			$widget_area_id = '';
			$custom_area_id = 'bellowsitem_'.$item->ID;
			//echo $custom_area_id;
			if( is_active_sidebar( $custom_area_id ) ){
				$widget_area_id = $custom_area_id;
			}
			else{
				$notice = __( 'The widget area is empty.' , 'bellows' );
				$notice.= ' <a target="_blank" href="'.admin_url( 'widgets.php' ).'">'.__( 'Assign a widget' , 'bellows' ).'</a>';
				global $wp_registered_sidebars;
				if( isset( $wp_registered_sidebars[$custom_area_id] ) ){
					$sidebar = $wp_registered_sidebars[$custom_area_id];
					$notice.= ' to <strong>'.$sidebar['name'].'</strong>';
				}

				$item_output.= bellows_admin_notice( $notice , false );
			}

			if( $widget_area_id ){
				ob_start();
				dynamic_sidebar( $widget_area_id );
				$widget_area = ob_get_contents();
				ob_end_clean();

				$item_output.= '<ul class="bellows-content-block bellows-widget-area">';
				$item_output.= $widget_area;
				$item_output.= '</ul>';
			}
		}




		/**
		 * Filter a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param object $item        Menu item data object.
		 * @param int    $depth       Depth of menu item. Used for padding.
		 * @param array  $args        An array of {@see wp_nav_menu()} arguments.
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	function prefix_classes( $args ){

		$k = 0;
		$found = false;
		foreach( $this->item_classes as $i => $class ){

			//The first class is custom, so ignore it
			//if( $k == 0 ){ $k++; continue; }

			//menu-item marks the first class we want to preix, so ignore everything before that
			if( !$found && $class == 'menu-item' ) $found = true;
			if( !$found ) continue;

			if( $class ){
				//remove menu-item-id
				// if( $args->bellows_source != 'menu' && $class == 'menu-item-'.$id ) ){
				// 	unset( $this->item_classes[$i] );
				// }
				// else
					$this->item_classes[$i] = 'bellows-'.$class;
				//add to end if using both
			}
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Page data object. Not used.
	 * @param int    $depth  Depth of page. Not Used.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function end_el( &$output, $item, $depth = 0, $args = array() ) {
		$output .= "</".BELLOWS_ITEM_TAG.">\n";
	}

} // Walker_Nav_Menu
