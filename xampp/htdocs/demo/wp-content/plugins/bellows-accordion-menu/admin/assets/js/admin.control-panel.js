(function($){

	var ssmenu_is_initialized = false;

	//jQuery( document ).ready( function($){
	jQuery(function($) {
		initialize_ssmenu( 'document.ready' );
	});

	//Backup
	$( window ).on( 'load', function(){
		initialize_ssmenu( 'window.load' );
	});

	function initialize_ssmenu( init_point ){

		if( ssmenu_is_initialized ) return;

		ssmenu_is_initialized = true;

		if( console && init_point == 'window.load' ) console.log( 'Notice: Bellows initialized via ' + init_point );

		//Manual code switcher
		$( '.ssmenu-manual-code-menu-selection' ).on( 'change' , function(){
			var $wrap = $( this ).closest( '.ssmenu-integration-code-wrap' );
			$wrap.find( '.ssmenu-integration-code' ).hide();
			$wrap.find( '.ssmenu-integration-code-'+$( this ).val() ).show();
		});


		//Color Gradients
		$('.ssmenu-color-stop').each( function(){
			var $colorstop = $(this);
			$colorstop.wpColorPicker({
				clear: _.throttle( function(){
					$colorstop.data( 'cleared' , true );
					//var hexcolor = $( this ).wpColorPicker( 'color' );
					//console.log( 'color = ' + $colorstop.wpColorPicker( 'color' ) );
					//$colorstop.wpColorPicker( 'color' , ' ' );
					//console.log( 'color = ' + $colorstop.wpColorPicker( 'color' ) );
					var $control = $(this).closest( 'td' );
					update_gradient_list( $control );
					//$colorstop.data( 'cleared' , false );
					
				}, 300 ),

				change: _.throttle( function( event , ui ){
					$colorstop.data( 'cleared' , false );
					//console.log( 'change ' + $(this).attr('class') );

					//var hexcolor = $( this ).wpColorPicker( 'color' );
					var $control = $(this).closest( 'td' );
					update_gradient_list( $control );

				}, 300 )
			});
		});


		$( '.ss-sub-section-tab' ).on( 'click' , function(){
			var $panel = $(this).closest( 'form' );
			$( this ).siblings().removeClass( 'ss-active' );
			$( this ).addClass( 'ss-active' );
			var group = $(this).data( 'section-group' );
			if( group == '_all' ){
				$panel.find( '.ss-field' ).show();
			}
			else{
				$panel.find( '.ss-field' ).hide();
				$panel.find( '.ss-field-group-' + group ).show();
			}

			ss_store( 'ss_menu_settings_tab' , $panel.parent().attr( 'id' ) , group );
		});


		//Show tab on page load
		$( '.postbox .group' ).each( function(){
			var group = ss_store( 'ss_menu_settings_tab' , $(this).attr('id') );
			if( group ){
				$(this).find( '[data-section-group='+group+']' ).trigger( 'click' );
			}
			else{
				$(this).find( '.ss-sub-section-tab:first-child' ).trigger( 'click' );
			}
		});



		$( '.ssmenu_configuration_notice_close, .ssmenu_configuration_close' ).on( 'click' , function(){
			$( '.ssmenu_configuration_wrap' ).fadeOut();
		});
		$( '.ssmenu_configuration_wrap' ).on( 'click' , function(e){
			if( $( e.target ).hasClass( 'ssmenu_configuration_wrap' ) ){
				$(this).fadeOut();
			}
		});

		$( '.ssmenu_configuration_toggle' ).on( 'click' , function(){
			$( '.ssmenu_configuration_container_wrap' ).fadeIn();
			$( '.ssmenu_configuration_container_wrap .ssmenu_configuration_input' ).focus();
		});

		$form = $( 'form.ssmenu_configuration_form' );
		$form.on( 'submit' , function(e){
			e.preventDefault();
			ssmenu_save_configuration( $form );
			return false;
		});

		$( '.ssmenu_configuration_create_button' ).on( 'click' , function(e){
			e.preventDefault();
			ssmenu_save_configuration( $form );
			return false;
		});

		$( '.ssmenu_configuration_button_delete' ).on( 'click' , function( e ){
			e.preventDefault();
			if( confirm( 'Are you sure you want to delete this Bellows Configuration?' ) ){
				ssmenu_delete_configuration( $(this) );
			}
			return false;
		});

		//Highlight code
		$( '.ssmenu-highlight-code' ).on( 'click' , function(e){
			ss_selectText( $(this)[0] );
		});



		//Open Hash Tab
		setTimeout( function(){
			if( window.location.hash ){
				//console.log( window.location.hash + '-tab ' + $( window.location.hash + '-tab' ).size() );

				$( window.location.hash + '-tab' ).trigger( 'click' );
			}
		} , 500 );


		$( '.ssmenu-welcome-dismiss' ).on( 'click' , function(e){
			e.preventDefault();
			$( '.ssmenu-welcome' ).fadeOut();
			$( '.ssmenu-welcome-video' ).attr( 'src' , '' );

			var data = {
				action: 'ssmenu_dismiss_welcome',
				ssmenu_nonce: $(this).data( 'ssmenu-nonce' )
			};
			jQuery.post( ajaxurl, data, function(response) {
				//console.log( response );
			});
		});

		$( '.ssmenu-welcome-dismiss-alert' ).remove();

		$( '.button-quickstart' ).on( 'click' , function(e){
			e.preventDefault();
			$( '.ssmenu-welcome' ).fadeIn().removeClass( 'ssmenu-welcome-hide' );
			$( '.ssmenu-welcome-video' ).attr( 'src' , $( '.ssmenu-welcome-video' ).data( 'src' ) );
		});




		$( '.bellows-autogenerator-code input' ).on( 'change' , function(){
			//console.log( $(this).attr( 'name' ) + ' :: ' + $(this).val() );
			//ON CHANGE, REGENERATE SHORTCODE

		});

		bellows_generator_ui();

	}

	function bellows_generator_ui(){

		var $all_previews = $( '.bellows-generator-preview' );

		//Select WordPress Menu or Auto Population
		$( 'input[name="bellows_gen_source"]' ).on( 'change' , function(){
			//console.log( $(this).val() );
			$( '.bellows-generator-tbr' ).removeClass( 'bellows-generator-tbr-revealed' );
			$( '.bellows-generator-tbr-' + $(this).val() ).addClass( 'bellows-generator-tbr-revealed' );

			//Reset Auto Pop Content Type selection
			$( 'input[name="bellows_gen_auto_type"]' ).prop( 'checked' , false );
		});

		//Select Posts or Terms
		$( 'input[name="bellows_gen_auto_type"]' ).on( 'change' , function(){
			//console.log( $(this).val() );
			$(this).closest( '.bellows-generator-tbr' ).find( '.bellows-generator-tbr' ).removeClass( 'bellows-generator-tbr-revealed' );
			$( '.bellows-generator-tbr-' + $(this).val() ).addClass( 'bellows-generator-tbr-revealed' );
		});

		//Code Toggles
		$( '.bellows-generator-code-toggles' ).each( function(){
			var $code = $(this).siblings( 'code' );
			$code.hide().first().show();
			var $toggles = $(this).find( '.bellows-generator-code-toggle' );
			$toggles.on( 'click' , function(){
				$toggles.removeClass( 'bellows-generator-code-toggle-selected' );
				$(this).addClass( 'bellows-generator-code-toggle-selected' );
				//console.log( 'click ' + '.bellows-generator-code-' + $(this).data( 'code-type' ) + ' :: ' + $code.length );
				$code.hide();
				$code.filter( '.bellows-generator-code-' + $(this).data( 'code-type' ) ).show();
			});
		});


		//Field expanders
		$( '.bellows-generator-field-toggle' ).on( 'click' , function(e){
			$(this).closest( '.bellows-generator-field' ).toggleClass( 'bellows-generator-field-expanded' );
		});


		// Toggle the Include Parent field appropriately when the Post Parent field is set or unset
		$post_parent_field = $( '.bellows-generator-tbr-posts .bellows-generator-field-post_parent input[name="posts_post_parent"]' );
		bellows_set_include_parent_visibility( $post_parent_field.val() != 0 );
		$post_parent_field.on( 'change', function(){
			bellows_set_include_parent_visibility( $(this).val() != 0 );
		});

		//Normal Menu
		bellows_generator_source_panel( 'menu' , 'bellows' , 'wp_nav_menu' );
		// var $menu_sc_code = $( '.bellows-generator-tbr-menu .bellows-generator-podium .bellows-generator-code-shortcode' );
		// var $menu_php_code = $( '.bellows-generator-tbr-menu .bellows-generator-podium .bellows-generator-code-php' );
		// var $menu_fields = $( '.bellows-generator-tbr-menu .bellows-generator-field' );
		// $( '.bellows-generator-tbr-menu input' ).on( 'change' , function(){
			
		// 	var q_args = bellows_generate_code_strings( $menu_fields , 'bellows' , $menu_sc_code , $menu_php_code );

		// 	var $preview = $( '.bellows-generator-tbr-menu .bellows-generator-preview' );
		// 	var data = {
		// 		'bellows_nonce': $preview.data( 'nonce' ),
		// 		'source'	: 'menu',
		// 		'args'		: q_args, //$.param( q_args ),
		// 		'action'	: 'bellows_generate_preview'
		// 	};
		// 	console.log( data );
		// 	$.post( ajaxurl, data, function(response) {
		// 		//console.log( response.menu );
		// 		//$preview.data( 'nonce' , response.nonce );
		// 		$all_previews.data( 'nonce' , response.nonce );
		// 		$preview.html( response.menu );
		// 		$preview.find( '.bellows' ).bellows();
		// 	}, 'json' );

		// });


		//Posts
		bellows_generator_source_panel( 'posts' , 'bellows_posts' , 'get_posts' );

		//Saved Query Toggle
		$( '.bellows-save-query-toggle' ).on( 'click' , function(){
			toggle_saved_queries( $(this).closest('.bellows-generator-preview-container') );
		});



		////////////////////////
		//
		//MAIN SAVE BUTTON
		//
		////////////////////////
		$( '.bellows-save-button' ).on( 'click' , function(){

			var $savebutton = $(this);
			$wrapper = $savebutton.closest( '.bellows-generator-tbr' );
			var $fields = $wrapper.find( '.bellows-generator-field' );
			var q_args = get_selected_query_args( $fields );

			

			//console.log( q_args );

			var data = {
				'_bellows_query_save_nonce': $(this).data( 'nonce' ),
				'action' : 'bellows_query_save',
				'query_id' : $savebutton.data( 'query-id' ),
				'query_title' : $wrapper.find( '.bellows-save-query-title' ).val(),
				'query_args' : $.param( q_args ),
				'query_type' : $savebutton.data( 'query-type' )
			};

			//console.log( data );

			$.post( ajaxurl, data, function(response) {

				$status_wrapper = $savebutton.closest('.bellows-generator-save').find( '.bellows-save-query-status-wrapper' );

				if( response == -1 ){
					//$this.text( 'Error communicating with server.' );
					$status_wrapper.find( '.bellows-save-query-status' ).remove();
					$status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-error">Error: '+ 'Error Communicating with Server or Authenticating Request' +'<span class="bellows-save-query-status-close"></span></div>' );
				}
				else{
					$savebutton.data( 'nonce' , response.nonce );

					//console.log( response );

					switch( response.status ){
						//Error
						case 2: 
							$status_wrapper.find( '.bellows-save-query-status' ).remove();
							$status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-error">Error: '+ response.msg +'<span class="bellows-save-query-status-close"></span></div>' );
							break;
						//Warning
						case 1:
							break;
						//Success
						case 0:
							$status_wrapper.find( '.bellows-save-query-status' ).remove();
							$status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-success">'+ response.msg +'<span class="bellows-save-query-status-close"></span></div>' );

							//Update current query ID
							$savebutton.data( 'query-id' , response.query_id );

							//Reload Query List
							if( response.query_list ){
								$wrapper.find( '.bellows-saved-queries-list' ).html( response.query_list );
							}

							break;

					}
				}
			}, 'json' );

		});


		//Close status
		$( '.bellows-save-query-status-wrapper' ).on( 'click', '.bellows-save-query-status-close', function(){
			$(this).parent().remove();
		});


		
		////////////////////////
		//
		//SAVED QUERIES: CODE
		//
		////////////////////////

		$( '.bellows-saved-queries' ).on( 'click' , '.bellows-saved-query-btn-code' , function(){
			$(this).closest('.bellows-saved-query').find( '.bellows-saved-query-code' ).slideToggle();
		});


		////////////////////////
		//
		//SAVED QUERIES: DELETE
		//
		////////////////////////

		$( '.bellows-saved-queries' ).on( 'click' , '.bellows-saved-query-btn-delete' , function(){

			var $btn = $(this);
			var $context = $btn.closest( '.bellows-generator-preview-container' );
			var $savebutton = $context.find( '.bellows-save-button' );

			if( confirm( 'Are you sure you want to delete this query?' ) ){

				var data = {
					'_bellows_query_save_nonce': $savebutton.data( 'nonce' ),
					'action' : 'bellows_saved_query_delete',
					'query_id' : $(this).closest( '.bellows-saved-query' ).data( 'qid' ),
				};

				$.post( ajaxurl, data, function(response) {

					$status_wrapper = $btn.closest('.bellows-generator-save').find( '.bellows-save-query-status-wrapper' );

					if( response == -1 ){
						//$this.text( 'Error communicating with server.' );
						$status_wrapper.find( '.bellows-save-query-status' ).remove();
						$status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-error">Error: '+ 'Error Communicating with Server or Authenticating Request' +'<span class="bellows-save-query-status-close"></span></div>' );
					}
					else{
						//$btn.data( 'nonce' , response.nonce );

						//console.log( response );

						switch( response.status ){
							//Error
							case 2: 
								$status_wrapper.find( '.bellows-save-query-status' ).remove();
								$status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-error">Error: '+ response.msg +'<span class="bellows-save-query-status-close"></span></div>' );
								break;
							//Warning
							case 1:
								break;
							//Success
							case 0:
								// $status_wrapper.find( '.bellows-save-query-status' ).remove();
								// $status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-success">'+ response.msg +'<span class="bellows-save-query-status-close"></span></div>' );

								set_notice( $context, 'success' , response.msg , true );
								$context.find( '.bellows-saved-query[data-qid="'+response.query_id+'"]' ).remove();

								//If we deleted the current query, reset
								if( response.query_id == $savebutton.data( 'query-id' ) ){

									set_query_id( $context, '-1' );
									set_query_title( $context, '' );
									//$savebutton.data( 'query-id' , '-1' );
									//$savebutton.siblings( '.bellows-save-query-title' ).val( '' );
								}

								//$btn.closest( '.bellows-saved-query' ).remove();
								break;

						}
					}
				}, 'json' );

			}

		});

		////////////////////////
		//
		//SAVED QUERIES: OPEN
		//
		////////////////////////
		$( '.bellows-saved-queries' ).on( 'click' , '.bellows-saved-query-btn-edit' , function(){

			var $btn = $(this);
			var $context = $btn.closest( '.bellows-generator-tbr' );
			var $savebutton = $context.find( '.bellows-save-button' );

			//Load parameters into form fields
			var data = {
				'_bellows_query_save_nonce': $savebutton.data( 'nonce' ),
				'action' : 'bellows_saved_query_load',
				'query_id' : $btn.closest( '.bellows-saved-query' ).data( 'qid' ),
			};

			$.post( ajaxurl, data, function(response) {

				//$status_wrapper = $btn.closest('.bellows-generator-save').find( '.bellows-save-query-status-wrapper' );

				if( response == -1 ){
					//$this.text( 'Error communicating with server.' );
					set_notice( $context, 'error' , 'Error: ' + 'Error Communicating with Server or Authenticating Request' , true );
				}
				else{

					switch( response.status ){
						//Error
						case 2: 
							set_notice( $context, 'error' , 'Error: ' + response.msg , true );
							break;
						//Warning
						case 1:
							break;
						//Success
						case 0:
							set_notice( $context, 'success' , response.msg , true );

							//If we deleted the current query, reset
							if( response.query_id ){
								set_query_id( $context, response.query_id );
								set_query_title( $context, response.query_title );
							}

							$context.find( '.bellows-generator-field' ).each( function(){
								name = $(this).data( 'name' );
								arg = $(this).data( 'arg' );
								input_type = $(this).data( 'type' );
								$input = $(this).find( 'input[name="'+name+'"]' );

								value = response.query_args[arg];

								switch( input_type ){
									case 'multicheck':

										val_arr = value.split(',');
										//console.log( val_arr );
										$input.each( function(){
											//console.log( $(this).attr( 'value' ) );
											if( val_arr.indexOf( $(this).attr('value') ) >= 0 ){
												$(this).prop( 'checked' , true );
												//$(this).trigger( 'change' );
											}
											else{
												$(this).prop( 'checked' , false );
											}
										});

										//$input.trigger( 'change' );

										break;
									case 'radio':
										$input.filter( '[value="'+value+'"]' ).prop('checked', true );
										break;
									case 'text':
									case 'post_author':
									case 'post_terms':
										//if it has an associated checkbox set loaded, check them
										//TODO: When check box set it loaded, make sure to check if anything is already entered in text box
										//no break
										//console.log( 'for ' + name );
										//console.log( $input.siblings( '.bellows-generator-ops-checklist' ) );
										if( $input.siblings( '.bellows-generator-ops-checklist' ).length ){
											$input.siblings( '.bellows-generator-ops-checklist' ).find( 'input[type="checkbox"]' ).each( function(){
												//console.log( 'check: ' + $(this).attr( 'value' ) );
												if( $(this).attr( 'value' ) == value ){
													$(this).prop( 'checked' , true );
												}
												else{
													$(this).prop( 'checked' , false );
												}
											});
										}
									default:
										$input.val( value );
										//$input.trigger( 'change' );
										break;
								}

								$input.trigger( 'change' ); // make sure code is rengerated at the end.

								//console.log( arg + ' :: ' + response.query_args[arg] );
								//$input.val( response.query_args[arg] )

							});

							break;

					}
				}
			}, 'json' );

		});



		////////////////////////
		//
		//SAVED QUERIES: SAVE
		//
		////////////////////////
		$( '.bellows-saved-queries' ).on( 'click' , '.bellows-saved-query-btn-save' , function(){

			var $btn = $(this);
			var $context = $btn.closest( '.bellows-generator-tbr' );
			var $savebutton = $context.find( '.bellows-save-button' );


			$wrapper = $savebutton.closest( '.bellows-generator-tbr' );
			var $fields = $wrapper.find( '.bellows-generator-field' );
			var q_args = get_selected_query_args( $fields );

			//Load parameters into form fields
			var data = {
				'_bellows_query_save_nonce': $savebutton.data( 'nonce' ),
				'action' : 'bellows_saved_query_save_over',
				'query_id' : $btn.closest( '.bellows-saved-query' ).data( 'qid' ),
				'query_args' : $.param( q_args ),
			};

			$.post( ajaxurl, data, function(response) {

				if( response == -1 ){
					set_notice( $context, 'error' , 'Error: ' + 'Error Communicating with Server or Authenticating Request' , true );
				}
				else{

					switch( response.status ){
						//Error
						case 2: 
							set_notice( $context, 'error' , 'Error: ' + response.msg , true );
							break;
						//Warning
						case 1:
							break;
						//Success
						case 0:
							set_notice( $context, 'success' , response.msg , true );
							break;
					}
				}
			}, 'json' );

		});



		function get_selected_query_args( $fields ){

			var q_args = [];
			
			$fields.each( function(){
				var $input = $(this).find( $(this).data( 'val-selector' ) );
				var val = $input.val();
				if( val == '' ) return;	//don't show undefined values

				switch( $(this).data( 'type' ) ){
				 	case 'multicheck':
				 		val = '';
				 		$input.each( function(i){
				 			//val_a[i] = $(this).val();
				 			val+= $(this).val() + ',';
				 		});
				 		//console.log( ' >> ' + val );
				 		if( val ) val = val.replace(/,\s*$/, "");
				 		break;
				 	case 'checkbox':
				 		//if( $input.val() != 'on' ) val = 'off';
				 		val = $input.is( ':checked' ); // ? 'true' : 'false';	//val() just returns its value regardless of state
				 		//console.log( $input.attr( 'name' ) + ' :: ' + $input.val() + ' :: ' + val );
				 		break;
				}

				var name = $input.attr( 'name' );
				var arg = $input.data( 'arg' );

//TODO - maybe don't ignore, as if they've set it, it shouldn't go to default any longer
//Or just don't ignore post type?

				if( val != $(this).data( 'default' ) || ( arg == 'config_id' ) ){
					q_args.push( { name : arg , value : val } );
				}
			});

			return q_args;
		}

		function set_notice( $context , msg_type , msg , clear_previous ){
			var $status_wrapper = $context.find( '.bellows-save-query-status-wrapper' );
			if( clear_previous != false ){
				$status_wrapper.find( '.bellows-save-query-status' ).remove();
			}
			$status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-'+msg_type+'">'+ msg +'<span class="bellows-save-query-status-close"></span></div>' );
		}
		function set_query_title( $context, title ){
			$context.find( '.bellows-save-query-title' ).val( title );
		}
		function set_query_id( $context , qid ){
			$context.find( '.bellows-save-button' ).data( 'query-id' , qid );
		}
		function toggle_saved_queries( $context , direction ){

			//console.log( 'toggle' );

			var $dropdown = $context.find( '.bellows-saved-queries' );
			var $toggle = $context.find( '.bellows-save-query-toggle');

			switch( direction ){
				case 'open':
					$dropdown.slideDown();
					$toggle.addClass( 'bellows-save-query-toggle-open' );
					break;
				case 'close':
					$dropdown.slideUp();
					$toggle.removeClass( 'bellows-save-query-toggle-open' );
					break;
				default:
					$dropdown.slideToggle();
					$toggle.toggleClass( 'bellows-save-query-toggle-open' );
					break;
			}

		}

		//"Create new query"
		$( '.bellows-saved-queries' ).on( 'click' , '.bellows-new-query' , function(){

			$btn = $(this);
			$savebutton = $btn.closest( '.bellows-generator-preview-container' ).find( '.bellows-save-button' );

			$savebutton.data( 'query-id' , '-1' );
			$savebutton.siblings( '.bellows-save-query-title' ).val( '' );
			$savebutton.siblings( '.bellows-saved-queries' ).slideUp();

			$status_wrapper = $btn.closest('.bellows-generator-save').find( '.bellows-save-query-status-wrapper' );

			$status_wrapper.find( '.bellows-save-query-status' ).remove();
			$status_wrapper.append( '<div class="bellows-save-query-status bellows-save-query-status-info">'+ 'New query initialized.  Enter a query name and click save.' +'<span class="bellows-save-query-status-close"></span></div>' );

			

		});

			//On Save Query click - send AJAX request to save
				//If a post ID is set for this query, update meta
				//If no post ID is set, create a new post
			//On clicking another option, if 
			//.....



		//Terms
		bellows_generator_source_panel( 'terms' , 'bellows_terms' , 'get_terms' );
		// var $terms_sc_code = $( '.bellows-generator-tbr-terms .bellows-generator-podium .bellows-generator-code-shortcode' );
		// var $terms_php_code = $( '.bellows-generator-tbr-terms .bellows-generator-podium .bellows-generator-code-php' );
		// var $terms_fields = $( '.bellows-generator-tbr-terms .bellows-generator-field' );
		// $( '.bellows-generator-tbr-terms input' ).on( 'change' , function(){
		// 	var q_args = bellows_generate_code_strings( $terms_fields , 'bellows_terms' , $terms_sc_code , $terms_php_code );
		// 	//console.log( 'q_args: ' );
		// 	console.log( q_args );

		// 	var $preview = $( '.bellows-generator-tbr-terms .bellows-generator-preview' );
		// 	var data = {
		// 		'bellows_nonce': $preview.data( 'nonce' ),
		// 		'source'	: 'terms',
		// 		'args'		: q_args, //$.param( q_args ),
		// 		'action'	: 'bellows_generate_preview'
		// 	};
		// 	console.log( data );
		// 	$.post( ajaxurl, data, function(response) {
		// 		//console.log( response.menu );
		// 		//$preview.data( 'nonce' , response.nonce );
		// 		$all_previews.data( 'nonce' , response.nonce );
		// 		$preview.html( response.menu );
		// 		$preview.find( '.bellows' ).bellows();
		// 	}, 'json' );

		// });


		//Load Authors
		$( '.bellows-generator-post-author-load' ).on( 'click' , function(e){
			e.preventDefault();
			var $this = $(this);
			var $field = $this.closest( '.bellows-generator-field' );
			var data = {
				'bellows_nonce': $this.data( 'nonce' ),
				'action' : 'bellows_generator_post_author_list'
			};
			$this.text( 'Loading...' );

			$.post( ajaxurl, data, function(response) {
				if( response == -1 ){
					$this.text( 'Could not load author options' );
				}
				else{
					$this.data( 'nonce' , response.nonce );
					if( response.ops ){
						$this.after( response.ops );
						var $checkboxes = $this.parent().find( 'input[type="checkbox"]' );
						$checkboxes.on( 'change' , function(){
							var $input = $field.find( $field.data( 'val-selector' ) );
							val = '';
					 		$checkboxes.filter( ':checked' ).each( function(i){
					 			val+= $(this).val() + ',';
					 		});
					 		if( val ) val = val.replace(/,\s*$/, "");
					 		$input.val( val ).trigger( 'change' );
						});
						$this.remove();
					}
					else{
						$this.after( '<p>No options available</p>' );
						$this.remove();
					}
				}
			}, 'json' );
		});


		//Terms
		$( '.bellows-generator-post-terms-load' ).on( 'click' , function(e){
			e.preventDefault();
			var $this = $(this);
			var $field = $this.closest( '.bellows-generator-field' );
			var data = {
				'bellows_nonce': $this.data( 'nonce' ),
				'tax_id' : $(this).data( 'tax-id' ),
				'action' : 'bellows_generator_post_terms_list'
			};
			$this.text( 'Loading...' );
			$.post( ajaxurl, data, function(response) {
				if( response == -1 ){
					$this.text( 'Could not load term options' );
				}
				else{
					$this.data( 'nonce' , response.nonce );
					if( response.ops ){
						$this.after( response.ops );
						var $checkboxes = $this.parent().find( 'input[type="checkbox"]' );

						//check any boxes of IDs already listed
						current_val = $field.find( $field.data( 'val-selector' ) ).val(); //$this.siblings( 'input[type="text"]' ).val();
						if( current_val != '' ){
							val_arr = current_val.split( ',' );
							$checkboxes.each( function(){
								if( val_arr.indexOf( $(this).attr( 'value' ) ) >= 0 ){
									$(this).prop('checked', true);
								}
							});
						}

						//when the checkbox is checked or unchecked, set the text box and trigger reload
						$checkboxes.on( 'change' , function(){
							var $input = $field.find( $field.data( 'val-selector' ) );
							val = '';
					 		$checkboxes.filter( ':checked' ).each( function(i){
					 			val+= $(this).val() + ',';
					 		});
					 		if( val ) val = val.replace(/,\s*$/, "");
					 		$input.val( val ).trigger( 'change' );
						});
						$this.remove();
					}
					else{
						$this.after( '<p>No options available</p>' );
						$this.remove();
					}
				}
			}, 'json' );
		});

		//Parent Terms
		$( '.bellows-generator-tbr-terms input[name="terms_taxonomy"]' ).on( 'change' , function(){
			var $tax = $( '.bellows-generator-field-taxonomy .bellows-generator-field-inner' );
			var val = $(this).val();
			//console.log( val );

			var $child_of = $( '.bellows-generator-field-child_of .bellows-generator-field-inner' );
			var $child_of_input = $( '.bellows-generator-field-child_of input[type="text"]' );
			var $child_of_sel = $child_of.find( '.bellows-generator-child_of-'+val );

			//console.log( $(this).prop( 'checked' ) ); //.val() );
			if( $(this).prop( 'checked' ) ){
				if( $child_of_sel.length ){
					$child_of_sel.show();
				}
				else{
					var data = {
						'bellows_nonce': $child_of_input.data( 'nonce' ),
						'tax_id'	: val,
						'action'	: 'bellows_generator_parent_terms'
					};
					$.post( ajaxurl, data, function(response) {
						//console.log( response.select );
						if( response == -1 ){
							$child_of.append( '<p class="bellows-generator-child_of-'+val+'">Could not load '+val+'s</p>' );
						}
						else{
							$child_of_input.data( 'nonce' , response.nonce );
							if( response.ops ){
								$child_of.append( '<p class="bellows-generator-child_of-'+val+'">'+response.ops+'</p>' );
								var $select = $child_of.find( '.bellows-generator-child_of-'+val+' select' );
								//$select.find( 'option[value=""]' ).remove().prependTo( $select );	//Move "Select a Page" into first position
								//$select.val( '' );	//Select "Select a Page" as primary option
								$select.on( 'change' , function(){
									$child_of_input.val( $(this).val() ).trigger( 'change' );
								});
							}
							else{
								$child_of.append( '<p class="bellows-generator-child_of-'+val+'">No options for '+val+'</p>' );
							}
						}
					}, 'json' );
				}
			}
			else{
				//$post_parent_sel.hide();
				$child_of_sel.hide();
			}
			
		});


		//Post parent based on Post Types
		$( '.bellows-generator-tbr-posts input[name="posts_post_type"]' ).on( 'change' , function(){

			var $post_parent = $( '.bellows-generator-field-post_parent .bellows-generator-field-inner' );
			var val = $(this).val();
			var $post_parent_sel = $( '.bellows-generator-post_parent-'+val );
			var $label = $post_parent.find('label');
			var $id_input = $post_parent.find( 'input[type="text"]' );
			//console.log( $(this).prop( 'checked' ) ); //.val() );
			if( $(this).prop( 'checked' ) ){
				if( $post_parent_sel.length ){
					$post_parent_sel.show();
				}
				else{
					var data = {
						'bellows_nonce': $label.data( 'nonce' ),
						'post_type'	: val,
						'action'	: 'bellows_generator_post_list'
					};
					$.post( ajaxurl, data, function(response) {
						//console.log( response.select );
						if( response == -1 ){
							$post_parent.append( '<p class="bellows-generator-post_parent-'+val+'">Could not load '+val+'s</p>' );
						}
						else{
							$label.data( 'nonce' , response.nonce );
							if( response.select ){
								$post_parent.append( '<p class="bellows-generator-post_parent-'+val+'">'+response.select+'</p>' );
								var $select = $post_parent.find( '.bellows-generator-post_parent-'+val+' select' );
								$select.find( 'option[value=""]' ).remove().prependTo( $select );	//Move "Select a Page" into first position
								$select.val( '' );	//Select "Select a Page" as primary option
								$select.on( 'change' , function(){
									$id_input.val( $(this).val() ).trigger( 'change' );
								});
							}
							else{
								$post_parent.append( '<p class="bellows-generator-post_parent-'+val+'">No options for '+val+'s</p>' );
							}
						}
					}, 'json' );
				}
			}
			else{
				$post_parent_sel.hide();
			}
			
		});
	}

	function bellows_set_include_parent_visibility( v ){
		var $e = $( '.bellows-generator-field-include_parent' );
		if( v ) $e.show();
		else $e.hide();
	}

	function bellows_generator_source_panel( source , shortcode , wp_func ){
		var $all_previews = $( '.bellows-generator-preview' );
		var $wrapper = $( '.bellows-generator-tbr-'+source );

		var $sc_code =  $wrapper.find( '.bellows-generator-podium .bellows-generator-code-shortcode' );
		var $php_code = $wrapper.find( '.bellows-generator-podium .bellows-generator-code-php' );
		
		var $fields = $wrapper.find( '.bellows-generator-field' );
		
		var $preview = $wrapper.find( '.bellows-generator-preview' );

		$wrapper.find( 'input' ).on( 'change' , function(){
			var q_args = bellows_generate_code_strings( $fields , shortcode , $sc_code , $php_code );
			//console.log( 'q_args: ' );
			//console.log( q_args );

			//Don't bother the server if no taxonomy is selected.
			if( source == 'terms' && !q_args.hasOwnProperty( 'taxonomy' ) ){
				$preview.html( '<center><em>You must select at least one taxonomy.</em></center>' );
				return;
			}

			var data = {
				'bellows_nonce': $preview.data( 'nonce' ),
				'source'	: source,
				'args'		: q_args, //$.param( q_args ),
				'action'	: 'bellows_generate_preview'
			};
			//console.log( data );
			$.post( ajaxurl, data, function(response) {
				if( response == -1 ){
					$preview.html( '<p>Could not load preview.  You may want to try reloading your page.</p>' );
				}
				else{
					$all_previews.data( 'nonce' , response.nonce );
					if( response.menu ){
						$preview.html( response.menu );
						$preview.find( '.bellows' ).bellows();
					}
					else{
						$preview.html( '<p>These parameters produce no results</p>' );
					}

					if( response.query_args ){
//console.log( response.query_args );
						var query_args = '<table class="bellows-generator-query-args">';
						for( var arg_name in response.query_args ){
							if( response.query_args.hasOwnProperty( arg_name ) ){
								var val = response.query_args[arg_name];
								if( Array.isArray( val ) ){
									var _val = '';
									for( key in val ){
										_val+= val[key] + '<br/>';
									}
									val = _val;
								}
								query_args += '<tr><td>'+arg_name+'</td><td>'+val+'</td></tr>';
							}
						}
						$preview.append( '<h5 class="bellows-generator-toggle-query-args">View '+wp_func+'() Arguments <i class="fa fa-angle-down"></i></h5>' + query_args );
						$preview.find( '.bellows-generator-toggle-query-args' ).on( 'click' , function(){
							$preview.find( '.bellows-generator-query-args' ).toggle();
						});
					}
				}
			}, 'json' ).fail( function( jqXHR, textStatus, errorThrown ){
				$preview.html( '<p>An error occurred.  Could not load preview.</p>' );
				//console.log( jqXHR.responseText );
				$preview.append( '<h5>Server response to AJAX request:</h5>' + '<p>' + jqXHR.responseText + '</p>' );
				
				//$preview.append( errorThrown );
			});

		});
	}

	function bellows_generate_code_strings( $fields , str_root , $sc_code , $php_code ){

		var sc_str = '['+str_root;
		var php_str = '<?php '+ str_root +'( ';
		var php_arg_2 = 'array( ';
		
		var q_args = {};

		$fields.each( function(){
			var $input = $(this).find( $(this).data( 'val-selector' ) );
			var val = $input.val();
			if( !val ) return;	//don't show undefined values

			switch( $(this).data( 'type' ) ){
			 	case 'multicheck':
			 		val = '';
			 		$input.each( function(i){
			 			//val_a[i] = $(this).val();
			 			val+= $(this).val() + ',';
			 		});
			 		//console.log( ' >> ' + val );
			 		if( val ) val = val.replace(/,\s*$/, "");
			 		break;
			 	case 'checkbox':
			 		//if( $input.val() != 'on' ) val = 'off';
			 		val = $input.is( ':checked' ); // ? 'true' : 'false';	//val() just returns its value regardless of state
			 		//console.log( $input.attr( 'name' ) + ' :: ' + $input.val() + ' :: ' + val );
			 		break;
			}

			var name = $input.attr( 'name' );
			var arg = $input.data( 'arg' );
			//console.log( arg + ' :: ' + val + ' :: ' + $(this).data( 'default' ) )
			//console.log( $(this).data( 'default' ) );
			if( val != $(this).data( 'default' ) || ( arg == 'config_id' ) ){

				//Shortcode
				if( arg != 'config_id' || val != 'main' ){
					sc_str+= ' ' + arg + '="' + val + '"';
				}

				//PHP
				if( arg == 'config_id' ){
					php_str+= "'"+val+"' , ";
				}
				else{
					if( val === true || val === false ){
						php_arg_2+= "'"+arg+"' => " + val + ", ";
					}
					else php_arg_2+= "'"+arg+"' => '" + val + "', ";
				}

				q_args[arg] = val;
			}
		});

		sc_str+= ']';
		php_str+= php_arg_2.replace(/,\s*$/, "") + ' ) ); ?>';

		$sc_code.text( sc_str );
		$php_code.text( php_str );

		return q_args;
		//return { 'sc_str' : sc_str , 'php_str' : php_str , 'php_arg_2' : php_arg_2 };
	}

	function update_gradient_list( $control ){
		var colors = '';
		var color = '';
		$control.find( '.ssmenu-color-stop' ).each( function(){
			if( $(this).data( 'cleared' ) ) color = '';
			else color = $( this ).wpColorPicker( 'color' );
			//console.log( color +',');
			if( color ){
				if( colors.length > 0 ) colors += ',';
				colors += color;
			}
		});
		//console.log( 'colors = ' + colors );
		$control.find( '.ssmenu-gradient-list' )
			.val( colors );
			//.trigger( 'change' );
	}

	function ssmenu_save_configuration( $form ){
		var data = {
			action: 'bellows_add_configuration',
			bellows_data: $form.serialize(),
			bellows_nonce: $form.find( '#_wpnonce' ).val()
		};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post( ajaxurl, data, function(response) {
			//console.log( response );

			if( response == -1 ){
				$( '.ssmenu_configuration_container_wrap' ).fadeOut();
				$( '.ssmenu_configuration_notice_error' ).fadeIn();

				$( '.ssmenu-error-message' ).text( 'Please try again.' );

				return;
			}
			else if( response.error ){
				$( '.ssmenu_configuration_container_wrap' ).fadeOut();
				$( '.ssmenu_configuration_notice_error' ).fadeIn();

				$( '.ssmenu-error-message' ).text( response.error );

				return;
			}
			else{
				$( '.ssmenu_configuration_container_wrap' ).fadeOut();
				$( '.ssmenu_configuration_notice_success' ).fadeIn();
			}

		}, 'json' ).fail( function(){
			$( '.ssmenu_configuration_container_wrap' ).fadeOut();
			$( '.ssmenu_configuration_notice_error' ).fadeIn();
		});
	}

	function ssmenu_delete_configuration( $a ){
		var data = {
			action: 'bellows_delete_configuration',
			bellows_data: {
				'ssmenu_configuration_id' : $a.data( 'ssmenu-configuration-id' )
			},
			bellows_nonce: $a.data( 'ssmenu-nonce' )
		};

		//console.log( data );

		jQuery.post( ajaxurl, data, function(response) {
			//console.log( response );

			if( response == -1 ){
				$( '.ssmenu_configuration_container_wrap' ).fadeOut();
				$( '.ssmenu_configuration_delete_notice_error' ).fadeIn();

				$( '.ssmenu-delete-error-message' ).text( 'Please try again.' );

				return;
			}
			else if( response.error ){
				$( '.ssmenu_configuration_container_wrap' ).fadeOut();
				$( '.ssmenu_configuration_delete_notice_error' ).fadeIn();

				$( '.ssmenu-delete-error-message' ).text( response.error );

				return;
			}
			else{
				$( '.ssmenu_configuration_container_wrap' ).fadeOut();
				$( '.ssmenu_configuration_delete_notice_success' ).fadeIn();

				var id = response.id;
				$( '#bellows_'+id+', #bellows_'+id+'-tab' ).remove();	//delete tab and content
				$( '.nav-tab-wrapper > a' ).first().trigger( 'click' );			//switch to first tab
			}

		}, 'json' ).fail( function(){
			$( '.ssmenu_configuration_container_wrap' ).fadeOut();
			$( '.ssmenu_configuration_delete_notice_error' ).fadeIn();
		});	

		
	}

	function ss_store( item , key , val ){
		val = val || false;

		var store = localStorage.getItem( item );

		var jstore = {};
		if( store ){
			jstore = JSON.parse( store );
		}

		//retrieve
		if( val === false ){
			if( jstore ){
				return jstore[key];
			}
		}

		//store
		else{
			jstore[key] = val;
			var jstore_string = JSON.stringify( jstore );
			localStorage.setItem( item , jstore_string );
		}

	}

	function ss_selectText( element ) {
		var doc = document
			//, text = element //doc.getElementById(element)
			, range, selection
		;
		if (doc.body.createTextRange) { //ms
			range = doc.body.createTextRange();
			range.moveToElementText( element );
			range.select();
		} else if (window.getSelection) { //all others
			selection = window.getSelection();        
			range = doc.createRange();
			range.selectNodeContents( element );
			selection.removeAllRanges();
			selection.addRange(range);
		}
	}



})(jQuery);


// if( ssmenu_control_panel.load_google_cse == 'on' ){
//   (function() {
// 	setTimeout( function(){
// 		var cx = '012195239863806736760:csnsnevlo9y';
// 		var gcse = document.createElement('script');
// 		gcse.type = 'text/javascript';
// 		gcse.async = true;
// 		gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
// 			'//www.google.com/cse/cse.js?cx=' + cx;
// 		var s = document.getElementsByTagName('script')[0];
// 		s.parentNode.insertBefore(gcse, s);
// 	}, 1000 );
//   })();
// }

  // (function() {
		//     var cx = '012189916002899296903:hyk1016hrqa';
		//     var gcse = document.createElement('script');
		//     gcse.type = 'text/javascript';
		//     gcse.async = true;
		//     gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
		//         '//www.google.com/cse/cse.js?cx=' + cx;
		//     var s = document.getElementsByTagName('script')[0];
		//     s.parentNode.insertBefore(gcse, s);
		//   })();
