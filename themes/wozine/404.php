<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 *@package dawn
 */

get_header(); ?>
<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<header class="page-header">
				<div class="container">
					<div class="page-not-found__bg">
						<img src="<?php echo dt_get_theme_option('page_not_found__bg', get_template_directory_uri() . '/assets/images/404-bg.png'); ?>" alt="<?php esc_html_e('Page not found', 'wozine');?>">
					</div>
					<h1 class="page-not-found__title" ><?php esc_html_e( 'PAGE NOT FOUND...OOPS!', 'wozine' ); ?></h1>
				</div>
			</header>
			<div class="page-content">
				<div class="container">
					<div class="not-found-content">
						<div class="not-found-desc"><?php esc_html_e( "You can try using the search box below.", 'wozine' ); ?></div>
						<?php get_search_form(); ?>
						<div class="not-found-back-home">
							<div class="not-found-back-home__desc"><?php esc_html_e( "or", 'wozine' ); ?></div>
						<?php printf( __('<a class="back-home__link" title="Back to Home" href="%1$s">Back to Home</a>', 'wozine'),
							esc_url( home_url( '/' ) ) );
						?>
						</div>
					</div>

					
				</div>
			</div><!-- .page-content -->

		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->
<?php
get_footer();
