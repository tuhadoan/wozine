<?php
/**
 * The Template for displaying all single posts
 *
 * @package dawn
 */

get_header(); ?>

<div class="container">
	<div class="row">
		<div id="primary" class="content-area col-md-8">
			<div id="content" class="site-content dawn-single-post" role="main">
				<?php
					// Start the Loop.
					while ( have_posts() ) : the_post();
						
						get_template_part( 'template-parts/single/content', get_post_format() );
						
						if ( 'post' === get_post_type() ) :?>
						<div class="author-info">
						<?php
							$author_avatar_size = apply_filters( 'dt_author_avatar_size', 340 );
						?>
							<div class="author-avatar">
								<?php echo get_avatar( get_the_author_meta( 'user_email' ), $author_avatar_size ); ?>
							</div>
							<div class="author-description">
								<div class="author-primary">
									<h5 class="author-title">
										<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>"><?php echo get_the_author(); ?></a>
									</h5>
									<div class="author-socials">
										<?php dt_show_author_social_links('', get_the_author_meta( 'ID' ), 'echo'); ?>
									</div>
								</div>
								<div class="author-desc"><?php echo get_the_author_meta('description'); ?></div>
							</div>
						</div>
						<?php
						endif;
						
						get_template_part( 'template-parts/single/single', 'related' );

						// If comments are open or we have at least one comment, load up the comment template.
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
					endwhile; // end of the loop.
				?>
			</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
	</div><!-- .row -->
</div><!-- #container -->
<?php
get_footer();
