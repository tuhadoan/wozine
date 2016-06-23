<?php
/**
 * The template part for displaying related posts
 *
 * @package Dawn
 */
?>

<div class="related-post-item">
	<article id="post-<?php the_ID(); ?>" class="post">
		<?php 
		if( has_post_thumbnail() ):?>
			<div class="rp-thumbnail dt-effect6">
				<a href="<?php echo esc_url(get_permalink()); ?>" title="<?php the_title();?>">
				<?php the_post_thumbnail('wozine-related-post-thumbnails');?>
				</a>
			</div>
			<?php
		endif;
		?>
		<?php the_title( sprintf('<h6 class="related-post-title h6 "><a href="%s" rel="bookmark">', esc_url(get_permalink()) ), '</a></h6>' ); ?>
		<div class="entry-meta">
			<?php
			printf('<div class="byline"><span class="author vcard">%1$s <a class="url fn n" href="%2$s" rel="author">%3$s</a></span></div>',
				esc_html__('By', 'wozine'),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);
			?>
			<?php
			dt_posted_on();
			?>
		</div><!-- .entry-meta -->
	</article>
</div>
