<?php
/**
 * wozine functions and definitions
 *
 * @package wozine
 */

$themeInfo            =  wp_get_theme();
$themeName            = trim($themeInfo['Title']);
$themeAuthor          = trim($themeInfo['Author']);
$themeAuthor_URI      = trim($themeInfo['AuthorURI']);
$themeVersion         = trim($themeInfo['Version']);

define('DT_THEME_NAME', $themeName);
define('DT_THEME_AUTHOR', $themeAuthor);
define('DT_THEME_AUTHOR_URI', $themeAuthor_URI);
define('DT_THEME_VERSION', $themeVersion);

if(!defined('DT_ADMIN_ASSETS_URI'))
define('DT_ADMIN_ASSETS_URI', get_template_directory_uri() . '/includes/admin/assets');

if(!defined('DT_ASSETS_URI'))
	define('DT_ASSETS_URI', get_template_directory_uri() . '/assets');

/*
 * Require dt core
 * Dont edit this
 */
do_action('dawn_theme_includes');

include_once (get_template_directory() . '/includes/core/dawn-core.php');
include_once (get_template_directory() . '/includes/dt-functions.php');
include_once (get_template_directory() . '/includes/dt-hooks.php');

include_once (get_template_directory().'/includes/walker.php');
// Plugins Required - recommended
$plugin_path = get_template_directory() . '/includes/plugins';
if ( file_exists( $plugin_path . '/tgmpa_register.php' ) ) {
	include_once ( $plugin_path. '/tgm-plugin-activation.php');
	include_once ($plugin_path . '/tgmpa_register.php');
}

/*
 * scripts enqueue for admin
 */
function dt_admin_js_and_css(){
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
}
add_action('admin_enqueue_scripts', 'dt_admin_js_and_css');

/**
 * Set up the content width value based on the theme's design.
 *
 * @see dt_content_width()
 *
 */
if ( ! isset( $content_width ) ) {
	$content_width = 640;
}

if ( ! function_exists( 'dt_setup' ) ) :
/**
 * wozine setup.
 *
 * Set up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support post thumbnails.
 *
 */
function dt_setup() {

	/*
	 * Make wozine available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on dawn, use a find and
	 * replace to change 'wozine' to the name of your theme in all
	 * template files.
	 */
	load_theme_textdomain( 'wozine', get_template_directory() . '/languages' );

	// This theme styles the visual editor to resemble the theme style.
	add_editor_style('assets/assets/css/editor-style.css');

	// Add RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );
	
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails, and declare two sizes.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 770, 520, true );
	add_image_size( 'wozine-post-thumbnails', 770, 520, true );

	add_image_size( 'wozine-blog-classic', 770, 520, true );
	add_image_size( 'wozine-blog-gallery', 750, 562, true ); //375, 281

	add_image_size( 'wozine-related-post-thumbnails', 244, 162, true );
	add_image_size( 'wozine-recent-posts-wg-thumb', 170, 115, true );
	
	/*
	 *  register thumbnail size for shortcodes
	 */
	add_image_size( 'wozine-smart-content-box-big-thumb', 572, 492, true );
	add_image_size( 'wozine-smart-content-box-type2-02', 298, 404, true );
	add_image_size( 'wozine-smart-content-box-type2-03', 298, 201, true );
	add_image_size( 'wozine-posts-slider-thumb', 370, 250, true );
	add_image_size( 'wozine-posts-slider-single_mode_thumb', 770, 487, true );
	// Post category
	add_image_size( 'wozine-post-category-big-thumb', 570, 385, true );
	add_image_size( 'wozine-post-category-grid-thumb', 270, 182, true );
	add_image_size( 'wozine-post-category-tpllist-big-thumb', 770, 347, true );
	add_image_size( 'wozine-post-slider-widget', 340, 460, true );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'main-menu'   => __( 'Main menu', 'wozine' ),
		//'secondary' => __( 'Secondary menu in Footer', 'wozine' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	/*
	 * Enable support for Post Formats.
	 * See https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'image', 'video', 'audio', 'quote', 'link', 'gallery',
	) );

	// This theme allows users to set a custom background.
	add_theme_support( 'custom-background', apply_filters( 'dt_custom_background_args', array(
		'default-color' => 'f5f5f5',
	) ) );

	// This theme uses its own gallery styles.
	add_filter( 'use_default_gallery_style', '__return_false' );
}
endif; // dt_setup
add_action( 'after_setup_theme', 'dt_setup' );

/**
 * Adjust content_width value for image attachment template.
 *
 */
function dt_content_width() {
	if ( is_attachment() && wp_attachment_is_image() ) {
		$GLOBALS['content_width'] = 810;
	}
}
add_action( 'template_redirect', 'dt_content_width' );

/**
 * Register widget areas.
 *
 */
function dt_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Main Sidebar', 'wozine' ),
		'id'            => 'main-sidebar',
		'description'   => __( 'Main sidebar that appears on the right.', 'wozine' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => __( 'Footer Widget #1', 'wozine' ),
		'id'            => 'footer-sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Widget #2', 'wozine' ),
		'id'            => 'footer-sidebar-2',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Widget #3', 'wozine' ),
		'id'            => 'footer-sidebar-3',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'dt_widgets_init' );

/**
 * Register styles AND scripts
 */
function dt_register_vendor_assets(){
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
	wp_register_style('slick', get_template_directory_uri() . '/assets/vendor/slick/slick.css');
	wp_register_style('slick-theme', get_template_directory_uri() . '/assets/vendor/slick/slick-theme.css');
	wp_register_script('slick', get_template_directory_uri() . '/assets/vendor/slick/slick.min.js', array('jquery'), '', false);
	
	wp_register_style('vendor-font-awesome',get_template_directory_uri().'/assets/vendor/font-awesome/css/font-awesome' . $suffix . '.css', array(), '4.6.2' );
	wp_register_style('vendor-elegant-icon',get_template_directory_uri().'/assets/vendor/elegant-icon/css/elegant-icon.css');
	
	wp_register_style( 'vendor-preloader', get_template_directory_uri().'/assets/vendor/preloader/preloader.css', '1.0.0' );
	wp_register_script( 'vendor-preloader', get_template_directory_uri().'/assets/vendor/preloader/preloader'.$suffix.'.js', array('jquery') , '1.0.0', false );
	
	wp_register_script( 'vendor-countdown', get_template_directory_uri().'/assets/vendor/jquery.countdown'.$suffix.'.js', array('vendor-appear') , '2.0.4', true );
	
	wp_register_script( 'vendor-stellar', get_template_directory_uri().'/assets/vendor/jquery.stellar'.$suffix.'.js', array('vendor-appear') , '2.0.4', true );
	
	wp_register_script( 'vendor-smartsidebar', get_template_directory_uri().'/assets/vendor/smartsidebar'.$suffix.'.js', array('jquery') , '1.0.0', true );
	
	
	wp_register_script( 'vendor-ajax-chosen', get_template_directory_uri().'/assets/vendor/chosen/ajax-chosen.jquery' . $suffix . '.js', array( 'jquery', 'vendor-chosen' ), '1.0.0', true );
	wp_register_script( 'vendor-appear', get_template_directory_uri().'/assets/vendor/jquery.appear' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );
	wp_register_script( 'vendor-typed', get_template_directory_uri().'/assets/vendor/typed' . $suffix .'.js', array( 'jquery','vendor-appear' ), '1.0.0', true );
	wp_register_script( 'vendor-easing', get_template_directory_uri().'/assets/vendor/easing' . $suffix . '.js', array( 'jquery' ), '1.3.0', true );
	wp_register_script( 'vendor-waypoints', get_template_directory_uri().'/assets/vendor/waypoints' . $suffix . '.js', array( 'jquery' ), '2.0.5', true );
	wp_register_script( 'vendor-transit', get_template_directory_uri().'/assets/vendor/jquery.transit' . $suffix . '.js', array( 'jquery' ), '0.9.12', true );
	wp_register_script( 'vendor-imagesloaded', get_template_directory_uri().'/assets/vendor/imagesloaded.pkgd' . $suffix . '.js', array( 'jquery' ), '3.1.8', true );
	
	wp_register_script( 'vendor-requestAnimationFrame', get_template_directory_uri().'/assets/vendor/requestAnimationFrame' . $suffix . '.js', null, '0.0.17', true );
	wp_register_script( 'vendor-parallax', get_template_directory_uri().'/assets/vendor/jquery.parallax' . $suffix . '.js', array( 'jquery'), '1.1.3', true );
	
	wp_register_script( 'vendor-boostrap', get_template_directory_uri().'/assets/vendor/bootstrap' . $suffix . '.js', array('jquery','vendor-imagesloaded'), '3.2.0', true );
	wp_register_script( 'vendor-superfish',get_template_directory_uri().'/assets/vendor/superfish-1.7.4.min.js',array( 'jquery' ),'1.7.4',true );
	
	wp_register_script( 'vendor-countTo', get_template_directory_uri().'/assets/vendor/jquery.countTo' . $suffix . '.js', array( 'jquery', 'vendor-appear' ), '2.0.2', true );
	wp_register_script( 'vendor-infinitescroll', get_template_directory_uri().'/assets/vendor/jquery.infinitescroll' . $suffix . '.js', array( 'jquery'), '2.1.0', true );
	
	wp_register_script( 'vendor-ProgressCircle', get_template_directory_uri().'/assets/vendor/ProgressCircle' . $suffix . '.js', array( 'jquery','vendor-appear'), '2.0.2', true );
	
	wp_register_style( 'vendor-magnific-popup', get_template_directory_uri().'/assets/vendor/magnific-popup/magnific-popup.css' );
	wp_register_script( 'vendor-magnific-popup', get_template_directory_uri().'/assets/vendor/magnific-popup/jquery.magnific-popup' . $suffix . '.js', array( 'jquery'), '0.9.9', true );
	
	wp_register_script( 'vendor-touchSwipe', get_template_directory_uri().'/assets/vendor/jquery.touchSwipe' . $suffix . '.js', array( 'jquery'), '1.6.6', true );
	
	wp_register_script( 'vendor-carouFredSel', get_template_directory_uri().'/assets/vendor/jquery.carouFredSel' . $suffix . '.js', array( 'jquery','vendor-touchSwipe', 'vendor-easing','vendor-imagesloaded','vendor-transit'), '6.2.1', true );
	wp_register_script( 'vendor-isotope', get_template_directory_uri().'/assets/vendor/isotope.pkgd' . $suffix . '.js', array( 'jquery', 'vendor-imagesloaded' ), '6.2.1', true );
	
	wp_register_script( 'vendor-easyzoom', get_template_directory_uri().'/assets/vendor/easyzoom/easyzoom' . $suffix . '.js', array( 'jquery'), '0.9.9', true );
	wp_register_script( 'vendor-unveil', get_template_directory_uri().'/assets/vendor/jquery.unveil' . $suffix . '.js', array( 'jquery'), '1.0.0', true );
	
	wp_register_script( 'vendor-theia-sticky-sidebar', get_template_directory_uri().'/assets/vendor/theia-sticky-sidebar-master/theia-sticky-sidebar.min.js', array( 'jquery'), '1.4.0', false );
}
add_action( 'template_redirect', 'dt_register_vendor_assets' );
/**
 * Enqueue styles
 */
function dt_enqueue_theme_styles(){
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$main_css_id = basename(get_template_directory());
	
	// Add Awesome font, used in the main stylesheet.
	wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css', array(), '3.3.5' );
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/assets/css/fonts/font-awesome/css/font-awesome.min.css', array(), '4.6.2');
	wp_enqueue_style( 'animate', get_template_directory_uri() . '/assets/css/animate.css', array());
	// Load our main stylesheet.
	wp_enqueue_style( $main_css_id, get_template_directory_uri() . '/assets/css/style' . $suffix . '.css', false, DT_THEME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'dt_enqueue_theme_styles' );
/**
 * Enqueue scripts
 */
function dt_scripts() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
	if ( is_singular() )
		wp_enqueue_script( 'comment-reply' );
	
	/**
	 * Register Google Font
	 */
	$g_fonts = array();
	
	$google_font = dt_get_theme_option('google_font','on');
	$font_subset = '';
	if($google_font == 'on')
	{
		$main_font = dt_get_theme_option('main_font',''); // for example, Playfair+Display:900
		$heading_font = dt_get_theme_option('heading_font','');
	
		if($heading_font != '')
		{
			$font_name = dt_extract_google_font_name_style($heading_font);
			array_push($g_fonts, $font_name);
		}else{
			/** put default font
			 *
			 * array_push($g_fonts, 'Poppins:300,400,700,900,300italic,400italic,700italic');
			 */
			array_push($g_fonts, 'Poppins:300,400,700,900,300italic,400italic,700italic');
		}
	
		// To use the $main_font as Main Font Family and Subset, it must load to the ended of $g_font array
		if($main_font !=''){
			$main_font = dt_extract_google_font_name($main_font);
			array_push($g_fonts, $main_font);
		}
		else
		{
			/** put default font
			 *
			 * array_push($g_fonts, 'Lato:300,400,700,900,300italic,400italic,700italic');
			 */
			array_push($g_fonts, 'Lato:300,400,700,900,300italic,400italic,700italic');
		}
	
	}
	//google font off
	else
	{
		/** put default fonts
		 *
		 * array_push($g_fonts,'Lato:300,400,700,900,300italic,400italic,700italic');
		 */
		array_push($g_fonts, 'Lato:300,400,700,900,300italic,400italic,700italic');
	}
	$g_fonts = implode('|', $g_fonts);
	// enqueue google font, only use subset for Main Font
	wp_enqueue_style( 'google-font', '//fonts.googleapis.com/css?family=' . $g_fonts);
	

	// Load the Internet Explorer specific stylesheet.
	wp_enqueue_style( 'ie', get_template_directory_uri() . '/assets/css/ie.css', array( 'wozine-style' ), '20131205' );
	wp_style_add_data( 'ie', 'conditional', 'lt IE 9' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'keyboard-image-navigation', get_template_directory_uri() . '/assets/js/keyboard-image-navigation.js', array( 'jquery' ), '20130402' );
	}
	
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery'), '3.5.5', true );
	
	wp_enqueue_script( 'hover-intent', get_template_directory_uri() . '/assets/js/jquery.hoverIntent.js', array('jquery'), '', true );

	wp_register_script( 'dt', get_template_directory_uri() . '/assets/js/script'.$suffix.'.js', array( 'jquery' ), DT_THEME_VERSION, true );
	$logo_retina = '';
	$dtL10n = array(
		'ajax_url'=>admin_url( 'admin-ajax.php', 'relative' ),
		'protocol'=>dt_get_protocol(),
		'breakpoint'=>apply_filters('dt_js_breakpoint', 900),
		'nav_breakpoint'=>apply_filters('dt_nav_breakpoint', 900),
		'cookie_path'=>COOKIEPATH,
		'screen_sm'=>768,
		'screen_md'=>992,
		'screen_lg'=>1200,
		'touch_animate'=>apply_filters('dh_js_touch_animate', true),
		'logo_retina'=>$logo_retina,
		'ajax_finishedMsg'=>esc_attr__('All posts displayed','wozine'),
		'ajax_msgText'=>esc_attr__('Loading the next set of posts...','wozine'),
		'woocommerce'=>(defined('WOOCOMMERCE_VERSION') ? 1 : 0),
		'imageLazyLoading'=>(dt_get_theme_option('woo-lazyload',1) ? 1 : 0),
		'add_to_wishlist_text'=>esc_attr(apply_filters('dt_yith_wishlist_is_active',defined( 'YITH_WCWL' )) ? apply_filters( 'dt_yith_wcwl_button_label', get_option( 'yith_wcwl_add_to_wishlist_text' )) : ''),
		'user_logged_in'=>(is_user_logged_in() ? 1 : 0),
		'loadingmessage'=>esc_attr__('Sending info, please wait...','wozine'),
	);
	wp_localize_script('dt', 'dtL10n', $dtL10n);
	wp_enqueue_script('dt');
}
add_action( 'wp_enqueue_scripts', 'dt_scripts' );

if ( ! function_exists( 'dt_renderurlajax' ) ) :
function dt_renderurlajax() {
?>
	<script type="text/javascript">
		var dt_ajaxurl = '<?php echo esc_js( admin_url('admin-ajax.php') ); ?>';
	</script>
	<?php
}
add_action('wp_head', 'dt_renderurlajax');
endif;

if ( ! function_exists( 'dt_the_attached_image' ) ) :
/**
 * Print the attached image with a link to the next attached image.
 */
function dt_the_attached_image() {
	$post = get_post();
	/**
	 * Filter the default attachment size.
	 *
	 * @param array $dimensions {
	 *     An array of height and width dimensions.
	 *
	 *     @type int $height Height of the image in pixels. Default 810.
	 *     @type int $width  Width of the image in pixels. Default 810.
	 * }
	 */
	$attachment_size     = apply_filters( 'dt_attachment_size', array( 810, 810 ) );
	$next_attachment_url = wp_get_attachment_url();

	/*
	 * Grab the IDs of all the image attachments in a gallery so we can get the URL
	 * of the next adjacent image in a gallery, or the first image (if we're
	 * looking at the last image in a gallery), or, in a gallery of one, just the
	 * link to that image file.
	 */
	$attachment_ids = get_posts( array(
		'post_parent'    => $post->post_parent,
		'fields'         => 'ids',
		'numberposts'    => -1,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID',
	) );

	// If there is more than 1 attachment in a gallery...
	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}

		// get the URL of the next image attachment...
		if ( $next_id ) {
			$next_attachment_url = get_attachment_link( $next_id );
		}

		// or get the URL of the first image attachment.
		else {
			$next_attachment_url = get_attachment_link( reset( $attachment_ids ) );
		}
	}

	printf( '<a href="%1$s" rel="attachment">%2$s</a>',
		esc_url( $next_attachment_url ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}
endif;

if ( ! function_exists( 'dt_list_authors' ) ) :
/**
 * Print a list of all site contributors who published at least one post.
 */
function dt_list_authors() {
	$contributor_ids = get_users( array(
		'fields'  => 'ID',
		'orderby' => 'post_count',
		'order'   => 'DESC',
		'who'     => 'authors',
	) );

	foreach ( $contributor_ids as $contributor_id ) :
		$post_count = count_user_posts( $contributor_id );

		// Move on if user has not published a post (yet).
		if ( ! $post_count ) {
			continue;
		}
	?>

	<div class="contributor">
		<div class="contributor-info">
			<div class="contributor-avatar"><?php echo get_avatar( $contributor_id, 132 ); ?></div>
			<div class="contributor-summary">
				<h2 class="contributor-name"><?php echo get_the_author_meta( 'display_name', $contributor_id ); ?></h2>
				<p class="contributor-bio">
					<?php echo get_the_author_meta( 'description', $contributor_id ); ?>
				</p>
				<a class="button contributor-posts-link" href="<?php echo esc_url( get_author_posts_url( $contributor_id ) ); ?>">
					<?php printf( _n( '%d Article', '%d Articles', $post_count, 'wozine' ), $post_count ); ?>
				</a>
			</div><!-- .contributor-summary -->
		</div><!-- .contributor-info -->
	</div><!-- .contributor -->

	<?php
	endforeach;
}
endif;

/**
 * Extend the default WordPress body classes.
 *
 * Adds body classes to denote:
 * 1. Single or multiple authors.
 * 2. Presence of header image except in Multisite signup and activate pages.
 * 3. Index views.
 * 4. Full-width content layout.
 * 5. Presence of footer widgets.
 * 6. Single views.
 * 7. Featured content layout.
 *
 * @param array $classes A list of existing body class values.
 * @return array The filtered body class list.
 */
function dt_body_classes( $classes ) {
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	if ( get_header_image() ) {
		$classes[] = 'header-image';
	} elseif ( ! in_array( $GLOBALS['pagenow'], array( 'wp-activate.php', 'wp-signup.php' ) ) ) {
		$classes[] = 'masthead-fixed';
	}

	if ( is_archive() || is_search() || is_home() ) {
		wp_enqueue_style('slick');
		wp_enqueue_script('slick');
	}

	if ( ( ! is_active_sidebar( 'sidebar-2' ) )
		|| is_page_template( 'page-templates/full-width.php' )
		|| is_page_template( 'page-templates/front-page.php' )
		|| is_attachment() ) {
		$classes[] = 'full-width';
	}

	if ( is_active_sidebar( 'sidebar-3' ) ) {
		$classes[] = 'footer-widgets';
	}

	if ( is_singular() && ! is_front_page() ) {
		$classes[] = 'singular';
	}
	
	// Sticky menu
	if( dt_get_theme_option('sticky-menu', '1') == '1' ){
		$classes[]	= 'sticky-menu';
	}

	return $classes;
}
add_filter( 'body_class', 'dt_body_classes' );

/**
 * Extend the default WordPress post classes.
 *
 * Adds a post class to denote:
 * Non-password protected page with a post thumbnail.
 *
 * @param array $classes A list of existing post class values.
 * @return array The filtered post class list.
 */
function dt_post_classes( $classes ) {
	if ( ! post_password_required() && ! is_attachment() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	}

	return $classes;
}
add_filter( 'post_class', 'dt_post_classes' );

/**
 * Create a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 * @global int $paged WordPress archive pagination page count.
 * @global int $page  WordPress paginated post page count.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function dt_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() ) {
		return $title;
	}

	// Add the site name.
	$title .= get_bloginfo( 'name', 'display' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}

	// Add a page number if necessary.
	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
		$title = "$title $sep " . sprintf( __( 'Page %s', 'wozine' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', 'dt_wp_title', 10, 2 );

if ( ! function_exists( 'dt_comment' ) ) :
/**
 * Template for comments and pingbacks.
*
* To override this walker in a child theme without modifying the comments template
* simply create your own dt_comment(), and that function will be used instead.
*
* Used as a callback by wp_list_comments() for displaying the comments.
*/
function dt_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
	case 'pingback' :
	case 'trackback' :
		// Display trackbacks differently than normal comments.
		?>
 	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
 		<p><?php esc_html_e( 'Pingback:', 'wozine' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( esc_html__( '(Edit)', 'wozine' ), '<span class="edit-link">', '</span>' ); ?></p>
 	<?php
 			break;
 		default :
 		// Proceed with normal comments.
 		global $post;
 	?>
 	<li <?php comment_class('block-author author'); ?> id="li-comment-<?php comment_ID(); ?>">
 		<div id="comment-<?php comment_ID(); ?>" class="comment">
 			<div class="blog-img-wrap">
	 			<a href="<?php  echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" class="image">
	 			<?php
	 				echo get_avatar( $comment, 70);
	 			?>
	 			</a>
 			</div>
 			<div class="group author-content">
 				<div class="meta box-left">
 					<div class="comment-author font-2">
 					<?php
 						printf( '<span class="fn author">%1$s</span>',
 							get_the_author_meta('display_name')
 						);
 					?>
 					</div>
 					<div class="date">
 					<?php 
 						printf( '<time datetime="%2$s" class="date">%3$s</time>',
 								esc_url( get_comment_link( $comment->comment_ID ) ),
 								get_comment_time( 'c' ),
 								/* translators: 1: date, 2: time */
 								sprintf( __( '%1$s  at %2$s ', 'wozine' ), get_comment_date(), get_comment_time() )
 							);?>
 					</div>
 							
 				</div>
 				
 				<div class="box-right">
                       <?php comment_reply_link( array_merge( $args, array( 'reply_text' => esc_html__( 'Reply', 'wozine' ), 'after' => ' <span></span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
 					<!-- .reply -->
                </div>
                
 				<div class="cmt box-comment">					
 					<?php if ( '0' == $comment->comment_approved ) : ?>
 					<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'wozine' ); ?></p>					
 				<?php endif; ?>
 					<?php comment_text(); ?>
 					<?php
 						edit_comment_link( esc_html__( 'Edit', 'wozine' ), '<p class="edit-link">', '</p>' ); ?>
 				</div><!-- .comment-content -->
             </div><!-- .comment-meta -->
 		</div><!-- #comment-## -->
 	<?php
 		break;
 	endswitch; // end comment_type check
 }
 endif;

 
 //change comment form
 if(!function_exists('dt_comment_form')){
 	function dt_comment_form( $args = array(), $post_id = null ) {
 		if ( null === $post_id )
 			$post_id = get_the_ID();
 		else
 			$id = $post_id;
 
 		$commenter = wp_get_current_commenter();
 		$user = wp_get_current_user();
 		$user_identity = $user->exists() ? $user->display_name : '';
 
 		$args = wp_parse_args( $args );
 		if ( ! isset( $args['format'] ) )
 			$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
 
 		$req      = get_option( 'require_name_email' );
 		$aria_req = ( $req ? " aria-required='true'" : '' );
 		$html5    = 'html5' === $args['format'];
 		$fields   =  array(
 			'author' => '<div class="col-md-6"><input id="author" class="" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' placeholder="'. ($req ? esc_html__('Your Name *','wozine') : esc_html__('Your Name','wozine')).'" /></div>',
 			'email'  => '<div class="col-md-6"><input id="email" class="l" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' placeholder="'.( $req ? esc_html__('Your Email *','wozine') : esc_html__('Your Email','wozine')).'" /></div>',
 			'url'    => '<div class="col-md-12"><input id="url" class="" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" placeholder="'. ($req ? esc_html__('Website','wozine') : esc_html__('Website','wozine')).'"  /></div>',
 		);
 
 		$required_text = '';
 
 		/**
 		 * Filter the default comment form fields.
 		 *
 		 * @since 3.0.0
 		 *
 		 * @param array $fields The default comment fields.
 		 */
 		$fields = apply_filters( 'comment_form_default_fields', $fields );
 		$defaults = array(
 			'fields'               => $fields,
 			'comment_field'        => '<div class="col-md-12"><textarea id="comment" name="comment" cols="25" rows="8" aria-required="true" class="" placeholder="'.esc_html__('Your Comment *','wozine').'"></textarea></div>',
 			'must_log_in'          => '<div class="col-md-12"><p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ,'wozine' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p></div>',
 			'logged_in_as'         => '<div class="col-md-12"><p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>','wozine'), get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p></div>',
 			'comment_notes_before' => '',
 			'comment_notes_after'  => '<div class="col-md-12"><p class="comment-notes">' . esc_html__( 'Your email address will not be published.','wozine' ) . ( $req ? $required_text : '' ) . '</p><p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s','wozine' ), ' <code>' . allowed_tags() . '</code>' ) . '</p></div>',
 			'id_form'              => 'commentform',
 			'id_submit'            => 'submit',
 			'title_reply'          => esc_html__( 'Leave a Reply','wozine' ),
 			'title_reply_to'       => esc_html__( 'Leave a Reply to %s','wozine'  ),
 			'cancel_reply_link'    => esc_html__( 'Cancel reply','wozine'  ),
 			'label_submit'         => esc_html__( 'Post comment' ,'wozine' ),
 			'format'               => 'xhtml',
 		);
 
 		/**
 		 * Filter the comment form default arguments.
 		 *
 		 * Use 'comment_form_default_fields' to filter the comment fields.
 		 *
 		 * @since 3.0.0
 		 *
 		 * @param array $defaults The default comment form arguments.
 	 	 */
 		$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );
 
 		?>
 		<?php if ( comments_open( $post_id ) ) : ?>
 			<?php
 			/**
 			 * Fires before the comment form.
 			 *
 			 * @since 3.0.0
 			 */
 			do_action( 'comment_form_before' );
 			
 			?>            
             
 			<div id="respond" class="form-send-comment comment-respond item-single-comment-form module">
 				<h5 class="comment-respond-heading"><span><?php echo esc_html__('Leave a comment','wozine');?></span></h5>
 				<?php if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) : ?>
 					<?php echo $args['must_log_in']; ?>
 					<?php
 					/**
 					 * Fires after the HTML-formatted 'must log in after' message in the comment form.
 					 *
 					 * @since 3.0.0
 					 */
 					do_action( 'comment_form_must_log_in_after' );
 					?>
 				<?php else : ?>
 					<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="comment-form form row"<?php echo $html5 ? ' novalidate' : ''; ?>>
 						<?php
 						/**
 						 * Fires at the top of the comment form, inside the <form> tag.
 						 *
 						 * @since 3.0.0
 						 */
 						do_action( 'comment_form_top' );
 						?>
						
 						<?php if ( is_user_logged_in() ) : ?>
 							<?php
 							/**
 							 * Filter the 'logged in' message for the comment form for display.
 							 *
 							 * @since 3.0.0
 							 *
 							 * @param string $args['logged_in_as'] The logged-in-as HTML-formatted message.
 							 * @param array  $commenter            An array containing the comment author's username, email, and URL.
 							 * @param string $user_identity        If the commenter is a registered user, the display name, blank otherwise.
 							 */
 							echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );
 							?>
 							<?php
 							/**
 							 * Fires after the is_user_logged_in() check in the comment form.
 							 *
 							 * @since 3.0.0
 							 *
 							 * @param array  $commenter     An array containing the comment author's username, email, and URL.
 							 * @param string $user_identity If the commenter is a registered user, the display name, blank otherwise.
 							 */
 							do_action( 'comment_form_logged_in_after', $commenter, $user_identity );
 							?>
 						<?php else : ?>
 							<?php echo $args['comment_notes_before']; ?>
 							<?php
 							/**
 							 * Fires before the comment fields in the comment form.
 							 *
 							 * @since 3.0.0
 							 */
 							do_action( 'comment_form_before_fields' );
 							/**
 							 * Fires after the comment fields in the comment form.
 							 *
 							 * @since 3.0.0
 							 */
 							do_action( 'comment_form_after_fields' );
 							?>
 						<?php endif; ?>
 						<?php
 						if (!is_user_logged_in() ) :
 						foreach ( (array) $args['fields'] as $name => $field ) {
 							/**
 							 * Filter a comment form field for display.
 							 *
 							 * The dynamic portion of the filter hook, $name, refers to the name
 							 * of the comment form field. Such as 'author', 'email', or 'url'.
 							 *
 							 * @since 3.0.0
 							 *
 							 * @param string $field The HTML-formatted output of the comment form field.
 							 */
 							echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
 						}
 						endif;
 						
 						
 						?>
 						<?php
 						/**
 						 * Filter the content of the comment textarea field for display.
 						 *
 						 * @since 3.0.0
 						 *
 						 * @param string $args['comment_field'] The content of the comment textarea field.
 						 */
 						echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );
 						?>
 						<div class="col-md-12">
 						<?php //echo $args['comment_notes_after']; ?>
 						<button name="submit" type="submit" id="<?php echo esc_attr( $args['id_submit'] ); ?>" class="btn btn-default"><?php echo esc_attr( $args['label_submit'] ); ?></button>
 							<?php comment_id_fields( $post_id ); ?>
 						<?php
 						/**
 						 * Fires at the bottom of the comment form, inside the closing </form> tag.
 						 *
 						 * @since 1.5.2
 						 *
 						 * @param int $post_id The post ID.
 						 */
 						do_action( 'comment_form', $post_id );
 						?>
 						</div>
 					</form>
 				<?php endif; ?>
 			</div><!-- #respond -->
 			<?php
 			/**
 			 * Fires after the comment form.
 			 *
 			 * @since 3.0.0
 			 */
 			do_action( 'comment_form_after' );
 		else :
 			/**
 			 * Fires after the comment form if comments are closed.
 			 *
 			 * @since 3.0.0
 			 */
 			do_action( 'comment_form_comments_closed' );
 		endif;
 	}
 }
 
 /*-----------------------------------------------------------------------------------*/
 /* You can add custom functions below */
 /*-----------------------------------------------------------------------------------*/
 
 
 
 
 
 
 
 
 /*-----------------------------------------------------------------------------------*/
 /* Don't add any code below here or the sky will fall down */
 /*-----------------------------------------------------------------------------------*/
 ?>