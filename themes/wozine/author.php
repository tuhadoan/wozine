<?php
/**
 * The template for displaying Author archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package dawn
 */

get_header(); ?>
<div class="container">
	<div class="row">
		<section id="primary" class="content-area col-md-8">
			<div id="content" class="site-content" role="main">

				<?php if ( have_posts() ) : ?>

				<header class="archive-header">
					<h1 class="archive-title">
						<?php
							/*
							 * Queue the first post, that way we know what author
							 * we're dealing with (if that is the case).
							 *
							 * We reset this later so we can run the loop properly
							 * with a call to rewind_posts().
							 */
							the_post();

							printf( __( 'All posts by %s', 'wozine' ), get_the_author() );
						?>
					</h1>
					<?php if ( get_the_author_meta( 'description' ) ) : ?>
					<div class="author-description"><?php the_author_meta( 'description' ); ?></div>
					<?php endif; ?>
				</header><!-- .archive-header -->

				<?php
						/*
						 * Since we called the_post() above, we need to rewind
						 * the loop back to the beginning that way we can run
						 * the loop properly, in full.
						 */
						rewind_posts();

						// Start the Loop.
						while ( have_posts() ) : the_post();
							get_template_part( 'template-parts/loop/content', get_post_format() );

						endwhile;
						// Previous/next page navigation.
						dt_paging_nav();

					else :
						// If no content, include the "No posts found" template.
						get_template_part( 'template-parts/loop/content', 'none' );

					endif;
				?>

			</div><!-- #content -->
	</section><!-- #primary -->

<?php
get_sidebar( 'content' );
?>
	</div><!-- .row -->
</div><!-- #container -->
<?php
get_footer();
