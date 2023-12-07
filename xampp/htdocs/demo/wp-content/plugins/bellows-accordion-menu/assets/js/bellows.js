
; (function ($, window, document, undefined) {

	"use strict";

	// undefined is used here as the undefined global variable in ECMAScript 3 is
	// mutable (ie. it can be changed by someone else). undefined isn't really being
	// passed in so we can ensure the value of it is truly undefined. In ES5, undefined
	// can no longer be modified.

	// window and document are passed through as local variable rather than global
	// as this (slightly) quickens the resolution process and can be more efficiently
	// minified (especially when both are regularly referenced in your plugin).

	// Create the defaults once
	var pluginName = "bellows",
		defaults = {
			folding: "multiple",	//multiple, single
			current: "off",		//off, on
			slide_speed: 400,
		};

	// The actual plugin constructor
	function Plugin(element, options) {
		this.element = element;
		// extend merges the contents of two or more objects, storing the result 
		// in the first object. The first object is generally empty as we don't 
		// want to alter the default options for future instances of the plugin
		this.settings = $.extend({}, defaults, options);
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(Plugin.prototype, {

		init: function () {
			// this.element = DOM Element
			// this.settings = settings object
			// call functions: this.yourOtherFunction(this.element, this.settings)
			var plugin = this;
			plugin.initialize_subtoggles(plugin);
			plugin.force_current_tree(plugin);
			if (plugin.settings.current == 'on') plugin.initialize_current_submenus(plugin);
			plugin.initialize_show_more(plugin);
			plugin.initialize_menu_toggle(plugin);
			//console.log( plugin.settings );
		},

		/*
		 * Initalize the submenu toggles to open and close the submenus on click
		 */
		initialize_subtoggles: function (plugin) {
			var $el = $(plugin.element);									//$el is the .bellows element
			$el.removeClass('bellows-nojs');
			//find all the subtoggles, as well as targets with disabled links, and add click callback
			$el.find('.bellows-subtoggle, .bellows-menu-item-has-children > span.bellows-target').on('click', function (e) {
				var $item = $(this).closest('.bellows-menu-item');		//find the item of this subtoggle
				plugin.toggle_submenu($item, plugin);					//toggle the submenu
				return false;												//do not allow the link to be followed
			});
		},

		/*
		 * Toggle the submenu of an item ($li) open or closed based on its current state
		 */
		toggle_submenu: function ($li, plugin) {
			//If the submenu is currently open, close it
			if ($li.hasClass('bellows-active')) {
				plugin.close_submenu($li, plugin);
			}
			//Otherwise, open the submenu
			else {
				plugin.open_submenu($li, plugin);

				//If folding is set to 'single' (one submenu open at a time), close any sibling submenus
				if (plugin.settings.folding == 'single') plugin.close_sibling_submenus($li, plugin);
			}
		},

		/*
		 * Open the submenu of item $li
		 */
		open_submenu: function ($li, plugin, speed = plugin.settings.slide_speed) {
			//Check if there is a submenu, first
			var $submenu = $li.find('> .bellows-submenu');
			if ($submenu.length) {
				//Add the active class, then slideDown the submenu
				$submenu.slideDown(parseInt(speed), function () {
					$li.trigger('bellowsopen');
					$li.addClass('bellows-active');
				});
			}
		},

		/*
		 * Close the submenu of item $li
		 */
		close_submenu: function ($li, plugin) {
			//Remove the active class, then slideUp the submenu

			// $li.removeClass( 'bellows-active' ).find( '> .bellows-submenu' ).slideUp( 400 , function(){
			// 		$li.trigger( 'bellowsclose' );
			// 	});

			$li.find('> .bellows-submenu').slideUp(parseInt(plugin.settings.slide_speed), function () {
				$li.removeClass('bellows-active');
				$li.trigger('bellowsclose');
			});
		},


		/*
		 * Close any submenus of items that are at the same level as $li
		 */
		close_sibling_submenus: function ($li, plugin) {
			$li.siblings().each(function () {		//Find all siblings
				plugin.close_submenu($(this), plugin);	//Close their submenus
			});
		},

		/*
		 * Open the submenu of the current menu item
		 */
		initialize_current_submenus: function (plugin) {
			var $current = null;

			// If there are priority items that are current, only open those ones
			var $priority = $(plugin.element).find('.bellows-current-menu-item.bellows-current-item-priority');
			if ($priority.length) {
				$current = $priority;
			}
			// Otherwise, open all current and ancestors
			else {
				$current = $(plugin.element).find('.bellows-current-menu-item, .bellows-current-menu-ancestor, .bellows-current-menu-parent');
			}
			$current.each(function () {
				plugin.open_submenu($(this), plugin, 0);
				$(this).parentsUntil('.bellows-nav', '.bellows-menu-item:not(.bellows-active)').each(function () {
					plugin.open_submenu($(this), plugin, 0);
				});
			});
		},

		/*
		 * Make sure that parents of current menu items are also marked as current
		 */
		force_current_tree: function (plugin) {
			$(plugin.element).find('.bellows-current-menu-item')
				.parents('.bellows-menu-item:not(.bellows-current-menu-item,.bellows-current-menu-parent,.bellows-current-menu-ancestor)')
				.addClass('bellows-current-menu-ancestor');
		},

		initialize_show_more: function (plugin) {
			$(plugin.element).find('.bellows-show-more-toggle').each(function () {

				//Create Show Less element
				var $closer = $(this).clone().attr('id', '').addClass('bellows-show-less-toggle').removeClass('bellows-show-more-toggle');
				var $closer_title = $closer.find('.bellows-target-title');
				// var $closer_icon =  $closer_title.find( 'i' );
				// if( $closer_icon.length ) $closer_title.html( $closer_icon );
				//$closer_title.append( $(this).data( 'show-less' ) );
				$closer_title.html('<i class="bellows-icon fa fa-angle-up"></i> ' + $(this).data('show-less'));
				$(this).closest('.bellows-submenu,.bellows-nav').append($closer);

				//Toggle Open on Show More
				$(this).find('> .bellows-target').on('click', function (e) {
					e.preventDefault();
					$(this).parent().toggleClass('bellows-show-less');
				});

				//Toggle Closed on Show Less
				$closer.find('> .bellows-target').on('click', function (e) {
					e.preventDefault();
					$(this).parent().siblings('.bellows-show-more-toggle').toggleClass('bellows-show-less');
				});
			});
		},

		initialize_menu_toggle: function (plugin) {
			var $menu = $(plugin.element);
			var id = plugin.element.id;
			// console.log( id );
			var $toggles = $('.bellows-menu-toggle[aria-controls="' + id + '"]');
			$toggles.on('click', function () {
				$menu.toggleClass('bellows-mobile-open');
				$toggles.toggleClass('bellows-mobile-open');
			});
		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {

		var args = arguments;

		if (options === undefined || typeof options === 'object') {
			return this.each(function () {
				if (!$.data(this, "plugin_" + pluginName)) {
					$.data(this, "plugin_" + pluginName, new Plugin(this, options));
				}
			});
		}
		else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
			// Cache the method call to make it possible to return a value
			var returns;
			this.each(function () {
				var instance = $.data(this, 'plugin_' + pluginName);

				// Tests that there's already a plugin-instance and checks that the requested public method exists
				if (instance instanceof Plugin && typeof instance[options] === 'function') {

					// Call the method of our plugin instance, and pass it the supplied arguments.
					returns = instance[options].apply(instance, Array.prototype.slice.call(args, 1));
				}

				// Allow instances to be destroyed via the 'destroy' method
				if (options === 'destroy') {
					$.data(this, 'plugin_' + pluginName, null);
				}
			});

			// If the earlier cached method gives a value back return the value, otherwise return this to preserve chainability.
			return returns !== undefined ? returns : this;
		}
	};

})(jQuery, window, document);

(function ($) {

	var bellows_is_initialized = false;

	//jQuery( document ).ready( function($){
	jQuery(function ($) {
		initialize_bellows('document.ready');
	});

	//Backup
	$(window).on('load', function () {
		initialize_bellows('window.load');
	});

	function initialize_bellows(init_point) {

		if (bellows_is_initialized) return;

		bellows_is_initialized = true;

		//if( ( typeof console != "undefined" ) && init_point == 'window.load' ) console.log( 'Notice: Bellows initialized via ' + init_point + '.  This indicates that an unrelated error on the site prevented it from loading via the normal document ready event.' );


		//Initialize Bellows
		$('.bellows').each(function () {
			var config_id = $(this).find('> .bellows-nav').data('bellows-config');
			$(this).bellows(bellows_data.config[config_id]);
		});

		//For WP Google Maps
		var maprefresher = function () {

			//WP Google Maps free
			if ($(this).find('#wpgmza_map').length) {
				//InitMap();
				InitMap();
			}
			//WP Google Maps Pro
			else if ($(this).find('.wpgmza_map').length) {

				for (var entry in wpgmaps_localize) {
					//console.log( 'init ' + wpgmaps_localize[entry]['id'] );
					//InitMap( wpgmaps_localize[entry]['id'], true );

					var wpgob = wpgmaps_localize[entry];
					var gmap = MYMAP[wpgob['id']].map;
					google.maps.event.trigger(gmap, "resize");

					var lat = wpgob['map_start_lat'];
					var lng = wpgob['map_start_lng'];

					var myLatLng = new google.maps.LatLng(lat, lng);
					gmap.setCenter(myLatLng);
				}
			}
			$(this).off('bellowsopen');
		};
		if (typeof InitMap == 'function') $('.bellows-menu-item').on('bellowsopen', maprefresher);



	}
})(jQuery);