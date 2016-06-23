<?php
if ( ! class_exists( 'DT_VisualComposer' ) && defined( 'WPB_VC_VERSION' ) ) :
	
	define( 'DTVC_ADD_ITEM_TITLE', __( "Add Item", 'dawnthemes' ) );
	define( 'DTVC_ITEM_TITLE', __( "Item", 'dawnthemes' ) );
	define( 'DTVC_MOVE_TITLE', __( 'Move', 'dawnthemes' ) );
	
	if ( ! class_exists( 'WPBakeryShortCode_VC_Tabs', false ) )
		require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-tabs.php' );
	
	if ( ! class_exists( 'WPBakeryShortCode_VC_Column', false ) )
		require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-column.php' );

	class DTWPBakeryShortcodeContainer extends WPBakeryShortCodesContainer {

		/**
		 * Find html template for shortcode output.
		 */
		protected function findShortcodeTemplate() {
			// Check template path in shortcode's mapping settings
			if ( ! empty( $this->settings['html_template'] ) && is_file( $this->settings( 'html_template' ) ) ) {
				return $this->setTemplate( $this->settings['html_template'] );
			}
			// Check template in theme directory
			$user_template = vc_manager()->getShortcodesTemplateDir( $this->getFilename() . '.php' );
			
			if ( is_file( $user_template ) ) {
				
				return $this->setTemplate( $user_template );
			}
		}

		protected function getFileName() {
			return $this->shortcode;
		}
	}

	class DTWPBakeryShortcode extends WPBakeryShortCode {

		/**
		 * Find html template for shortcode output.
		 */
		protected function findShortcodeTemplate() {
			// Check template path in shortcode's mapping settings
			if ( ! empty( $this->settings['html_template'] ) && is_file( $this->settings( 'html_template' ) ) ) {
				return $this->setTemplate( $this->settings['html_template'] );
			}
			// Check template in theme directory
			$user_template = vc_manager()->getShortcodesTemplateDir( $this->getFilename() . '.php' );
			if ( is_file( $user_template ) ) {
				return $this->setTemplate( $user_template );
			}
		}

		protected function getFileName() {
			return $this->shortcode;
		}
	}

	class WPBakeryShortCode_DT_Carousel extends WPBakeryShortCode_VC_Tabs {

		static $filter_added = false;

		public function __construct( $settings ) {
			parent::__construct( $settings );
			// WPBakeryVisualComposer::getInstance()->addShortCode( array( 'base' => 'vc_tab' ) );
			if ( ! self::$filter_added ) {
				$this->addFilter( 'vc_inline_template_content', 'setCustomTabId' );
				self::$filter_added = true;
			}
		}

		protected $predefined_atts = array( 'tab_id' => DTVC_ITEM_TITLE, 'title' => '' );

		public function contentAdmin( $atts, $content = null ) {
			$width = $custom_markup = '';
			$shortcode_attributes = array( 'width' => '1/1' );
			foreach ( $this->settings['params'] as $param ) {
				if ( $param['param_name'] != 'content' ) {
					if ( isset( $param['value'] ) && is_string( $param['value'] ) ) {
						$shortcode_attributes[$param['param_name']] = $param['value'];
					} elseif ( isset( $param['value'] ) ) {
						$shortcode_attributes[$param['param_name']] = $param['value'];
					}
				} else 
					if ( $param['param_name'] == 'content' && $content == NULL ) {
						$content = $param['value'];
					}
			}
			extract( shortcode_atts( $shortcode_attributes, $atts ) );
			
			// Extract tab titles
			
			preg_match_all( 
				'/dt_carousel_item title="([^\"]+)"(\stab_id\=\"([^\"]+)\"){0,1}/i', 
				$content, 
				$matches, 
				PREG_OFFSET_CAPTURE );
			
			$output = '';
			$tab_titles = array();
			
			if ( isset( $matches[0] ) ) {
				$tab_titles = $matches[0];
			}
			$tmp = '';
			if ( count( $tab_titles ) ) {
				$tmp .= '<ul class="clearfix tabs_controls">';
				foreach ( $tab_titles as $tab ) {
					preg_match( 
						'/title="([^\"]+)"(\stab_id\=\"([^\"]+)\"){0,1}/i', 
						$tab[0], 
						$tab_matches, 
						PREG_OFFSET_CAPTURE );
					if ( isset( $tab_matches[1][0] ) ) {
						$tmp .= '<li><a href="#tab-' .
							 ( isset( $tab_matches[3][0] ) ? $tab_matches[3][0] : sanitize_title( $tab_matches[1][0] ) ) .
							 '">' . $tab_matches[1][0] . '</a></li>';
					}
				}
				$tmp .= '</ul>' . "\n";
			} else {
				$output .= do_shortcode( $content );
			}
			$elem = $this->getElementHolder( $width );
			
			$iner = '';
			foreach ( $this->settings['params'] as $param ) {
				$custom_markup = '';
				$param_value = isset( $$param['param_name'] ) ? $$param['param_name'] : '';
				if ( is_array( $param_value ) ) {
					// Get first element from the array
					reset( $param_value );
					$first_key = key( $param_value );
					$param_value = $param_value[$first_key];
				}
				$iner .= $this->singleParamHtmlHolder( $param, $param_value );
			}
			if ( isset( $this->settings["custom_markup"] ) && $this->settings["custom_markup"] != '' ) {
				if ( $content != '' ) {
					$custom_markup = str_ireplace( "%content%", $tmp . $content, $this->settings["custom_markup"] );
				} else 
					if ( $content == '' && isset( $this->settings["default_content_in_template"] ) &&
						 $this->settings["default_content_in_template"] != '' ) {
						$custom_markup = str_ireplace( 
							"%content%", 
							$this->settings["default_content_in_template"], 
							$this->settings["custom_markup"] );
					} else {
						$custom_markup = str_ireplace( "%content%", '', $this->settings["custom_markup"] );
					}
				$iner .= do_shortcode( $custom_markup );
			}
			$elem = str_ireplace( '%wpb_element_content%', $iner, $elem );
			$output = $elem;
			
			return $output;
		}

		/**
		 * Find html template for shortcode output.
		 */
		protected function findShortcodeTemplate() {
			// Check template path in shortcode's mapping settings
			if ( ! empty( $this->settings['html_template'] ) && is_file( $this->settings( 'html_template' ) ) ) {
				return $this->setTemplate( $this->settings['html_template'] );
			}
			// Check template in theme directory
			$user_template = vc_manager()->getShortcodesTemplateDir( $this->getFilename() . '.php' );
			if ( is_file( $user_template ) ) {
				return $this->setTemplate( $user_template );
			}
		}

		protected function getFileName() {
			return $this->shortcode;
		}

		public function getTabTemplate() {
			return '<div class="wpb_template">' .
				 do_shortcode( '[dt_carousel_item title="' . DTVC_ITEM_TITLE . '" tab_id=""][/dt_carousel_item]' ) .
				 '</div>';
		}
	}

	class WPBakeryShortCode_DT_Carousel_Item extends WPBakeryShortCode_VC_Column {

		protected $controls_css_settings = 'tc vc_control-container';

		protected $controls_list = array( 'add', 'edit', 'clone', 'delete' );

		protected $predefined_atts = array( 'tab_id' => DTVC_ITEM_TITLE, 'title' => '' );

		protected $controls_template_file = 'editors/partials/backend_controls_tab.tpl.php';

		public function __construct( $settings ) {
			parent::__construct( $settings );
		}

		public function customAdminBlockParams() {
			return ' id="tab-' . $this->atts['tab_id'] . '"';
		}

		public function mainHtmlBlockParams( $width, $i ) {
			return 'data-element_type="' . $this->settings["base"] . '" class="wpb_' . $this->settings['base'] .
				 ' wpb_sortable wpb_content_holder"' . $this->customAdminBlockParams();
		}

		public function containerHtmlBlockParams( $width, $i ) {
			return 'class="wpb_column_container vc_container_for_children"';
		}

		public function getColumnControls( $controls, $extended_css = '' ) {
			return $this->getColumnControlsModular( $extended_css );
		}

		/**
		 * Find html template for shortcode output.
		 */
		protected function findShortcodeTemplate() {
			// Check template path in shortcode's mapping settings
			if ( ! empty( $this->settings['html_template'] ) && is_file( $this->settings( 'html_template' ) ) ) {
				return $this->setTemplate( $this->settings['html_template'] );
			}
			// Check template in theme directory
			$user_template = vc_manager()->getShortcodesTemplateDir( $this->getFilename() . '.php' );
			if ( is_file( $user_template ) ) {
				return $this->setTemplate( $user_template );
			}
		}

		protected function getFileName() {
			return $this->shortcode;
		}
	}

	class WPBakeryShortCode_DT_Testimonial extends WPBakeryShortCode_DT_Carousel {

		static $filter_added = false;

		public function __construct( $settings ) {
			parent::__construct( $settings );
			if ( ! self::$filter_added ) {
				$this->addFilter( 'vc_inline_template_content', 'setCustomTabId' );
				self::$filter_added = true;
			}
		}

		protected $predefined_atts = array( 'tab_id' => DTVC_ITEM_TITLE, 'title' => '' );

		public function contentAdmin( $atts, $content = null ) {
			$width = $custom_markup = '';
			$shortcode_attributes = array( 'width' => '1/1' );
			foreach ( $this->settings['params'] as $param ) {
				if ( $param['param_name'] != 'content' ) {
					if ( isset( $param['value'] ) && is_string( $param['value'] ) ) {
						$shortcode_attributes[$param['param_name']] = $param['value'];
					} elseif ( isset( $param['value'] ) ) {
						$shortcode_attributes[$param['param_name']] = $param['value'];
					}
				} else 
					if ( $param['param_name'] == 'content' && $content == NULL ) {
						$content = $param['value'];
					}
			}
			extract( shortcode_atts( $shortcode_attributes, $atts ) );
			
			// Extract tab titles
			
			preg_match_all( 
				'/dt_testimonial_item title="([^\"]+)"(\stab_id\=\"([^\"]+)\"){0,1}/i', 
				$content, 
				$matches, 
				PREG_OFFSET_CAPTURE );
			
			$output = '';
			$tab_titles = array();
			
			if ( isset( $matches[0] ) ) {
				$tab_titles = $matches[0];
			}
			$tmp = '';
			if ( count( $tab_titles ) ) {
				$tmp .= '<ul class="clearfix tabs_controls">';
				foreach ( $tab_titles as $tab ) {
					preg_match( 
						'/title="([^\"]+)"(\stab_id\=\"([^\"]+)\"){0,1}/i', 
						$tab[0], 
						$tab_matches, 
						PREG_OFFSET_CAPTURE );
					if ( isset( $tab_matches[1][0] ) ) {
						$tmp .= '<li><a href="#tab-' .
							 ( isset( $tab_matches[3][0] ) ? $tab_matches[3][0] : sanitize_title( $tab_matches[1][0] ) ) .
							 '">' . $tab_matches[1][0] . '</a></li>';
					}
				}
				$tmp .= '</ul>' . "\n";
			} else {
				$output .= do_shortcode( $content );
			}
			$elem = $this->getElementHolder( $width );
			
			$iner = '';
			foreach ( $this->settings['params'] as $param ) {
				$custom_markup = '';
				$param_value = isset( $$param['param_name'] ) ? $$param['param_name'] : '';
				if ( is_array( $param_value ) ) {
					// Get first element from the array
					reset( $param_value );
					$first_key = key( $param_value );
					$param_value = $param_value[$first_key];
				}
				$iner .= $this->singleParamHtmlHolder( $param, $param_value );
			}
			if ( isset( $this->settings["custom_markup"] ) && $this->settings["custom_markup"] != '' ) {
				if ( $content != '' ) {
					$custom_markup = str_ireplace( "%content%", $tmp . $content, $this->settings["custom_markup"] );
				} else 
					if ( $content == '' && isset( $this->settings["default_content_in_template"] ) &&
						 $this->settings["default_content_in_template"] != '' ) {
						$custom_markup = str_ireplace( 
							"%content%", 
							$this->settings["default_content_in_template"], 
							$this->settings["custom_markup"] );
					} else {
						$custom_markup = str_ireplace( "%content%", '', $this->settings["custom_markup"] );
					}
				$iner .= do_shortcode( $custom_markup );
			}
			$elem = str_ireplace( '%wpb_element_content%', $iner, $elem );
			$output = $elem;
			
			return $output;
		}

		/**
		 * Find html template for shortcode output.
		 */
		protected function findShortcodeTemplate() {
			// Check template path in shortcode's mapping settings
			if ( ! empty( $this->settings['html_template'] ) && is_file( $this->settings( 'html_template' ) ) ) {
				return $this->setTemplate( $this->settings['html_template'] );
			}
			// Check template in theme directory
			$user_template = vc_manager()->getShortcodesTemplateDir( $this->getFilename() . '.php' );
			if ( is_file( $user_template ) ) {
				return $this->setTemplate( $user_template );
			}
		}

		protected function getFileName() {
			return $this->shortcode;
		}

		public function getTabTemplate() {
			return '<div class="wpb_template">' .
				 do_shortcode( '[dt_testimonial_item title="' . DTVC_ITEM_TITLE . '" tab_id=""][/dt_testimonial_item]' ) .
				 '</div>';
		}
	}

	class WPBakeryShortCode_DT_Testimonial_Item extends DTWPBakeryShortcode {
	}
	
	// Shortcode Container
	class WPBakeryShortCode_DT_Product_Slider extends DTWPBakeryShortcodeContainer {
	}
	// Shortcode
	class WPBakeryShortCode_DT_Button extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Instagram extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Post extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Post_Grid extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Mailchimp extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Box_Feature extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Video extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Counter extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Countdown extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_Client extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Cart extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Layered_Nav_Filters extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Layered_Nav extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Price_Filter extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Product_Categories extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Product_Search extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Product_Tag_Cloud extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Product_Mansory extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_Product_Categories_Grid extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Product_Lookbooks extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Products extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Special_Product extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Products_Grid extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Recent_Reviews extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Recently_Viewed_Products extends DTWPBakeryShortcode {
	}

	class WPBakeryShortCode_DT_WC_Top_Rated_Products extends DTWPBakeryShortcode {
	}

	class DT_VisualComposer {

		public $param_holder = 'div';

		public static $button_styles = array( 'Default' => '', 'Outlined' => 'outline' );

		public function __construct() {
			if ( function_exists( 'vc_set_as_theme' ) ) :
				vc_set_as_theme( true );
			endif;
			
			if ( function_exists( 'vc_disable_frontend' ) ) :
				vc_disable_frontend();
			 else :
				if ( class_exists( 'NewVisualComposer' ) )
					NewVisualComposer::disableInline();
			endif;
			add_action( 'init', array( &$this, 'map' ), 20 );
			add_action( 'init', array( &$this, 'add_params' ), 50 );
			if ( is_admin() ) {
				add_action( 'do_meta_boxes', array( &$this, 'remove_vc_teaser_meta_box' ), 1 );
				add_action( 'admin_print_scripts-post.php', array( &$this, 'enqueue_scripts' ),100 );
				add_action( 'admin_print_scripts-post-new.php', array( &$this, 'enqueue_scripts' ),100 );
				
				$vc_params_js = DTINC_ASSETS_URL . '/js/vc-params.js';
				vc_add_shortcode_param( 'nullfield', array( &$this, 'nullfield_param' ), $vc_params_js );
				vc_add_shortcode_param( 
					'product_attribute_filter', 
					array( &$this, 'product_attribute_filter_param' ), 
					$vc_params_js );
				vc_add_shortcode_param( 'product_attribute', array( &$this, 'product_attribute_param' ), $vc_params_js );
				vc_add_shortcode_param( 'products_ajax', array( &$this, 'products_ajax_param' ), $vc_params_js );
				vc_add_shortcode_param( 'product_brand', array( &$this, 'product_brand_param' ), $vc_params_js );
				vc_add_shortcode_param( 'product_lookbook', array( &$this, 'product_lookbook_param' ), $vc_params_js );
				vc_add_shortcode_param( 'product_category', array( &$this, 'product_category_param' ), $vc_params_js );
				vc_add_shortcode_param( 'ui_datepicker', array( &$this, 'ui_datepicker_param' ) );
				vc_add_shortcode_param( 'post_category', array( &$this, 'post_category_param' ), $vc_params_js );
				vc_add_shortcode_param( 'ui_slider', array( &$this, 'ui_slider_param' ) );
				vc_add_shortcode_param( 'dropdown_group', array( &$this, 'dropdown_group_param' ) );
			}
		}

		public function map() {
			$is_wp_version_3_6_more = version_compare( 
				preg_replace( '/^([\d\.]+)(\-.*$)/', '$1', get_bloginfo( 'version' ) ), 
				'3.6' ) >= 0;
			vc_map( 
				array( 
					'base' => 'dt_button', 
					'name' => __( 'Button', 'dawnthemes' ), 
					'description' => __( 'Eye catching button.', 'dawnthemes' ), 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_button', 
					'icon' => 'dt-vc-icon-dt_button', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_menu_anchor', 
					'name' => __( 'Menu Anchor', 'dawnthemes' ), 
					'description' => __( 'Add a menu anchor points.', 'dawnthemes' ), 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-menu_anchor', 
					'icon' => 'dt-vc-icon-menu_anchor', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_post', 
					'name' => __( 'Post', 'dawnthemes' ), 
					'description' => __( 'Display post.', 'dawnthemes' ), 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_post', 
					'icon' => 'dt-vc-icon-dt_post', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_post_grid', 
					'name' => __( 'Post Zigzag', 'dawnthemes' ), 
					'description' => __( 'Display post with 2 styles.', 'dawnthemes' ), 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_post', 
					'icon' => 'dt-vc-icon-dt_post', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			
			vc_map( 
				array( 
					'base' => 'dt_mailchimp', 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'name' => __( 'Mailchimp Subscribe', 'dawnthemes' ), 
					'description' => __( 'Widget Mailchimp Subscribe.', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_mailchimp', 
					'icon' => 'dt-vc-icon-dt_mailchimp', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_box_feature', 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'name' => __( 'Box Feature', 'dawnthemes' ), 
					'description' => __( 'Box Feature.', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_box_feature', 
					'icon' => 'dt-vc-icon-dt_box_feature', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_instagram', 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'name' => __( 'Instagram', 'dawnthemes' ), 
					'description' => __( 'Instagram.', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_instagram', 
					'icon' => 'dt-vc-icon-dt_instagram', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			
			vc_map( 
				array( 
					'base' => 'dt_carousel', 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'name' => __( 'Carousel Content', 'dawnthemes' ), 
					'description' => __( 'Animated carousel with carousel', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_carousel', 
					'icon' => 'dt-vc-icon-dt_carousel', 
					'show_settings_on_create' => true, 
					'is_container' => true, 
					'js_view' => 'DTVCCarousel', 
					'params' => array(), 
					"custom_markup" => '
						  <div class="wpb_tabs_holder wpb_holder clearfix vc_container_for_children">
						  <ul class="tabs_controls">
						  </ul>
						  %content%
						  </div>', 
					'default_content' => '
					  [dt_carousel_item title="' . __( 'Item 1', 'dawnthemes' ) . '" tab_id="' . time() . '-1-' . rand( 0, 100 ) . '"][/dt_carousel_item]
					  [dt_carousel_item title="' . __( 'Item 2', 'dawnthemes' ) . '" tab_id="' . time() . '-2-' . rand( 0, 100 ) . '"][/dt_carousel_item]
					  [dt_carousel_item title="' . __( 'Item 3', 'dawnthemes' ) . '" tab_id="' . time() . '-3-' . rand( 0, 100 ) . '"][/dt_carousel_item]
					  ' ) );
			vc_map( 
				array( 
					'name' => __( 'Carousel Item', 'dawnthemes' ), 
					'base' => 'dt_carousel_item', 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'allowed_container_element' => 'vc_row', 
					'is_container' => true, 
					'content_element' => false, 
					'params' => array(), 
					'js_view' => 'DTVCCarouselItem' ) );
			vc_map( 
				array( 
					'base' => 'dt_testimonial', 
					'name' => __( 'Testimonial', 'dawnthemes' ), 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'description' => __( 'Animated Testimonial with slider', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_testimonial', 
					'icon' => 'dt-vc-icon-dt_testimonial', 
					'show_settings_on_create' => true, 
					'is_container' => true, 
					'js_view' => 'DTVCTestimonial', 
					'params' => array(), 
					"custom_markup" => '
						  <div class="wpb_tabs_holder wpb_holder clearfix vc_container_for_children">
						  <ul class="tabs_controls">
						  </ul>
						  %content%
						  </div>', 
					'default_content' => '
					  [dt_testimonial_item title="' . __( 'Item 1', 'dawnthemes' ) . '" tab_id="' . time() . '-1-' . rand( 0, 100 ) . '"][/dt_testimonial_item]
					  [dt_testimonial_item title="' . __( 'Item 2', 'dawnthemes' ) . '" tab_id="' . time() . '-2-' . rand( 0, 100 ) . '"][/dt_testimonial_item]
					  [dt_testimonial_item title="' . __( 'Item 3', 'dawnthemes' ) . '" tab_id="' . time() . '-3-' . rand( 0, 100 ) . '"][/dt_testimonial_item]
					  ' ) );
			vc_map( 
				array( 
					'name' => __( 'Testimonial Item', 'dawnthemes' ), 
					'base' => 'dt_testimonial_item', 
					'allowed_container_element' => 'vc_row', 
					'is_container' => true, 
					'content_element' => false, 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'params' => array(), 
					'js_view' => 'DTVCTestimonialItem' ) );
			vc_map( 
				array( 
					'base' => 'dt_video', 
					'name' => __( 'Video Player', 'dawnthemes' ), 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_video', 
					'icon' => 'dt-vc-icon-dt_video', 
					'show_settings_on_create' => true, 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_counter', 
					'name' => __( 'Counter', 'dawnthemes' ), 
					'description' => __( 'Display Counter.', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_counter', 
					'icon' => 'dt-vc-icon-dt_counter', 
					'show_settings_on_create' => true, 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_countdown', 
					'name' => __( 'Coundown', 'dawnthemes' ), 
					'description' => __( 'Display Countdown.', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_countdown', 
					'icon' => 'dt-vc-icon-dt_countdown', 
					'show_settings_on_create' => true, 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'params' => array() ) );
			vc_map( 
				array( 
					'base' => 'dt_client', 
					'name' => __( 'Client', 'dawnthemes' ), 
					'description' => __( 'Display list clients.', 'dawnthemes' ), 
					'class' => 'dt-vc-element dt-vc-element-dt_client', 
					'icon' => 'dt-vc-icon-dt_client', 
					'show_settings_on_create' => true, 
					"category" => __( "dawnthemes", 'dawnthemes' ), 
					'params' => array() ) );
			$this->_woocommerce_map();
		}

		protected function _woocommerce_map() {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
				return;
			
			vc_map( 
				array( 
					'base' => 'dt_product_slider', 
					'name' => __( 'Product Slider', 'dawnthemes' ), 
					'description' => __( 'Animated products with carousel.', 'dawnthemes' ), 
					'as_parent' => array( 
						'only' => 'product_category,product_categories,dtwc_product_brands,products,related_products,product_attribute,featured_products,top_rated_products,best_selling_products,sale_products,recent_products' ), 
					'content_element' => true, 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'js_view' => 'VcColumnView', 
					'show_settings_on_create' => true, 
					'params' => array( 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Carousel Title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'Enter text which will be used as widget title. Leave blank if no title is needed.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'dropdown', 
							'heading' => __( 'Title color', 'dawnthemes' ), 
							'param_name' => 'title_color', 
							'default', 
							'dependency' => array( 'element' => "title", 'not_empty' => true ), 
							'value' => array( 
								__( 'Default', 'dawnthemes' ) => 'default', 
								__( 'Primary', 'dawnthemes' ) => 'primary', 
								__( 'Success', 'dawnthemes' ) => 'success', 
								__( 'Info', 'dawnthemes' ) => 'info', 
								__( 'Warning', 'dawnthemes' ) => 'warning', 
								__( 'Danger', 'dawnthemes' ) => 'danger' ) ), 
						array( 
							'type' => 'dropdown', 
							'heading' => __( 'Transition', 'dawnthemes' ), 
							'param_name' => 'fx', 
							'std' => 'scroll', 
							'value' => array( 
								'Scroll' => 'scroll', 
								'Directscroll' => 'directscroll', 
								'Fade' => 'fade', 
								'Cross fade' => 'crossfade', 
								'Cover' => 'cover', 
								'Cover fade' => 'cover-fade', 
								'Uncover' => 'cover-fade', 
								'Uncover fade' => 'uncover-fade' ), 
							'description' => __( 'Indicates which effect to use for the transition.', 'dawnthemes' ) ), 
						
						// array(
						// 'param_name' => 'scroll_item',
						// 'heading' => __( 'The number of items to scroll', 'dawnthemes' ),
						// 'type' => 'ui_slider',
						// 'holder' => $this->param_holder,
						// 'value' => '1',
						// 'data_min' => '1',
						// 'data_max' => '6',
						// ),
						array( 
							'param_name' => 'scroll_speed', 
							'heading' => __( 'Transition Scroll Speed (ms)', 'dawnthemes' ), 
							'type' => 'ui_slider', 
							'value' => '700', 
							'data_min' => '100', 
							'data_step' => '100', 
							'data_max' => '3000' ), 
						
						array( 
							"type" => "dropdown", 
							"heading" => __( "Easing", 'dawnthemes' ), 
							"param_name" => "easing", 
							'std' => 'linear', 
							"value" => array( 
								'linear' => 'linear', 
								'swing' => 'swing', 
								'easeInQuad' => 'easeInQuad', 
								'easeOutQuad' => 'easeOutQuad', 
								'easeInOutQuad' => 'easeInOutQuad', 
								'easeInCubic' => 'easeInCubic', 
								'easeOutCubic' => 'easeOutCubic', 
								'easeInOutCubic' => 'easeInOutCubic', 
								'easeInQuart' => 'easeInQuart', 
								'easeOutQuart' => 'easeOutQuart', 
								'easeInOutQuart' => 'easeInOutQuart', 
								'easeInQuint' => 'easeInQuint', 
								'easeOutQuint' => 'easeOutQuint', 
								'easeInOutQuint' => 'easeInOutQuint', 
								'easeInExpo' => 'easeInExpo', 
								'easeOutExpo' => 'easeOutExpo', 
								'easeInOutExpo' => 'easeInOutExpo', 
								'easeInSine' => 'easeInSine', 
								'easeOutSine' => 'easeOutSine', 
								'easeInOutSine' => 'easeInOutSine', 
								'easeInCirc' => 'easeInCirc', 
								'easeOutCirc' => 'easeOutCirc', 
								'easeInOutCirc' => 'easeInOutCirc', 
								'easeInElastic' => 'easeInElastic', 
								'easeOutElastic' => 'easeOutElastic', 
								'easeInOutElastic' => 'easeInOutElastic', 
								'easeInBack' => 'easeInBack', 
								'easeOutBack' => 'easeOutBack', 
								'easeInOutBack' => 'easeInOutBack', 
								'easeInBounce' => 'easeInBounce', 
								'easeOutBounce' => 'easeOutBounce', 
								'easeInOutBounce' => 'easeInOutBounce' ), 
							"description" => __( 
								"Select the animation easing you would like for slide transitions <a href=\"http://jqueryui.com/resources/demos/effect/easing.html\" target=\"_blank\"> Click here </a> to see examples of these.", 
								'dawnthemes' ) ), 
						array( 
							'type' => 'checkbox', 
							'heading' => __( 'Item no Padding ?', 'dawnthemes' ), 
							'param_name' => 'no_padding', 
							'description' => __( 'Item No Padding', 'dawnthemes' ), 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
						array( 
							'type' => 'checkbox', 
							'heading' => __( 'Autoplay ?', 'dawnthemes' ), 
							'param_name' => 'auto_play', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
						array( 
							'type' => 'checkbox', 
							'heading' => __( 'Hide Slide Pagination ?', 'dawnthemes' ), 
							'param_name' => 'hide_pagination', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
						array( 
							'type' => 'checkbox', 
							'heading' => __( 'Hide Previous/Next Control ?', 'dawnthemes' ), 
							'param_name' => 'hide_control', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
						array( 
							'type' => 'dropdown', 
							'heading' => __( 'Previous/Next Control Position', 'dawnthemes' ), 
							'param_name' => 'control_position', 
							'std' => 'default', 
							'dependency' => array( 'element' => "title", 'not_empty' => true ), 
							'value' => array( 
								__( 'Default', 'dawnthemes' ) => 'default', 
								__( 'Center with Title', 'dawnthemes' ) => 'center', 
								__( 'Right with Title', 'dawnthemes' ) => 'right' ) ), 
						array( 
							'param_name' => 'visibility', 
							'heading' => __( 'Visibility', 'dawnthemes' ), 
							'type' => 'dropdown', 
							'std' => 'all', 
							'value' => array( 
								__( 'All Devices', 'dawnthemes' ) => "all", 
								__( 'Hidden Phone', 'dawnthemes' ) => "hidden-phone", 
								__( 'Hidden Tablet', 'dawnthemes' ) => "hidden-tablet", 
								__( 'Hidden PC', 'dawnthemes' ) => "hidden-pc", 
								__( 'Visible Phone', 'dawnthemes' ) => "visible-phone", 
								__( 'Visible Tablet', 'dawnthemes' ) => "visible-tablet", 
								__( 'Visible PC', 'dawnthemes' ) => "visible-pc" ) ), 
						array( 
							'param_name' => 'el_class', 
							'heading' => __( '(Optional) Extra class name', 'dawnthemes' ), 
							'type' => 'textfield', 
							"description" => __( 
								"If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Product", 'dawnthemes' ), 
					"base" => "product", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Display a single product.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select product", 'dawnthemes' ), 
							'single_select' => true, 
							'admin_label' => true, 
							"param_name" => "id" ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Special Product", 'dawnthemes' ), 
					"base" => "dt_wc_special_product", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Display a single Special product.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select product", 'dawnthemes' ), 
							'single_select' => true, 
							'admin_label' => true, 
							"param_name" => "id" ), 
						array( 
							"type" => "attach_image", 
							"heading" => __( "Product Image", 'dawnthemes' ), 
							"param_name" => "product_image", 
							"value" => "", 
							"description" => __( 
								"Select image from media library. If blank will use default Product Image", 
								'dawnthemes' ) ) ) )

				 );
			vc_map( 
				array( 
					"name" => __( "Product Page", 'dawnthemes' ), 
					"base" => "product_page", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Show a single product page.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select product", 'dawnthemes' ), 
							'single_select' => true, 
							'admin_label' => true, 
							"param_name" => "id" ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Product Masonry", 'dawnthemes' ), 
					"base" => "dt_wc_product_mansory", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List products with Masonry layout.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "product_category", 
							"heading" => __( "Categories", 'dawnthemes' ), 
							"param_name" => "category", 
							"admin_label" => true ), 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"admin_label" => true, 
							"value" => 12 ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( 2, 3, 4, 5, 6 ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'show', 
							'heading' => __( 'Show', 'dawnthemes' ), 
							'value' => array( 
								__( 'All Products', 'dawnthemes' ) => '', 
								__( 'Featured Products', 'dawnthemes' ) => 'featured', 
								__( 'On-sale Products', 'dawnthemes' ) => 'onsale' ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'orderby', 
							'heading' => __( 'Order by', 'dawnthemes' ), 
							'std' => 'date', 
							'value' => array( 
								__( 'Date', 'dawnthemes' ) => 'date', 
								__( 'Price', 'dawnthemes' ) => 'price', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Sales', 'dawnthemes' ) => 'sales' ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'order', 
							'heading' => _x( 'Order', 'Sorting order', 'dawnthemes' ), 
							'std' => 'asc', 
							'value' => array( __( 'ASC', 'dawnthemes' ) => 'asc', __( 'DESC', 'dawnthemes' ) => 'desc' ) ), 
						array( 
							'param_name' => 'hide_all_filter', 
							'heading' => __( 'Hide All Filter Products', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'param_name' => 'hide_free', 
							'heading' => __( 'Hide free products', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes'  ) => '1' ) ), 
						array( 
							'param_name' => 'show_hidden', 
							'heading' => __( 'Show hidden products', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Product Category", 'dawnthemes' ), 
					"base" => "product_category", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List products in a category shortcode.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "product_category", 
							"heading" => __( "Categories", 'dawnthemes' ), 
							"param_name" => "category", 
							"admin_label" => true ), 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"admin_label" => true, 
							"value" => 12 ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							'class' => 'dtwc-woo-product-page-dropdown', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ), 
						
						array( 
							"type" => "dropdown", 
							"class" => "", 
							"heading" => __( "Query type", 'dawnthemes' ), 
							"param_name" => "operator", 
							'std' => 'IN', 
							"value" => array( 
								__( 'IN', 'dawnthemes' ) => 'IN', 
								__( 'AND', 'dawnthemes' ) => 'AND', 
								__( 'NOT IN', 'dawnthemes' ) => 'NOT IN' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Product Categories", 'dawnthemes' ), 
					"base" => "product_categories", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List all (or limited) product categories.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "product_category", 
							"heading" => __( "Categories", 'dawnthemes' ), 
							"param_name" => "ids", 
							'select_field' => 'id', 
							"admin_label" => true ), 
						array( 
							"type" => "textfield", 
							"heading" => __( "Number", 'dawnthemes' ), 
							"param_name" => "number", 
							"admin_label" => true, 
							'description' => __( 
								'You can specify the number of category to show (Leave blank to display all categories).', 
								'dawnthemes' ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => '1', 
							"heading" => __( "Hide Empty", 'dawnthemes' ), 
							"param_name" => "hide_empty", 
							"value" => array( __( 'Yes', 'dawnthemes' ) => '1', __( 'No', 'dawnthemes' ) => '0' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Product Categories Grid", 'dawnthemes' ), 
					"base" => "product_categories_grid", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Display categories with grid layout.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "product_category", 
							"heading" => __( "Categories", 'dawnthemes' ), 
							"param_name" => "ids", 
							'select_field' => 'id', 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => '1', 
							"heading" => __( "Grid Style", 'dawnthemes' ), 
							"param_name" => "style", 
							'admin_label' => true, 
							"value" => array( __( 'Style 1', 'dawnthemes' ) => '1', __( 'Style 2', 'dawnthemes' ) => '2' ,__( 'Style 3', 'dawnthemes' ) => '3' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => '1', 
							"heading" => __( "Grid Gutter", 'dawnthemes' ), 
							"param_name" => "gutter", 
							"value" => array( __( 'Yes', 'dawnthemes' ) => '1', __( 'No', 'dawnthemes' ) => '0' ) ), 
						array( 
							"type" => "textfield", 
							"heading" => __( "Number", 'dawnthemes' ), 
							"param_name" => "number", 
							"admin_label" => true, 
							'description' => __( 
								'You can specify the number of category to show (Leave blank to display all categories).', 
								'dawnthemes' ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Category Order', 'dawnthemes' ) => 'order', 
								__( 'Name', 'dawnthemes' ) => 'name', 
								__( 'Term ID', 'dawnthemes' ) => 'term_id', 
								__( 'Taxonomy Count', 'dawnthemes' ) => 'count', 
								__( 'ID', 'dawnthemes' ) => 'id', ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => '1', 
							"heading" => __( "Hide Empty", 'dawnthemes' ), 
							"param_name" => "hide_empty", 
							"value" => array( __( 'Yes', 'dawnthemes' ) => '1', __( 'No', 'dawnthemes' ) => '0' ) ) ) ) );
			if ( taxonomy_exists( 'product_lookbook' ) ) {
				vc_map( 
					array( 
						"name" => __( "Product Lookbooks", 'dawnthemes' ), 
						"base" => "dt_wc_product_lookbooks", 
						"category" => __( "WooCommerce", 'dawnthemes' ), 
						"icon" => "dt-vc-icon-dt_woo", 
						"class" => "dt-vc-element dt-vc-element-dt_woo", 
						'description' => __( 'List all products by lookbooks.', 'dawnthemes' ), 
						"params" => array( 
							array( 
								"type" => "product_lookbook", 
								"heading" => __( "Lookbooks", 'dawnthemes' ), 
								"param_name" => "ids", 
								"admin_label" => true ), 
							array( 
								"type" => "dropdown", 
								"heading" => __( "Style", 'dawnthemes' ), 
								"param_name" => "style", 
								"std" => 'slider', 
								"admin_label" => true, 
								"value" => array( __( 'Slider', 'dawnthemes' ) => 'slider', __( 'Grid', 'dawnthemes' ) => 'grid' ) ) ) ) );
			}
			if ( taxonomy_exists( 'product_brand' ) ) {
				vc_map( 
					array( 
						"name" => __( "Product Brands", 'dawnthemes' ), 
						"base" => "dtwc_product_brands", 
						"category" => __( "WooCommerce", 'dawnthemes' ), 
						"icon" => "dt-vc-icon-dt_woo", 
						"class" => "dt-vc-element dt-vc-element-dt_woo", 
						'description' => __( 'List all (or limited) product brands.', 'dawnthemes' ), 
						"params" => array( 
							array( 
								"type" => "product_brand", 
								"heading" => __( "Brands", 'dawnthemes' ), 
								"param_name" => "ids", 
								"admin_label" => true ), 
							array( 
								"type" => "textfield", 
								"heading" => __( "Number", 'dawnthemes' ), 
								"param_name" => "number", 
								"admin_label" => true, 
								'description' => __( 
									'You can specify the number of brand to show (Leave blank to display all brands).', 
									'dawnthemes' ) ), 
							array( 
								"type" => "dropdown", 
								"heading" => __( "Columns", 'dawnthemes' ), 
								"param_name" => "columns", 
								"std" => 4, 
								"admin_label" => true, 
								"value" => array( 2, 3, 4, 5, 6 ) ), 
							array( 
								"type" => "dropdown", 
								"heading" => __( "Products Ordering", 'dawnthemes' ), 
								"param_name" => "orderby", 
								'std' => 'date', 
								"value" => array( 
									__( 'Publish Date', 'dawnthemes' ) => 'date', 
									__( 'Modified Date', 'dawnthemes' ) => 'modified', 
									__( 'Random', 'dawnthemes' ) => 'rand', 
									__( 'Alphabetic', 'dawnthemes' ) => 'title', 
									__( 'Popularity', 'dawnthemes' ) => 'popularity', 
									__( 'Rate', 'dawnthemes' ) => 'rating', 
									__( 'Price', 'dawnthemes' ) => 'price' ) ), 
							array( 
								"type" => "dropdown", 
								"class" => "", 
								'std' => 'ASC', 
								"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
								"param_name" => "order", 
								"value" => array( 
									__( 'Ascending', 'dawnthemes' ) => 'ASC', 
									__( 'Descending', 'dawnthemes' ) => 'DESC' ) ), 
							array( 
								"type" => "dropdown", 
								"class" => "", 
								'std' => '1', 
								"heading" => __( "Hide Empty", 'dawnthemes' ), 
								"param_name" => "hide_empty", 
								"value" => array( __( 'Yes', 'dawnthemes' ) => '1', __( 'No', 'dawnthemes' ) => '0' ) ) ) ) );
			}
			vc_map( 
				array( 
					"name" => __( "Add To Cart", 'dawnthemes' ), 
					"base" => "add_to_cart", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Display a single product price + cart button.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select product", 'dawnthemes' ), 
							'single_select' => true, 
							'admin_label' => true, 
							"param_name" => "id" ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Add To Cart URL", 'dawnthemes' ), 
					"base" => "add_to_cart_url", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Show URL on the add to cart button.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select product", 'dawnthemes' ), 
							'single_select' => true, 
							'admin_label' => true, 
							"param_name" => "id" ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Add To Cart URL", 'dawnthemes' ), 
					"base" => "add_to_cart", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Get the add to cart URL for a product.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select product", 'dawnthemes' ), 
							'single_select' => true, 
							'admin_label' => true, 
							"param_name" => "id" ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "Products", 'dawnthemes' ), 
					"base" => "products", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List multiple products shortcode.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select products", 'dawnthemes' ), 
							"param_name" => "ids", 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Products Grid", 'dawnthemes' ), 
					"base" => "dt_wc_products_grid", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List multiple products grid shortcode.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textarea_html', 
							'holder' => 'div', 
							'heading' => __( 'Text Description', 'js_composer' ), 
							'param_name' => 'content', 
							'value' => __( 
								'<p>I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>', 
								'js_composer' ) ), 
						array( 
							"type" => "attach_image", 
							"heading" => __( "Image Description", 'dawnthemes' ), 
							"param_name" => "image_desc", 
							"value" => "", 
							"description" => __( "Select image from media library.", 'dawnthemes' ) ), 
						array( 
							"type" => "products_ajax", 
							"heading" => __( "Select products", 'dawnthemes' ), 
							"param_name" => "ids", 
							"admin_label" => true ), 
						array(
							"type" => "textfield",
							"heading" => __( "Product Per Page", 'dawnthemes' ),
							"param_name" => "per_page",
							"admin_label" => true,
							"value" => 12 ),
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Recent Products", 'dawnthemes' ), 
					"base" => "recent_products", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Recent Products shortcode.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"admin_label" => true, 
							"value" => 12 ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "Sale Products", 'dawnthemes' ), 
					"base" => "sale_products", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List all products on sale.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"value" => 12, 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "Best Selling Products", 'dawnthemes' ), 
					"base" => "best_selling_products", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List best selling products on sale.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"value" => 12, 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "Top Rated Products", 'dawnthemes' ), 
					"base" => "top_rated_products", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List top rated products on sale.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"value" => 12, 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "Featured Products", 'dawnthemes' ), 
					"base" => "featured_products", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Output featured products.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"value" => 12, 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							"class" => "", 
							'std' => 'ASC', 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "Product Attribute", 'dawnthemes' ), 
					"base" => "product_attribute", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'List products with an attribute shortcode.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "product_attribute", 
							"heading" => __( "Attribute", 'dawnthemes' ), 
							"param_name" => "attribute", 
							"admin_label" => true ), 
						array( 
							"type" => "product_attribute_filter", 
							"heading" => __( "Filter", 'dawnthemes' ), 
							"param_name" => "filter", 
							"admin_label" => true ), 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "per_page", 
							"value" => 12, 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							'std' => 'date', 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ), 
						array( 
							"type" => "dropdown", 
							'std' => 'ASC', 
							"class" => "", 
							"heading" => __( "Ascending or Descending", 'dawnthemes' ), 
							"param_name" => "order", 
							"value" => array( 
								__( 'Ascending', 'dawnthemes' ) => 'ASC', 
								__( 'Descending', 'dawnthemes' ) => 'DESC' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Related products", 'dawnthemes' ), 
					"base" => "related_products", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Output the related products.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "textfield", 
							"heading" => __( "Product Per Page", 'dawnthemes' ), 
							"param_name" => "posts_per_page", 
							"value" => 12, 
							"admin_label" => true ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Columns", 'dawnthemes' ), 
							"param_name" => "columns", 
							"std" => 4, 
							"admin_label" => true, 
							"value" => array( '', 1, 2, 3, 4, 5, 6 ) ), 
						array( 
							"type" => "dropdown", 
							"heading" => __( "Products Ordering", 'dawnthemes' ), 
							"param_name" => "orderby", 
							"std" => "rand", 
							"value" => array( 
								__( 'Publish Date', 'dawnthemes' ) => 'date', 
								__( 'Modified Date', 'dawnthemes' ) => 'modified', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Alphabetic', 'dawnthemes' ) => 'title', 
								__( 'Popularity', 'dawnthemes' ) => 'popularity', 
								__( 'Rate', 'dawnthemes' ) => 'rating', 
								__( 'Price', 'dawnthemes' ) => 'price' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Shop Messages", 'dawnthemes' ), 
					"base" => "shop_messages", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'show_settings_on_create' => false, 
					'description' => __( 'Show messages.', 'dawnthemes' ), 
					"params" => array( array( 'type' => 'nullfield', 'param_name' => 'nullfield' ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Order Tracking", 'dawnthemes' ), 
					"base" => "woocommerce_order_tracking", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'show_settings_on_create' => false, 
					'description' => __( 'Order tracking page shortcode.', 'dawnthemes' ), 
					"params" => array( array( 'type' => 'nullfield', 'param_name' => 'nullfield' ) ) ) );
			vc_map( 
				array( 
					"name" => __( "Cart", 'dawnthemes' ), 
					"base" => "woocommerce_cart", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'show_settings_on_create' => false, 
					'description' => __( 'Cart page shortcode.', 'dawnthemes' ), 
					"params" => array( array( 'type' => 'nullfield', 'param_name' => 'nullfield' ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "Checkout", 'dawnthemes' ), 
					"base" => "woocommerce_checkout", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'show_settings_on_create' => false, 
					'description' => __( 'Checkout page shortcode.', 'dawnthemes' ), 
					"params" => array( array( 'type' => 'nullfield', 'param_name' => 'nullfield' ) ) ) );
			vc_map( 
				array( 
					"name" => __( "My Account", 'dawnthemes' ), 
					"base" => "woocommerce_my_account", 
					"category" => __( "WooCommerce", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'My account shortcode.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							"type" => "textfield", 
							"heading" => __( "Number of orders", 'dawnthemes' ), 
							"param_name" => "order_count", 
							"admin_label" => true, 
							"value" => 12, 
							'description' => __( 
								'You can specify the number of orders to show (use -1 to display all orders).', 
								'dawnthemes' ) ) ) ) );
			
			// Woocommerce Widgets
			vc_map( 
				array( 
					"name" => __( "WC Cart", 'dawnthemes' ), 
					"base" => "dt_wc_cart", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Cart.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'param_name' => 'hide_if_empty', 
							'heading' => __( 'Hide if cart is empty', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Layered Nav Filters", 'dawnthemes' ), 
					"base" => "dt_wc_layered_nav_filters", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Layered Nav Filters.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Active Filters', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			
			$attribute_array = array();
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( $attribute_taxonomies )
				foreach ( $attribute_taxonomies as $tax )
					if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) )
						$attribute_array[$tax->attribute_name] = $tax->attribute_name;
			
			vc_map( 
				array( 
					"name" => __( "WC Layered Nav", 'dawnthemes' ), 
					"base" => "dt_wc_layered_nav", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Layered Nav.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Filter by', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'attribute', 
							'heading' => __( 'Attribute', 'dawnthemes' ), 
							'value' => $attribute_array ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'display_type', 
							'std' => 'list', 
							'heading' => __( 'Display type', 'dawnthemes' ), 
							'value' => array( __( 'List', 'dawnthemes' ) => 'list', __( 'Dropdown', 'dawnthemes' ) => 'dropdown' ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'query_type', 
							'heading' => __( 'Query type', 'dawnthemes' ), 
							'std' => 'and', 
							'value' => array( __( 'AND', 'dawnthemes' ) => 'and', __( 'OR', 'dawnthemes' ) => 'or' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Price Filter", 'dawnthemes' ), 
					"base" => "dt_wc_price_filter", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Price Filter.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Filter by price', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Product Categories", 'dawnthemes' ), 
					"base" => "dt_wc_product_categories", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Product Categories.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Product Categories', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'orderby', 
							'heading' => __( 'Order by', 'dawnthemes' ), 
							'std' => 'order', 
							'value' => array( __( 'Category Order', 'dawnthemes' ) => 'order', __( 'Name', 'dawnthemes' ) => 'name' ) ), 
						array( 
							'param_name' => 'dropdown', 
							'heading' => __( 'Show as dropdown', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'param_name' => 'count', 
							'heading' => __( 'Show post counts', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'param_name' => 'hierarchical', 
							'heading' => __( 'Show hierarchy', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'std' => '1', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'param_name' => 'show_children_only', 
							'heading' => __( 'Only show children of the current category', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			
			vc_map( 
				array( 
					"name" => __( "WC Product Search", 'dawnthemes' ), 
					"base" => "dt_wc_product_search", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Product Search.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Search Products', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Product Tags", 'dawnthemes' ), 
					"base" => "dt_wc_product_tag_cloud", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Product Tags.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Product Tags', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Products", 'dawnthemes' ), 
					"base" => "dt_wc_products", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Products.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Products', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'value' => 5, 
							'heading' => __( 'Number of products to show', 'dawnthemes' ), 
							'param_name' => 'number' ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'show', 
							'heading' => __( 'Show', 'dawnthemes' ), 
							'value' => array( 
								__( 'All Products', 'dawnthemes' ) => '', 
								__( 'Featured Products', 'dawnthemes' ) => 'featured', 
								__( 'On-sale Products', 'dawnthemes' ) => 'onsale' ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'orderby', 
							'std' => 'date', 
							'heading' => __( 'Order by', 'dawnthemes' ), 
							'value' => array( 
								__( 'Date', 'dawnthemes' ) => 'date', 
								__( 'Price', 'dawnthemes' ) => 'price', 
								__( 'Random', 'dawnthemes' ) => 'rand', 
								__( 'Sales', 'dawnthemes' ) => 'sales' ) ), 
						array( 
							'type' => 'dropdown', 
							'param_name' => 'order', 
							'std' => 'asc', 
							'heading' => _x( 'Order', 'Sorting order', 'dawnthemes' ), 
							'value' => array( __( 'ASC', 'dawnthemes' ) => 'asc', __( 'DESC', 'dawnthemes' ) => 'desc' ) ), 
						array( 
							'param_name' => 'hide_free', 
							'heading' => __( 'Hide free products', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'param_name' => 'show_hidden', 
							'heading' => __( 'Show hidden products', 'dawnthemes' ), 
							'type' => 'checkbox', 
							'value' => array( __( 'Yes,please', 'dawnthemes' ) => '1' ) ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Recent Reviews", 'dawnthemes' ), 
					"base" => "dt_wc_recent_reviews", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Recent Reviews.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Recent Reviews', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'value' => 5, 
							'heading' => __( 'Number of products to show', 'dawnthemes' ), 
							'param_name' => 'number' ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Recently Viewed", 'dawnthemes' ), 
					"base" => "dt_wc_recently_viewed_products", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Recently Viewed.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Recently Viewed Products', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'value' => 5, 
							'heading' => __( 'Number of products to show', 'dawnthemes' ), 
							'param_name' => 'number' ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
			vc_map( 
				array( 
					"name" => __( "WC Top Rated Products", 'dawnthemes' ), 
					"base" => "dt_wc_top_rated_products", 
					"category" => __( "Woocommerce Widgets", 'dawnthemes' ), 
					"icon" => "dt-vc-icon-dt_woo", 
					"class" => "dt-vc-element dt-vc-element-dt_woo", 
					'description' => __( 'Woocommerce Widget Top Rated Products.', 'dawnthemes' ), 
					"params" => array( 
						array( 
							'type' => 'textfield', 
							'value' => __( 'Top Rated Products', 'dawnthemes' ), 
							'heading' => __( 'Widget title', 'dawnthemes' ), 
							'param_name' => 'title', 
							'description' => __( 
								'What text use as a widget title. Leave blank to use default widget title.', 
								'dawnthemes' ) ), 
						array( 
							'type' => 'textfield', 
							'value' => 5, 
							'heading' => __( 'Number of products to show', 'dawnthemes' ), 
							'param_name' => 'number' ), 
						array( 
							'type' => 'textfield', 
							'heading' => __( 'Extra class name', 'dawnthemes' ), 
							'param_name' => 'el_class', 
							'description' => __( 
								'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 
								'dawnthemes' ) ) ) ) );
		}

		public function add_params() {
			vc_add_param( 
				"vc_row", 
				array( 
					"type" => "dropdown", 
					"group" => __( 'Row Type', 'dawnthemes' ), 
					"class" => "", 
					"heading" => "Type", 
					'std' => 'full_width', 
					"param_name" => "wrap_type", 
					"value" => array( 
						__( "Full Width", 'dawnthemes' ) => "full_width", 
						__( "In Container", 'dawnthemes' ) => "in_container" ) ) );
			
			vc_add_param( 
				"vc_row_inner", 
				array( 
					"type" => "dropdown", 
					"group" => __( 'Row Type', 'dawnthemes' ), 
					"class" => "", 
					"heading" => "Type", 
					"param_name" => "wrap_type", 
					'std' => 'full_width', 
					"value" => array( 
						__( "Full Width", 'dawnthemes' ) => "full_width", 
						__( "In Container", 'dawnthemes' ) => "in_container" ) ) );
			
			$params = array( 
				'dt_instagram' => array( 
					array( 
						'param_name' => 'username', 
						'heading' => __( 'Instagram Username', 'dawnthemes' ), 
						'description' => '', 
						'type' => 'textfield', 
						'admin_label' => true ), 
					array( 
						'param_name' => 'images_number', 
						'heading' => __( 'Number of Images to Show', 'dawnthemes' ), 
						'type' => 'textfield', 
						'value' => '12' ), 
					array( 
						'param_name' => 'refresh_hour', 
						'heading' => __( 'Check for new images on every (hours)', 'dawnthemes' ), 
						'type' => 'textfield', 
						'value' => '5' ) ), 
				'dt_button' => array( 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Text', 'dawnthemes' ), 
						'holder' => 'button', 
						'class' => 'wpb_button', 
						'admin_label' => true, 
						'param_name' => 'title', 
						'value' => __( 'Button', 'dawnthemes' ), 
						'description' => __( 'Text on the button.', 'dawnthemes' ) ), 
					array( 
						'type' => 'href', 
						'heading' => __( 'URL (Link)', 'dawnthemes' ), 
						'param_name' => 'href', 
						'description' => __( 'Button link.', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Target', 'dawnthemes' ), 
						'param_name' => 'target', 
						'std' => '_self', 
						'value' => array( 
							__( 'Same window', 'dawnthemes' ) => '_self', 
							__( 'New window', 'dawnthemes' ) => "_blank" ), 
						'dependency' => array( 
							'element' => 'href', 
							'not_empty' => true, 
							'callback' => 'vc_button_param_target_callback' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Style', 'dawnthemes' ), 
						"param_holder_class" => 'dt-btn-style-select', 
						'param_name' => 'style', 
						'value' => self::$button_styles, 
						'description' => __( 'Button style.', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Size', 'dawnthemes' ), 
						'param_name' => 'size', 
						'std' => '', 
						'value' => array( 
							__( 'Default', 'dawnthemes' ) => '', 
							__( 'Large', 'dawnthemes' ) => 'lg', 
							__( 'Small', 'dawnthemes' ) => 'sm', 
							__( 'Extra small', 'dawnthemes' ) => 'xs', 
							__( 'Custom size', 'dawnthemes' ) => 'custom' ), 
						'description' => __( 'Button size.', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'font_size', 
						'heading' => __( 'Font Size (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '14', 
						'data_min' => '0', 
						'dependency' => array( 'element' => "size", 'value' => array( 'custom' ) ), 
						'data_max' => '50' ), 
					array( 
						'param_name' => 'border_width', 
						'heading' => __( 'Border Width (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '1', 
						'data_min' => '0', 
						'dependency' => array( 'element' => "size", 'value' => array( 'custom' ) ), 
						'data_max' => '20' ), 
					array( 
						'param_name' => 'padding_top', 
						'heading' => __( 'Padding Top (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '6', 
						'data_min' => '0', 
						'dependency' => array( 'element' => "size", 'value' => array( 'custom' ) ), 
						'data_max' => '100' ), 
					array( 
						'param_name' => 'padding_right', 
						'heading' => __( 'Padding Right (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '30', 
						'data_min' => '0', 
						'dependency' => array( 'element' => "size", 'value' => array( 'custom' ) ), 
						'data_max' => '100' ), 
					array( 
						'param_name' => 'padding_bottom', 
						'heading' => __( 'Padding Bottom (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '6', 
						'data_min' => '0', 
						'dependency' => array( 'element' => "size", 'value' => array( 'custom' ) ), 
						'data_max' => '100' ), 
					array( 
						'param_name' => 'padding_left', 
						'heading' => __( 'Padding Right (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '30', 
						'data_min' => '0', 
						'dependency' => array( 'element' => "size", 'value' => array( 'custom' ) ), 
						'data_max' => '100' ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Color', 'dawnthemes' ), 
						'param_name' => 'color', 
						'std' => 'default', 
						'value' => array( 
							__( 'Default', 'dawnthemes' ) => 'default', 
							__( 'Primary', 'dawnthemes' ) => 'primary', 
							__( 'Success', 'dawnthemes' ) => 'success', 
							__( 'Info', 'dawnthemes' ) => 'info', 
							__( 'Warning', 'dawnthemes' ) => 'warning', 
							__( 'Danger', 'dawnthemes' ) => 'danger', 
							__( 'White', 'dawnthemes' ) => 'white', 
							__( 'Black', 'dawnthemes' ) => 'black', 
							__( 'Custom', 'dawnthemes' ) => 'custom' ), 
						'description' => __( 'Button color.', 'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Background Color', 'dawnthemes' ), 
						'param_name' => 'background_color', 
						'dependency' => array( 'element' => "color", 'value' => array( 'custom' ) ), 
						'description' => __( 'Select background color for button.', 'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Border Color', 'dawnthemes' ), 
						'param_name' => 'border_color', 
						'dependency' => array( 'element' => "color", 'value' => array( 'custom' ) ), 
						'description' => __( 'Select border color for button.', 'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Text Color', 'dawnthemes' ), 
						'param_name' => 'text_color', 
						'dependency' => array( 'element' => "color", 'value' => array( 'custom' ) ), 
						'description' => __( 'Select text color for button.', 'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Hover Background Color', 'dawnthemes' ), 
						'param_name' => 'hover_background_color', 
						'dependency' => array( 'element' => "color", 'value' => array( 'custom' ) ), 
						'description' => __( 'Select background color for button when hover.', 'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Hover Border Color', 'dawnthemes' ), 
						'param_name' => 'hover_border_color', 
						'dependency' => array( 'element' => "color", 'value' => array( 'custom' ) ), 
						'description' => __( 'Select border color for button when hover.', 'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Hover Text Color', 'dawnthemes' ), 
						'param_name' => 'hover_text_color', 
						'dependency' => array( 'element' => "color", 'value' => array( 'custom' ) ), 
						'description' => __( 'Select text color for button when hover.', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Button Full Width', 'dawnthemes' ), 
						'param_name' => 'block_button', 
						'value' => array( __( 'Yes, please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Button full width of a parent', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Alignment', 'dawnthemes' ), 
						'param_name' => 'alignment', 
						'std' => 'left', 
						'value' => array( 
							__( 'Left', 'dawnthemes' ) => 'left', 
							__( 'Center', 'dawnthemes' ) => 'center', 
							__( 'Right', 'dawnthemes' ) => 'right' ), 
						'description' => __( 'Button alignment (Not use for Button full width)', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Show Tooltip/Popover', 'dawnthemes' ), 
						'param_name' => 'tooltip', 
						'value' => array( 
							__( 'No', 'dawnthemes' ) => '', 
							__( 'Tooltip', 'dawnthemes' ) => 'tooltip', 
							__( 'Popover', 'dawnthemes' ) => 'popover' ), 
						'description' => __( 'Display a tooltip or popover with descriptive text.', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Tip position', 'dawnthemes' ), 
						'param_name' => 'tooltip_position', 
						'std' => 'top', 
						'value' => array( 
							__( 'Top', 'dawnthemes' ) => 'top', 
							__( 'Bottom', 'dawnthemes' ) => 'bottom', 
							__( 'Left', 'dawnthemes' ) => 'left', 
							__( 'Right', 'dawnthemes' ) => 'right' ), 
						'dependency' => array( 'element' => "tooltip", 'value' => array( 'tooltip', 'popover' ) ), 
						'description' => __( 'Choose the display position.', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Popover Title', 'dawnthemes' ), 
						'param_name' => 'tooltip_title', 
						'dependency' => array( 'element' => "tooltip", 'value' => array( 'popover' ) ) ), 
					array( 
						'type' => 'textarea', 
						'heading' => __( 'Tip/Popover Content', 'dawnthemes' ), 
						'param_name' => 'tooltip_content', 
						'dependency' => array( 'element' => "tooltip", 'value' => array( 'tooltip', 'popover' ) ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Tip/Popover trigger', 'dawnthemes' ), 
						'param_name' => 'tooltip_trigger', 
						'std' => 'hover', 
						'value' => array( __( 'Hover', 'dawnthemes' ) => 'hover', __( 'Click', 'dawnthemes' ) => 'click' ), 
						'dependency' => array( 'element' => "tooltip", 'value' => array( 'tooltip', 'popover' ) ), 
						'description' => __( 'Choose action to trigger the tooltip.', 'dawnthemes' ) ) ), 
				'dt_menu_anchor' => array( 
					array( 
						'param_name' => 'name', 
						'heading' => __( 'Name Of Menu Anchor', 'dawnthemes' ), 
						'type' => 'textfield', 
						'admin_label' => true, 
						"description" => __( 
							"This name will be the id you will have to use in your one page menu.", 
							'dawnthemes' ) ) ), 
				'dt_video' => array( 
					array( 
						'param_name' => 'type', 
						'heading' => __( 'Video Type', 'dawnthemes' ), 
						'type' => 'dropdown', 
						'admin_label' => true, 
						'std' => 'inline', 
						'value' => array( __( 'Iniline', 'dawnthemes' ) => 'inline', __( 'Popup', 'dawnthemes' ) => 'popup' ) ), 
					array( 
						'type' => 'attach_image', 
						'heading' => __( 'Background', 'dawnthemes' ), 
						'param_name' => 'background', 
						'dependency' => array( 'element' => "type", 'value' => array( 'popup' ) ), 
						'description' => __( 'Video Background.', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'video_embed', 
						'heading' => __( 'Embedded Code', 'dawnthemes' ), 
						'type' => 'textfield', 
						'value' => '', 
						'description' => __( 
							'Used when you select Video format. Enter a Youtube, Vimeo, Soundcloud, etc URL. See supported services at <a href="http://codex.wordpress.org/Embeds" target="_blank">http://codex.wordpress.org/Embeds</a>.', 
							'dawnthemes' ) ) ), 
				'dt_post' => array( 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Layout', 'dawnthemes' ), 
						'param_name' => 'layout', 
						'std' => 'default', 
						'admin_label' => true, 
						'value' => array( 
							__( 'Default', 'dawnthemes' ) => 'default', 
							__( 'Masonry', 'dawnthemes' ) => 'masonry', 
							__( 'Center', 'dawnthemes' ) => 'center' ), 
						'std' => 'default', 
						'description' => __( 'Select the layout for the blog shortcode.', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Columns', 'dawnthemes' ), 
						'param_name' => 'columns', 
						'std' => 2, 
						'value' => array( 
							__( '2', 'dawnthemes' ) => '2', 
							__( '3', 'dawnthemes' ) => '3', 
							__( '4', 'dawnthemes' ) => '4' ), 
						'dependency' => array( 'element' => "layout", 'value' => array( 'grid', 'masonry' ) ), 
						'description' => __( 'Select whether to display the layout in 2, 3 or 4 column.', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Posts Per Page', 'dawnthemes' ), 
						'param_name' => 'posts_per_page', 
						'value' => 5, 
						'description' => __( 'Select number of posts per page.Set "-1" to display all', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Order by', 'dawnthemes' ), 
						'param_name' => 'orderby', 
						'std' => 'latest', 
						'value' => array( 
							__( 'Recent First', 'dawnthemes' ) => 'latest', 
							__( 'Older First', 'dawnthemes' ) => 'oldest', 
							__( 'Title Alphabet', 'dawnthemes' ) => 'alphabet', 
							__( 'Title Reversed Alphabet', 'dawnthemes' ) => 'ralphabet' ) ), 
					array( 
						'type' => 'post_category', 
						'heading' => __( 'Categories', 'dawnthemes' ), 
						'param_name' => 'categories', 
						'admin_label' => true, 
						'description' => __( 'Select a category or leave blank for all', 'dawnthemes' ) ), 
					array( 
						'type' => 'post_category', 
						'heading' => __( 'Exclude Categories', 'dawnthemes' ), 
						'param_name' => 'exclude_categories', 
						'description' => __( 'Select a category to exclude', 'dawnthemes' ) ), 
					
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Post Title', 'dawnthemes' ), 
						'param_name' => 'hide_post_title', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide the post title below the featured', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Link Title To Post', 'dawnthemes' ), 
						'param_name' => 'link_post_title', 
						'std' => 'yes', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes', __( 'No', 'dawnthemes' ) => 'no' ), 
						'description' => __( 
							'Choose if the title should be a link to the single post page.', 
							'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Thumbnail', 'dawnthemes' ), 
						'param_name' => 'hide_thumbnail', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide the post featured', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Excerpt', 'dawnthemes' ), 
						'param_name' => 'hide_excerpt', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'dependency' => array( 
							'element' => "layout", 
							'value' => array( 'default', 'medium', 'grid', 'masonry', 'zigzag', 'center' ) ), 
						'description' => __( 'Hide excerpt', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Number of words in Excerpt', 'dawnthemes' ), 
						'param_name' => 'excerpt_length', 
						'value' => 30, 
						'dependency' => array( 'element' => 'hide_excerpt', 'is_empty' => true ), 
						'description' => __( 'The number of words', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Date', 'dawnthemes' ), 
						'param_name' => 'hide_date', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide date in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Timeline Month', 'dawnthemes' ), 
						'param_name' => 'hide_month', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'dependency' => array( 'element' => "layout", 'value' => array( 'timeline' ) ), 
						'description' => __( 'Hide timeline month', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Comment', 'dawnthemes' ), 
						'param_name' => 'hide_comment', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide comment in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Category', 'dawnthemes' ), 
						'param_name' => 'hide_category', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide category in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Author', 'dawnthemes' ), 
						'param_name' => 'hide_author', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'dependency' => array( 
							'element' => "layout", 
							'value' => array( 'default', 'medium', 'grid', 'masonry', 'zigzag', 'center' ) ), 
						'description' => __( 'Hide author in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Read More Link', 'dawnthemes' ), 
						'param_name' => 'hide_readmore', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'dependency' => array( 
							'element' => "layout", 
							'value' => array( 'default', 'medium', 'grid', 'masonry', 'zigzag', 'center' ) ), 
						'description' => __( 'Choose to hide the link', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Show Tags', 'dawnthemes' ), 
						'param_name' => 'show_tag', 
						'std' => 'no', 
						'value' => array( __( 'No', 'dawnthemes' ) => 'no', __( 'Yes', 'dawnthemes' ) => 'yes' ), 
						'dependency' => array( 
							'element' => "layout", 
							'value' => array( 'default', 'medium', 'grid', 'masonry', 'zigzag', 'center' ) ), 
						'description' => __( 'Choose to show the tags', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'std' => 'page_num', 
						'heading' => __( 'Pagination', 'dawnthemes' ), 
						'param_name' => 'pagination', 
						'value' => array( 
							__( 'Page Number', 'dawnthemes' ) => 'page_num', 
							__( 'Load More Button', 'dawnthemes' ) => 'loadmore', 
							__( 'Infinite Scrolling', 'dawnthemes' ) => 'infinite_scroll', 
							__( 'No', 'dawnthemes' ) => 'no' ), 
						'description' => __( 'Choose pagination type.', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Load More Button Text', 'dawnthemes' ), 
						'param_name' => 'loadmore_text', 
						'dependency' => array( 'element' => "pagination", 'value' => array( 'loadmore' ) ), 
						'value' => __( 'Load More', 'dawnthemes' ) ) ), 
				'dt_post_grid' => array( 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Layout Style', 'dawnthemes' ), 
						'param_name' => 'layout_style', 
						'std' => 'list', 
						'value' => array( __( 'List', 'dawnthemes' ) => 'list', __( 'Grid', 'dawnthemes' ) => 'grid' ), 
						'description' => __( 'Select style to display the latest posts.', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Columns', 'dawnthemes' ), 
						'param_name' => 'columns', 
						'std' => 2, 
						'value' => array( 
							__( '2', 'dawnthemes' ) => '2', 
							__( '3', 'dawnthemes' ) => '3', 
							__( '4', 'dawnthemes' ) => '4' ), 
						'dependency' => array( 'element' => "layout_style", 'value' => array( 'grid' ) ), 
						'description' => __( 'Select whether to display the layout in 1, 2, 3 or 4 column.', 'dawnthemes' ) ), 
					
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Posts Per Page', 'dawnthemes' ), 
						'param_name' => 'posts_per_page', 
						'value' => 12, 
						'description' => __( 'Select number of posts per page.Set "-1" to display all', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Order by', 'dawnthemes' ), 
						'param_name' => 'orderby', 
						'value' => array( 
							__( 'Recent First', 'dawnthemes' ) => 'latest', 
							__( 'Older First', 'dawnthemes' ) => 'oldest', 
							__( 'Title Alphabet', 'dawnthemes' ) => 'alphabet', 
							__( 'Title Reversed Alphabet', 'dawnthemes' ) => 'ralphabet' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Pagination', 'dawnthemes' ), 
						'param_name' => 'hide_pagination', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide pagination of slider', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Date', 'dawnthemes' ), 
						'param_name' => 'hide_date', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide date in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Author', 'dawnthemes' ), 
						'param_name' => 'hide_author', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide author in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Comment', 'dawnthemes' ), 
						'param_name' => 'hide_comment', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide comment in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Category', 'dawnthemes' ), 
						'param_name' => 'hide_category', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide Category in post meta info', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Excerpt', 'dawnthemes' ), 
						'param_name' => 'hide_excerpt', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ), 
						'description' => __( 'Hide excerpt', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Number of words in Excerpt', 'dawnthemes' ), 
						'param_name' => 'excerpt_length', 
						'value' => 30, 
						'dependency' => array( 'element' => 'hide_excerpt', 'is_empty' => true ), 
						'description' => __( 'The number of words', 'dawnthemes' ) ), 
					array( 
						'type' => 'post_category', 
						'heading' => __( 'Categories', 'dawnthemes' ), 
						'param_name' => 'categories', 
						'admin_label' => true, 
						'description' => __( 'Select a category or leave blank for all', 'dawnthemes' ) ) ), 
				'dt_mailchimp' => array( 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Title', 'dawnthemes' ), 
						'param_name' => 'title', 
						'description' => __( 
							'Enter text which will be used as widget title. Leave blank if no title is needed.', 
							'dawnthemes' ) ) ), 
				'dt_slider' => array(), 
				'dt_carousel' => array( 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Carousel Title', 'dawnthemes' ), 
						'param_name' => 'title', 
						'description' => __( 
							'Enter text which will be used as widget title. Leave blank if no title is needed.', 
							'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Transition', 'dawnthemes' ), 
						'param_name' => 'fx', 
						'std' => 'scroll', 
						'value' => array( 
							'Scroll' => 'scroll', 
							'Directscroll' => 'directscroll', 
							'Fade' => 'fade', 
							'Cross fade' => 'crossfade', 
							'Cover' => 'cover', 
							'Cover fade' => 'cover-fade', 
							'Uncover' => 'cover-fade', 
							'Uncover fade' => 'uncover-fade' ), 
						'description' => __( 'Indicates which effect to use for the transition.', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'visible', 
						'heading' => __( 'The number of visible items', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '1', 
						'data_min' => '1', 
						'data_max' => '6' ), 
					array( 
						'param_name' => 'scroll_speed', 
						'heading' => __( 'Transition Scroll Speed (ms)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '700', 
						'data_min' => '100', 
						'data_step' => '100', 
						'data_max' => '3000' ), 
					
					array( 
						"type" => "dropdown", 
						"heading" => __( "Easing", 'dawnthemes' ), 
						"param_name" => "easing", 
						'std' => 'linear', 
						"value" => array( 
							'linear' => 'linear', 
							'swing' => 'swing', 
							'easeInQuad' => 'easeInQuad', 
							'easeOutQuad' => 'easeOutQuad', 
							'easeInOutQuad' => 'easeInOutQuad', 
							'easeInCubic' => 'easeInCubic', 
							'easeOutCubic' => 'easeOutCubic', 
							'easeInOutCubic' => 'easeInOutCubic', 
							'easeInQuart' => 'easeInQuart', 
							'easeOutQuart' => 'easeOutQuart', 
							'easeInOutQuart' => 'easeInOutQuart', 
							'easeInQuint' => 'easeInQuint', 
							'easeOutQuint' => 'easeOutQuint', 
							'easeInOutQuint' => 'easeInOutQuint', 
							'easeInExpo' => 'easeInExpo', 
							'easeOutExpo' => 'easeOutExpo', 
							'easeInOutExpo' => 'easeInOutExpo', 
							'easeInSine' => 'easeInSine', 
							'easeOutSine' => 'easeOutSine', 
							'easeInOutSine' => 'easeInOutSine', 
							'easeInCirc' => 'easeInCirc', 
							'easeOutCirc' => 'easeOutCirc', 
							'easeInOutCirc' => 'easeInOutCirc', 
							'easeInElastic' => 'easeInElastic', 
							'easeOutElastic' => 'easeOutElastic', 
							'easeInOutElastic' => 'easeInOutElastic', 
							'easeInBack' => 'easeInBack', 
							'easeOutBack' => 'easeOutBack', 
							'easeInOutBack' => 'easeInOutBack', 
							'easeInBounce' => 'easeInBounce', 
							'easeOutBounce' => 'easeOutBounce', 
							'easeInOutBounce' => 'easeInOutBounce' ), 
						"description" => __( 
							"Select the animation easing you would like for slide transitions <a href=\"http://jqueryui.com/resources/demos/effect/easing.html\" target=\"_blank\"> Click here </a> to see examples of these.", 
							'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Item no Padding ?', 'dawnthemes' ), 
						'param_name' => 'no_padding', 
						'description' => __( 'Item No Padding', 'dawnthemes' ), 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Autoplay ?', 'dawnthemes' ), 
						'param_name' => 'auto_play', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Slide Pagination ?', 'dawnthemes' ), 
						'param_name' => 'hide_pagination', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Previous/Next Control ?', 'dawnthemes' ), 
						'param_name' => 'hide_control', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ) ), 
				'dt_wc_special_product' => array(), 
				'dt_carousel_item' => array( 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Title', 'dawnthemes' ), 
						'param_name' => 'title', 
						'description' => __( 'Item title.', 'dawnthemes' ) ) ), 
				'dt_testimonial' => array( 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Background Transparent?', 'dawnthemes' ), 
						'param_name' => 'background_transparent', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Color', 'dawnthemes' ), 
						'param_name' => 'color', 
						'description' => __( 'Custom color.', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Columns', 'dawnthemes' ), 
						'param_name' => 'columns', 
						'std' => '1', 
						'value' => array( __( '1 Column', 'dawnthemes' ) => '1', __( '2 Columns', 'dawnthemes' ) => '2' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Style', 'dawnthemes' ), 
						'param_name' => 'style', 
						'std' => 'style-1', 
						'value' => array( 
							__( 'Style 1', 'dawnthemes' ) => 'style-1', 
							__( 'Style 2', 'dawnthemes' ) => 'style-2' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Transition', 'dawnthemes' ), 
						'param_name' => 'fx', 
						'std' => 'scroll', 
						'value' => array( 
							'Scroll' => 'scroll', 
							'Directscroll' => 'directscroll', 
							'Fade' => 'fade', 
							'Cross fade' => 'crossfade', 
							'Cover' => 'cover', 
							'Cover fade' => 'cover-fade', 
							'Uncover' => 'cover-fade', 
							'Uncover fade' => 'uncover-fade' ), 
						'description' => __( 'Indicates which effect to use for the transition.', 'dawnthemes' ) ) ), 
				'dt_testimonial_item' => array( 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Title', 'dawnthemes' ), 
						'param_name' => 'title', 
						'description' => __( 'Item title.', 'dawnthemes' ) ), 
					array( 
						'type' => 'textarea_safe', 
						'holder' => 'div', 
						'heading' => __( 'Text', 'dawnthemes' ), 
						'param_name' => 'text', 
						'value' => __( 
							'I am testimonial. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 
							'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Author', 'dawnthemes' ), 
						'param_name' => 'author', 
						'description' => __( 'Testimonial author.', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Company', 'dawnthemes' ), 
						'param_name' => 'company', 
						'description' => __( 'Author company.', 'dawnthemes' ) ), 
					array( 
						'type' => 'attach_image', 
						'heading' => __( 'Avatar', 'dawnthemes' ), 
						'param_name' => 'avatar', 
						'description' => __( 'Avatar author.', 'dawnthemes' ) ) ), 
				'dt_counter' => array( 
					array( 
						'param_name' => 'speed', 
						'heading' => __( 'Counter Speed', 'dawnthemes' ), 
						'type' => 'textfield', 
						'value' => '2000' ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Number', 'dawnthemes' ), 
						'param_name' => 'number', 
						'description' => __( 'Enter the number.', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Format number displayed ?', 'dawnthemes' ), 
						'dependency' => array( 'element' => "number", 'not_empty' => true ), 
						'param_name' => 'format', 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Thousand Separator', 'dawnthemes' ), 
						'param_name' => 'thousand_sep', 
						'dependency' => array( 'element' => "format", 'not_empty' => true ), 
						'value' => ',', 
						'description' => __( 'This sets the thousand separator of displayed number.', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Decimal Separator', 'dawnthemes' ), 
						'param_name' => 'decimal_sep', 
						'dependency' => array( 'element' => "format", 'not_empty' => true ), 
						'value' => '.', 
						'description' => __( 'This sets the decimal separator of displayed number.', 'dawnthemes' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Number of Decimals', 'dawnthemes' ), 
						'param_name' => 'num_decimals', 
						'dependency' => array( 'element' => "format", 'not_empty' => true ), 
						'value' => 0, 
						'description' => __( 
							'This sets the number of decimal points shown in displayed number.', 
							'dawnthemes' ) ), 
					
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Custom Number Color', 'dawnthemes' ), 
						'param_name' => 'number_color', 
						'dependency' => array( 'element' => "number", 'not_empty' => true ), 
						'description' => __( 'Select color for number.', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'number_font_size', 
						'heading' => __( 'Custom Number Font Size (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '40', 
						'data_min' => '10', 
						'dependency' => array( 'element' => "number", 'not_empty' => true ), 
						'data_max' => '120' ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Units', 'dawnthemes' ), 
						'param_name' => 'units', 
						'description' => __( 
							'Enter measurement units (if needed) Eg. %, px, points, etc. Graph value and unit will be appended to the graph title.', 
							'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Custom Units Color', 'dawnthemes' ), 
						'param_name' => 'units_color', 
						'dependency' => array( 'element' => "units", 'not_empty' => true ), 
						'description' => __( 'Select color for number.', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'units_font_size', 
						'heading' => __( 'Custom Units Font Size (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '30', 
						'data_min' => '10', 
						'dependency' => array( 'element' => "units", 'not_empty' => true ), 
						'data_max' => '120' ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Icon', 'dawnthemes' ), 
						'param_name' => 'icon', 
						"param_holder_class" => 'dt-font-awesome-select', 
						"value" => dt_font_awesome_options(), 
						'description' => __( 'Button icon.', 'dawnthemes' ) ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Custom Icon Color', 'dawnthemes' ), 
						'param_name' => 'icon_color', 
						'dependency' => array( 'element' => "icon", 'not_empty' => true ), 
						'description' => __( 'Select color for icon.', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'icon_font_size', 
						'heading' => __( 'Custom Icon Size (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '40', 
						'data_min' => '10', 
						'dependency' => array( 'element' => "icon", 'not_empty' => true ), 
						'data_max' => '120' ), 
					array( 
						'type' => 'dropdown', 
						'std' => 'top', 
						'heading' => __( 'Icon Postiton', 'dawnthemes' ), 
						'param_name' => 'icon_position', 
						'dependency' => array( 'element' => "icon", 'not_empty' => true ), 
						'value' => array( __( 'Top', 'dawnthemes' ) => 'top', __( 'Left', 'dawnthemes' ) => 'left' ) ), 
					array( 
						'type' => 'textfield', 
						'heading' => __( 'Title', 'dawnthemes' ), 
						'param_name' => 'text', 
						'admin_label' => true ), 
					array( 
						'type' => 'colorpicker', 
						'heading' => __( 'Custom Title Color', 'dawnthemes' ), 
						'param_name' => 'text_color', 
						'dependency' => array( 'element' => "text", 'not_empty' => true ), 
						'description' => __( 'Select color for title.', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'text_font_size', 
						'heading' => __( 'Custom Title Font Size (px)', 'dawnthemes' ), 
						'type' => 'ui_slider', 
						'value' => '18', 
						'data_min' => '10', 
						'dependency' => array( 'element' => "text", 'not_empty' => true ), 
						'data_max' => '120' ) ), 
				'dt_countdown' => array( 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Style', 'dawnthemes' ), 
						'param_name' => 'style', 
						'admin_label' => true, 
						'value' => array( __( 'White', 'dawnthemes' ) => 'white', __( 'Black', 'dawnthemes' ) => 'black' ), 
						'description' => __( 'Select style.', 'dawnthemes' ) ), 
					array( 
						'type' => 'ui_datepicker', 
						'heading' => __( 'Countdown end', 'dawnthemes' ), 
						'param_name' => 'end', 
						'description' => __( 'Please select day to end.', 'dawnthemes' ), 
						'value' => '' ) ), 
				'dt_box_feature' => array( 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Style', 'dawnthemes' ), 
						'param_name' => 'style', 
						'std' => '1', 
						'value' => array( 
							__( 'Style 1', 'dawnthemes' ) => '1', 
							__( 'Style 2', 'dawnthemes' ) => "2", 
							__( 'Style 3', 'dawnthemes' ) => "3", 
							__( 'Style 4', 'dawnthemes' ) => "4", 
							__( 'Style 5', 'dawnthemes' ) => "5" ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Content Position', 'dawnthemes' ), 
						'param_name' => 'content_position', 
						'std' => 'default', 
						'dependency' => array( 'element' => 'style', 'value' => array( '4' ) ), 
						'value' => array( 
							__( 'Default', 'dawnthemes' ) => 'default', 
							__( 'Top', 'dawnthemes' ) => "top", 
							__( 'Bottom', 'dawnthemes' ) => "bottom", 
							__( 'Left', 'dawnthemes' ) => "left", 
							__( 'Right', 'dawnthemes' ) => "right", 
							__( 'Full Box', 'dawnthemes' ) => "full-box" ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Full Box with Primary Soild Background ?', 'dawnthemes' ), 
						'param_name' => 'primary_background', 
						'dependency' => array( 'element' => 'content_position', 'value' => array( 'full-box' ) ), 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Text color', 'dawnthemes' ), 
						'param_name' => 'text_color', 
						'dependency' => array( 'element' => 'style', 'value' => array( '5' ) ), 
						'std' => 'white', 
						'value' => array( __( 'White', 'dawnthemes' ) => "white", __( 'Black', 'dawnthemes' ) => "black" ) ), 
					array( 
						'type' => 'attach_image', 
						'heading' => __( 'Image Background', 'dawnthemes' ), 
						'param_name' => 'bg', 
						'description' => __( 'Image Background.', 'dawnthemes' ) ), 
					array( 
						'type' => 'href', 
						'heading' => __( 'Image URL (Link)', 'dawnthemes' ), 
						'param_name' => 'href', 
						'description' => __( 'Image Link.', 'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Target', 'dawnthemes' ), 
						'param_name' => 'target', 
						'std' => '_self', 
						'value' => array( 
							__( 'Same window', 'dawnthemes' ) => '_self', 
							__( 'New window', 'dawnthemes' ) => "_blank" ), 
						'dependency' => array( 'element' => 'href', 'not_empty' => true ) ), 
					array( 
						'param_name' => 'link_title', 
						'heading' => __( 'Button Text', 'dawnthemes' ), 
						'type' => 'textfield', 
						'value' => '', 
						'dependency' => array( 'element' => 'style', 'value' => array( '4' ) ), 
						'description' => __( 'Button link text', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'title', 
						'heading' => __( 'Title', 'dawnthemes' ), 
						'admin_label' => true, 
						'type' => 'textfield', 
						'value' => '', 
						'description' => __( 'Box Title', 'dawnthemes' ) ), 
					array( 
						'param_name' => 'sub_title', 
						'heading' => __( 'Sub Title', 'dawnthemes' ), 
						'type' => 'textfield', 
						'value' => '', 
						'description' => __( 'Box Sub Title', 'dawnthemes' ) ) ), 
				'dt_client' => array( 
					array( 
						'type' => 'attach_images', 
						'heading' => __( 'Images', 'dawnthemes' ), 
						'param_name' => 'images', 
						'value' => '', 
						'description' => __( 'Select images from media library.', 'dawnthemes' ) ), 
					array( 
						'type' => 'exploded_textarea', 
						'heading' => __( 'Custom links', 'dawnthemes' ), 
						'param_name' => 'custom_links', 
						'description' => __( 
							'Enter links for each image here. Divide links with linebreaks (Enter) . ', 
							'dawnthemes' ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Display type', 'dawnthemes' ), 
						'param_name' => 'display', 
						'value' => array( 
							__( 'Slider', 'dawnthemes' ) => 'slider', 
							__( 'Image grid', 'dawnthemes' ) => 'grid' ), 
						'description' => __( 'Select display type.', 'dawnthemes' ) ), 
					array( 
						'type' => 'checkbox', 
						'heading' => __( 'Hide Slide Pagination ?', 'dawnthemes' ), 
						'param_name' => 'hide_pagination', 
						'dependency' => array( 'element' => 'display', 'value' => array( 'slider' ) ), 
						'value' => array( __( 'Yes,please', 'dawnthemes' ) => 'yes' ) ), 
					array( 
						'param_name' => 'visible', 
						'heading' => __( 'The number of visible items on a slide or on a grid row', 'dawnthemes' ), 
						'type' => 'dropdown', 
						'value' => array( 2, 3, 4, 5, 6 ) ), 
					array( 
						'type' => 'dropdown', 
						'heading' => __( 'Image style', 'dawnthemes' ), 
						'param_name' => 'style', 
						'value' => array( 
							__( 'Normal', 'dawnthemes' ) => 'normal', 
							__( 'Grayscale and Color on hover', 'dawnthemes' ) => 'grayscale' ), 
						'description' => __( 'Select image style.', 'dawnthemes' ) ) ) );
			
			$shortcode_optional_param = array( 
				'dt_wc_special_product', 
				'dt_button', 
				'dt_animation', 
				'dt_post', 
				'dt_post_grid', 
				'dt_instagram', 
				'dt_slider', 
				'dt_carousel', 
				'dt_testimonial', 
				'dt_client', 
				'dt_counter', 
				'dt_countdown' );
			foreach ( $params as $shortcode => $param ) {
				foreach ( $param as $attr ) {
					vc_add_param( $shortcode, $attr );
				}
				if ( in_array( $shortcode, $shortcode_optional_param ) ) {
					foreach ( (array) $this->_get_optional_param() as $optional_param ) {
						vc_add_param( $shortcode, $optional_param );
					}
				}
			}
			
			return;
		}

		public function remove_vc_teaser_meta_box() {
			$post_types = get_post_types( '', 'names' );
			foreach ( $post_types as $post_type ) {
				remove_meta_box( 'vc_teaser', $post_type, 'side' );
			}
			return;
		}

		protected function _get_optional_param() {
			$optional_param = array( 
				array( 
					'param_name' => 'visibility', 
					'heading' => __( 'Visibility', 'dawnthemes' ), 
					'type' => 'dropdown', 
					'std' => 'all', 
					'value' => array( 
						__( 'All Devices', 'dawnthemes' ) => "all", 
						__( 'Hidden Phone', 'dawnthemes' ) => "hidden-phone", 
						__( 'Hidden Tablet', 'dawnthemes' ) => "hidden-tablet", 
						__( 'Hidden PC', 'dawnthemes' ) => "hidden-pc", 
						__( 'Visible Phone', 'dawnthemes' ) => "visible-phone", 
						__( 'Visible Tablet', 'dawnthemes' ) => "visible-tablet", 
						__( 'Visible PC', 'dawnthemes' ) => "visible-pc" ) ), 
				array( 
					'param_name' => 'el_class', 
					'heading' => __( '(Optional) Extra class name', 'dawnthemes' ), 
					'type' => 'textfield', 
					'value' => '', 
					"description" => __( 
						"If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 
						'dawnthemes' ) ) );
			return $optional_param;
		}

		public function pricing_table_feature_param( $settings, $value ) {
			$value_64 = base64_decode( $value );
			$value_arr = json_decode( $value_64 );
			if ( empty( $value_arr ) && ! is_array( $value_arr ) ) {
				for ( $i = 0; $i < 2; $i++ ) {
					$option = new stdClass();
					$option->content = '<i class="fa fa-check"></i> I am a feature';
					$value_arr[] = $option;
				}
			}
			$param_line = '';
			$param_line .= '<div class="pricing-table-feature-list clearfix">';
			$param_line .= '<table>';
			$param_line .= '<thead>';
			$param_line .= '<tr>';
			$param_line .= '<td>';
			$param_line .= __( 'Content (<em>Add Arbitrary text or HTML.</em>)', 'dawnthemes' );
			$param_line .= '</td>';
			$param_line .= '<td>';
			$param_line .= '</td>';
			$param_line .= '</tr>';
			$param_line .= '</thead>';
			$param_line .= '<tbody>';
			if ( is_array( $value_arr ) && ! empty( $value_arr ) ) {
				foreach ( $value_arr as $k => $v ) {
					$param_line .= '<tr>';
					$param_line .= '<td>';
					$param_line .= '<textarea id="content">' . esc_textarea( $v->content ) . '</textarea>';
					$param_line .= '</td>';
					$param_line .= '<td align="left" style="padding:5px;">';
					$param_line .= '<a href="#" class="pricing-table-feature-remove" onclick="return pricing_table_feature_remove(this);"  title="' .
						 __( 'Remove', 'dawnthemes' ) . '">-</a>';
					$param_line .= '</td>';
					$param_line .= '</tr>';
				}
			}
			$param_line .= '</tbody>';
			$param_line .= '<tfoot>';
			$param_line .= '<tr>';
			$param_line .= '<td colspan="3">';
			$param_line .= '<a href="#" onclick="return pricing_table_feature_add(this);" class="button" title="' .
				 __( 'Add', 'dawnthemes' ) . '">' . __( 'Add', 'dawnthemes' ) . '</a>';
			$param_line .= '</td>';
			$param_line .= '</tfoot>';
			$param_line .= '</table>';
			$param_line .= '<input type="hidden" name="' . $settings['param_name'] . '" class="wpb_vc_param_value' .
				 $settings['param_name'] . ' ' . $settings['type'] . '" value="' . $value . '">';
			$param_line .= '</div>';
			return $param_line;
		}

		public function post_category_param( $settings, $value ) {
			$dependency = vc_generate_dependencies_attributes( $settings );
			$categories = get_categories( array( 'orderby' => 'NAME', 'order' => 'ASC' ) );
			
			$class = 'dt-chosen-multiple-select';
			$selected_values = explode( ',', $value );
			$html = array();
			$html[] = '<div class="post_category_param">';
			$html[] = '<select id="' . $settings['param_name'] . '" ' .
				 ( isset( $settings['single_select'] ) ? '' : 'multiple="multiple"' ) . ' class="' . $class . '" ' .
				 $dependency . '>';
			$r = array();
			$r['pad_counts'] = 1;
			$r['hierarchical'] = 1;
			$r['hide_empty'] = 1;
			$r['show_count'] = 0;
			$r['selected'] = $selected_values;
			$r['menu_order'] = false;
			$html[] = dt_walk_category_dropdown_tree( $categories, 0, $r );
			$html[] = '</select>';
			$html[] = '<input id= "' . $settings['param_name'] .
				 '" type="hidden" class="wpb_vc_param_value dt-chosen-value wpb-textinput" name="' .
				 $settings['param_name'] . '" value="' . $value . '" />';
			$html[] = '</div>';
			
			return implode( "\n", $html );
		}

		public function dropdown_group_param( $param, $param_value ) {
			$css_option = vc_get_dropdown_option( $param, $param_value );
			$param_line = '';
			$param_line .= '<select name="' . $param['param_name'] .
				 '" class="dt-chosen-select wpb_vc_param_value wpb-input wpb-select ' . $param['param_name'] . ' ' .
				 $param['type'] . ' ' . $css_option . '" data-option="' . $css_option . '">';
			foreach ( $param['optgroup'] as $text_opt => $opt ) {
				if ( is_array( $opt ) ) {
					$param_line .= '<optgroup label="' . $text_opt . '">';
					foreach ( $opt as $text_val => $val ) {
						if ( is_numeric( $text_val ) && ( is_string( $val ) || is_numeric( $val ) ) ) {
							$text_val = $val;
						}
						$selected = '';
						if ( $param_value !== '' && (string) $val === (string) $param_value ) {
							$selected = ' selected="selected"';
						}
						$param_line .= '<option class="' . $val . '" value="' . $val . '"' . $selected . '>' .
							 htmlspecialchars( $text_val ) . '</option>';
					}
					$param_line .= '</optgroup>';
				} elseif ( is_string( $opt ) ) {
					if ( is_numeric( $text_opt ) && ( is_string( $opt ) || is_numeric( $opt ) ) ) {
						$text_opt = $opt;
					}
					$selected = '';
					if ( $param_value !== '' && (string) $opt === (string) $param_value ) {
						$selected = ' selected="selected"';
					}
					$param_line .= '<option class="' . $opt . '" value="' . $opt . '"' . $selected . '>' .
						 htmlspecialchars( $text_opt ) . '</option>';
				}
			}
			$param_line .= '</select>';
			return $param_line;
		}

		public function nullfield_param( $settings, $value ) {
			return '';
		}

		public function product_attribute_param( $settings, $value ) {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
				return '';
			
			$output = '';
			$attributes = wc_get_attribute_taxonomies();
			$output .= '<select name= "' . $settings['param_name'] . '" data-placeholder="' .
				 __( 'Select Attibute', 'dawnthemes' ) .
				 '" class="dt-product-attribute dt-chosen-select wpb_vc_param_value wpb-input wpb-select ' .
				 $settings['param_name'] . ' ' . $settings['type'] . '">';
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $attr ) :
					if ( taxonomy_exists( wc_attribute_taxonomy_name( $attr->attribute_name ) ) ) {
						if ( $name = wc_attribute_taxonomy_name( $attr->attribute_name ) ) {
							$output .= '<option value="' . esc_attr( $name ) . '"' . selected( $value, $name, false ) .
								 '>' . $attr->attribute_name . '</option>';
						}
					}
				endforeach
				;
			}
			$output .= '</select>';
			return $output;
		}

		public function product_attribute_filter_param( $settings, $value ) {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
				return '';
			
			$output = '';
			$args = array( 'orderby' => 'name', 'hide_empty' => false );
			$filter_ids = explode( ',', $value );
			$attributes = wc_get_attribute_taxonomies();
			$output .= '<select id= "' . $settings['param_name'] . '" multiple="multiple"  data-placeholder="' .
				 __( 'Select Attibute Filter', 'dawnthemes' ) .
				 '" class="dt-product-attribute-filter dt-chosen-multiple-select dt-chosen-select wpb_vc_param_value wpb-input wpb-select ' .
				 $settings['param_name'] . ' ' . $settings['type'] . '">';
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $attr ) :
					if ( taxonomy_exists( wc_attribute_taxonomy_name( $attr->attribute_name ) ) ) {
						if ( $name = wc_attribute_taxonomy_name( $attr->attribute_name ) ) {
							$terms = get_terms( $name, $args );
							if ( ! empty( $terms ) ) {
								foreach ( $terms as $term ) {
									$v = $term->slug;
									$output .= '<option data-attr="' . esc_attr( $name ) . '" value="' . esc_attr( $v ) .
										 '"' . selected( in_array( $v, $filter_ids ), true, false ) . '>' .
										 esc_html( $term->name ) . '</option>';
								}
							}
						}
					}
				endforeach
				;
			}
			$output .= '</select>';
			$output .= '<input id= "' . $settings['param_name'] .
				 '" type="hidden" class="wpb_vc_param_value wpb-textinput" name="' . $settings['param_name'] .
				 '" value="' . $value . '" />';
			return $output;
		}

		public function product_brand_param( $settings, $value ) {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
				return '';
			$output = '';
			$brands_slugs = explode( ',', $value );
			$args = array( 'orderby' => 'name', 'hide_empty' => true );
			$brands = get_terms( 'product_brand', $args );
			$output .= '<select id= "' . $settings['param_name'] . '" multiple="multiple" data-placeholder="' .
				 __( 'Select brands', 'dawnthemes' ) . '" class="dt-chosen-multiple-select dt-chosen-select-nostd ' .
				 $settings['param_name'] . ' ' . $settings['type'] . '">';
			if ( ! empty( $brands ) ) {
				foreach ( $brands as $brand ) :
					$output .= '<option value="' . esc_attr( $brand->term_id ) . '"' .
						 selected( in_array( $brand->term_id, $brands_slugs ), true, false ) . '>' .
						 esc_html( $brand->name ) . '</option>';
				endforeach
				;
			}
			$output .= '</select>';
			$output .= '<input id= "' . $settings['param_name'] .
				 '" type="hidden" class="wpb_vc_param_value wpb-textinput" name="' . $settings['param_name'] .
				 '" value="' . $value . '" />';
			return $output;
		}

		public function product_lookbook_param( $settings, $value ) {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
				return '';
			$output = '';
			$lookbook_slugs = explode( ',', $value );
			$args = array( 'orderby' => 'name', 'hide_empty' => true );
			$lookbooks = get_terms( 'product_lookbook', $args );
			$output .= '<select id= "' . $settings['param_name'] . '" multiple="multiple" data-placeholder="' .
				 __( 'Select lookbooks', 'dawnthemes' ) . '" class="dt-chosen-multiple-select dt-chosen-select-nostd ' .
				 $settings['param_name'] . ' ' . $settings['type'] . '">';
			if ( ! empty( $lookbooks ) ) {
				foreach ( $lookbooks as $lookbook ) :
					$output .= '<option value="' . esc_attr( $lookbook->term_id ) . '"' .
						 selected( in_array( $lookbook->term_id, $lookbook_slugs ), true, false ) . '>' .
						 esc_html( $lookbook->name ) . '</option>';
				endforeach
				;
			}
			$output .= '</select>';
			$output .= '<input id= "' . $settings['param_name'] .
				 '" type="hidden" class="wpb_vc_param_value wpb-textinput" name="' . $settings['param_name'] .
				 '" value="' . $value . '" />';
			return $output;
		}

		public function product_category_param( $settings, $value ) {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
				return '';
			$output = '';
			$category_slugs = explode( ',', $value );
			$args = array( 'orderby' => 'name', 'hide_empty' => true );
			$multiple = isset($settings['multiple']) && $settings['multiple'] == false ? '':' multiple="multiple"';
			$categories = get_terms( 'product_cat', $args );
			$output .= '<select id= "' . $settings['param_name'] . '" '.$multiple.' data-placeholder="' .
				 __( 'Select categories', 'dawnthemes' ) . '" class="dt-chosen-multiple-select dt-chosen-select-nostd ' .
				 $settings['param_name'] . ' ' . $settings['type'] . '">';
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $cat ) :
					$s = isset( $settings['select_field'] ) ? $cat->term_id : $cat->slug;
					$output .= '<option value="' . esc_attr( $s ) . '"' .
						 selected( in_array( $s, $category_slugs ), true, false ) . '>' . esc_html( $cat->name ) .
						 '</option>';
				endforeach
				;
			}
			$output .= '</select>';
			$output .= '<input id= "' . $settings['param_name'] .
				 '" type="hidden" class="wpb_vc_param_value wpb-textinput" name="' . $settings['param_name'] .
				 '" value="' . $value . '" />';
			return $output;
		}

		public function products_ajax_param( $settings, $value ) {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) )
				return '';
			
			$product_ids = array();
			if ( ! empty( $value ) )
				$product_ids = array_map( 'absint', explode( ',', $value ) );
			
			$output = '<select data-placeholder="' . __( 'Search for a product...', 'dawnthemes' ) . '" id= "' .
				 $settings['param_name'] . '" ' . ( isset( $settings['single_select'] ) ? '' : 'multiple="multiple"' ) .
				 ' class="dt-chosen-multiple-select dt-chosen-ajax-select-product ' . $settings['param_name'] . ' ' .
				 $settings['type'] . '">';
			if ( isset( $settings['single_select'] ) ) {
				$output .= '<option value=""></option>';
			}
			if ( ! empty( $product_ids ) ) {
				foreach ( $product_ids as $product_id ) {
					$product = get_product( $product_id );
					if ( $product->get_sku() ) {
						$identifier = $product->get_sku();
					} else {
						$identifier = '#' . $product->id;
					}
					
					$product_name = sprintf( __( '%s &ndash; %s', 'dawnthemes' ), $identifier, $product->get_title() );
					
					$output .= '<option value="' . esc_attr( $product_id ) . '" selected="selected">' .
						 esc_html( $product_name ) . '</option>';
				}
			}
			$output .= '</select>';
			$output .= '<input id= "' . $settings['param_name'] .
				 '" type="hidden" class="wpb_vc_param_value wpb-textinput" name="' . $settings['param_name'] .
				 '" value="' . $value . '" />';
			
			return $output;
		}

		public function ui_datepicker_param( $param, $param_value ) {
			$param_line = '';
			$value = $param_value;
			$value = htmlspecialchars( $value );
			$param_line .= '<input id="' . $param['param_name'] . '" name="' . $param['param_name'] .
				 '" readonly class="wpb_vc_param_value wpb-textinput ' . $param['param_name'] . ' ' . $param['type'] .
				 '" type="text" value="' . $value . '"/>';
			if ( ! defined( 'DT_UISLDER_PARAM' ) ) {
				define( 'DT_UISLDER_PARAM', 1 );
				$param_line .= '<link media="all" type="text/css" href="' . DTINC_ASSETS_URL .
					 '/vendor/jquery-ui-bootstrap/jquery-ui-1.10.0.custom.css?ver=1.10.0" rel="stylesheet" />';
			}
			$param_line .= '<script>
					jQuery(function() {
					jQuery( "#' . $param['param_name'] . '" ).datepicker({showButtonPanel: true});
					});</script>	
				';
			return $param_line;
		}

		public function ui_slider_param( $settings, $value ) {
			$data_min = ( isset( $settings['data_min'] ) && ! empty( $settings['data_min'] ) ) ? 'data-min="' .
				 absint( $settings['data_min'] ) . '"' : 'data-min="0"';
			$data_max = ( isset( $settings['data_max'] ) && ! empty( $settings['data_max'] ) ) ? 'data-max="' .
				 absint( $settings['data_max'] ) . '"' : 'data-max="100"';
			$data_step = ( isset( $settings['data_step'] ) && ! empty( $settings['data_step'] ) ) ? 'data-step="' .
				 absint( $settings['data_step'] ) . '"' : 'data-step="1"';
			
			return '<input name="' . $settings['param_name'] . '" class="wpb_vc_param_value wpb-textinput ' .
				 $settings['param_name'] . ' ' . $settings['type'] . '" type="text" value="' . $value . '"/>';
		}

		public function enqueue_scripts() {
			$pricing_table_feature_tmpl = '';
			$pricing_table_feature_tmpl .= '<tr><td><textarea id="content"></textarea></td><td align="left" style="padding:5px;"><a href="#" class="pricing-table-feature-remove" onclick="return pricing_table_feature_remove(this);"  title="' .
				 __( 'Remove', 'dawnthemes' ) . '">-</a></td></tr>';
			wp_enqueue_style( 
				'dt-vc-admin', 
				DTINC_ASSETS_URL . '/css/vc-admin.css', 
				array( 'vendor-font-awesome', 'vendor-elegant-icon', 'vendor-chosen' ), 
				DTINC_VERSION );
			wp_register_script( 
				'dt-vc-custom', 
				DTINC_ASSETS_URL . '/js/vc-custom.js', 
				array( 'jquery', 'jquery-ui-datepicker' ), 
				DTINC_VERSION, 
				true );
			$dtvcL10n = array( 
				'pricing_table_max_item_msg' => __( 'Pricing Table element only support display 5 item', 'dawnthemes' ), 
				'item_title' => DTVC_ITEM_TITLE, 
				'add_item_title' => DTVC_ADD_ITEM_TITLE, 
				'move_title' => DTVC_MOVE_TITLE, 
				'pricing_table_feature_tmpl' => $pricing_table_feature_tmpl );
			wp_localize_script( 'dt-vc-custom', 'dtvcL10n', $dtvcL10n );
			wp_enqueue_script( 'dt-vc-custom' );
		}
	}
	new DT_VisualComposer();

	function dt_vc_el_increment() {
		static $count = 0;
		$count++;
		return $count;
	}






























endif;