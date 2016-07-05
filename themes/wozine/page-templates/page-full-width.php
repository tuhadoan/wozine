<?php
/**
 * Template Name: Full Width Page
 *
 * @subpackage Dawn
 */
get_header(); ?>
<div id="main-content" class="main-content">
	<div class="container-full">
		<div class="row">
			<div id="primary" class="content-area col-md-12">
				<div id="content" class="site-content" role="main">
					<?php
						// Start the Loop.
						while ( have_posts() ) : the_post();

							the_content();

							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) {
								comments_template();
							}
						endwhile;
					?>
				</div><!-- #content -->
			</div><!-- #primary -->
		</div>
	</div>
	
</div><!-- #main-content -->

<?php
get_footer();
