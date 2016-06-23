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
          <!-- BEGIN: Sidebar Offcanvas Header -->
          <div class="sidebar-offcanvas-header">
            <!-- BEGIN: User Panel -->
            <div class="user-panel pull-left">
              <div class="ava">
                <i class="fa fa-user"></i>
              </div>
              <div class="user-welcome">Hello, <a href="#">Admin</a></div>
            </div><!-- END: User Panel -->
            <!-- BEGIN: Search Box -->
            <form id="search-box" action="#" method="post" class="search-box" tabindex="-1">
              <i class="fa fa-search search-icon"></i>
              <input type="text" value="" placeholder="What do you want to find?" class="keywords">
            </form><!-- END: Search Box -->            
            <!-- BEGIN: Toggle Wrap -->
            <div class="toggle-wrap pull-right">
              <span class="toggle-icon search-toggle"><i class="fa fa-search"></i></span>
            </div><!-- END: Toggle Wrap -->
          </div><!-- END: Sidebar Offcanvas Header -->
          <!-- BEGIN: User Menu -->
          <div class="box-user-menu">
            <ul class="user-menu">
              <li><a href="#"><i class="fa fa-pencil-square-o menu-item-icon"></i> Profile <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#"><i class="fa fa-clock-o menu-item-icon"></i> Order History <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#"><i class="fa fa-ticket menu-item-icon"></i> My Tickets <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#"><i class="fa fa-money menu-item-icon"></i> Account Balance <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#"><i class="fa fa-cog menu-item-icon"></i> Setting <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
            </ul>
          </div><!-- END: User Menu -->
          <!-- BEGIN: Create an Event -->
          <div class="box-create-event">
            <button class="button-create-event" data-toggle="modal" data-target="#creat-event-modal"><i class="fa fa-plus-square"></i> Create an Event</button>
          </div><!-- END: Create an Event-->
          <!-- BEGIN: Cart -->
          <div class="box-cart">
            <h3 class="cart-title"><i class="fa fa-shopping-cart"></i> Cart</h3>
            <a class="cart-view" href="#">
              Have 1 item(s).  Total: <strong>$42.00</strong>
            </a>
          </div><!-- END: Cart -->
          <!-- BEGIN: Categories -->
          <div class="box-categories">
            <h3 class="box-categories-title"><i class="fa fa-folder-o"></i> Categories</h3>
            <ul class="cat-menu">
              <li><a href="#">All <span class="count">18</span> <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#">Entertaiment <span class="count">08</span> <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#">Networking &amp; Meetup <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#">Education <span class="count">04</span> <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#">Community &amp; Charity <span class="count">03</span> <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#">Seminars &amp; Workshops <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#">Sport <span class="count">03</span> <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
              <li><a href="#">Exhibitions <i class="fa fa-angle-right menu-item-arrow"></i></a></li>
            </ul>
          </div><!-- END: Categories -->
</div>

<div id="page" class="hfeed site">
	<div class="offcanvas-overlay"></div>
	<div id="dt-sticky-navigation-holder" data-height="60">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="menu-toggle"><i class="fa fa-bars"></i></div>
					<div class="sticky-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="logo" src="<?php echo dt_get_theme_option('logo', DT_ASSETS_URI . '/images/logo.png');?>"/></a>
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
							<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="logo" src="<?php echo dt_get_theme_option('logo', DT_ASSETS_URI . '/images/logo.png');?>"/></a></h1>
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
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img class="logo" src="<?php echo dt_get_theme_option('logo', DT_ASSETS_URI . '/images/logo.png');?>"/></a>
							</div>
							<div class="dt-mainnav-wrapper">
								<?php if( has_nav_menu('main-menu') ): ?>
								<nav id="primary-navigation" class="site-navigation primary-navigation">
									<?php wp_nav_menu( array( 'theme_location' => 'main-menu', 'menu_class' => 'nav-menu', 'menu_id' => 'main-menu' ) ); ?>
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
