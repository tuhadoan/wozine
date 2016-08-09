<?php
/**
 * The Header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package dawn
 */
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]> -->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
</head>
<?php 
$is_sticky_menu = dt_get_theme_option('sticky_menu','yes') == 'yes' ? ' is_sticky_menu' : '';
?>
<body <?php body_class(); ?>>
<div class="offcanvas">
      <div class="dt-sidenav-wrapper">
			<?php if( has_nav_menu('main-menu') ): ?>
			<nav id="side-navigation" class="site-navigation side-navigation">
				<?php wp_nav_menu( array( 'theme_location' => 'main-menu', 'menu_class' => 'nav-menu', 'menu_id' => 'main-menu' ) ); ?>
			</nav>
			<?php else :?>
			<p class="dt-alert"><?php esc_html_e('Please sellect menu for Main navigation', 'wozine'); ?></p>
			<?php endif; ?>
		</div>
</div>

<div id="page" class="hfeed site">
	<div class="offcanvas-overlay"></div>
	<div id="dt-sticky-navigation-holder" data-height="60">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="menu-toggle"><i class="fa fa-bars"></i></div>
					<div class="sticky-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="logo" src="<?php echo dt_get_theme_option('logo', DT_ASSETS_URI . '/images/logo.png');?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"/></a>
					</div>
					<div class="dt-sticky-mainnav-wrapper">
						
					</div>
				</div>
			</div>
		</div>
	</div>

	<header id="header" class="site-header <?php echo esc_attr( $is_sticky_menu );?>" role="banner">
		<div class="top-header">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-sm-12">
						<div class="menu-toggle"><i class="fa fa-bars"></i></div>
						<div class="logo-wrap">
							<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="logo" src="<?php echo dt_get_theme_option('logo', DT_ASSETS_URI . '/images/logo.png');?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"/></a></h1>
						</div>
					</div>
					<div class="col-md-6 col-sm-12">
						<div class="top-header-box visible-lg">
							<form id="search-box" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="GET" class="" tabindex="-1" role="form">
				              <input type="search" value="<?php echo get_search_query(); ?>" name="s" placeholder="<?php esc_attr_e('Type to search&hellip;', 'wozine');?>" class="keywords">
				              <button type="submit"><i class="fa fa-search search-icon"></i></button>
				            </form>      
						</div>
						<div class="top-header-socials visible-lg">
							<ul>
								<li><a href="#" title=""><i class="fa fa-facebook"></i></a></li>
								<li><a href="#" title=""><i class="fa fa-twitter"></i></a></li>
								<li><a href="#" title=""><i class="fa fa-google-plus"></i></a></li>
								<li><a href="#" title=""><i class="fa fa-vimeo"></i></a></li>
								<li><a href="#" title=""><i class="fa fa-instagram"></i></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="dt-main-menu">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="header-main">
							<div class="menu-toggle"><i class="fa fa-bars"></i></div>
							<div class="sticky-logo">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="logo" src="<?php echo dt_get_theme_option('logo', DT_ASSETS_URI . '/images/logo.png');?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"/></a>
							</div>
							<div class="dt-mainnav-wrapper">
								<?php if( has_nav_menu('main-menu') ): ?>
								<nav id="primary-navigation" class="site-navigation primary-navigation">
									<?php
										wp_nav_menu(array( 'theme_location'  => 'main-menu','is_megamenu' => true));
									?>
								</nav>
								<?php else :?>
								<p class="dt-alert"><?php esc_html_e('Please sellect menu for Main navigation', 'wozine'); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php if (!is_search() && !is_front_page()  && !is_404()) : ?>
		<div id="dt_breadcrumbs" class="wrap">
			<div class="container">
				<div class="dt_breadcrumb__wrapper clearfix">
					<?php do_action('wozine_breadcrumbs', 10); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</header><!-- #header -->
	<?php $no_padding = dt_get_post_meta('no_padding'); ?>
	<div id="main" class="site-main <?php echo (!empty($no_padding) ? ' no-padding':'') ?>">
