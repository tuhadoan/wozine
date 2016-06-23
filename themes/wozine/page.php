<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other 'pages' on your WordPress site will use a different template.
 *
 * @package dawn
 */
get_header(); ?>

<div id="main-content" class="main-content">
	<div class="container">
		<div class="row">
			<div id="primary" class="content-area col-md-8">
				<div id="content" class="site-content" role="main">

					<?php
						// Start the Loop.
						while ( have_posts() ) : the_post();

							// Include the page content template.
							get_template_part( 'template-parts/single/content', 'page' );

							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) {
								comments_template();
							}
						endwhile;
					?>

				</div><!-- #content -->
			</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
		</div><!-- .row -->
	</div><!-- #container -->
</div><!-- #main-content -->

<?php
get_footer();
