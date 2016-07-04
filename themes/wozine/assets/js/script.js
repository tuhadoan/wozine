/**
 */
;( function( $ ) {
	'use strict';
	var body = $( 'body' ),
		$win = $( window );
	
	var dtLoadmore = function(element, options,callback){
		 this.$element    = $(element);
		 this.callback = callback;
		 this.options = $.extend({},dtLoadmore.defaults, options);
		 this.contentSelector = this.options.contentSelector || this.$element.find('.loadmore-wrap');
		 this.options.contentSelector = this.contentSelector;
		 this.init();
	}
	dtLoadmore.defaults = {
			contentSelector: null,
			nextSelector: "div.navigation a:first",
			navSelector: "div.navigation",
			itemSelector: "div.post",
			dataType: 'html',
			finishedMsg: "<em>Congratulations, you've reached the end of the internet.</em>",
			maxPage: undefined,
			loading:{
				speed:0,
				start: undefined
			},
			state: {
		        isDuringAjax: false,
		        isInvalidPage: false,
		        isDestroyed: false,
		        isDone: false, // For when it goes all the way through the archive.
			    isPaused: false,
			    isBeyondMaxPage: false,
			    currPage: 1
			}
	};
	dtLoadmore.prototype.init = function(){
		this.create();
	}
	dtLoadmore.prototype.create = function(){
	 var self 			= this, 
	 	$this 			= this.$element,
	 	contentSelector = this.contentSelector,
		action 			= this.action,
		btn 			= this.btn,
		loading 		= this.loading,
		options 		= this.options;
		
		var _determinepath = function(path){
			if (path.match(/^(.*?)\b2\b(.*?$)/)) {
               path = path.match(/^(.*?)\b2\b(.*?$)/).slice(1);
           } else if (path.match(/^(.*?)2(.*?$)/)) {
               if (path.match(/^(.*?page=)2(\/.*|$)/)) {
                   path = path.match(/^(.*?page=)2(\/.*|$)/).slice(1);
                   return path;
               }
               path = path.match(/^(.*?)2(.*?$)/).slice(1);

           } else {
               if (path.match(/^(.*?page=)1(\/.*|$)/)) {
                   path = path.match(/^(.*?page=)1(\/.*|$)/).slice(1);
                   return path;
               } else {
               	options.state.isInvalidPage = true;
               }
           }
			return path;
		}
		if(!$(options.nextSelector).length){
			return;
		}
		
		// callback loading
		options.callback = function(data, url) {
           if (self.callback) {
           	self.callback.call($(options.contentSelector)[0], data, options, url);
           }
       };
       
       options.loading.start = function($btn) {
			 	if(options.state.isBeyondMaxPage)
			 		return;
       		$btn.hide();
               $(options.navSelector).hide();
               $btn.closest('.loadmore-action').find('.loadmore-loading').show(options.loading.speed, $.proxy(function() {
               	loadAjax(options,$btn);
               }, self));
        };
		
		var loadAjax = function(options,$btn){
			var path = $(options.nextSelector).attr('href');
				path = _determinepath(path);
			
			var callback=options.callback,
				desturl,frag,box,children,data;
			
			options.state.currPage++;
			options.maxPage = $(options.contentSelector).data('maxpage') || options.maxPage;
			// Manually control maximum page
           if ( options.maxPage !== undefined && options.state.currPage > options.maxPage ){
           	options.state.isBeyondMaxPage = true;
               return;
           }
           desturl = path.join(options.state.currPage);
           box = $('<div/>');
           box.load(desturl + ' ' + options.itemSelector,undefined,function(responseText){
           	children = box.children();
           	if (children.length === 0) {
           		$btn.closest('.loadmore-action').find('.loadmore-loading').hide(options.loading.speed,function(){
           			options.state.isBeyondMaxPage = true;
           			$btn.html(options.finishedMsg).show();
           		});
                   return ;
               }
           	frag = document.createDocumentFragment();
               while (box[0].firstChild) {
                   frag.appendChild(box[0].firstChild);
               }
               $(options.contentSelector)[0].appendChild(frag);
               data = children.get();
               $btn.closest('.loadmore-action').find('.loadmore-loading').hide();
               if(options.maxPage !== undefined && options.maxPage == options.state.currPage ){
               	options.state.isBeyondMaxPage = true;
               	$btn.html(options.finishedMsg);
               }
               $btn.show(options.loading.speed);
               options.callback(data);
              
           });
		}
		
		
		$(document).on('click','[data-paginate="loadmore"] .btn-loadmore',function(e){
			 e.stopPropagation();
			 e.preventDefault();
			 options.loading.start.call($(options.contentSelector)[0],$(this));
		});
	}
	
	
	dtLoadmore.prototype.update = function(key){
		if ($.isPlainObject(key)) {
           this.options = $.extend(true,this.options,key);
       }
	}
	$.fn.dtLoadmore = function(options,callback){
		var thisCall = typeof options;
		switch (thisCall) {
	         // method
	         case 'string':
	             var args = Array.prototype.slice.call(arguments, 1);
	             this.each(function () {
	                 var instance = $.data(this, 'dtloadmore');
	                 if (!instance) {
	                     return false;
	                 }
	                 if (!$.isFunction(instance[options]) || options.charAt(0) === '_') {
	                     return false;
	                 }
	                 instance[options].apply(instance, args);
	             });
	
	         break;
	
	         case 'object':
	             this.each(function () {
		             var instance = $.data(this, 'dtloadmore');
		             if (instance) {
		                 instance.update(options);
		             } else {
		                 instance = new dtLoadmore(this, options, callback);
		                 $.data(this, 'dtloadmore', instance);
		             }
		         });
	
	         break;
	
	     }

		return this;
	};
	
	$.fn.dt_mediaelementplayer = function(options){
		var defaults = {};
		var options = $.extend(defaults, options);
		
		return this.each(function() {
			var el				= $(this);
			el.attr('width','100%').attr('height','100%'); 
			$(el).closest('.video-embed-wrap').each(function(){
				var aspectRatio = $(this).height() / $(this).width();
				$(this).attr('data-aspectRatio',aspectRatio).css({'height': $(this).width() *  aspectRatio + 'px', 'width': '100%'});
			});
			/*
			el.mediaelementplayer({
				// none: forces fallback view
				mode: 'auto',
				// if the <video width> is not specified, this is the default
				defaultVideoWidth: '100%',
				// if the <video height> is not specified, this is the default
				defaultVideoHeight: '100%',
				// if set, overrides <video width>
				videoWidth: '100%',
				// if set, overrides <video height>
				videoHeight: '100%',
				// width of audio player
				audioWidth: "100%",
				// height of audio player
				audioHeight: 30,
				// initial volume when the player starts
				startVolume: 0.8,
				// useful for <audio> player loops
				loop: false,
				// enables Flash and Silverlight to resize to content size
				enableAutosize: true,
				// the order of controls you want on the control bar (and other plugins below)
				features: ['playpause','progress','duration','volume','fullscreen'],
				// Hide controls when playing and mouse is not over the video
				alwaysShowControls: false,
				// force iPad's native controls
				iPadUseNativeControls: false,
				// force iPhone's native controls
				iPhoneUseNativeControls: false,
				// force Android's native controls
				AndroidUseNativeControls: false,
				// forces the hour marker (##:00:00)
				alwaysShowHours: false,
				// show framecount in timecode (##:00:00:00)
				showTimecodeFrameCount: false,
				// used when showTimecodeFrameCount is set to true
				framesPerSecond: 25,
				// turns keyboard support on and off for this instance
				enableKeyboard: true,
				// when this player starts, it will pause other players
				pauseOtherPlayers: true,
				// array of keyboard commands
				keyActions: [],
				/*mode: 'shim'/
			});*/
			window.setTimeout(function(){
				$(el).closest('.video-embed-wrap').css({'height': '100%', 'width': '100%'});
			},1000);
			$(el).closest('.mejs-container').css({'height': '100%', 'width': '100%'});
		});
		
	};
	
	window.dt = {
			
			init: function(){
				var self = this;

				// remove DT preload
				$('.dt-post-category, .dt-posts-slider').removeClass('dt-preload');

				// Smart Content Box shortcode
				if( $('.dt-smart-content-box').length ){
					var $window = $( window );
					var $windowW = $window.width();

					var DTSmartContentBox = function($windowW, isResize){
						if($windowW <= 1200){
							var $blockBig = $('.dt-smart-content-box .smart-content-box__wrap .dt-smcb-block1 .dt-module-thumb');
							var $blockBigW = $blockBig.width();
							var $blockBigH = Math.round($blockBigW * 0.8052);
							$('.dt-smart-content-box .smart-content-box__wrap .dt-smcb-block1 .dt-module-thumb').css("height", $blockBigH);

							var $blockList = $('.dt-smart-content-box .smart-content-box__wrap .dt-smcb-block2 .dt-module-thumb');
							var $blockListW = $blockList.width();
							var $blockListH = Math.round($blockListW * 0.568);
							$('.dt-smart-content-box .smart-content-box__wrap .dt-smcb-block2 .dt-module-thumb, .dt-smart-content-box .smart-content-box__wrap .dt-smcb-block3 .dt-module-thumb').css("height", $blockListH);
						}else{
							$('.dt-smart-content-box .smart-content-box__wrap .dt-smcb-block1 .dt-module-thumb').attr("style", '');
							$('.dt-smart-content-box .smart-content-box__wrap .dt-smcb-block2 .dt-module-thumb, .dt-smart-content-box .smart-content-box__wrap .dt-smcb-block3 .dt-module-thumb').attr("style", '');
						}
					}
					DTSmartContentBox($windowW, false );
					$window.resize(function(){
						$windowW = $window.width();
						DTSmartContentBox($windowW, true);
					});
				}

				this.menu_toggle();
				this.menu();
				this.scrollToTOp();
				this.slickSlider();
				//PopUp
				this.magnificpopupInit();
				//Responsive embed iframe
				this.responsiveEmbedIframe();
				$(window).resize(function(){
					self.responsiveEmbedIframe();
				});
				//isotope
				this.isotopeInit();
				$(window).resize(function(){
					$('[data-layout="masonry"]').each(function(){
						var $this = $(this),
							container = $this.find('.masonry-wrap');
							container.isotope( 'layout' );
					});
				});
				
				//Load more
				this.loadmoreInit();
				//Infinite Scrolling
				this.infiniteScrollInit();
				//Media element player
				this.mediaelementplayerInit();
				// sticky-sidebar
				this.sticky_sidebar();
				// ajax load next-prev content
				this.ajax_nav_content();	
			},
			
			menu_toggle: function(){
				//Off Canvas menu
				$('.menu-toggle').on('click',function(e){
					e.stopPropagation();
					e.preventDefault();
					if($('body').hasClass('open-offcanvas')){
						$('body').removeClass('open-offcanvas').addClass('close-offcanvas');
						$('.menu-toggle').removeClass('x');
					}else{
						$('body').removeClass('close-offcanvas').addClass('open-offcanvas');
						
					}
				});
				$('body').on('mousedown', $.proxy( function(e){
					var element = $(e.target);
					if($('.offcanvas').length && $('body').hasClass('open-offcanvas')){
						if(!element.is('.offcanvas') && element.parents('.offcanvas').length === 0 && !element.is('.navbar-toggle') && element.parents('.navbar-toggle').length === 0 )
						{
							$('body').removeClass('open-offcanvas');
							$('.menu-toggle').removeClass('x');
						}
					}
				}, this) );
				
				//Open Search Box
				$('.sidebar-offcanvas-header .search-toggle').click(function(){
					$('.sidebar-offcanvas-header .user-panel').hide();
					$('.sidebar-offcanvas-header .toggle-wrap').hide();
					$('.sidebar-offcanvas-header .search-box').fadeIn();
				});

				//Close Search Box
				$(document).click(function (e) {		
					if (!$(e.target).hasClass("search-toggle") 
			        	&& $(e.target).parents(".sidebar-offcanvas-header").length === 0) 
				    {
				        $(".search-box").hide();
				        $('.sidebar-offcanvas-header .user-panel').fadeIn();
						$('.sidebar-offcanvas-header .toggle-wrap').fadeIn();
				    }
				});
			},
			
			menu: function(){
				$('#primary-navigation .nav-menu li').hoverIntent(function(){
					$(this).addClass('item-hover');			
					$(this).find(' > ul').show().addClass('animated x2 slideInUp');
				},function(){
					$(this).find('ul').removeClass('animated x2 slideInUp').fadeOut();
					$(this).removeClass('item-hover');
				});
				
				// Sticky menu
				var iScrollPos = 0;

				$win.scroll(function () {
					if( $('body').hasClass('sticky-menu') ){
						var iCurScrollPos = $(this).scrollTop();
						var $doc 		= document.documentElement;
						var $top_offset = (window.pageYOffset || $doc.scrollTop) - ($doc.clientTop || 0);
					    var $wpadminbar = 0;
						if( $("#wpadminbar").length > 0 && $win.width() > 600 && iCurScrollPos > $("#wpadminbar").height()){
							var $wpadminbar = $("#wpadminbar").height();
						}

						var $sticky_nav_height = $('#dt-sticky-navigation-holder').attr('data-height');
					    if (iCurScrollPos > iScrollPos) {
					        //Scrolling Down
					        $('#dt-sticky-navigation-holder .dt-sticky-mainnav-wrapper #primary-navigation').appendTo($('#dt-main-menu .dt-mainnav-wrapper'));

					        $('#dt-sticky-navigation-holder').css('top',- $sticky_nav_height);
							$('#dt-sticky-navigation-holder').removeClass('scrolled bgc');
					    } else if( $top_offset > $('#dt-main-menu').offset().top + 250 ){
					       //Scrolling Up
					       $('#dt-main-menu .dt-mainnav-wrapper #primary-navigation').appendTo($('#dt-sticky-navigation-holder .dt-sticky-mainnav-wrapper'));

					       	$('#dt-sticky-navigation-holder').css({'top': $wpadminbar });
							$('#dt-sticky-navigation-holder').addClass('scrolled bgc');
					    }else{
					    	$('#dt-sticky-navigation-holder .dt-sticky-mainnav-wrapper #primary-navigation').appendTo($('#dt-main-menu .dt-mainnav-wrapper'));
					    	
					    	$('#dt-sticky-navigation-holder').css('top',- $sticky_nav_height);
							$('#dt-sticky-navigation-holder').removeClass('scrolled bgc');
					    }
					    
					    iScrollPos = iCurScrollPos;
					    
					    if(iCurScrollPos == 0){
							$('#dt-sticky-navigation-holder').removeClass('scrolled bgc');
					    }
					}
				});
			},
			
			scrollToTOp: function(){
				//Go to top
				$(window).scroll(function () {
					if ($(this).scrollTop() > 500) {
						$('.go-to-top').addClass('on');
					}
					else {
						$('.go-to-top').removeClass('on');
					}
				});
				$('body').on( 'click', '.go-to-top', function () {
					$("html, body").animate({
						scrollTop: 0
					}, 800);
					return false;
				});
			},
			
			slickSlider: function(){
				if( $('.dt-slick-slider').length ){
					$('.dt-slick-slider').each(function(){
						var $this = $(this);
						var $infinite, $slidesToShow, $slidesToScroll, $dots;

						$dots = $(this).attr('data-dots');
						$infinite = $(this).attr('data-infinite');
						$slidesToShow = parseInt($(this).attr('data-visible'));
						$slidesToScroll = parseInt($(this).attr('data-scroll'));

						$($this).removeClass('dt-preload');

						$dots = ($dots == '1' || $dots == 'true') ? true : false;
						$infinite = ($infinite == '1' || $infinite == 'true') ? true : false;

						$(this).find('.dt-slick-items').slick({
							dots: $dots,
							infinite: $infinite,
							slidesToShow: $slidesToShow,
							slidesToScroll: $slidesToScroll,
							nextArrow: '<div class="navslider"><span class="next"><i class="fa fa-chevron-right"></i></span></div>',
							prevArrow: '<div class="navslider"><span class="prev"><i class="fa fa-chevron-left"></i></span></div>',
						});
					});
				}

				if( $('.dt-posts-slider').length ){
					$('.dt-posts-slider').each(function(){
						var $this = $(this);
						var $mode, $infinite, $slidesToShow, $slidesToScroll, $dots, $arrows;

						$mode = $(this).attr('data-mode');
						$dots = $(this).attr('data-dots');
						$arrows = $(this).attr('data-arrows');
						$infinite = $(this).attr('data-infinite');
						$slidesToShow = parseInt($(this).attr('data-visible'));
						$slidesToScroll = parseInt($(this).attr('data-scroll'));

						$($this).removeClass('dt-preload');

						$dots = ($dots == '1' || $dots == 'true') ? true : false;
						$arrows = ($arrows == '1' || $arrows == 'true') ? true : false;
						$infinite = ($infinite == '1' || $infinite == 'true') ? true : false;

						if($mode == 'single_mode'){
							$($this).find('.posts-slider').slick({
							  	dots: false,
							  	infinite: true,
							  	autoplay: false,
							  	speed: 300,
							  	fade: true,
							  	cssEase: 'linear',
							  	nextArrow: '<div class="navslider"><span class="next"><i class="fa fa-chevron-right"></i></span></div>',
								prevArrow: '<div class="navslider"><span class="prev"><i class="fa fa-chevron-left"></i></span></div>',
							});
						}else{
							$($this).find('.posts-slider').slick({
								infinite: $infinite,
								slidesToShow: $slidesToShow,
								slidesToScroll: $slidesToScroll,
								dots: $dots,
								arrows: $arrows,
								nextArrow: '<div class="navslider"><span class="next"><i class="fa fa-arrow-right"></i></span></div>',
								prevArrow: '<div class="navslider"><span class="prev"><i class="fa fa-arrow-left"></i></span></div>',
								responsive: [
						             {
						               breakpoint: 1024,
						               settings: {
						                 slidesToShow: $slidesToShow,
						                 slidesToScroll: $slidesToScroll,
						                 infinite: $infinite,
						                 dots: $dots
						               }
						             },
						             {
						               breakpoint: 600,
						               settings: {
						                 slidesToShow: 2,
						                 slidesToScroll: 2
						               }
						             },
						             {
						               breakpoint: 480,
						               settings: {
						                 slidesToShow: 1,
						                 slidesToScroll: 1
						               }
						             }
						             // You can unslick at a given breakpoint now by adding:
						             // settings: "unslick"
						             // instead of a settings object
						           ]
							});
						}
						
					});
				}

				if( $('.related_posts-slider').length ){
					$('.related_posts-slider').removeClass('dt-preload');
					$('.related_posts-slider').slick({
						infinite: true,
						slidesToShow: 3,
						slidesToScroll: 3,
						nextArrow: '<div class="navslider"><span class="next"><i class="fa fa-arrow-right"></i></span></div>',
						prevArrow: '<div class="navslider"><span class="prev"><i class="fa fa-arrow-left"></i></span></div>',
						responsive: [
				             {
				               breakpoint: 1024,
				               settings: {
				                 slidesToShow: 3,
				                 slidesToScroll: 3,
				                 infinite: true,
				                 dots: true
				               }
				             },
				             {
				               breakpoint: 600,
				               settings: {
				                 slidesToShow: 2,
				                 slidesToScroll: 2
				               }
				             },
				             {
				               breakpoint: 480,
				               settings: {
				                 slidesToShow: 1,
				                 slidesToScroll: 1
				               }
				             }
				             // You can unslick at a given breakpoint now by adding:
				             // settings: "unslick"
				             // instead of a settings object
				           ]
					});
				}
			},
			magnificpopupInit: function(){
				if($().magnificPopup){
					$("a[data-rel='magnific-popup-video']").each(function(){
						$(this).magnificPopup({
							type: 'inline',
							mainClass: 'dh-mfp-popup',
							fixedContentPos: true,
							callbacks : {
							    open : function(){
							    	$(this.content).find(".video-embed.video-embed-popup,.audio-embed.audio-embed-popup").dt_mediaelementplayer();
							    	$(this.content).find('iframe:visible').each(function(){
										if(typeof $(this).attr('src') != 'undefined'){
											if( $(this).attr('src').toLowerCase().indexOf("youtube") >= 0 || $(this).attr('src').toLowerCase().indexOf("vimeo") >= 0  || $(this).attr('src').toLowerCase().indexOf("twitch.tv") >= 0 || $(this).attr('src').toLowerCase().indexOf("kickstarter") >= 0 || $(this).attr('src').toLowerCase().indexOf("dailymotion") >= 0) {
												$(this).attr('data-aspectRatio', this.height / this.width).removeAttr('height').removeAttr('width');
												if($(this).attr('src').indexOf('wmode=transparent') == -1) {
													if($(this).attr('src').indexOf('?') == -1){
														$(this).attr('src',$(this).attr('src') + '?wmode=transparent');
													} else {
														$(this).attr('src',$(this).attr('src') + '&wmode=transparent');
													}
												}
											}
										} 
									});
							    	$(this.content).find('iframe[data-aspectRatio]').each(function() {
									 	var newWidth = $(this).parent().width();
										var $this = $(this);
										$this.width(newWidth).height(newWidth * $this.attr('data-aspectRatio'));
										
								   });
							    },
							    close: function() {
							    	$(this.st.el).closest('.video-embed-shortcode').find('.video-embed-shortcode').html($(this.st.el).data('video-inline'));
							    }
							}
						});
					});
					$("a[data-rel='magnific-popup']").magnificPopup({
						type: 'image',
						mainClass: 'dh-mfp-popup',
						fixedContentPos: true,
						gallery:{
							enabled: true
						}
					});
					$("a[data-rel='magnific-popup-verticalfit']").magnificPopup({
						type: 'image',
						mainClass: 'dh-mfp-popup',
						overflowY: 'scroll',
						fixedContentPos: true,
						image: {
							verticalFit: false
						},
						gallery:{
							enabled: true
						}
					});
					$("a[data-rel='magnific-single-popup']").magnificPopup({
						type: 'image',
						mainClass: 'dh-mfp-popup',
						fixedContentPos: true,
						gallery:{
							enabled: false
						}
					});
				}
			},
			responsiveEmbedIframe: function(){
				
			},
			isotopeInit: function(){
				var self = this;
				$('[data-layout="masonry"]').each(function(){
					var $this = $(this),
						container = $this.find('.masonry-wrap'),
						itemColumn = $this.data('masonry-column'),
						itemWidth,
						container_width = container.width();
						if(self.getViewport().width > 992){
							itemWidth = container_width / itemColumn;
						}else if(self.getViewport().width <= 992 && self.getViewport().width >= 768){
							itemWidth = container_width / 2;
						}else {
							itemWidth = container_width / 1;
						}
						container.isotope({
							layoutMode: 'masonry',
							itemSelector: '.masonry-item',
							transitionDuration : '0.8s',
							getSortData : { 
								title : function (el) { 
									return $(el).data('title');
								}, 
								date : function (el) { 
									return parseInt($(el).data('date'));
								} 
							},
							masonry : {
								gutter : 0
							}
						}).isotope( 'layout' );
						
						imagesLoaded($this,function(){
							container.isotope( 'layout' );
						});
					if(!$this.hasClass('masonry-inited')){
						$this.addClass('masonry-inited');
						var filter = $this.find('.masonry-filter ul a');
						filter.on('click',function(e){
							e.stopPropagation();
							e.preventDefault();
							
							var $this = jQuery(this);
							// don't proceed if already selected
							if ($this.hasClass('selected')) {
								return false;
							}
							
							var filters = $this.closest('ul');
							filters.find('.selected').removeClass('selected');
							$this.addClass('selected');
							$this.closest('.masonry-filter').find('.filter-heaeding h3').text($this.text());
							var options = {
								layoutMode : 'masonry',
								transitionDuration : '0.8s',
								getSortData : { 
									title : function (el) { 
										return $(el).data('title');
									}, 
									date : function (el) { 
										return parseInt($(el).data('date'));
									} 
								}
							}, 
							key = filters.attr('data-filter-key'), 
							value = $this.attr('data-filter-value');
				
							value = value === 'false' ? false : value;
							options[key] = value;
							container.isotope(options);
							var wrap = $this.closest('[data-layout="masonry"]');
						});
						$('[data-masonry-toogle="selected"]').trigger('click');
					}
				});
				
			},
			easyZoomInit: function(){
				if($().easyZoom) {
					$('.easyzoom').easyZoom();
				}
			},
			mediaelementplayerInit: function(){
				if($().mediaelementplayer) {
					$(".video-embed:not(.video-embed-popup),.audio-embed:not(.audio-embed-popup)").dt_mediaelementplayer();
				}
			},
			loadmoreInit: function(){
				var self = this;
				$('[data-paginate="loadmore"]').each(function(){
					var $this = $(this);
					$this.dtLoadmore({
						contentSelector : $this.data('contentselector') || null,
						navSelector  : $this.find('div.paginate'),            
				   	    nextSelector : $this.find('div.paginate a.next'),
				   	    itemSelector : $this.data('itemselector'),
				   	    finishedMsg : $this.data('finishedmsg') || dtL10n.ajax_finishedMsg
					},function(newElements){
						//DT.woocommerceLazyLoading();
						$(newElements).find(".video-embed:not(.video-embed-popup),.audio-embed:not(.audio-embed-popup)").dt_mediaelementplayer();
						
						if($this.hasClass('masonry')){
							$this.find('.masonry-wrap').isotope('appended', $(newElements));
							if($this.find('.masonry-filter').length){
								var selector = $this.find('.masonry-filter').find('a.selected').data('filter-value');
								$this.find('.masonry-wrap').isotope({ filter: selector });
							}
							imagesLoaded(newElements,function(){
								self.magnificpopupInit();
								self.responsiveEmbedIframe();
								//self.carouselInit();
								if($this.hasClass('masonry')){
									$this.find('.masonry-wrap').isotope('layout');
								}
							});
						}
					});
				});
			},
			infiniteScrollInit: function(){
				var self = this;
				//Posts
				$('[data-paginate="infinite_scroll"]').each(function(){
					var $this = $(this);
					var finishedmsg = $this.data('finishedmsg') || dtL10n.ajax_finishedMsg,
						msgtext = $this.data('msgtext') || dtL10n.ajax_msgText,
						maxPage = $this.data('contentselector') ? $($this.data('contentselector')).data('maxpage') : undefined;
					$this.find('.infinite-scroll-wrap').infinitescroll({
						navSelector  : $this.find('div.paginate'),            
				   	    nextSelector : $this.find('div.paginate a.next'),    
				   	    itemSelector : $this.data('itemselector'),
				   	    contentSelector : $this.data('contentselector') || $this.find('.infinite-scroll-wrap'),
				        msgText: " ",
				        maxPage:maxPage,
				        loading: {
				        	speed:0,
				        	finishedMsg: finishedmsg,
							msgText: $this.data('msgtext') || dtL10n.ajax_msgText,
							selector: $this.data('loading-selector') || $this,
							msg: $('<div class="infinite-scroll-loading"><div class="fade-loading"><i></i><i></i><i></i><i></i></div><div class="infinite-scroll-loading-msg">' + msgtext +'</div></div>')
						},
						errorCallback: function(){
							$this.find('.infinite-scroll-loading-msg').html(finishedmsg).animate({ opacity: 1 }, 2000, function () {
				                $(this).parent().fadeOut('fast',function(){
				                	$this.find('.infinite-scroll-loading-msg').html(msgtext);
				                });
				            });
						}
					},function(newElements){
						
						$(newElements).find(".video-embed:not(.video-embed-popup),.audio-embed:not(.audio-embed-popup)").dt_mediaelementplayer();
						
						if($this.hasClass('masonry')){
							$this.find('.masonry-wrap').isotope('appended', $(newElements));
							if($this.find('.masonry-filter').length){
								var selector = $this.find('.masonry-filter').find('a.selected').data('filter-value');
								$this.find('.masonry-wrap').isotope({ filter: selector });
							}
							imagesLoaded(newElements,function(){
								self.magnificpopupInit();
								self.responsiveEmbedIframe();
								//self.carouselInit();
								if($this.hasClass('masonry')){
									$this.find('.masonry-wrap').isotope('layout');
								}
							});
						}
						
					});
				});
				
			},
			ajax_nav_content: function(){
				var self = this;
				$('.dt-next-prev-wrap').each(function(){
					var $this = $(this);
					$($this).find('a').on('click', function(e){
						e.preventDefault();
						var $_this = $(this);
						var $wrap_id = $($_this).parents('.dt-next-prev-wrap').attr('data-target');
						if( ! $(this).hasClass('ajax-page-disabled') ){
							
							jQuery('#'+$wrap_id+' .dt-content__wrap').addClass('dt-loading');

							var cat 			= $($_this).parents('.dt-next-prev-wrap').attr('data-cat'),
								orderby 		= $($_this).parents('.dt-next-prev-wrap').attr('data-orderby'),
								order 			= $($_this).parents('.dt-next-prev-wrap').attr('data-order'),
								hover_thumbnail = $($_this).parents('.dt-next-prev-wrap').attr('data-hover-thumbnail'),
								offset			= $($_this).attr('data-offset'),
								current_page	= $($_this).attr('data-current-page'),
								posts_per_page  = $($_this).parents('.dt-next-prev-wrap').attr('data-posts-per-page'),
								template  		= $($_this).parents('.dt-next-prev-wrap').attr('data-template');
								
							$.ajax({
									url : dt_ajaxurl,
									data:{
										action			: 'dt_nav_content',
										cat 			: cat,
										orderby 		: orderby,
										order 			: order,
										hover_thumbnail : hover_thumbnail,
										offset 			: offset,
										current_page	: current_page,
										posts_per_page  : posts_per_page,
										template 		: template,
									},
									type: 'POST',
									success: function(data){
										if(data != ''){
											setTimeout(function(){
												$('#'+$wrap_id+' .dt-content__wrap').removeClass('dt-loading');
												
												$('#'+$wrap_id+' .dt-content__wrap .dt-content').html(data).hide();
												$('#'+$wrap_id+' .dt-content__wrap .dt-content').fadeIn('slow');
							                	
							                	// uddate current page - offset
							                	var current_page	= parseInt( $($_this).attr('data-current-page') );
							                	var current_offset	= parseInt( $($_this).attr('data-offset') );

							                	if( $($_this).hasClass('dt-ajax-next-page') ) {
							                		$('#'+$wrap_id+' .dt-next-prev-wrap .dt-ajax-next-page').attr('data-current-page', current_page + 1);
							                		var prev_page = parseInt( $($_this).attr('data-current-page') - 1 );
							                		$('#'+$wrap_id+' .dt-next-prev-wrap .dt-ajax-prev-page').attr('data-current-page', prev_page);

							                		$($_this).attr('data-offset', parseInt(offset) + parseInt(posts_per_page));

							                		$('#'+$wrap_id+' .dt-ajax-prev-page').removeClass('ajax-page-disabled');
							                		$('#'+$wrap_id+' .dt-ajax-prev-page').attr('data-offset', parseInt(offset) - parseInt(posts_per_page));

							                	}else if( $($_this).hasClass('dt-ajax-prev-page') ){
							                		$('#'+$wrap_id+' .dt-next-prev-wrap .dt-ajax-prev-page').attr('data-current-page', current_page - 1);
							                		$('#'+$wrap_id+' .dt-next-prev-wrap .dt-ajax-next-page').attr('data-current-page', current_page);

							                		if(current_offset <= 0){
							                			$($_this).addClass('ajax-page-disabled');
							                			$($_this).attr('data-offset', 0);
							                			$('#'+$wrap_id+' .dt-ajax-next-page').attr('data-offset', parseInt(posts_per_page));
							                			$('#'+$wrap_id+' .dt-next-prev-wrap .dt-ajax-next-page').attr('data-current-page', 1);

							                		}else{
							                			$($_this).attr('data-offset', parseInt(current_offset) - parseInt(posts_per_page));

							                			$('#'+$wrap_id+' .dt-ajax-next-page').attr('data-offset', parseInt(current_offset) + parseInt(posts_per_page));
							                		}
							                		
							                		$('#'+$wrap_id+' .dt-ajax-next-page').removeClass('ajax-page-disabled');
							                		
												}

							                	// hidden action
							                	if( $('#'+$wrap_id+' #dt-ajax-no-p').length > 0 ){
							                		$_this.addClass('ajax-page-disabled');
							                	}

											},500);
											
										}else{

										}
									}
							});
						}
					});
				});
			},
			sticky_sidebar: function(){
				var self = this;
				$('[data-sticky-sidebar="sticky_sidebar"]').each(function(){
					var $this = $(this);
					var $containerSelector = $this.attr('data-container-selector');
					 $($containerSelector).theiaStickySidebar({
					 	// Settings
					 	// An additional top margin in pixels. Defaults to 0.
      					additionalMarginTop: 30,
      					// An additional bottom margin in pixels. Defaults to 0.
      					additionalMarginBottom: 0,
					 });
				});
			},
			getViewport: function() {
			    var e = window, a = 'inner';
			    if (!('innerWidth' in window )) {
			        a = 'client';
			        e = document.documentElement || document.body;
			    }
			    return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
			},
			hex2rgba: function(hex,opacity){
				hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
				var rgb = {
					r: hex >> 16,
					g: (hex & 0x00FF00) >> 8,
					b: (hex & 0x0000FF)
				};
				if( !rgb ) return null;
				if( opacity === undefined ) opacity = 1;
				return 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', ' + parseFloat(opacity) + ')';
			},
			enableAnimation: function(){
				return this.getViewport().width > 992 && !this.isTouch();
			},
			determinepath: function(path){
				path =  path || undefined;
				if(path === undefined)
					return undefined;
				
				if (path.match(/^(.*?)\b2\b(.*?$)/)) {
	                path = path.match(/^(.*?)\b2\b(.*?$)/).slice(1);
	            } else if (path.match(/^(.*?)2(.*?$)/)) {
	                if (path.match(/^(.*?page=)2(\/.*|$)/)) {
	                    path = path.match(/^(.*?page=)2(\/.*|$)/).slice(1);
	                    return path;
	                }
	                path = path.match(/^(.*?)2(.*?$)/).slice(1);

	            } else {
	                if (path.match(/^(.*?page=)1(\/.*|$)/)) {
	                    path = path.match(/^(.*?page=)1(\/.*|$)/).slice(1);
	                    return path;
	                }
	            }
				return path;
			},
			isTouch: function(){
				return !!('ontouchstart' in window) || ( !! ('onmsgesturechange' in window) && !! window.navigator.maxTouchPoints);
			},
			history: function() {
		        return !!window.history && !!history.pushState;
			}
	};
	
	$(document).ready(function(){
		dt.init();
	});
	//
})( jQuery );
