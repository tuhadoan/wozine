<?php
if(!function_exists('dt_get_theme_option_name')){
	function dt_get_theme_option_name(){
		$lang = '';
		$theme_name = 'dt_theme_'.basename(get_template_directory());
		$theme_name = apply_filters('dt_get_theme_option_name', $theme_name);
		return $theme_name;
	}
}

if(!function_exists('dt_get_theme_option')){
	function dt_get_theme_option($option,$default = null){
		global $dt_theme_options;
		if(empty($option))
			return $default;


		$_option_name = dt_get_theme_option_name();

		if ( empty( $dt_theme_options ) ) {
			$dt_theme_options = get_option($_option_name);
		}

		if(is_page() || (defined('WOOCOMMERCE_VERSION') && is_woocommerce())){
			if($option == 'header-style'){
				$page_value = dt_get_post_meta('header_style');
				if( $page_value !== null && $page_value !== array() && $page_value !== false && $page_value != '-1'){
					return apply_filters('dt_get_theme_option', $page_value, $option);
				}
			}
			if($option == 'show-topbar'){
				$page_value = dt_get_post_meta('show_topbar');
				if($page_value !== null && $page_value !== array() && $page_value !== false && $page_value != '-1'){
					return apply_filters('dt_get_theme_option', $page_value, $option);
				}
			}
			if($option == 'menu-transparent'){
				$page_value = dt_get_post_meta('menu_transparent');
				if($page_value !== null && $page_value !== array() && $page_value !== false &&  $page_value != '-1'){
					return apply_filters('dt_get_theme_option', $page_value, $option);
				}
			}
			if($option == 'footer-area'){
				$page_value = dt_get_post_meta('footer_area');
				if($page_value !== null && $page_value !== array() && $page_value !== false &&  $page_value != '-1'){
					return apply_filters('dt_get_theme_option', $page_value, $option);
				}
			}
			if($option == 'footer-menu'){
				$page_value = dt_get_post_meta('footer_menu');
				if($page_value !== null && $page_value !== array() && $page_value !== false &&  $page_value != '-1'){
					return apply_filters('dt_get_theme_option', $page_value, $option);
				}
			}
		}
		if(isset($dt_theme_options[$option]) && $dt_theme_options[$option] !== '' && $dt_theme_options[$option] !== null && $dt_theme_options[$option] !== array() && $dt_theme_options[$option] !== false){
			$value = $dt_theme_options[$option];
			return apply_filters('dt_get_theme_option', $value, $option);
		}else{
			return $default;
		}
	}
}

function dt_sc_get_id(){
	$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	$token = '';
	$id = session_id();
	for ($i = 0; $i < 32; ++$i)
	{
		$token .= $chars[(rand(0, $max))];
	}
	return 'dt_sc_'.substr(md5($token.$id),0,10);
}

/*
 * Breadcrumbs
 */
if( !function_exists('dt_breadcrumbs') ):
function dt_breadcrumbs(){
	$tpl_name = '/template-parts/tpl.breadcrumb.php';
	if( is_file( get_template_directory() . $tpl_name ) )
		include ( get_template_directory() . $tpl_name );
}
endif;

function dt_echo($string=''){
	return $string;
}

function dt_is_ajax(){
	if ( defined( 'DOING_AJAX' ) ) {
		return true;
	}

	return ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) ? true : false;

}

function dt_css_minify( $css ) {
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\/\*[^\!](.*?)\*\//', '', $css );
	$css = preg_replace( '/(,|:|;|\{|}) /', '$1', $css );
	$css = preg_replace( '/ (,|;|\{|})/', '$1', $css );
	$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
	$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
	return trim( $css );
}

function dt_placeholder_img_src() {
	return apply_filters( 'dt_placeholder_img_src', get_template_directory_uri() . '/assets/images/placeholder.png' );
}

function dt_do_not_reply_address(){
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) === 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}
	return apply_filters( 'dt_do_not_reply_address', 'noreply@' . $sitename );
}

function dt_instagram($username,$images_number=12,$refresh_hour){
	if (false === ($instagram = get_transient('instagram-'.sanitize_title_with_dashes($username)))) {

		$remote = wp_remote_get('http://instagram.com/'.trim($username),array( 'decompress' => false ));

		if ( is_wp_error( $remote ) )
			return new WP_Error( 'site_down',__( 'Unable to communicate with Instagram.', 'dawnthemes' ));

		if ( 200 != wp_remote_retrieve_response_code( $remote ) )
			return new WP_Error( 'invalid_response',__( 'Instagram did not return a 200.', 'dawnthemes' ));

		$shards = explode( 'window._sharedData = ', $remote['body'] );
		$insta_json = explode( ';</script>', $shards[1] );
		$insta_array = json_decode( $insta_json[0], TRUE );

		if ( !$insta_array )
			return new WP_Error( 'bad_json', __( 'Instagram has returned invalid data.', 'dawnthemes' ) );

		// old style
		if ( isset( $insta_array['entry_data']['UserProfile'][0]['userMedia'] ) ) {
			$images = $insta_array['entry_data']['UserProfile'][0]['userMedia'];
			$type = 'old';
			// new style
		} else if ( isset( $insta_array['entry_data']['ProfilePage'][0]['user']['media']['nodes'] ) ) {
			$images = $insta_array['entry_data']['ProfilePage'][0]['user']['media']['nodes'];
			$type = 'new';
		} else {
			return  new WP_Error( 'bad_json',__( 'Instagram has returned invalid data.', 'dawnthemes' ));
		}

		if ( !is_array( $images ) )
			return new WP_Error( 'bad_json',__( 'Instagram has returned invalid data.', 'dawnthemes'));
			
		$instagram = array();

		switch ( $type ) {
			case 'old':
				foreach ( $images as $image ) {

					if ( $image['user']['username'] == $username ) {
							
						$image['link']						  = preg_replace( "/^http:/i", "", $image['link'] );
						$image['images']['thumbnail']		   = preg_replace( "/^http:/i", "", $image['images']['thumbnail'] );
						$image['images']['standard_resolution'] = preg_replace( "/^http:/i", "", $image['images']['standard_resolution'] );
						$image['images']['low_resolution']	  = preg_replace( "/^http:/i", "", $image['images']['low_resolution'] );
							
						$instagram[] = array(
							'description'   => $image['caption']['text'],
							'link'		  	=> $image['link'],
							'time'		  	=> $image['created_time'],
							'comments'	  	=> $image['comments']['count'],
							'likes'		 	=> $image['likes']['count'],
							'thumbnail'	 	=> $image['images']['thumbnail'],
							'large'		 	=> $image['images']['standard_resolution'],
							'small'		 	=> $image['images']['low_resolution'],
							'type'		  	=> $image['type']
						);
					}
				}
				break;
			default:
				foreach ( $images as $image ) {

					$image['display_src'] = preg_replace( "/^http:/i", "", $image['display_src'] );

					if ( $image['is_video']  == true ) {
						$type = 'video';
					} else {
						$type = 'image';
					}

					$instagram[] = array(
						'description'   => __( 'Instagram Image', 'dawnthemes' ),
						'link'		  	=> '//instagram.com/p/' . $image['code'],
						'time'		  	=> $image['date'],
						'comments'	  	=> $image['comments']['count'],
						'likes'		 	=> $image['likes']['count'],
						'thumbnail'	 	=> $image['display_src'],
						'type'		  	=> $type
					);
				}
				break;
		}
			
		// do not set an empty transient - should help catch private or empty accounts
		if ( ! empty( $instagram ) ) {
			$instagram = base64_encode( serialize( $instagram ) );
			set_transient( 'instagram-'.sanitize_title_with_dashes( $username ), $instagram, apply_filters( 'dt_instagram_cache_time', HOUR_IN_SECONDS*absint($refresh_hour) ) );
		}
	}




	if ( ! empty( $instagram ) ) {

		$instagram = unserialize( base64_decode( $instagram ) );
		$images_data =  array_slice( $instagram, 0, $images_number );
		return $images_data;
	}
	return new WP_Error( 'bad_json', __( 'Instagram has returned invalid data.', 'dawnthemes' ) );;
}

function dt_print_string($string=''){
	$allowedtags = array(
		'div'=>array(
			'class'=>array(),
		),
		'a' => array(
			'href' => array(),
			'target' => array(),
			'title' => array(),
			'rel' => array(),
		),
		'img' => array(
			'src' => array()
		),
		'h1' => array(),
		'h2' => array(),
		'h3' => array(),
		'h4' => array(),
		'h5' => array(),
		'p' => array(),
		'br' => array(),
		'hr' => array(),
		'span' => array(
			'class'=>array()
		),
		'em' => array(),
		'strong' => array(),
		'small' => array(),
		'b' => array(),
		'i' => array(
			'class'=>array()
		),
		'u' => array(),
		'ul' => array(),
		'ol' => array(),
		'li' => array(),
		'blockquote' => array(),
	);
	$allowedtags = apply_filters('dt_print_string_allowed_tags', $allowedtags);
	//$string = wp_kses($string, $allowedtags);
	return $string;
}

function dt_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}
	// Look within passed path within the theme - this is priority
	$located = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);
	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_name ), '2.1' );
		return;
	}
	$located = apply_filters( 'dt_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'dt_before_get_template', $template_name, $template_path, $located, $args );
	include( $located );
	do_action( 'dt_after_get_template', $template_name, $template_path, $located, $args );
}

function dt_get_main_class($body = false){
	
	$class = dt_get_main_col_class('',$body);
	
	if (is_home() || is_front_page()){
		$layout =  dt_get_theme_option('blog-layout','right-sidebar');
		$class = dt_get_main_col_class($layout,$body);
	}elseif (is_post_type_archive( 'portfolio' ) || is_tax( 'portfolio_category' ) ){
		$layout =  dt_get_theme_option('portfolio-main-layout','full-width');
		$class = dt_get_main_col_class($layout,$body);
	}elseif (is_archive() || is_search()) {
		$layout = dt_get_theme_option('archive-layout', 'full-width');
		$class = dt_get_main_col_class($layout,$body);
	}elseif (is_singular('portfolio')){
		$layout = dt_get_theme_option('portfolio-single-layout', 'full-width');
		$class = dt_get_main_col_class($layout,$body);
	}elseif (is_single()){
		$layout = dt_get_theme_option('single-layout', 'right-sidebar');
		$class  =  dt_get_main_col_class($layout,$body);
	}else{
		$layout =  dt_get_theme_option('main-layout','right-sidebar');
		$class = dt_get_main_col_class($layout,$body);
	}
	
	if(defined('WOOCOMMERCE_VERSION')){
		if(is_shop())
		{
			remove_action('dt_left_sidebar','dt_left_sidebar');
			remove_action('dt_left_sidebar_extra','dt_get_extra_sidebar');
			remove_action('dt_right_sidebar','dt_get_sidebar');
			remove_action('dt_right_sidebar_extra','dt_get_extra_sidebar');
			$layout = dt_get_theme_option('woo-shop-layout','full-width');
			$class = dt_get_main_col_class($layout,$body,11);
		}
		elseif (is_product_category() || is_product_tag() || is_product_taxonomy())
		{
			remove_action('dt_left_sidebar','dt_left_sidebar');
			remove_action('dt_left_sidebar_extra','dt_get_extra_sidebar');
			remove_action('dt_right_sidebar','dt_get_sidebar');
			remove_action('dt_right_sidebar_extra','dt_get_extra_sidebar');
			$layout = dt_get_theme_option('woo-category-layout','right-sidebar');
			$class = dt_get_main_col_class($layout,$body,11);
		}
		elseif (is_product())
		{
			remove_action('dt_left_sidebar','dt_left_sidebar');
			remove_action('dt_left_sidebar_extra','dt_get_extra_sidebar');
			remove_action('dt_right_sidebar','dt_get_sidebar');
			remove_action('dt_right_sidebar_extra','dt_get_extra_sidebar');
			$layout =  dt_get_theme_option('woo-product-layout','full-width');
			$class = dt_get_main_col_class($layout,$body,11);
		}
		
	}
	
	if($body)
		return $class;
	
	$class .=' main-wrap';
	$class =  apply_filters('dt_get_main_class',$class);
	return esc_attr($class);
}

function dt_get_sidebar(){
	get_sidebar();
}

function dt_get_extra_sidebar(){
	get_sidebar('extra');
}

function dt_get_main_col_class($layout='',$body = false,$priority = 10){
	$col_class = 'col-md-12';
	if(empty($col_class))
		return $col_class;
	if(!$body){
		if($layout == 'full-width'){
			$col_class = 'col-md-12';
		}elseif ($layout == 'left-sidebar'){
			$col_class = 'col-md-8';
			//add_action('dt_left_sidebar','dt_get_sidebar',$priority);
			add_action('dt_right_sidebar','dt_get_sidebar',$priority);
		}elseif ($layout == 'right-sidebar'){
			$col_class = 'col-md-8';
			add_action('dt_right_sidebar','dt_get_sidebar',$priority);
		}
	}
	if($body){
		if(empty($layout))
			$layout = 'fullwidth';
		
		return 'page-layout-'.$layout;
	}
	return $col_class;
}

function dt_container_class(){
	$main_layout = dt_get_theme_option('site-layout','wide');
	$container_class = 'container'; 
	if($main_layout == 'wide'){
		$wide_container = dt_get_theme_option('wide-container','fixedwidth');
		if($wide_container == 'fullwidth'):
			if((is_post_type_archive( 'portfolio' ) || is_tax( 'portfolio_category' )) && (dt_get_theme_option('portfolio-gap',1) != '1'))
				$container_class = 'container-full';
			else
				$container_class = 'container-fluid';
		endif;
	}
	$container_class = apply_filters('dt_container_class', $container_class);
	echo esc_attr($container_class);
}

function dt_social($use = array(),$hover = true,$soild_bg=false,$outlined=false){
	$socials = apply_filters('dt_social',array(
		'facebook'=>array(
				'label'=>esc_html__('Facebook','wozine'),
				'url'=>dt_get_theme_option('facebook-url')
		),
		'twitter'=>array(
				'label'=>esc_html__('Twitter','wozine'),
				'url'=>dt_get_theme_option('twitter-url')
		),
		'google-plus'=>array(
				'label'=>esc_html__('Google+','wozine'),
				'url'=>dt_get_theme_option('google-plus-url')
		),
		'pinterest'=>array(
				'label'=>esc_html__('Pinterest','wozine'),
				'url'=>dt_get_theme_option('pinterest-url')
		),
		'linkedin'=>array(
				'label'=>esc_html__('LinkedIn','wozine'),
				'url'=>dt_get_theme_option('linkedin-url')
		),
		'rss'=>array(
				'label'=>esc_html__('RSS','wozine'),
				'url'=>dt_get_theme_option('rss-url')
		),
		'instagram'=>array(
				'label'=>esc_html__('Instagram','wozine'),
				'url'=>dt_get_theme_option('instagram-url')
		),
		'github'=>array(
				'label'=>esc_html__('GitHub','wozine'),
				'url'=>dt_get_theme_option('github-url')
		),
		'behance'=>array(
				'label'=>esc_html__('Behance','wozine'),
				'url'=>dt_get_theme_option('behance-url')
		),
		'stack-exchange'=>array(
				'label'=>esc_html__('StackExchange','wozine'),
				'url'=>dt_get_theme_option('stack-exchange-url')
		),
		'tumblr'=>array(
				'label'=>esc_html__('Tumblr','wozine'),
				'url'=>dt_get_theme_option('tumblr-url')
		),
		'soundcloud'=>array(
				'label'=>esc_html__('SoundCloud','wozine'),
				'url'=>dt_get_theme_option('soundcloud-url')
		),
		'dribbble'=>array(
				'label'=>esc_html__('Dribbble','wozine'),
				'url'=>dt_get_theme_option('dribbble-url')
		),
				
	));
	echo '<div class="dt-socials-list">';
	foreach ((array)$socials  as $social=>$data):
		if(in_array($social, $use)):
			if(empty($data['url']))
				$data['url'] = '#';
			echo '<div class="dt-socials-item '.$social.'">';
			echo '<a class="dt-socials-item-link" href="'.esc_url($data['url']).'" title="'.esc_attr($data['label']).'" ><i class="fa fa-'.$social.' '.($hover ? $social.'-bg-hover':'').' '.($soild_bg ? $social.'-bg':'').' '.($outlined ? $social.'-outlined':'').'"></i></a>';
			echo '</div>';
		endif;
	endforeach;
	echo '</div>';
	return ;
}

function dt_enqueue_google_font(){
	$protocol = is_ssl() ? 'https' : 'http';
	$typography_arr = array('body-typography','navbar-typography','h1-typography','h2-typography','h3-typography','h4-typography','h5-typography','h6-typography');
	foreach ($typography_arr as $font){
		$typography = dt_get_theme_option($font);
		if(!empty($typography['font-family'])){
			$font_family = str_replace(" ", "+", $typography['font-family']);
			$font_style = '400';
			if(!empty($typography['font-style'])){
				$font_style = $typography['font-style'];
			}
			$subset = "";
			if(!empty($typography['subset'])  && $typography['subset'] !=="latin"){
				$subset = "&subset=".$typography['subset'];
			}
			wp_enqueue_style( 'dt-'.sanitize_title($font_family).'-'.$font_style, "$protocol://fonts.googleapis.com/css?family=$font_family:$font_style$subset",false);
		}
	}
}

function dt_get_protocol(){
	return  is_ssl() ? 'https' : 'http';
}

function dt_share($title='',$facebook = true,$twitter = true,$google = true,$pinterest = true,$linkedin = true,$outlined=false){
?>
	<div class="share-links">
		<?php if(!empty($title)):?>
		<h4><?php echo esc_html($title)?></h4>
		<?php endif;?>
		<div class="share-icons">
			<?php if($facebook):?>
			<span class="facebook-share">
				<a href="<?php echo esc_url('http://www.facebook.com/sharer.php?u='.get_the_permalink()) ?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=220,width=600');return false;" title="<?php echo esc_html_e('Facebook','wozine')?>"><i class="fa fa-facebook<?php echo ($outlined ? ' facebook-outlined':'')?>"></i></a>
			</span>
			<?php endif;?>
			<?php if($twitter):?>
			<span  class="twitter-share">
				<a href="<?php echo esc_url('https://twitter.com/share?url='.get_the_permalink()) ?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=260,width=600');return false;" title="<?php echo esc_html_e('Twitter','wozine')?>"><i class="fa fa-twitter<?php echo ($outlined ? ' twitter-outlined':'')?>"></i></a>
			</span>
			<?php endif;?>
			<?php if($google):?>
			<span class="google-plus-share">
				<a href="<?php echo esc_url('https://plus.google.com/share?url='.get_the_permalink()) ?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" title="<?php echo esc_html_e('Google +','wozine')?>"><i class="fa fa-google-plus<?php echo ($outlined ? ' google-plus-outlined':'')?>"></i></a>
			</span>
			<?php endif;?>
			<?php if($pinterest):?>
			<span class="pinterest-share">
				<a href="<?php echo esc_url('http://pinterest.com/pin/create/button/?url='.get_the_permalink().'&media='.(function_exists('the_post_thumbnail') ? wp_get_attachment_url(get_post_thumbnail_id()):'').'&description='.get_the_title()) ?>" title="<?php echo esc_html_e('pinterest','wozine')?>"><i class="fa fa-pinterest<?php echo ($outlined ? ' pinterest-outlined':'')?>"></i></a>
			</span>
			<?php endif;?>
			<?php if($linkedin):?>
			<span class="linkedin-share">
				<a href="<?php echo esc_url('http://www.linkedin.com/shareArticle?mini=true&url='.get_the_permalink().'&title='.get_the_title())?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" title="<?php echo esc_html_e('Linked In','wozine')?>"><i class="fa fa-linkedin<?php echo ($outlined ? ' linkedin-outlined':'')?>"></i></a>
			</span>
			<?php endif;?>
		</div>
	</div>
<?php
}

function dt_get_post_meta($meta = '',$post_id='',$default=null){
	$post_id = empty($post_id) ? get_the_ID() : $post_id;
	if(defined('WOOCOMMERCE_VERSION')){
		if(is_shop())
			$post_id = wc_get_page_id( 'shop' );
		elseif (is_cart())
			$post_id = wc_get_page_id( 'cart' );
		elseif (is_checkout())
			$post_id = wc_get_page_id( 'checkout' );
		elseif (is_account_page())
			$post_id = wc_get_page_id( 'myaccount' );
		elseif (is_order_received_page())
			$post_id = wc_get_page_id( 'checkout' );
		elseif (is_add_payment_method_page())
			$post_id = wc_get_page_id( 'myaccount' );	
	}
	if(is_search()){
		$post_id = 0;
	}
	if(empty($meta))
		return false;
	$value = get_post_meta($post_id,'_dt_'.$meta, true);
	if($value !== '' && $value !== null && $value !== array() && $value !== false)
		return apply_filters('dt_get_post_meta', $value, $meta, $post_id);
	return $default;
}

function dt_highlighted_post($cats = null,$cats_extra=null,$post_type='post'){
	$args = array(
		'meta_key' => '_dt_featured',
		'meta_value' => '1', 
		'posts_per_page' => 5,
		'post_type'=>$post_type,
		'ignore_sticky_posts' => 1,
		'orderby' => 'date',
		'order' => 'DESC',
	);
	if(!empty($cats)):
		$args['cat'] = $cats;
	endif;
	$extra = '';
	$r = new WP_Query($args);
	if($r->have_posts()):
	?>
	<div class="highlighted">
		<div class="container">
			<div class="row">
				<div class="col-md-8 col-sm-8">
					<div class="caroufredsel" data-infinite="1" data-height="variable" data-responsive="1" data-speed="7000" data-autoplay="1" data-scroll-fx="crossfade" data-visible="1">
						<div class="caroufredsel-wrap">
							<ul class="caroufredsel-items">
							<?php while ($r->have_posts()):$r->the_post(); global $post;?>
								<?php if(!has_post_thumbnail()) continue;?>
								<li class="caroufredsel-item">
									<?php 
									$post_format = get_post_format();
									$post_format_icon='';
									if($post_format == 'video')
										$post_format_icon = '<i class="highlighted-format-icon format-video"></i>';
									elseif ($post_format == 'gallery')
										$post_format_icon = '<i class="highlighted-format-icon format-gallery"></i>';
									echo dt_echo($post_format_icon);
									?>
									<a href="<?php the_permalink();?>">
										<?php the_post_thumbnail('dt-thumbnail')?>
									</a>
									<div class="highlighted-caption">
										<time datetime="<?php echo get_the_date('Y-m-d\TH:i:sP')?>"><?php echo get_the_date('M j, Y') ?></time>
										<h3>
											<a href="<?php the_permalink()?>"><?php the_title()?></a>
										</h3>
									</div>
								</li>
							<?php endwhile;?>
							</ul>
							<a class="caroufredsel-prev" href="#" style="display: block;"></a>
							<a class="caroufredsel-next" href="#" style="display: block;"></a>
						</div>
						<div class="caroufredsel-pagination"></div>
					</div>
				</div>
				<div class="col-md-4 col-sm-4">
					<?php if(!empty($cats_extra)):?>
						<?php 
						wp_reset_query();
						if(!empty($cats_extra))
							$args['cat'] = $cats_extra;
						
						$args['posts_per_page'] = 3;
						?>
						<?php 
						$e_r = new WP_Query($args);
						if($e_r->have_posts()):
						?>
						<div class="highlighted-extra">
							<?php $i=0;?>
							<?php while ($e_r->have_posts()):$e_r->the_post();global $post;?>
								<?php if(!has_post_thumbnail()) continue;?>
							<article class="highlighted-extra-item<?php echo(($i==0)?' large':' small') ?>">
								<div class="highlighted-extra-item-wrap">
									<?php 
									$post_format_icon='';
									$post_format = get_post_format();
									if($post_format == 'video')
										$post_format_icon = '<i class="highlighted-format-icon format-video"></i>';
									elseif ($post_format == 'gallery')
										$post_format_icon = '<i class="highlighted-format-icon format-gallery"></i>';
									echo dt_echo($post_format_icon);
									?>
									<a href="<?php the_permalink();?>">
										<?php the_post_thumbnail('dt-thumbnail')?>
									</a>
									<div class="highlighted-caption">
										<time datetime="<?php echo get_the_date('Y-m-d\TH:i:sP') ?>"><?php echo get_the_date('M j, Y') ?></time>
										<h3>
											<a href="<?php the_permalink()?>"><?php the_title()?></a>
										</h3>
									</div>
								</div>
							</article>
							<?php $i++?>
							<?php endwhile;?>
						</div>
						<?php 
						endif;
						wp_reset_query();
						?>
					<?php endif;?>
				</div>
			</div>
		</div>
	</div>
	<?php
	endif;
	wp_reset_query();
}

function dt_related_post(){
	global $post;
	$categories = get_the_category($post->ID);
	
	if (!$categories) {
		return;
	}
	
	$args = array(
		'posts_per_page' => 3,
		'post__not_in' => array($post->ID),
		'orderby' => 'rand', //random posts
		'meta_key' => "_thumbnail_id",
        'category__in' => wp_get_post_categories($post->ID)
	);

	$related = new WP_Query($args);
?>
<?php if($related->have_posts()): ?>
<div class="related-post">
	<div class="related-post-title">
		<h3><span><?php echo esc_html_e("You may also like",'wozine')?></span></h3>
	</div>
	<div class="row related-post-items">
		<?php while ($related->have_posts()): $related->the_post();global $post;?>
			<div class="related-post-item col-md-4 col-sm-6">
				<?php dt_post_featured('','',false,true);?>
				<div class="entry-meta top-meta">
				<?php dt_post_meta(true,false,false,false,true,', ')?>
				</div>
				<h4 class="post-title" data-itemprop="name"><a href="<?php the_permalink();?>"><?php the_title();?></a></h4>
				<div class="excerpt">
				<?php 
					$excerpt = $post->post_excerpt;
					if(empty($excerpt))
						$excerpt = $post->post_content;
						
					$excerpt = strip_shortcodes($excerpt);
					$excerpt = wp_trim_words($excerpt,15,'...');
					echo  '<p>' . $excerpt . '</p>';
				?>
				</div>
				<div class="entry-meta bottom-meta">
				<?php dt_post_meta(false,true,false,true,true,', ')?>
				</div>
				<div class="readmore-link">
					<a href="<?php the_permalink()?>"><?php esc_html_e("Read More", 'wozine');?></a>
				</div>
			</div>
		<?php endwhile;?>
	</div>
</div>
	<?php endif;?>
<?php
	
	wp_reset_query();
}

function dt_nth_word($text, $nth = 1, $echo = true,$is_typed = false,$typed_color = ''){
	$text = strip_shortcodes($text);
	$text = wp_strip_all_tags( $text );
	if ( 'characters' == _x( 'words', 'word count: words or characters?','wozine') && preg_match( '/^utf\-?8$/i', get_option( 'blog_charset' ) ) ) {
		$text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $text ), ' ' );
		preg_match_all( '/./u', $text, $words_array );
		$sep = '';
	} else {
		$words_array = preg_split( "/[\n\r\t ]+/", $text, null, PREG_SPLIT_NO_EMPTY );
		$sep = ' ';
	}
	$nth_class=$nth;
	if($nth == 'last')
		$nth = count($words_array) - 1;
	if($nth == 'first')
		$nth = 0;
	
	if(isset($words_array[$nth]) && !$is_typed){
		$words_array[$nth] = '<span class="nth-word-'.$nth_class.'">'.$words_array[$nth].'</span>';
	}
	if($is_typed){
		$string =  $words_array[$nth];
		$words_array[$nth] = '<span'.(!empty($typed_color) ? ' style="color:'.$typed_color.'" ' :'').'><span class="nth-typed"></span></span>';
		return array(implode($sep, $words_array),$string);
	}
	if($echo)
		echo implode($sep, $words_array);
	else 
		return implode($sep, $words_array);
}

function dt_trim_characters($string, $count=50, $ellipsis = FALSE)
{
	$trimstring = substr($string,0,$count);
	if (strlen($string) > $count) {
		if (is_string($ellipsis)){
			$trimstring .= $ellipsis;
		}
		elseif ($ellipsis){
			$trimstring .= '&hellip;';
		}
	}
	return $trimstring;
}


function dt_post_nav() {
	global $post;

	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous )
		return;
	?>
	<nav class="post-navigation" role="navigation">
		<div class="row">
			<?php $prev_link = get_previous_post_link( '%link', _x( '%title', 'Previous post link', 'wozine' ) ); ?>
			<div class="col-sm-6">
			<?php if($prev_link):?>
				<div class="prev-post">
					<span>
					<?php echo esc_html_e('Previous article','wozine')?>
					</span>
					<?php echo dt_echo($prev_link)?>
				</div>
			<?php endif;?>
			</div>
			<?php $next_link = get_next_post_link( '%link', _x( '%title', 'Next post link', 'wozine' ) ); ?>
			<div class="col-sm-6">
			<?php if(!empty($next_link)):?>
				<div class="next-post">
					<span>
						<?php echo esc_html_e('Next article','wozine')?>
					</span>
					<?php echo dt_echo($next_link)?>
				</div>
			<?php endif;?>
			</div>
		</div>
	</nav>
	<?php
}

function dt_the_breadcrumb(){
	if( ( defined('WOOCOMMERCE_VERSION') && is_woocommerce() ) || ( is_tax( 'product_cat' ) || is_tax( 'product_tag' ) ) ) {
		woocommerce_breadcrumb(array(
			'wrap_before' => '<ul class="breadcrumb" prefix="v: http://rdf.data-vocabulary.org/#">',
			'wrap_after' => '</ul>',
			'before' => '<li>',
			'after' => '</li>',
			'delimiter' => ''
		));
	}else{
		echo dt_get_breadcrumb();
	}
}

function dt_get_breadcrumb($args = array()){
	return apply_filters('dt_get_breadcrumb', false,$args);
}

function dt_page_title($echo = true){
	$title = "";
	
	if ( is_category() )
	{
		$title = single_cat_title('',false);
	}
	elseif (is_day())
	{
		$title = esc_html_e('Archive for date:','wozine')." ".get_the_time('F jS, Y');
	}
	elseif (is_month())
	{
		$title = esc_html_e('Archive for month:','wozine')." ".get_the_time('F, Y');
	}
	elseif (is_year())
	{
		$title = esc_html_e('Archive for year:','wozine')." ".get_the_time('Y');
	}
	elseif (is_search())
	{
		global $wp_query;
		if(!empty($wp_query->found_posts))
		{
			if($wp_query->found_posts > 1)
			{
				$title =  $wp_query->found_posts ." ". esc_html_e('search results for','wozine').' <span class="search-query">'.esc_attr( get_search_query() ).'</span>';
			}
			else
			{
				$title =  $wp_query->found_posts ." ". esc_html_e('search result for','wozine').' <span class="search-query">'.esc_attr( get_search_query() ).'</span>';
			}
		}
		else
		{
			if(!empty($_GET['s']))
			{
				$title = esc_html_e('Search results for','wozine').' <span class="search-query">'.esc_attr( get_search_query() ).'</span>';
			}
			else
			{
				$title = esc_html_e('To search the site please enter a valid term','wozine');
			}
		}
	
	}
	elseif (is_author())
	{
		$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
	
		if(isset($curauth->nickname)) $title = $curauth->nickname;
	
	}
	elseif (is_tag())
	{
		$title =single_tag_title('',false);
	}
	elseif(is_tax())
	{
		$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		$title = $term->name;
	
	} elseif ( is_front_page() && !is_home() ) {
	    $title = get_the_title(get_option('page_on_front'));
	    
	} elseif ( is_home() && !is_front_page() ) {
	    $title = get_the_title(get_option('page_for_posts'));
	    
	} elseif ( is_404() ) {
	    $title = esc_html_e('404 - Page not found','wozine');
	}
	else {
		$title = get_the_title();
	}
	
	if (isset($_GET['paged']) && !empty($_GET['paged']))
	{
		$title .= " (".esc_html_e('Page','wozine')." ".$_GET['paged'].")";
	}

	if( defined('WOOCOMMERCE_VERSION') && is_woocommerce() && ( is_product() || is_shop() ) && !is_search() ) {
// 		if( ! is_product() ) {
// 			$title = woocommerce_page_title( false );
// 		}else{
// 			$title = 
// 		}
		$title = woocommerce_page_title( false );
	}
	if(is_post_type_archive( 'portfolio' )){
		$title = esc_html(dt_get_theme_option('portfolio-archive-title',esc_html_e('My Portfolio','wozine')));
	}
	if($echo)
		echo dt_echo($title);
	else
		return $title;
}

function dt_portfolio_featured($post_id='',$post_format='',$is_shortcode = false,$hide_action = false,$layout = '',$link_to_project_url = false){
	$post_id  = empty($post_id) ? get_the_ID() : $post_id;
	$format = dt_get_post_meta('portfolio_format');
	$post_id  = empty($post_id) ? get_the_ID() : $post_id;
	$post_format = $format;
	$thumb_size = !is_singular('portfolio') || $is_shortcode ? 'regular' : 'dt-full';
	if(dt_get_post_meta('masonry_size',$post_id,'normal') === 'double'):
		$thumb_size = 'wide';
	elseif (dt_get_post_meta('masonry_size',$post_id,'normal') === 'tall'):
		$thumb_size = 'tall';
	elseif (dt_get_post_meta('masonry_size',$post_id,'normal') === 'wide_tall'):
		$thumb_size = 'wide_tall';
	endif;
	if(is_singular('portfolio')){
		$thumb_size = 'dt-full';
	}
	if($layout == 'wall'){
		$thumb_size = 'dt-thumbnail';
	}
	if($layout == 'grid'){
		$thumb_size = 'regular';
	}
	
	$thumb_size = apply_filters('dt_portfolio_featured_thumbnail_size', $thumb_size,$post_id,$layout);
	
	$featured_class = !empty($post_format) ? ' '.$post_format.'-featured' : '';
	$view_action = get_the_permalink();
	$target='';
	if($link_to_project_url && ($project_url = dt_get_post_meta('url'))){
		$view_action = $project_url;
		$target = '  target="_blank"';
	}
	if($post_format == 'gallery'){
		$gallery_ids = explode(',',dt_get_post_meta('gallery'));
		$gallery_ids = array_filter($gallery_ids);
		if(!empty($gallery_ids) && is_array($gallery_ids)):
		?>
		<div class="portfolio-featured<?php echo esc_attr($featured_class) ?>">
			<div class="caroufredsel" data-visible="1" data-responsive="1" data-infinite="1" data-autoplay="0">
				<div class="caroufredsel-wrap">
					<ul class="caroufredsel-items">
						<?php foreach ($gallery_ids as $id):?>
							<?php if($id):?>
							<?php 
							$image = wp_get_attachment_image_src($id,'dt-full');
							?>
							<li class="caroufredsel-item">
								<a href="<?php echo @$image[0] ?>" title="<?php echo get_the_title($id)?>" data-rel="magnific-popup">
									<?php echo wp_get_attachment_image($id,$thumb_size)?>
								</a>
							</li>
							<?php endif;?>
						<?php endforeach;?>
					</ul>
					<a href="#" class="caroufredsel-prev"></a>
					<a href="#" class="caroufredsel-next"></a>
					
				</div>
			</div>
		</div>
		<?php if(!$hide_action):?>
		<div class="portfolio-action">
			<a class="zoom-action" href="#"><i class="fa fa-search"></i></a>
			<a class="view-action" href="<?php echo esc_url($view_action)?>" <?php echo ($target) ?>><i class="fa fa-link"></i></a>
		</div>
		<div class="portfolio-overlay"></div>
		<?php endif;?>
		<?php
		endif;
	}elseif ($post_format == 'video'){
		$video_args = array();
		if($mp4 = dt_get_post_meta('video_mp4'))
			$video_args['mp4'] = $mp4;
		if ( $ogv = dt_get_post_meta('video_ogv') )
			$video_args['ogv'] = $ogv;
		if($webm = dt_get_post_meta('video_webm'))
			$video_args['webm'] = $webm;
		
		$video_poster = get_post_thumbnail_id($post_id);
		$poster='';
		if(has_post_thumbnail()){
			$post_thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),$thumb_size);
			$poster_attr = ' poster="' . esc_url(@$post_thumb[0]) . '"';
			if(!is_singular() || $is_shortcode)
				$poster = @$post_thumb[0];
		}
		
		if(!empty($video_poster)){
			$poster_image = wp_get_attachment_image_src($video_poster, $thumb_size);
			$poster_attr = ' poster="' . esc_url(@$poster_image[0]) . '"';
			if(!is_singular() || $is_shortcode)
				$poster = @$poster_image[0];
		}
		
		
		if(!empty($video_args)):
			$video_html = '<div id="video-featured-'.$post_id.'" class="video-embed-wrap'.(!empty($poster) ? ' mfp-hide':'').'">';
			$video = '<video controls="controls" '.$poster_attr.' preload="0" class="video-embed'.(!empty($poster) ? ' video-embed-popup':'').'">';
			$source = '<source type="%s" src="%s" />';
			foreach ( $video_args as $video_type => $video_src ) {
				$video_type = wp_check_filetype( $video_src, wp_get_mime_types() );
				$video .= sprintf( $source, $video_type['type'], esc_url( $video_src ) );
			}
			$video .= '</video>';
			$video_html .=$video;
			$video_html .='</div>';
			echo '<div class="portfolio-featured'.$featured_class.'">';
				if(!empty($poster)){
					echo '<div class="video-poster"><img alt="'.get_the_title().'" src="'.$poster.'"></div>';
				}
				echo dt_echo($video_html);
			echo '</div>';
			if(!$hide_action):
			echo '<div class="portfolio-action">';
			echo '<a class="zoom-action" data-video-inline="'.esc_attr($video).'" href="#video-featured-'.$post_id.'" data-rel="magnific-portfolio-video"><i class="fa fa-play"></i></a>';
			echo '<a class="view-action" href="'.$view_action.'" '.$target.'><i class="fa fa-link"></i></a>';
			echo '</div>';
			echo '<div class="portfolio-overlay"></div>';
			endif;
		elseif($embed = dt_get_post_meta('video_embed')):
			if(!empty($embed)){
				echo '<div class="portfolio-featured '.$post_format.'-featured">';
				
				echo '<div id="embed-featured-'.$post_id.'" class="embed-wrap'.(!empty($poster) ? ' mfp-hide':'').'">';
				echo apply_filters('dt_embed_video', $embed); 
				echo '</div>';
				if(!empty($poster)){
					echo '<div class="video-poster"><img alt="'.get_the_title().'" src="'.$poster.'"></div>';
				}
				echo '</div>';
				if(!$hide_action):
				echo '<div class="portfolio-action">';
				echo '<a class="zoom-action" href="#embed-featured-'.$post_id.'" data-rel="magnific-portfolio-video"><i class="fa fa-play"></i></a>';
				echo '<a class="view-action" href="'.$view_action.'"'.$target.'><i class="fa fa-link"></i></a>';
				echo '</div>';
				echo '<div class="portfolio-overlay"></div>';
				endif;
			}
		endif;
		
		
		
	}elseif (has_post_thumbnail()){
		$thumb_img = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),'dt-full');
		$thumb = wp_get_attachment_image(get_post_thumbnail_id($post_id), $thumb_size);
		echo '<div class="portfolio-featured'.$featured_class.'">';
		if(!is_singular() || $is_shortcode){
			echo '<a href="'.get_the_permalink().'">'.$thumb.'</a>';
		}else{
			echo dt_echo($thumb);
		}
		echo '</div>';
		if(!$hide_action):
		echo '<div class="portfolio-action">';
		echo '<a class="zoom-action" href="'.esc_url($thumb_img[0]).'" title="'.esc_attr(get_the_title(get_post_thumbnail_id($post_id))).'" data-rel="magnific-single-popup"><i class="fa fa-search"></i></a>';
		echo '<a class="view-action" href="'.$view_action.'"'.$target.'><i class="fa fa-link"></i></a>';
		echo '</div>';
		echo '<div class="portfolio-overlay"></div>';
		endif;
	}
	return;
}

function dt_post_featured($post_id='',$post_format='',$is_shortcode = false,$is_related = false,$entry_featured_class = '',$layout = ''){
	$post_id  = empty($post_id) ? get_the_ID() : $post_id;
	$post_format = empty($post_format) ? get_post_format() : $post_format;
	$thumb_size = !is_singular() || $is_shortcode || $is_related ? 'dt-thumbnail' : 'dt-full';
	
	switch ($layout){
		case 'grid':
			$thumb_size = 'wozine-blog-grid';
			break;
		case 'classic':
			$thumb_size = 'wozine-post-thumbnails';
			break;
		case 'masonry':
			$thumb_size = 'dt-full';
			break;
		default:
			$thumb_size = 'wozine-post-thumbnails';
			break;
	}
	
	$thumb_size = apply_filters('dt_post_featured_thumbnail_size', $thumb_size,$post_id);
	$featured_class = !empty($post_format) ? ' '.$post_format.'-featured' : '';
	
	if($is_related){
		if(has_post_thumbnail()){
			$thumb = get_the_post_thumbnail($post_id,$thumb_size,array('itemprop'=>'image'));
			echo '<div class="entry-featured'.$featured_class.'">';
			echo '<a href="'.get_the_permalink().'" title="'.esc_attr(get_the_title(get_post_thumbnail_id($post_id))).'">'.$thumb.'</a>';
			echo '</div>';
		}
	}else{
		if($post_format == 'gallery'){
			$gallery_ids = explode(',',dt_get_post_meta('gallery'));
			$gallery_ids = array_filter($gallery_ids);
			if(!empty($gallery_ids) && is_array($gallery_ids)):
			wp_enqueue_style('slick');
			wp_enqueue_script('slick');
			?>
			<div class="entry-featured<?php echo esc_attr($featured_class) ?><?php echo ' '.$entry_featured_class?>">
				<?php $data_items = ($layout == 'grid' || $layout == 'masonry') ? 1 : 2; ?>
				<div class="dt-slick-slider dt-preload" data-visible="<?php echo esc_attr($data_items)?>" data-scroll="<?php echo esc_attr($data_items)?>" data-infinite="true" data-autoplay="false" data-dots="false">
					<div class="dt-slick-wrap">
						<div class="dt-slick-items">
							<?php foreach ($gallery_ids as $id):?>
								<?php if($id):?>
								<?php 
								$image = wp_get_attachment_image_src($id,'wozine-blog-gallery');
								?>
								<div class="dt-slick-item">
									<div class="dt-slide-img">
										<a href="<?php echo @$image[0] ?>" title="<?php echo get_the_title($id)?>" data-rel="magnific-popup">
											<?php echo wp_get_attachment_image($id,'wozine-blog-gallery');?>
										</a>
									</div>
								</div>
								<?php endif;?>
							<?php endforeach;?>
						</div>
					</div>
				</div>
			</div>
			<?php
			endif;
		}elseif ($post_format == 'video'){
			wp_enqueue_style( 'mediaelement' );
			wp_enqueue_script('mediaelement');
			if(is_single()){
				$video_args = array();
				if($mp4 = dt_get_post_meta('video_mp4'))
					$video_args['mp4'] = $mp4;
				if ( $ogv = dt_get_post_meta('video_ogv') )
					$video_args['ogv'] = $ogv;
				if($webm = dt_get_post_meta('video_webm'))
					$video_args['webm'] = $webm;
				
				$poster = dt_get_post_meta('video_poster');
				$poster_attr='';
				
				if(has_post_thumbnail()){
					$post_thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),$thumb_size);
					$poster_attr = ' poster="' . esc_url(@$post_thumb[0]) . '"';
				}
				
				if(!empty($poster)){
					$poster_image = wp_get_attachment_image_src($poster, $thumb_size);
					$poster_attr = ' poster="' . esc_url(@$poster_image[0]) . '"';
				}
				
				
				if(!empty($video_args)):
					$video = '<div id="video-featured-'.$post_id.'" class="video-embed-wrap"><video controls="controls" '.$poster_attr.' preload="0" class="video-embed">';
					$source = '<source type="%s" src="%s" />';
					foreach ( $video_args as $video_type => $video_src ) {
						$video_type = wp_check_filetype( $video_src, wp_get_mime_types() );
						$video .= sprintf( $source, $video_type['type'], esc_url( $video_src ) );
					}
					$video .= '</video></div>';
					echo '<div class="entry-featured'.$featured_class.'">';
						echo dt_echo($video);
					echo '</div>';
				elseif($embed = dt_get_post_meta('video_embed')):
					if(!empty($embed)):
						echo '<div class="entry-featured '.$post_format.'-featured '.$entry_featured_class.'">';
						echo '<div id="video-featured-'.$post_id.'" class="embed-wrap">';
						echo apply_filters('dt_embed_video', $embed); 
						echo '</div>';
						echo '</div>';
					endif;
				elseif (has_post_thumbnail()):
					$thumb = get_the_post_thumbnail($post_id,$thumb_size,array('data-itemprop'=>'image'));
					echo '<div class="entry-featured post-thumbnail'.$featured_class.' '.$entry_featured_class.'">';
					if(!is_singular() || $is_shortcode){
						echo '<a class="dt-image-link" href="'.get_the_permalink().'" title="'.esc_attr(get_the_title(get_post_thumbnail_id($post_id))).'">'.$thumb.'</a>';
					}else{
						echo dt_echo($thumb);
					}
					echo '</div>';
				endif;
			}else{
				if(has_post_thumbnail()){
					$thumb = get_the_post_thumbnail($post_id,$thumb_size,array('data-itemprop'=>'image'));
				}else{
					$thumb = '<img src="'.get_template_directory_uri().'/assets/images/no-thumb_700x350.png" alt="'.get_the_title().'">';
				}
				echo '<div class="entry-featured'.$featured_class.' '.$entry_featured_class.'">';
				echo '<a class="dt-image-link" href="'.get_the_permalink().'" title="'.esc_attr(get_the_title(get_post_thumbnail_id($post_id))).'">'.$thumb.'<i class="fa fa-play" aria-hidden="true"></i></a>';
				echo '</div>';
			}
		}elseif ($post_format == 'audio'){
			$audio_args = array();
			
			if($embed = dt_get_post_meta('audio_embed'))
				$audio_args['embed'] = $embed;
			
			if($mp3 = dt_get_post_meta('audio_mp3'))
				$audio_args['mp3'] = $mp3;
			
			if($ogg = dt_get_post_meta('audio_ogg'))
				$audio_args['ogg'] = $ogg;
			
			
			if(!empty($audio_args)){
				if(isset($audio_args['embed'])){
					echo '<div id="audio-featured-'.$post_id.'" class="entry-featured audio-embed">';
					echo '<div class="embed-wrap">';
						echo wp_oembed_get(esc_attr($embed));
					echo '</div>';
					echo '</div>';
				}else{
					$audio = '<div id="audio-featured-'.$post_id.'" class="audio-embed-wrap"><audio class="audio-embed">';
					$source = '<source type="%s" src="%s" />';
					foreach ( $audio_args as $type => $audio_src ) {
						$audio_type = wp_check_filetype( $audio_src, wp_get_mime_types() );
						$audio .= sprintf( $source, $audio_type['type'], esc_url( $audio_src ) );
					}
					$audio .='</audio></div>';
					echo '<div class="entry-featured'.$featured_class.' '.$entry_featured_class.'">';
					echo dt_echo($audio);
					echo '</div>';
				}
			}
		}elseif (has_post_thumbnail()){
			$thumb = get_the_post_thumbnail($post_id,$thumb_size,array('data-itemprop'=>'image'));
			echo '<div class="entry-featured post-thumbnail'.$featured_class.' '.$entry_featured_class.'">';
			if(!is_singular() || $is_shortcode){
				echo '<a class="dt-image-link" href="'.get_the_permalink().'" title="'.esc_attr(get_the_title(get_post_thumbnail_id($post_id))).'">'.$thumb.'</a>';
			}else{
				echo dt_echo($thumb);
			}
			echo '</div>';
		}
	}
	return;
}

function dt_post_meta($show_date=true,$show_comment = true,$show_category= true,$show_author = true,$echo = true,$meta_separator= ', ',$date_format = null,$icon = false) {
	if(empty($date_format))
		$date_format = get_option( 'date_format' );
	$post_type = get_post_type();
	//$show_date = false;
	//$meta_separator = false;
	$html = array();
	// Author
	$author_html = '';
	if($show_author){
		$author_html .= '<span class="meta-author">';
		if($icon)
			$author_html .= '<i class="fa fa-pencil-square-o"></i>';//esc_html_e('By', 'wozine');
		$author = sprintf(
			'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ), get_the_author_meta( 'nicename' ) ) ),
			esc_attr( sprintf( esc_html_e( 'Posts by %s', 'wozine'), get_the_author() ) ),
			get_the_author()
		);
		$author_html .= sprintf(esc_html_e('By %1$s', 'wozine'),$author);
		$author_html .= '</span>';
		$html[] = $author_html;
	}
	// Date
	$date_html = '';
	if($show_date){
		$date_html .= '<span class="meta-date">';
		$date_html .= '<time datetime="' . esc_attr(get_the_date('c')) . '" data-itemprop="dateCreated">';
		if($icon)
			$date_html .= '<i class="fa fa-clock-o"></i>';
		$date_html .= esc_html(get_the_date($date_format));
		$date_html .= '</time>';
		$date_html .= '</span>';
		$html[] = $date_html;
	}
	// Categories
	$categories_html = '';
	if($show_category){
		$categories_html .= '<span class="meta-category">';
		if($icon)
			$categories_html .= '<i class="fa fa-folder-open-o"></i>';
		$categories_html .= sprintf(esc_html_e('In %1$s','wozine'),get_the_category_list(', '));
		$categories_html .= '</span>';
		$html[] = $categories_html;
	}
	
	
	// Comments
	$comments_html = '';
	if (comments_open()) {
		$comment_title = '';
		$comment_number = get_comments_number();
		if (get_comments_number() == 0) {
			$comment_title = sprintf(esc_html_e('Leave a comment on: &ldquo;%s&rdquo;', 'wozine') , get_the_title());
			$comment_number = '0 '.esc_html_e('Comment', 'wozine');
		} else if (get_comments_number() == 1) {
			$comment_title = sprintf(esc_html_e('View a comment on: &ldquo;%s&rdquo;', 'wozine') , get_the_title());
			$comment_number = '1 ' . esc_html_e('Comment', 'wozine');
		} else {
			$comment_title = sprintf(esc_html_e('View all comments on: &ldquo;%s&rdquo;', 'wozine') , get_the_title());
			$comment_number =  get_comments_number() . ' ' . esc_html_e('Comments', 'wozine');
		}
			
		$comments_html.= '<span class="meta-comment">';
		if($icon)
			$comments_html .= '<i class="fa fa-comment-o"></i>';
		$comments_html .= '<a' . ' href="' . esc_url(get_comments_link()) . '"' . ' title="' . esc_attr($comment_title) . '"' . ' class="meta-comments">';
		$comments_html.=  $comment_number . '</a></span> ';
		$comments_html.='<meta content="UserComments:'.get_comments_number().'" itemprop="interactionCount">';
	}
	if($show_comment)
		$html[] = $comments_html;
	
	if($meta_separator !== false && !$icon)
		$html = implode('<span class="meta-separator">'.$meta_separator.'</span>', $html);
	else 
		$html = implode("\n",$html);
	
	if($echo)
		echo dt_echo($html);
	else 
		return $html;
}

function dt_timeline_date($args=array()){
	$defaults = array(
			'prev_post_month' 	=> null,
			'post_month' 		=> 'null'
	);
	$args = wp_parse_args( $args, $defaults );
	if( $args['prev_post_month'] != $args['post_month'] ) {
	?>
		<div class="timeline-date">
			<span class="timeline-date-title"><?php echo get_the_date('M Y')?></span>
		</div>
		<?php
	}
}

function dt_paginate_links_short($args = array(), $query = null){
	global $wp_rewrite, $wp_query;
	do_action( 'dt_pagination_short_start' );
	
	if ( !empty($query)) {
		$wp_query = $query;
	}
	
	if ( 1 >= $wp_query->max_num_pages )
		return;
	
	$paged = ( get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1 );
	$max_num_pages = intval( $wp_query->max_num_pages );
	// Setting up default values based on the current URL.
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$url_parts    = explode( '?', $pagenum_link );
	
	// Get max pages and current page out of the current query, if available.
	$total   = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
	$current = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	
	// Append the format placeholder to the base URL.
	$pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';
	
	// URL base depends on permalink settings.
	$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';
	
	
	$defaults = array(
		'base' => esc_url(add_query_arg( 'paged', '%#%' )),
		'format' => $format,
		'total' => $max_num_pages,
		'current' => $paged,
		'prev_text' => '<i class="fa fa-angle-left"></i>',
		'next_text' => '<i class="fa fa-angle-right"></i>',
		'add_fragment' => '',
		'add_args'=>array(),
		'before' => '<div class="paginate"><div class="paginate_links"><span class="pagination-meta">'.sprintf(esc_html_e("%d/%d", 'wozine'), $paged, $max_num_pages).'</span>',
		'after' => '</div></div>',
		'echo' => true,
	);
	$defaults = apply_filters( 'dt_pagination_short_args_defaults', $defaults );
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'dt_pagination_short_args', $args );
	if ( isset( $url_parts[1] ) ) {
		// Find the format argument.
		$format_query = parse_url( str_replace( '%_%', $args['format'], $args['base'] ), PHP_URL_QUERY );
		wp_parse_str( $format_query, $format_arg );

		// Remove the format argument from the array of query arguments, to avoid overwriting custom format.
		wp_parse_str( esc_url(remove_query_arg( array_keys( $format_arg ), $url_parts[1] ), $query_args ));
		$args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $query_args ) );
	}
	$add_args = $args['add_args'];
	$current  = (int) $args['current'];
	$prev_href='';
	$next_href='';
	if ($current && 1 < $current ) :
		$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current - 1, $link );
		if ( $add_args )
			$link = esc_url(add_query_arg( $add_args, $link ));
		$link .= $args['add_fragment'];
		$prev_href = ' href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '"';
	endif;
	if ($current && ( $current < $total || -1 == $total ) ) :
		$link = str_replace( '%_%', $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current + 1, $link );
		if ( $add_args )
			$link = esc_url(add_query_arg( $add_args, $link ));
		$link .= $args['add_fragment'];
		$next_href = ' href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '"';
	endif;
	$page_links[] = '<a class="prev page-numbers" '.$prev_href.'>' . $args['prev_text'] . '</a>';
	$page_links[] = '<a class="next page-numbers" ' .$next_href. '>' . $args['next_text'] . '</a>';
	$page_links = join("\n", $page_links);
	$page_links = $args['before'] . $page_links . $args['after'];
	$page_links = apply_filters( 'dt_pagination_short', $page_links );
	
	do_action( 'dt_pagination_short_end' );
	
	if ( $args['echo'] )
		echo dt_echo($page_links);
	else
		return $page_links;
}

function dt_paginate_links( $args = array(), $query = null ){
	global $wp_rewrite, $wp_query;
	$temp_query = $wp_query;
	do_action( 'dt_pagination_start' );

	if ( !empty($query)) {
		$wp_query = $query;
	}

	if ( 1 >= $wp_query->max_num_pages )
		return;

	$paged = ( get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1 );

	$max_num_pages = intval( $wp_query->max_num_pages );

	$defaults = array(
			'base' => esc_url(add_query_arg( 'paged', '%#%' )),
			'format' => '',
			'total' => $max_num_pages,
			'current' => $paged,
			'prev_next' => true,
			'prev_text' => '<i class="fa fa-angle-left"></i>',
			'next_text' => '<i class="fa fa-angle-right"></i>',
			'show_all' => false,
			'end_size' => 1,
			'mid_size' => 1,
			'add_fragment' => '',
			'type' => 'plain',
			'before' => '<div class="paginate paging-navigation"><div class="paginate_links pagination loop-pagination">',
			'after' => '</div></div>',
			'echo' => true,
			'use_search_permastruct' => true
	);

	$defaults = apply_filters( 'dt_pagination_args_defaults', $defaults );

	if( $wp_rewrite->using_permalinks() && ! is_search() )
		$defaults['base'] = user_trailingslashit( trailingslashit( get_pagenum_link() ) . 'page/%#%' );

	if ( is_search() )
		$defaults['use_search_permastruct'] = false;

	if ( is_search() ) {
		if ( class_exists( 'BP_Core_User' ) || $defaults['use_search_permastruct'] == false ) {
			$search_query = get_query_var( 's' );
			$paged = get_query_var( 'paged' );
			$base = esc_url(add_query_arg( 's', urlencode( $search_query ) ));
			$base = esc_url(add_query_arg( 'paged', '%#%' ));
			$defaults['base'] = $base;
		} else {
			$search_permastruct = $wp_rewrite->get_search_permastruct();
			if ( ! empty( $search_permastruct ) ) {
				$base = get_search_link();
				$base = esc_url(add_query_arg( 'paged', '%#%', $base ));
				$defaults['base'] = $base;
			}
		}
	}

	$args = wp_parse_args( $args, $defaults );

	$args = apply_filters( 'dt_pagination_args', $args );

	if ( 'array' == $args['type'] )
		$args['type'] = 'plain';

	$pattern = '/\?(.*?)\//i';

	preg_match( $pattern, $args['base'], $raw_querystring );
	if(!empty($raw_querystring)){
		if( $wp_rewrite->using_permalinks() && $raw_querystring )
			$raw_querystring[0] = str_replace( '', '', $raw_querystring[0] );
		$args['base'] = str_replace( $raw_querystring[0], '', $args['base'] );
		$args['base'] .= substr( $raw_querystring[0], 0, -1 );
	}
	$page_links = paginate_links( $args );

	$page_links = str_replace( array( '&#038;paged=1\'', '/page/1\'' ), '\'', $page_links );

	$page_links = $args['before'] . $page_links . $args['after'];

	$page_links = apply_filters( 'dt_pagination', $page_links );

	do_action( 'dt_pagination_end' );
	
	$wp_query = $temp_query;
	
	if ( $args['echo'] )
		echo dt_print_string($page_links);
	else
		return $page_links;

}

/**
 * Returns the first found number from an string
 * Parsing depends on given locale (grouping and decimal)
 *
 * Examples for input:
 * '  2345.4356,1234' = 23455456.1234
 * '+23,3452.123' = 233452.123
 * ' 12343 ' = 12343
 * '-9456km' = -9456
 * '0' = 0
 * '2 054,10' = 2054.1
 * '2'054.52' = 2054.52
 * '2,46 GB' = 2.46
 *
 * @param string|float|int $value
 * @return float|null
 */
function dt_get_number($value)
{
	if (is_null($value)) {
		return null;
	}

	if (!is_string($value)) {
		return floatval($value);
	}

	//trim spaces and apostrophes
	$value = str_replace(array('\'', ' '), '', $value);

	$separatorComa = strpos($value, ',');
	$separatorDot  = strpos($value, '.');

	if ($separatorComa !== false && $separatorDot !== false) {
		if ($separatorComa > $separatorDot) {
			$value = str_replace('.', '', $value);
			$value = str_replace(',', '.', $value);
		}
		else {
			$value = str_replace(',', '', $value);
		}
	}
	elseif ($separatorComa !== false) {
		$value = str_replace(',', '.', $value);
	}

	return floatval($value);
}


function dt_format_color( $color ='' ) {
	if(strstr($color,'rgba')){
		return $color;
	}
	
	$hex = trim( str_replace( '#', '', $color ) );
	if(empty($hex))
		return '';

	if ( strlen( $hex ) == 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( $hex ){
		if ( ! preg_match( '/^#[a-f0-9]{6}$/i', $hex ) ) {
			return '#' . $hex;
		}
	}
	return '';
}


function dt_morphsearchform(){
	
	ob_start();
	?>
	<div class="morphsearch" id="morphsearch">
		<form class="morphsearch-form" method="get"  action="<?php echo esc_url( home_url( '/' ) ) ?>" role="form">
			<input type="search" name="s" placeholder="<?php esc_html__('Search...','wozine')?>" class="morphsearch-input">
			<button type="submit" class="morphsearch-submit"></button>
		</form>
		<div class="morphsearch-content<?php echo (defined( 'WOOCOMMERCE_VERSION' )  ? ' has-3colum':'') ?>">
			<?php if ( defined( 'WOOCOMMERCE_VERSION' ) ) { ?>
			<div class="dummy-column">
				<h2><?php esc_html_e('Product','wozine') ?></h2>
				<?php 
				$query_args = array(
		    		'posts_per_page' => 6,
		    		'post_status' 	 => 'publish',
		    		'post_type' 	 => 'product',
		    		'no_found_rows'  => 1,
					'orderby'		 =>'date',
		    		'order'          => 'DESC'
		    	);
				$query_args['meta_query'] = WC()->query->get_meta_query();
				$r = new WP_Query( $query_args );
				if ( $r->have_posts() ) {
					while ( $r->have_posts() ) {
						$r->the_post();
						global $product;
						?>
						<a href="<?php the_permalink()?>" class="dummy-media-object">
							<?php echo dt_echo($product->get_image('dt-thumbnail-square')); ?>
							<div>
								<h3><?php echo dt_echo($product->get_title()); ?></h3>
								<?php if ( ! empty( $show_rating ) ) echo dt_echo($product->get_rating_html()); ?>
								<div class="price">
									<?php echo dt_echo($product->get_price_html()); ?>
								</div>
							</div>
						</a>
						<?php
					}
				}
				wp_reset_query();
				?>
			</div>
			<?php }?>
			<div class="dummy-column">
				<h2><?php esc_html_e('Popular','wozine') ?></h2>
				<?php 
				$re = new WP_Query(array(
					'posts_per_page'      => 6,
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
					'meta_key'			  => "_thumbnail_id",
					'orderby'			  =>'comment_count',
					'order' 			  => 'DESC',
				) );
				if ($re->have_posts()) :
				?>
				<?php while ( $re->have_posts() ) : $re->the_post(); ?>
				<a href="<?php the_permalink()?>" class="dummy-media-object">
					<?php the_post_thumbnail('dt-thumbnail-square')?>
					<div>
						<h3><?php the_title()?></h3>
						<?php echo '<span>'.sprintf(esc_html_e('%s Comment','wozine'),get_comments_number()).'</span>'; ?>
					</div>
				</a>
				<?php endwhile; ?>
				<?php 
				endif;
				wp_reset_query();
				?>
			</div>
			<div class="dummy-column">
				<h2><?php esc_html_e('Recent','wozine') ?></h2>
				<?php 
				$rc = new WP_Query(array(
					'posts_per_page'      => 6,
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
					'meta_key' => "_thumbnail_id",
				) );
				if ($rc->have_posts()) :
				?>
				<?php while ( $rc->have_posts() ) : $rc->the_post(); ?>
				<a href="<?php the_permalink()?>" class="dummy-media-object">
					<?php the_post_thumbnail('dt-thumbnail-square')?>
					<div>
						<h3><?php the_title()?></h3>
						<span>
							<time datetime="<?php echo get_the_date('Y-m-d\TH:i:sP') ?>"><?php echo get_the_date('M j, Y') ?></time>
						</span>
					</div>
				</a>
				<?php endwhile; ?>
				<?php 
				endif;
				wp_reset_query();
				?>
			</div>
		</div>
		<span class="morphsearch-close"></span>
	</div>
	<div class="morphsearch-overlay"></div>
	<?php
	return ob_get_clean();
}

/*
 * Get Google Font name from a full family_name
 *
 * @family_name			get from google fonts. For example: Playfair+Display:900 or http://fonts.googleapis.com/css?family=Roboto:400,500,500italic
 * @out_put				for example: "Playfair Display"
 */
if(!function_exists('dt_get_google_font_name')){
	function dt_get_google_font_name($family_name){
		$name = $family_name;
		if(startsWith($family_name, 'http')){
			// $family_name is a full link, so first, we need to cut off the link
			$idx = strpos($name,'=');
			if($idx > -1){
				$name = substr($name, $idx);
			}
		}
		$idx = strpos($name,':');
		if($idx > -1){
			$name = substr($name, 0, $idx);
			$name = str_replace('+',' ', $name);
		}
		return $name;
	}
}

/*
 * Get Google Font name from a full family_name and full settings
 *
 * @family_name			get from google fonts. For example: Playfair+Display:900 or http://fonts.googleapis.com/css?family=Open+Sans:600italic,700italic,400,700&subset=latin,greek,vietnamese
 * @out_put				for example: "Open+Sans:600italic,700italic,400,700&subset=latin,greek,vietnamese"
 */
if(!function_exists('dt_extract_google_font_name')){
	function dt_extract_google_font_name($font_url){
		$name = $font_url;
		if(startsWith($font_url, 'http')){
			// $family_name is a full link, so first, we need to cut off the link
			$idx = strpos($name,'=');
			if($idx > -1){
				$name = substr($name, $idx + 1);
			}
		}

		return $name;
	}
}

/*
 * Get Google Font name from a full family_name and styles
 *
 * @family_name			get from google fonts. For example: Playfair+Display:900 or http://fonts.googleapis.com/css?family=Open+Sans:600italic,700italic,400,700&subset=latin,greek,vietnamese
 * @out_put				for example: "Open+Sans:600italic,700italic,400,700"
 */
if(!function_exists('dt_extract_google_font_name_style')){
	function dt_extract_google_font_name_style($family_name){
		$name = $family_name;
		if(startsWith($family_name, 'http')){
			// $family_name is a full link, so first, we need to cut off the link
			$idx = strpos($name,'=');
			if($idx > -1){
				$name = substr($name, $idx + 1);
			}
		}
		$idx = strpos($name,'&');
		if($idx > -1){
			$name = substr($name, 0, $idx);
			$name = str_replace('+',' ', $name);
		}
		return $name;
	}
}

/**
 * Get related posts
 *
 * @params $post_id (optional). If not passed, it will try to get global $post
 */
if(!function_exists('dt_get_related_posts')){
	function dt_get_related_posts( $post_id = null ) {
		if(!$post_id){
			global $post;
			if($post) {
				$post_id = $post->ID;
			} else {
				// return if cannot find any post
				return;
			}
		}

		$number = dt_get_theme_option('related_posts_count', 5);
		$relatedPostsOrderBy = dt_get_theme_option('related_posts_order_by', 'date'); // date or rand

		$args = array(
			'post_status' => 'publish',
			'posts_per_page' => $number,
			'orderby' => $relatedPostsOrderBy,
			'ignore_sticky_posts' => 1,
			'post__not_in' => array ($post_id)
		);
		 
		$get_related_post_by = dt_get_theme_option('related_posts_by','cat');

		if ($get_related_post_by == 'cat') {
			$categories = wp_get_post_categories($post_id);
				
			$args['category__in'] = $categories;
		} else {
			$posttags = wp_get_post_tags($post_id);
			$array_tags = array();
			if ($posttags) {
				foreach($posttags as $tag) {
					$tags = $tag->term_id ;
					array_push ( $array_tags, $tags);
				}
			}
				
			$args['tag__in'] = $array_tags;
		}

		$related_items = new WP_Query( $args );
		return $related_items;
	}
}

if ( ! function_exists( 'dt_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
*/
function dt_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		get_the_date(),
		esc_attr( get_the_modified_date( 'c' ) ),
		get_the_modified_date()
	);

	printf( '<span class="posted-on"><span class="screen-reader-text">%1$s </span><a href="%2$s" rel="bookmark">%3$s</a></span>',
		_x( 'Posted on', 'Used before publish date.', 'wozine' ),
		esc_url( get_permalink() ),
		$time_string
	);
	
}
endif;

if ( ! function_exists( 'dt_paging_nav_ajax' ) ) :
function dt_paging_nav_ajax($loadmore_text = 'Load More', $query = null){
	// Don't print empty markup if there's only one page.
	global $wp_query;
	$term_query = $wp_query;
	if($query){
		$wp_query = $query;
	}
	if ( $wp_query->max_num_pages < 2 ) {
		return;
	}
	?>
	<div class="loadmore-action">
		<div class="loadmore-loading"><div class="dtwl-navloading"><div class="dtwl-navloader"></div></div></div>
		<button type="button" class="btn-loadmore"><?php echo esc_html($loadmore_text) ?></button>
	</div>
	<?php
	$wp_query = $term_query;
}
endif;

if ( ! function_exists( 'dt_paging_nav_default' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable. Default WordPress style
*/
function dt_paging_nav_default() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	?>
	<nav class="wp-paging-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'wozine' ); ?></h1>
		<div class="nav-links">

			<?php if ( get_next_posts_link() ) : ?>
			<div class="nav-previous"><?php next_posts_link( esc_html__( 'Previous Posts', 'wozine' ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<div class="nav-next"><?php previous_posts_link( esc_html__( 'Next Posts', 'wozine' ) ); ?></div>
			<?php endif; ?>

		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if ( ! function_exists( 'dt_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 */
function dt_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}

	?>
	<nav class="navigation post-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Post navigation', 'wozine' ); ?></h1>
		<div class="nav-links">
			<?php
			if ( is_attachment() ) :
				previous_post_link( '%link', __ ( '<span class="meta-nav">Published In</span>%title', 'wozine' ) );
			else :
				previous_post_link( '%link', __ ( '<span class="meta-nav">Previous Post</span>%title', 'wozine' ) );
				next_post_link( '%link', __ ( '<span class="meta-nav">Next Post</span>%title', 'wozine' ) );
			endif;
			?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

/**
 * Find out if blog has more than one category.
 *
 * @return boolean true if blog has more than 1 category
 */
function dt_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'dt_category_count' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'dt_category_count', $all_the_cool_cats );
	}

	if ( 1 !== (int) $all_the_cool_cats ) {
		// This blog has more than 1 category so dt_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so dt_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in dt_categorized_blog.
 *
 */
function dt_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'dt_category_count' );
}
add_action( 'edit_category', 'dt_category_transient_flusher' );
add_action( 'save_post',     'dt_category_transient_flusher' );


if( !function_exists('dt_show_post_share') ):
/**
 * Display Social Share buttons for FaceBook, Twitter, LinkedIn, Google+, Thumblr, Pinterest, Email
 */
function dt_print_social_share($id = false){
	if(!$id){
		$id = get_the_ID();
	}
	if( dt_get_theme_option('show_post_share', '1') == '0' )
		return;
	
	wp_enqueue_script('vendor-theia-sticky-sidebar');
	?>
	<div class="share-links sticky_sidebar" data-sticky-sidebar="sticky_sidebar" data-container-selector=".post-content .sticky_sidebar">
		<ul>
		<?php if(dt_get_theme_option('sharing_facebook', '1')=='1'){ ?>
	  		<li>
	  		 	<a class="trasition-all" title="<?php esc_html_e('Share on Facebook','wozine');?>" href="#" target="_blank" rel="nofollow" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+'<?php echo urlencode(get_permalink($id)); ?>','facebook-share-dialog','width=626,height=436');return false;"><i class="fa fa-facebook"></i>
	  		 	</a>
	  		</li>
    	<?php }
		
		if(dt_get_theme_option('sharing_twitter', '1')=='1'){ ?>
	    	<li>
		    	<a class="trasition-all" href="#" title="<?php esc_html_e('Share on Twitter','wozine');?>" rel="nofollow" target="_blank" onclick="window.open('http://twitter.com/share?text=<?php echo urlencode(get_the_title($id)); ?>&url=<?php echo urlencode(get_permalink($id)); ?>','twitter-share-dialog','width=626,height=436');return false;"><i class="fa fa-twitter"></i>
		    	</a>
	    	</li>
    	<?php }
		
		if(dt_get_theme_option('sharing_linkedIn', '0')=='1'){ ?>
			   	<li>
			   	 	<a class="trasition-all" href="#" title="<?php esc_html_e('Share on LinkedIn','wozine');?>" rel="nofollow" target="_blank" onclick="window.open('http://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink($id)); ?>&title=<?php echo urlencode(get_the_title($id)); ?>&source=<?php echo urlencode(get_bloginfo('name')); ?>','linkedin-share-dialog','width=626,height=436');return false;"><i class="fa fa-linkedin"></i>
			   	 	</a>
			   	</li>
	   	<?php }
		
		if(dt_get_theme_option('sharing_tumblr', '0')=='1'){ ?>
		   	<li>
		   	   <a class="trasition-all" href="#" title="<?php esc_html_e('Share on Tumblr','wozine');?>" rel="nofollow" target="_blank" onclick="window.open('http://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink($id)); ?>&name=<?php echo urlencode(get_the_title($id)); ?>','tumblr-share-dialog','width=626,height=436');return false;"><i class="fa fa-tumblr"></i>
		   	   </a>
		   	</li>
    	<?php }
		
		if(dt_get_theme_option('sharing_google', '1')=='1'){ ?>
	    	 <li>
	    	 	<a class="trasition-all" href="#" title="<?php esc_html_e('Share on Google Plus','wozine');?>" rel="nofollow" target="_blank" onclick="window.open('https://plus.google.com/share?url=<?php echo urlencode(get_permalink($id)); ?>','googleplus-share-dialog','width=626,height=436');return false;"><i class="fa fa-google-plus"></i>
	    	 	</a>
	    	 </li>
    	 <?php }
		 
		 if(dt_get_theme_option('sharing_pinterest', '1')=='1'){ ?>
	    	 <li>
	    	 	<a class="trasition-all" href="#" title="<?php esc_html_e('Pin this','wozine');?>" rel="nofollow" target="_blank" onclick="window.open('//pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($id)) ?>&media=<?php echo urlencode(wp_get_attachment_url( get_post_thumbnail_id($id))); ?>&description=<?php echo urlencode(get_the_title($id)) ?>','pin-share-dialog','width=626,height=436');return false;"><i class="fa fa-pinterest"></i>
	    	 	</a>
	    	 </li>
    	 <?php }
		 
		 if(dt_get_theme_option('sharing_email', '1')=='1'){ ?>
	    	<li>
		    	<a class="trasition-all" href="mailto:?subject=<?php echo get_the_title($id) ?>&body=<?php echo urlencode(get_permalink($id)) ?>" title="<?php esc_html_e('Email this','wozine');?>"><i class="fa fa-envelope"></i>
		    	</a>
		   	</li>
   		<?php } ?>
   		</ul>
   	</div>
<?php
}
endif;

if(!function_exists('dt_show_author_social_links')):
/**
 * Display Author social link
 * @param String $field the field of the users record.
 * @param int $user_id Optional. User ID.
 * @param String $echo Optional. True.
 */
function dt_show_author_social_links($field = '', $user_id = false, $echo = true){
	$dawn_author_links = array('facebook', 'twitter', 'google', 'flickr', 'instagram', 'pinterest', 'envelope');
	$html = '';
	foreach($dawn_author_links as $account){
		$url = get_the_author_meta($account, $user_id);
			
		if($url != ''){
			if($account == 'envelope') $url = 'mailto:' . $url;
			$html .= '<a href="' . $url . '" target="_blank"><i class="fa fa-'.$account.'"></i></a>';
		}
	}
	if($echo){
		echo $html;
	}else{
		return $html;
	}
}
endif;