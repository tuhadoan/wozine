<?php
if (! class_exists ( 'DTInit' )) :
	class DTInit {
		public $version = '1.0.0';
		
		public function __construct() {
			$this->_define_constants ();
			$this->_includes ();
			add_action('init', array(&$this,'init'));
		}
		
		public function init(){
			//dt_tooltip shortcode
			add_shortcode('dt_tooltip',array(&$this,'dt_tooltip_shortcode'));
			load_plugin_textdomain( 'dawnthemes' , false,  basename(DAWN_CORE_DIR).'/languages' );
		}
		
		private function _define_constants() {
			if(!defined('DTINC_VERSION'))
				define( 'DTINC_VERSION', $this->version );
			if(!defined('DTINC_DIR'))
				define( 'DTINC_DIR', dirname ( __FILE__ ) );
			if(!defined('DTINC_URL'))
				define( 'DTINC_URL', untrailingslashit( plugins_url( '/', dirname(__FILE__) ) ) . '/includes' );
			if(!defined('DTINC_ASSETS_URL'))
				define( 'DTINC_ASSETS_URL', untrailingslashit( plugins_url( '/', dirname(__FILE__) ) ) . '/assets' );
		}
		private function _includes() {
			
			// Utils
			include_once (DTINC_DIR . '/data-functions.php');
			
			// Register
			include_once (DTINC_DIR . '/register.php');
			// Hook
			include_once (DTINC_DIR . '/hook.php');
			//Visual Composer
			include_once (DTINC_DIR . '/visual-composer.php');
			// Widgets
			include_once (DTINC_DIR . '/widgets.php');
			// Breadcrumb
			include_once (DTINC_DIR . '/breadcrumb.php');
			//Woocommerce
			include_once (DTINC_DIR . '/woocommerce.php');
			
			if(!class_exists('SMK_Sidebar_Generator'))
				include_once (DTINC_DIR . '/lib/smk-sidebar-generator/smk-sidebar-generator.php');
			
			// Admin
			if (is_admin ()) {
				include_once (DTINC_DIR . '/admin/functions.php');
				include_once (DTINC_DIR . '/admin/admin.php');
			}
			
			//Controller
			include_once (DTINC_DIR . '/controller.php');
		}
		
		public function dt_tooltip_shortcode($atts,$content=null){
			$tooltip = '';
			extract(shortcode_atts(array(
			'text'			=>'',
			'url'			=>'#',
			'type'			=>'',
			'position'		=>'',
			'title'			=>'',
			'trigger'		=>'',
			), $atts));
			$data_el = '';
			if(!empty($type)){
				$data_el = ' data-toggle="'.$type.'" data-container="body" data-original-title="'.($type === 'tooltip' ? esc_attr(do_shortcode( shortcode_unautop( $content) )) : esc_attr($title)).'" data-trigger="'.$trigger.'" data-placement="'.$position.'" '.($type === 'popover'?' data-content="'.esc_attr(do_shortcode( shortcode_unautop( $content) )).'"':'').'';
			}
			if(!empty($data_el))
				$tooltip = '<a'.$data_el.' href="'.esc_url($url).'">'.do_shortcode( shortcode_unautop( $text) ).'</a>';
			return $tooltip;
		}
		
	}
	new DTInit ();
endif;
