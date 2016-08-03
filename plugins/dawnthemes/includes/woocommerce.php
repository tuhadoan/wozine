<?php
if ( ! class_exists( 'DH_Woocommerce' ) ) :
	class DH_Woocommerce {
	
		protected static $_instance = null;
	
		public function __construct() {
			add_action( 'init', array( &$this, 'init' ) );
			
			if(!is_admin())
				add_action( 'template_redirect', array( &$this, 'add_navbar_minicart' ) );
			global $pagenow;
			if ( is_admin() && isset( $_GET['activated'] ) && $pagenow === 'themes.php' ) {
				add_action( 'init', array( &$this, 'update_product_image_size' ), 1 );
			}
			if(is_admin()){
				add_action( 'created_term', array(&$this,'product_cat_heading_save'), 10,3 );
				add_action( 'edit_term',array(&$this, 'product_cat_heading_save'), 10,3 );
				
				//cat heading
				add_action('product_cat_add_form_fields',array(&$this,'product_tax_add_heading_fields'),20,3);
				add_action('product_cat_edit_form_fields',array(&$this,'product_tax_edit_heading_fields'),20,3);
				
				//tag heading
				add_action('product_tag_add_form_fields',array(&$this,'product_tax_add_heading_fields'),20,3);
				add_action('product_tag_edit_form_fields',array(&$this,'product_tax_edit_heading_fields'),20,3);
				
				//lookbook heading
				add_action('product_lookbook_add_form_fields',array(&$this,'product_tax_add_heading_fields'),20,3);
				add_action('product_lookbook_edit_form_fields',array(&$this,'product_tax_edit_heading_fields'),20,3);
				
				
				//brand heading
				add_action('product_brand_add_form_fields',array(&$this,'product_tax_add_heading_fields'),20,3);
				add_action('product_brand_edit_form_fields',array(&$this,'product_tax_edit_heading_fields'),20,3);
			}
			add_action( 'wp_ajax_dt_json_search_products', array( &$this, 'json_search_products' ) );
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		public function init() {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) ) {
				return;
			}
			
			
			if(dt_get_theme_option('woo-minicart-style','side') == 'side'){
				add_action('wp_footer', array(&$this,'get_minicart_side'));
			}
			
			add_filter('dt_get_theme_option', array(&$this,'dt_get_theme_option_shop_filter'),10,2);
			
			//add_action( 'wp_enqueue_scripts', array( &$this, 'removeprettyPhoto' ), 199 );
			add_filter('dt_get_theme_option', array(&$this,'dt_get_theme_option'),100,2);
			add_filter('woocommerce_product_reviews_tab_title', array(&$this,'product_reviews_tab_title'),10,2);
			
			if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
				// WooCommerce 2.1 or above is active
				add_filter( 'woocommerce_enqueue_styles', '__return_false' );
			} else {
				// WooCommerce is less than 2.1
				define( 'WOOCOMMERCE_USE_CSS', false );
			}
			
			//WooCommerce 2.5 action
			remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
			remove_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );
			remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
			
			add_filter('dt_use_feature_product_image_in_single', '__return_true');
			
			// remove wrapper
			remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
			remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
			
			//remove result count
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
			
			// remove page title
			add_filter( 'woocommerce_show_page_title', '__return_false' );
			
			// remove default loop price
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
			
			// Loop shop per page
			add_filter( 'loop_shop_per_page', array( &$this, 'loop_shop_per_page' ) );
			// Loop thumbnail
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', array( &$this, 'template_loop_product_thumbnail' ), 10 );
			
			if(apply_filters('dt_use_template_loop_product_frist_thumbnail', true)){
				add_action( 'woocommerce_before_shop_loop_item_title', array( &$this, 'template_loop_product_frist_thumbnail' ), 11 );
			}
			// Loop actions
			//add_action( 'woocommerce_after_shop_loop_item', array( &$this, 'template_loop_quickview' ), 11 );
			
			//wishlist
			// add_action( 'woocommerce_before_shop_loop_item_title', array( &$this, 'template_loop_wishlist' ), 12 );
			
			// Quick view
			add_action( 'wp_ajax_wc_loop_product_quickview', array( &$this, 'quickview' ) );
			add_action( 'wp_ajax_nopriv_wc_loop_product_quickview', array( &$this, 'quickview' ) );
			
			// Remove minicart
			add_action( 'wp_ajax_wc_minicart_remove_item', array( &$this, 'minicart_remove_item' ) );
			add_action( 'wp_ajax_nopriv_wc_minicart_remove_item', array( &$this, 'minicart_remove_item' ) );
			// add_to_cart_fragments
			add_filter( 'add_to_cart_fragments', array( &$this, 'add_to_cart_fragments' ) );
			
			// Upsell products
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
			add_action( 'woocommerce_after_single_product_summary', array( &$this, 'upsell_display' ), 15 );
			
			// Related products
			add_filter( 'woocommerce_output_related_products_args', array( &$this, 'related_products_args' ) );
			
			// Cross sell products
			remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
			add_action( 'woocommerce_cart_collaterals', array( &$this, 'cross_sell_display' ), 15 );

			//Single product info
			//remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			
			//Single Product tabs
			add_filter('woocommerce_product_tabs', array(&$this,'custom_product_tabs'),50);
			
			
			add_action('template_redirect', array(&$this,'single_fullwidth_layout'),99);
		}
		
		public function custom_product_tabs($tabs){
			global $product, $post;
			$product_custom_tab = dt_get_post_meta('hide_custom_tab');
			if(dt_get_theme_option('woo-custom-tab',1) && empty($product_custom_tab)){
				$tabs['custom_tab'] = array(
					'title'    => dt_get_theme_option('woo-custom-tab-title','Custom Tab'),
					'priority' => apply_filters('dt_custom_tab_priority', 25),
					'callback' => array(&$this,'custom_tab_content')
				);
			}
			return $tabs;
		}
		
		public function custom_tab_content(){
			global $product, $post;
			$deafult_custom_tab_content = apply_filters('dt_default_custom_tab_content', 'I am custom tab default content. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.');
			echo do_shortcode(dt_get_post_meta('custom_tab_content',$product->id,$deafult_custom_tab_content));
		}
		
		public function add_navbar_minicart(){
			$header_style = dt_get_theme_option('header-style','classic');
			// add minicart in nav
			// if($header_style =='classic')
			// 	add_filter( 'wp_nav_menu_items', array( &$this, 'navbar_minicart' ), 12, 2 );
		}
		
		public function dt_get_theme_option($value, $option){
			if(is_singular('product') && ($option == 'sticky-menu') && (dt_get_theme_option('single-product-style','style-1') == 'style-2' )){
				return '0';
			}
			return $value;
		}

		public function single_fullwidth_layout(){
			//form field
			if(dt_get_theme_option('woo-product-layout','full-width') == 'full-width'){
				remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs',10);
			}
			if(is_product() ){
				$attachment_ids = wc_get_product(get_the_ID())->get_gallery_attachment_ids();
				if(empty($attachment_ids)){
					add_filter( 'dt_use_feature_product_image_in_single', '__return_true' );
				}
				add_action( 'woocommerce_single_product_summary', array( &$this, 'single_sharing' ), 50 );
			}
		}
		
		public function dt_get_theme_option_shop_filter($value, $option){
			if($option == 'woo-shop-filter'){
				switch ($value){
					case 'shop':
						if(is_shop())
							return true;
						else 
							return false;
					break;
					case 'taxonomy':
						if(is_product_taxonomy())
							return true;
						else
							return false;
						break;
					case 'all':
						if(is_shop() || is_product_taxonomy())
							return true;
						else
							return false;
					break;
				}
			}
			return $value;
		}

		public static function content($is_ajax = false){
			global $wp_query;
			?>
			<?php
			if ( is_singular( 'product' ) ) {
	
				while ( have_posts() ) : the_post();
	
					wc_get_template_part( 'content', 'single-product' );
	
				endwhile;
	
			} else { ?>
				<?php 
				/**
				 * script
				 * {{
				 */
				if(!$is_ajax)
					wp_enqueue_script('vendor-carouFredSel');
				?>
				<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
	
					<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
	
				<?php endif; ?>
				<?php do_action( 'woocommerce_archive_description' ); ?>
					<?php 
					$current_view_mode = dt_get_theme_option('dt_woocommerce_view_mode','grid');
					if(isset($_GET['mode']) && in_array($_GET['mode'], array('grid','list'))){
						$current_view_mode =  $_GET['mode'];
					}
					$grid_mode_href= ($current_view_mode == 'list' ? ' href="'.esc_url(add_query_arg('mode','grid')).'"' :'');
					$list_mode_href= ($current_view_mode == 'grid' ? ' href="'.esc_url(add_query_arg('mode','list')).'"' :'');
					
					$dt_ul_product_class = '';
					$woo_products_pagination = dt_get_theme_option('woo-products-pagination','page_num');
					if($woo_products_pagination === 'infinite_scroll'){
						$dt_ul_product_class = 'infinite-scroll-wrap';
					}elseif ($woo_products_pagination === 'loadmore'){
						$dt_ul_product_class = 'loadmore-wrap';
					}
					
					?>
					<div class="shop-toolbar">
						<?php if(!dt_get_theme_option('woo-shop-filter',0)):?>
						<?php woocommerce_catalog_ordering() ?>
						<?php endif;?>
						<div class="view-mode">
							<a class="grid-mode<?php echo ($current_view_mode == 'grid' ? ' active' :'')?>" title="<?php esc_attr_e('Grid','dawnthemes')?>" <?php echo ($grid_mode_href)?>><i class="fa fa-th"></i></a>
							<a class="list-mode<?php echo ($current_view_mode == 'list' ? ' active' :'')?>" title="<?php esc_attr_e('List','dawnthemes')?>" <?php echo ($list_mode_href) ?>><i class="fa fa-list"></i></a>							
						</div>
						<?php if(dt_get_theme_option('woo-shop-filter',0)):?>
							<div class="filter-toggle-button">
								<a class="filter" title="<?php esc_attr_e('Filter','dawnthemes')?>" href="#"><i class="fa fa-filter"></i> <?php esc_html_e('Filter','dawnthemes')?></a>
							</div>
						<?php endif;?>
					</div>
					<?php if(dt_get_theme_option('woo-shop-filter',0) && is_active_sidebar('sidebar-shop-filter')) :?>
					<div class="sidebar-shop-filter" data-toggle="shop-filter-ajax">
						<?php 
						$sidebars_widgets = wp_get_sidebars_widgets();
						$count = count( (array) $sidebars_widgets[ 'sidebar-shop-filter' ] );
						$count = absint($count);
						?>
						<div class="sidebar-shop-filter-wrap sidebar-shop-filter-<?php echo esc_attr($count)?>">
							<?php
							dynamic_sidebar('sidebar-shop-filter')
							?>
						</div>
					</div>
					<?php endif;?>
					<div class="shop-loop-wrap <?php echo $dt_ul_product_class?>">
						<div class="filter-ajax-loading">
							<div class="fade-loading"><i></i><i></i><i></i><i></i></div>
						</div>
						<div class="shop-loop <?php echo esc_attr($current_view_mode)?>" >
							<?php if(is_search() && is_post_type_archive( 'product' )):?>
								<h3 class="woocommerce-search-text"><?php dt_page_title() ?></h3>
							<?php endif;?>
							<?php if ( have_posts() ) : ?>
							<?php woocommerce_product_loop_start(); ?>
								<?php woocommerce_product_subcategories(); ?>
								
								<?php while ( have_posts() ) : the_post(); ?>
			
									<?php wc_get_template_part( 'content', 'product' ); ?>
			
								<?php endwhile; // end of the loop. ?>
							<?php woocommerce_product_loop_end(); ?>
							
							<?php do_action('woocommerce_after_shop_loop'); ?>
							
							<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>
		
								<?php wc_get_template( 'loop/no-products-found.php' ); ?>
				
							<?php endif;
							?>
						</div>
					</div>
				<?php
			}
		}
		
		public function product_reviews_tab_title($title, $key){
			global $product, $post;
			if($key === 'reviews'){
				return sprintf( __( 'Reviews <span>%d</span>', 'woocommerce' ), $product->get_review_count() );
			}
			return $title;
		}
		
		public function cross_sell_display() {
			woocommerce_cross_sell_display( 2, 2 );
		}

		public function upsell_display() {
			if ( dt_get_theme_option( 'woo-product-layout', 'full-width' ) === 'full-width' ) {
				woocommerce_upsell_display( 4, 4 );
			} else {
				woocommerce_upsell_display( 3, 3 );
			}
		}

		public function related_products_args() {
			if ( dt_get_theme_option( 'woo-product-layout', 'full-width' ) === 'full-width' ) {
				$args = array( 'posts_per_page' => 4, 'columns' => 4 );
				return $args;
			}
			
			$args = array( 'posts_per_page' => 3, 'columns' => 3 );
			return $args;
		}
		
		// Number of products per page
		public function loop_shop_per_page() {
			$per_page = dt_get_theme_option( 'woo-per-page', 12 );
			if ( isset( $_GET['per_page'] ) )
				$per_page = absint( $_GET['per_page'] );
			return $per_page;
		}

		public function removeprettyPhoto() {
			wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
			wp_dequeue_script( 'prettyPhoto-init' );
			wp_dequeue_script( 'prettyPhoto' );
		}

		public function update_product_image_size() {
			if ( ! defined( 'WOOCOMMERCE_VERSION' ) ) {
				return;
			}
			$catalog = array( 'width' => '300', 'height' => '350', 'crop' => 1 );
			$single = array( 'width' => '800', 'height' => '850', 'crop' => 1 );
			$thumbnail = array( 'width' => '100', 'height' => '150', 'crop' => 1 );
			
			update_option( 'shop_catalog_image_size', $catalog );
			update_option( 'shop_single_image_size', $single );
			update_option( 'shop_thumbnail_image_size', $thumbnail );
		}

		protected function _cart_items_text( $count ) {
			$product_item_text = "";
			
			if ( $count > 1 ) {
				$product_item_text = str_replace( '%', number_format_i18n( $count ), __( '% items', 'dawnthemes' ) );
			} elseif ( $count == 0 ) {
				$product_item_text = __( '0 items', 'dawnthemes' );
			} else {
				$product_item_text = __( '1 item', 'dawnthemes' );
			}
			
			return $product_item_text;
		}

		public function add_to_cart_fragments( $fragments ) {
			$fragments['div.minicart'] = $this->_get_minicart( true, true );
			$fragments['span.minicart-icon'] = $this->_get_minicart_icon();
			$fragments['a.cart-icon-mobile'] = $this->_get_minicart_mobile();
			return $fragments;
		}

		public function navbar_minicart( $items, $args ) {
			if ( $args->theme_location == 'primary' 
					&& defined( 'WOOCOMMERCE_VERSION' ) 
					&& dt_get_theme_option( 'woo-cart-nav', 1 )
				) {
				$items .= '<li class="navbar-minicart navbar-minicart-nav"></a>'.$this->_get_minicart(false,false,false).'</li>';
			}
			return $items;
		}

		public function minicart_remove_item() {
			global $woocommerce;
			$response = array();
			if ( ! isset( $_GET['item'] ) && ! isset( $_GET['_wpnonce'] ) ) {
				exit();
			}
			$woocommerce->cart->set_quantity( $_GET['item'], 0 );
			$cart_total = $woocommerce->cart->get_cart_total();
			$cart_count = absint($woocommerce->cart->cart_contents_count);
			$response['minicart_text'] = $cart_count;
			$response['minicart'] = $this->_get_minicart( true );
			// widget cart update
			ob_start();
			woocommerce_mini_cart();
			$mini_cart = ob_get_clean();
			$response['widget'] = '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';
			
			echo json_encode( $response );
			exit();
		}

		protected function _get_minicart_mobile() {
			global $woocommerce;
			$cart_total = $woocommerce->cart->get_cart_total();
			$cart_count = $woocommerce->cart->cart_contents_count;
			$cart_output = '<a href="' . $woocommerce->cart->get_cart_url() . '" title="' . __( 'View Cart', 'dawnthemes' ) . '"  class="cart-icon-mobile">'.$this->_get_minicart_icon2().' '.( ! empty( $cart_count ) ? '<span>' . $cart_count . '</span>' : '' ) . '</a>';
			return $cart_output;
		}

		protected function _get_minicart_icon() {
			global $woocommerce;
			$cart_total = $woocommerce->cart->get_cart_total();
			$cart_count = absint( $woocommerce->cart->cart_contents_count );
			$cart_has_item = '';
			if ( ! empty( $cart_count ) ) {
				$cart_has_item = ' has-item';
			}
			
			return '<span class="minicart-icon' . $cart_has_item . '">'.$this->_get_minicart_icon2().'<span>'.$cart_count.'</span></span>';
		}
		
		public function get_topbar_minicart(){
			return $this->_get_minicart(false,false,true);
		}

		public function get_minicart(){
			return '<div class="navbar-minicart">'.$this->_get_minicart().'</div>';	
		}
		
		public function get_minicart_side(){
			echo '<div class="minicart-side"><div class="minicart-side-title"><h4>'.esc_html__('Shopping Cart','dawnthemes').'</h4></div><div class="minicart-side-content">'.$this->_get_minicart(true,true).'</div></div>';
			return;
		}
		
		protected function _get_minicart( $content = false, $_flag = false ,$topbar=false) {
			global $woocommerce;
			$cart_total = $woocommerce->cart->get_cart_total();
			$cart_count = absint( $woocommerce->cart->cart_contents_count );
			$cart_count_text = $this->_cart_items_text( $cart_count );
			
			if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {
				$cart_url = apply_filters( 'woocommerce_get_checkout_url', WC()->cart->get_cart_url() );
			} else {
				$cart_url = esc_url( $woocommerce->cart->get_cart_url() );
			}
			$cart_has_item = '';
			
			if ( ! empty( $cart_count ) ) {
				$cart_has_item = ' has-item';
			}
			$minicart = '';
			if ( ! $content ) {
				$minicart .= '<a class="minicart-link" href="' . $cart_url . '"><span class="minicart-icon '.$cart_has_item.'">'.$this->_get_minicart_icon2() . '<span>'.$cart_count.'</span></span>'.($topbar ? __('My Cart','dawnthemes'):'').'</a>';
				if(dt_get_theme_option('woo-minicart-style','side') == 'mini'){
					$minicart .= '<div class="minicart" style="display:none">';
				}
			}
			if ( $content && $_flag ) {
				$minicart .= '<div class="minicart" style="display:none">';
			}
			if( ((dt_get_theme_option('woo-minicart-style','side') == 'mini') ||  $content)){
				if (! empty( $cart_count ) ) {
					$minicart .= '<div class="minicart-header">' . $cart_count_text . ' ' . __( 'in the shopping cart', 'dawnthemes' ) . '</div>';
					$minicart .= '<div class="minicart-body">';
					foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $cart_item ) {
						
						$cart_product = $cart_item['data'];
						$product_title = $cart_product->get_title();
						$product_short_title = ( strlen( $product_title ) > 25 ) ? substr( $product_title, 0, 22 ) . '...' : $product_title;
						
						if ( $cart_product->exists() && $cart_item['quantity'] > 0 ) {
							$minicart .= '<div class="cart-product clearfix">';
							$minicart .= '<div class="cart-product-image"><a class="cart-product-img" href="' .get_permalink( $cart_item['product_id'] ) . '">' . $cart_product->get_image() . '</a></div>';
							$minicart .= '<div class="cart-product-details">';
							$minicart .= '<div class="cart-product-title"><a href="' .get_permalink( $cart_item['product_id'] ) . '">' . apply_filters( 'woocommerce_cart_widget_product_title', $product_short_title, $cart_product ) . '</a></div>';
							$minicart .= '<div class="cart-product-quantity-price">' . $cart_item['quantity'] . ' x ' .woocommerce_price( $cart_product->get_price() ) . '</div>';
							// $minicart .= '<div class="cart-product-quantity">' . __('Quantity:', 'dawnthemes') . ' ' .
							// $cart_item['quantity'] . '</div>';
							$minicart .= '</div>';
							$minicart .= apply_filters( 'woocommerce_cart_item_remove_link',sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( $woocommerce->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'dawnthemes' ) ), $cart_item_key );
							$minicart .= '</div>';
						}
					}
					$minicart .= '</div>';
					$minicart .= '<div class="minicart-footer">';
					$minicart .= '<div class="minicart-total">' . __( 'Cart Subtotal', 'dawnthemes' ) . ' ' . $cart_total . '</div>';
					$minicart .= '<div class="minicart-actions clearfix">';
					if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {
	 					$cart_url = apply_filters( 'woocommerce_get_checkout_url', WC()->cart->get_cart_url() );
						$checkout_url = apply_filters( 'woocommerce_get_checkout_url', WC()->cart->get_checkout_url() );
						
						$minicart .= '<a class="viewcart-button button" href="' . esc_url( $cart_url ) . '"><span class="text">' . __( 'View Cart', 'dawnthemes' ) . '</span></a>';
						$minicart .= '<a class="checkout-button button" href="' . esc_url( $checkout_url ) .'"><span class="text">' . __( 'Checkout', 'dawnthemes' ) . '</span></a>';
					} else {
						$minicart .= '<a class="viewcart-button button" href="' . esc_url( $woocommerce->cart->get_cart_url() ) . '"><span class="text">' . __( 'View Cart', 'dawnthemes' ) . '</span></a>';
						$minicart .= '<a class="checkout-button button" href="' . esc_url( $woocommerce->cart->get_checkout_url() ) . '"><span class="text">' . __( 'Checkout', 'dawnthemes' ) . '</span></a>';
					}
					$minicart .= '</div>';
					$minicart .= '</div>';
				} else {
					$minicart .= '<div class="minicart-header no-items show">' . __( 'Your shopping bag is empty.', 'dawnthemes' ) . '</div>';
					$shop_page_url = "";
					if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {
						$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
					} else {
						$shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
					}
					
					$minicart .= '<div class="minicart-footer">';
					$minicart .= '<div class="minicart-actions clearfix">';
					$minicart .= '<a class="button no-item-button" href="' . esc_url( $shop_page_url ) . '"><span class="text">' . __( 'Go to the shop', 'dawnthemes' ) . '</span></a>';
					$minicart .= '</div>';
					$minicart .= '</div>';
				}
			}
			if ( $content && $_flag ) {
				$minicart .= '</div>';
			}
			if ( ! $content ) {
				if(dt_get_theme_option('woo-minicart-style','side') == 'mini'){
				$minicart .= '</div>';
				}
			}
			
			return $minicart;
		}

		public function single_sharing() {
			if ( dt_get_theme_option( 'show-woo-share', 1 ) ) {
				dt_share( 
					'', 
					dt_get_theme_option( 'woo-fb-share', 1 ), 
					dt_get_theme_option( 'woo-tw-share', 1 ), 
					dt_get_theme_option( 'woo-go-share', 1 ), 
					dt_get_theme_option( 'woo-pi-share', 0 ), 
					dt_get_theme_option( 'woo-li-share', 1 ));
			}
		}

		public function template_loop_wishlist() {
			if ( $this->_yith_wishlist_is_active() ) {
				echo do_shortcode( '[yith_wcwl_add_to_wishlist]' );
			}
			return;
		}
		
		public function yith_wishlist_is_active(){
			return $this->_yith_wishlist_is_active();
		}

		protected function _yith_wishlist_is_active() {
			return defined( 'YITH_FUNCTIONS' );
		}
		
		/**
		 * 
		 */
		public function template_loop_quickview() {
			global $product;
			if(apply_filters('dt_woocommerce_quickview', true))
				echo '<div class="shop-loop-quickview"><a data-product_id ="' . $product->id . '" title="' .esc_attr__( 'Quick view', 'dawnthemes' ) . '" href="' . esc_url( $product->get_permalink() ) . '">'. __( 'Quick view', 'dawnthemes' ) .'</a></div>';
		}

		public function quickview() {
			global $woocommerce, $post, $product;
			$product_id = $_POST['product_id'];
			$product = get_product( $product_id );
			$post = get_post( $product_id );
			$output = '';
			
			ob_start();
			woocommerce_get_template( 'content-quickview.php' );
			$output = ob_get_contents();
			ob_end_clean();
			
			echo trim($output);
			die();
		}

		public function template_loop_product_thumbnail() {
			$frist = $this->_product_get_frist_thumbnail();
			$thumbnail_size = 'shop_catalog';
			echo '<div class="shop-loop-thumbnail'.(apply_filters('dt_use_template_loop_product_frist_thumbnail', true) && $frist != '' ? ' shop-loop-front-thumbnail':'').'">' . woocommerce_get_product_thumbnail($thumbnail_size) . '</div>';
		}

		public function template_loop_product_frist_thumbnail() {
			if ( ( $frist = $this->_product_get_frist_thumbnail() ) != '' ) {
				echo '<div class="shop-loop-thumbnail shop-loop-back-thumbnail">' . $frist . '</div>';
			}
		}

		protected function _product_get_frist_thumbnail() {
			global $product, $post;
			$image = '';
			$current_view_mode = get_option('dt_woocommerce_view_mode',dt_get_theme_option('dt_woocommerce_view_mode','grid'));
			$thumbnail_size = 'shop_catalog';
			if ( version_compare( WOOCOMMERCE_VERSION, "2.0.0" ) >= 0 ) {
				$attachment_ids = $product->get_gallery_attachment_ids();
				$image_count = 0;
				if ( $attachment_ids ) {
					foreach ( $attachment_ids as $attachment_id ) {
						if ( get_post_meta( $attachment_id, '_woocommerce_exclude_image' ) )
							continue;
						
						$image = wp_get_attachment_image( $attachment_id, $thumbnail_size );
						
						$image_count++;
						if ( $image_count == 1 )
							break;
					}
				}
			} else {
				$attachments = get_posts( 
					array( 
						'post_type' => 'attachment', 
						'numberposts' => - 1, 
						'post_status' => null, 
						'post_parent' => $post->ID, 
						'post__not_in' => array( get_post_thumbnail_id() ), 
						'post_mime_type' => 'image', 
						'orderby' => 'menu_order', 
						'order' => 'ASC' ) );
				$image_count = 0;
				if ( $attachments ) {
					foreach ( $attachments as $attachment ) {
						
						if ( get_post_meta( $attachment->ID, '_woocommerce_exclude_image' ) == 1 )
							continue;
						
						$image = wp_get_attachment_image( $attachment->ID, $thumbnail_size );
						
						$image_count++;
						
						if ( $image_count == 1 )
							break;
					}
				}
			}
			return $image;
		}

		public function json_search_products() {
			$term = (string) sanitize_text_field( stripslashes( $_GET['term'] ) );
			$exclude = array();
			$post_types = array( 'product' );
			if ( empty( $term ) )
				die();
			if ( ! empty( $_GET['exclude'] ) ) {
				$exclude = array_map( 'intval', explode( ',', $_GET['exclude'] ) );
			}
			
			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				's'              => $term,
				'fields'         => 'ids',
				'exclude'        => $exclude
			);
			
			if ( is_numeric( $term ) ) {
			
				if ( false === array_search( $term, $exclude ) ) {
					$posts2 = get_posts( array(
						'post_type'      => $post_types,
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'post__in'       => array( 0, $term ),
						'fields'         => 'ids'
					) );
				} else {
					$posts2 = array();
				}
			
				$posts3 = get_posts( array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'post_parent'    => $term,
					'fields'         => 'ids',
					'exclude'        => $exclude
				) );
			
				$posts4 = get_posts( array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $term,
							'compare' => 'LIKE'
						)
					),
					'fields'         => 'ids',
					'exclude'        => $exclude
				) );
			
				$posts = array_unique( array_merge( get_posts( $args ), $posts2, $posts3, $posts4 ) );
			
			} else {
			
				$args2 = array(
					'post_type'      => $post_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => '_sku',
							'value'   => $term,
							'compare' => 'LIKE'
						)
					),
					'fields'         => 'ids',
					'exclude'        => $exclude
				);
			
				$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );
			
			}
			$found_products = array();
			if ( $posts )
				foreach ( $posts as $post ) {
					$product = wc_get_product( $post );
					if ( ! current_user_can( 'read_product', $post ) ) {
						continue;
					}
					$found_products[$post] = $this->_formatted_name( $product );
				}
			wp_send_json( $found_products );
		}

		protected function _formatted_name( WC_Product $product ) {
			if ( $product->get_sku() ) {
				$identifier = $product->get_sku();
			} else {
				$identifier = '#' . $product->id;
			}
			
			return sprintf( __( '%s &ndash; %s', 'dawnthemes' ), $identifier, $product->get_title() );
		}
		
		public function _get_minicart_icon2(){
			return apply_filters('dt_woocommerce_minicart_icon','<svg xml:space="preserve" style="enable-background:new 0 0 459.529 459.529;" viewBox="0 0 459.529 459.529" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" version="1.1">
	<g>
		<g>
			<path d="M17,55.231h48.733l69.417,251.033c1.983,7.367,8.783,12.467,16.433,12.467h213.35c6.8,0,12.75-3.967,15.583-10.2
				l77.633-178.5c2.267-5.383,1.7-11.333-1.417-16.15c-3.117-4.817-8.5-7.65-14.167-7.65H206.833c-9.35,0-17,7.65-17,17
				s7.65,17,17,17H416.5l-62.9,144.5H164.333L94.917,33.698c-1.983-7.367-8.783-12.467-16.433-12.467H17c-9.35,0-17,7.65-17,17
				S7.65,55.231,17,55.231z"/>
			<path d="M135.433,438.298c21.25,0,38.533-17.283,38.533-38.533s-17.283-38.533-38.533-38.533S96.9,378.514,96.9,399.764
				S114.183,438.298,135.433,438.298z"/>
			<path d="M376.267,438.298c0.85,0,1.983,0,2.833,0c10.2-0.85,19.55-5.383,26.35-13.317c6.8-7.65,9.917-17.567,9.35-28.05
				c-1.417-20.967-19.833-37.117-41.083-35.7c-21.25,1.417-37.117,20.117-35.7,41.083
				C339.433,422.431,356.15,438.298,376.267,438.298z"/>
		</g>
	</g>
</svg>');
		}
		
		public function product_tax_add_heading_fields($taxonomy){
			if('product_cat' === $taxonomy):
			?>
			<div class="form-field">
				<label for="product_cat_short_description"><?php _e('Short Description','dawnthemes')?></label>
				<input id="product_cat_short_description" type="text" aria-required="true" size="40" value="" name="product_cat_short_description">
			</div>
			<?php endif;?>
			<div class="form-field">
				<label><?php _e( 'Heading Background', 'dawnthemes' ); ?></label>
				<div id="product_cat_heading_thumbnail" style="float:left;margin-right:10px;">
					<img src="<?php echo woocommerce_placeholder_img_src(); ?>" width="60px" height="60px" />
				</div>
				<div style="line-height:60px;">
					<input type="hidden" id="product_cat_heading_thumbnail_id" name="product_cat_heading_thumbnail_id" />
					<button type="submit" class=" button product_cat_heding_upload"><?php _e('Upload/Add image', 'dawnthemes'); ?></button>
					<button type="submit" class=" button product_cat_heding_remove"><?php _e('Remove image', 'dawnthemes'); ?></button>
				</div>
				<script type="text/javascript">
			
					 // Only show the "remove image" button when needed
					 if ( ! jQuery('#product_cat_heading_thumbnail_id').val() )
						 jQuery('.product_cat_heding_remove').hide();
			
					// Uploading files
					var product_cat_heading_file_frame;
			
					jQuery(document).on( 'click', '.product_cat_heding_upload', function( event ){
			
						event.preventDefault();
			
						// If the media frame already exists, reopen it.
						if ( product_cat_heading_file_frame ) {
							product_cat_heading_file_frame.open();
							return;
						}
			
						// Create the media frame.
						product_cat_heading_file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php _e( 'Choose an image', 'dawnthemes' ); ?>',
							button: {
								text: '<?php _e( 'Use image', 'dawnthemes' ); ?>',
							},
							multiple: false
						});
			
						// When an image is selected, run a callback.
						product_cat_heading_file_frame.on( 'select', function() {
							attachment = product_cat_heading_file_frame.state().get('selection').first().toJSON();
			
							jQuery('#product_cat_heading_thumbnail_id').val( attachment.id );
							jQuery('#product_cat_heading_thumbnail img').attr('src', attachment.url );
							jQuery('.product_cat_heding_remove').show();
						});
			
						// Finally, open the modal.
						product_cat_heading_file_frame.open();
					});
			
					jQuery(document).on( 'click', '.product_cat_heding_remove', function( event ){
						jQuery('#product_cat_heading_thumbnail img').attr('src', '<?php echo woocommerce_placeholder_img_src(); ?>');
						jQuery('#product_cat_heading_thumbnail_id').val('');
						jQuery('.product_cat_heding_remove').hide();
						return false;
					});
			
				</script>
				<div class="clear"></div>
			</div>
			<div class="form-field">
				<label for="product_cat_heading_title"><?php _e('Heading Title', 'dawnthemes')?></label>
				<input id="product_cat_heading_title" type="text" aria-required="true" size="40" value="" name="product_cat_heading_title">
			</div>
			<div class="form-field">
				<label for="product_cat_heading_sub_title"><?php _e('Heading Sub Title','dawnthemes')?></label>
				<input id="product_cat_heading_sub_title" type="text" aria-required="true" size="40" value="" name="product_cat_heading_sub_title">
			</div>
			<div class="form-field">
				<label for="product_cat_heading_button_text"><?php _e('Heading Button Text','dawnthemes')?></label>
				<input id="product_cat_heading_button_text" type="text" aria-required="true" size="40" value="" name="product_cat_heading_button_text">
			</div>
			<div class="form-field">
				<label for="product_cat_heading_button_link"><?php _e('Heading Sub Link','dawnthemes')?></label>
				<input id="product_cat_heading_button_link" type="text" aria-required="true" size="40" value="" name="product_cat_heading_button_link">
			</div>
			<?php
		}
		
		public function product_tax_edit_heading_fields($term, $taxonomy){
			global $woocommerce;
			$image 			= '';
			$thumbnail_id 	= get_woocommerce_term_meta( $term->term_id, 'product_cat_heading_thumbnail_id', true );
			if ($thumbnail_id) :
				$image = wp_get_attachment_url( $thumbnail_id );
			else :
				$image = woocommerce_placeholder_img_src();
			endif;
			$product_cat_short_description = get_woocommerce_term_meta( $term->term_id, 'product_cat_short_description', true );
			$product_cat_heading_title = get_woocommerce_term_meta( $term->term_id, 'product_cat_heading_title', true );
			$product_cat_heading_sub_title = get_woocommerce_term_meta( $term->term_id, 'product_cat_heading_sub_title', true );
			$product_cat_heading_button_text = get_woocommerce_term_meta( $term->term_id, 'product_cat_heading_button_text', true );
			$product_cat_heading_button_link = get_woocommerce_term_meta( $term->term_id, 'product_cat_heading_button_link', true );
			
			
			if('product_cat' === $taxonomy):
			?>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="product_cat_short_description"><?php _e('Short Description','dawnthemes')?></label>
				</th>
				<td>
					<input id="product_cat_short_description" type="text" aria-required="true" size="40" value="<?php echo esc_attr($product_cat_short_description)?>" name="product_cat_short_description">
				</td>
			</tr>
			<?php endif;?>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label><?php _e( 'Heading Background', 'dawnthemes' ); ?></label>
				</th>
				<td>
					<div id="product_cat_heading_thumbnail" style="float:left;margin-right:10px;">
						<img src="<?php echo $image; ?>" width="60px" height="60px" />
					</div>
					<div style="line-height:60px;">
						<input type="hidden" value="<?php echo esc_attr($thumbnail_id)?>" id="product_cat_heading_thumbnail_id" name="product_cat_heading_thumbnail_id" />
						<button type="submit" class="button product_cat_heding_upload"><?php _e('Upload/Add image', 'dawnthemes'); ?></button>
						<button type="submit" class="button product_cat_heding_remove"><?php _e('Remove image', 'dawnthemes'); ?></button>
					</div>
					<script type="text/javascript">
				
						 // Only show the "remove image" button when needed
						 if ( ! jQuery('#product_cat_heading_thumbnail_id').val() )
							 jQuery('.product_cat_heding_remove').hide();
				
						// Uploading files
						var product_cat_heading_file_frame;
				
						jQuery(document).on( 'click', '.product_cat_heding_upload', function( event ){
				
							event.preventDefault();
				
							// If the media frame already exists, reopen it.
							if ( product_cat_heading_file_frame ) {
								product_cat_heading_file_frame.open();
								return;
							}
				
							// Create the media frame.
							product_cat_heading_file_frame = wp.media.frames.downloadable_file = wp.media({
								title: '<?php _e( 'Choose an image', 'dawnthemes' ); ?>',
								button: {
									text: '<?php _e( 'Use image', 'dawnthemes' ); ?>',
								},
								multiple: false
							});
				
							// When an image is selected, run a callback.
							product_cat_heading_file_frame.on( 'select', function() {
								attachment = product_cat_heading_file_frame.state().get('selection').first().toJSON();
				
								jQuery('#product_cat_heading_thumbnail_id').val( attachment.id );
								jQuery('#product_cat_heading_thumbnail img').attr('src', attachment.url );
								jQuery('.product_cat_heding_remove').show();
							});
				
							// Finally, open the modal.
							product_cat_heading_file_frame.open();
						});
				
						jQuery(document).on( 'click', '.product_cat_heding_remove', function( event ){
							jQuery('#product_cat_heading_thumbnail img').attr('src', '<?php echo woocommerce_placeholder_img_src(); ?>');
							jQuery('#product_cat_heading_thumbnail_id').val('');
							jQuery('.product_cat_heding_remove').hide();
							return false;
						});
				
					</script>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="product_cat_heading_title"><?php _e('Heading Title','dawnthemes')?></label></th>
				<td><input id="product_cat_heading_title" type="text" aria-required="true" size="40" value="<?php echo esc_attr($product_cat_heading_title)?>" name="product_cat_heading_title"></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="product_cat_heading_sub_title"><?php _e('Heading Sub Title','dawnthemes')?></label></th>
				<td><input id="product_cat_heading_sub_title" type="text" aria-required="true" size="40" value="<?php echo esc_attr($product_cat_heading_sub_title)?>" name="product_cat_heading_sub_title"></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="product_cat_heading_button_text"><?php _e('Heading Button Text','dawnthemes')?></label></th>
				<td><input id="product_cat_heading_button_text" type="text" aria-required="true" size="40" value="<?php echo esc_attr($product_cat_heading_button_text)?>" name="product_cat_heading_button_text"></td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="product_cat_heading_button_link"><?php _e('Heading Sub Link','dawnthemes')?></label></th>
				<td><input id="product_cat_heading_button_link" type="text" aria-required="true" size="40" value="<?php echo esc_attr($product_cat_heading_button_link)?>" name="product_cat_heading_button_link"></td>
			</tr>
			<?php
		}
		
		public function product_cat_heading_save( $term_id, $tt_id, $taxonomy ) {
			$fields = array(
				'product_cat_short_description',
				'product_cat_heading_thumbnail_id',
				'product_cat_heading_title',
				'product_cat_heading_sub_title',
				'product_cat_heading_button_text',
				'product_cat_heading_button_link',
			);
			foreach ($fields as $field){
				if(isset($_POST[$field])){
					$value = !empty($_POST[$field]) ? wp_kses_post($_POST[$field]):'';
					update_woocommerce_term_meta( $term_id, $field, $value );
				}
			}
		}
	}
	new DH_Woocommerce();
endif;
