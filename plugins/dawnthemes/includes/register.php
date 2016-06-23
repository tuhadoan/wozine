<?php
if ( ! class_exists( 'DTRegister' ) ) :

	class DTRegister {

		public function __construct() {
			add_action( 'init', array( &$this, 'init' ) );
		}

		public function init() {
			if ( is_admin() ) {
				$this->register_vendor_assets();
			} else {
				add_action( 'template_redirect', array( &$this, 'register_vendor_assets' ) );
			}
		}

		public function register_vendor_assets() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			
			if(!is_admin())
				wp_deregister_style('dtvc-form-font-awesome');
			
			wp_register_style('vendor-font-awesome',DTINC_ASSETS_URL . '/vendor/font-awesome/css/font-awesome' . $suffix . '.css', array(), '4.2.0' );
			wp_register_style('vendor-elegant-icon',DTINC_ASSETS_URL . '/vendor/elegant-icon/css/elegant-icon.css');
			
			wp_register_style('vendor-jquery-ui-bootstrap',DTINC_ASSETS_URL . '/vendor/jquery-ui-bootstrap/jquery-ui-1.10.0.custom.css', array(), '1.10.0' );
			
			wp_register_script( 'vendor-ace-editor', DTINC_ASSETS_URL. '/vendor/ace/ace.js', array( 'jquery' ), DAWN_CORE_VERSION, true );
			
			wp_register_style( 'vendor-preloader', DTINC_ASSETS_URL . '/vendor/preloader/preloader.css', '1.0.0' );
			wp_register_script( 'vendor-preloader', DTINC_ASSETS_URL . '/vendor/preloader/preloader'.$suffix.'.js', array('jquery') , '1.0.0', false );
				
			
			wp_register_style( 'vendor-datetimepicker', DTINC_ASSETS_URL . '/vendor/datetimepicker/jquery.datetimepicker.css', '2.4.0' );
			wp_register_script( 'vendor-datetimepicker', DTINC_ASSETS_URL . '/vendor/datetimepicker/jquery.datetimepicker.js', array( 'jquery' ), '2.4.0', true );
			
			wp_register_style( 'vendor-chosen', DTINC_ASSETS_URL . '/vendor/chosen/chosen.min.css', '1.1.0' );
			wp_register_script( 'vendor-chosen', DTINC_ASSETS_URL . '/vendor/chosen/chosen.jquery' . $suffix .'.js', array( 'jquery' ), '1.0.0', true );
			
			wp_register_script( 'vendor-ajax-chosen', DTINC_ASSETS_URL . '/vendor/chosen/ajax-chosen.jquery' . $suffix . '.js', array( 'jquery', 'vendor-chosen' ), '1.0.0', true );
			wp_register_script( 'vendor-appear', DTINC_ASSETS_URL . '/vendor/jquery.appear' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
			wp_register_script( 'vendor-typed', DTINC_ASSETS_URL . '/vendor/typed' . $suffix .'.js', array( 'jquery','vendor-appear' ), '1.0.0', true );
			wp_register_script( 'vendor-easing', DTINC_ASSETS_URL . '/vendor/easing' . $suffix . '.js', array( 'jquery' ), '1.3.0', true );
			wp_register_script( 'vendor-waypoints', DTINC_ASSETS_URL . '/vendor/waypoints' . $suffix . '.js', array( 'jquery' ), '2.0.5', true );
			wp_register_script( 'vendor-transit', DTINC_ASSETS_URL . '/vendor/jquery.transit' . $suffix . '.js', array( 'jquery' ), '0.9.12', true );
			
			wp_register_script( 'vendor-requestAnimationFrame', DTINC_ASSETS_URL . '/vendor/requestAnimationFrame' . $suffix . '.js', null, '0.0.17', true );
			wp_register_script( 'vendor-parallax', DTINC_ASSETS_URL . '/vendor/jquery.parallax' . $suffix . '.js', array( 'jquery'), '1.1.3', true );
				
			
			wp_register_script( 'vendor-boostrap', DTINC_ASSETS_URL . '/vendor/bootstrap' . $suffix . '.js', false, '3.2.0', true );
			wp_register_script( 'vendor-boostrap-hover-dropdown', DTINC_ASSETS_URL . '/vendor/bootstrap-hover-dropdown' . $suffix . '.js', array( 'jquery' ), '2.0.10', true );
			wp_register_script( 'vendor-superfish',DTINC_ASSETS_URL . '/vendor/superfish-1.7.4.min.js',array( 'jquery' ),'1.7.4',true );
			wp_register_script( 'vendor-imagesloaded', DTINC_ASSETS_URL . '/vendor/imagesloaded.pkgd' . $suffix . '.js', array( 'jquery' ), '3.1.8', true );
			wp_register_script( 'vendor-countTo', DTINC_ASSETS_URL . '/vendor/jquery.countTo' . $suffix . '.js', array( 'jquery', 'vendor-appear' ), '2.0.2', true );
			wp_register_script( 'vendor-infinitescroll', DTINC_ASSETS_URL . '/vendor/jquery.infinitescroll' . $suffix . '.js', array( 'jquery' ), '2.0.2', true );
			
			wp_register_script( 'vendor-ProgressCircle', DTINC_ASSETS_URL . '/vendor/ProgressCircle' . $suffix . '.js', array( 'jquery' ), '2.0.2', true );
			
			wp_register_style( 'vendor-magnific-popup', DTINC_ASSETS_URL . '/vendor/magnific-popup/magnific-popup.css' );
			wp_register_script( 'vendor-magnific-popup', DTINC_ASSETS_URL . '/vendor/magnific-popup/jquery.magnific-popup' . $suffix . '.js', array( 'jquery' ), '0.9.9', true );
			
			wp_register_script( 'vendor-touchSwipe', DTINC_ASSETS_URL . '/vendor/jquery.touchSwipe' . $suffix . '.js', array( 'jquery'), '1.6.6', true );
			
			wp_register_script( 'vendor-carouFredSel', DTINC_ASSETS_URL . '/vendor/jquery.carouFredSel' . $suffix . '.js', array( 'jquery','vendor-touchSwipe', 'vendor-easing' ), '6.2.1', true );
			wp_register_script( 'vendor-isotope', DTINC_ASSETS_URL . '/vendor/isotope.pkgd' . $suffix . '.js', array( 'jquery', 'vendor-imagesloaded' ), '6.2.1', true );
		}
		
	}
	new DTRegister();


endif;