<?php
/**
 * The template part for displaying single posts
 *
 * @package Dawn
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php dt_post_featured(); ?>
	<header class="entry-header">
		<?php if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) && dt_categorized_blog() ) : ?>
		<div class="post-category">
			<span class="cat-links"><?php echo get_the_category_list( esc_html_x( ' ', 'Used between list items, there is a space after the comma.', 'wozine' ) ); ?></span>
		</div>
		<?php
		endif;
			the_title( '<h1 class="entry-title">', '</h1>' );
		?>

		<div class="entry-meta">
			<?php
			printf('<span class="byline"><span class="author vcard">%1$s <a class="url fn n" href="%2$s" rel="author">%3$s</a></span></span>',
				esc_html__('By', 'wozine'),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);
			?>
			<?php
			if ( 'post' == get_post_type() )
				dt_posted_on();
		
			edit_post_link( __( ' Edit', 'wozine' ), '<span class="edit-link">', '</span>' );
			?>
			<div class="entry-meta__express">
			<?php
			if ( comments_open() && get_comments_number() ) :
			?>
				<span class="comments-link"><i class="fa fa-comments"></i><?php comments_popup_link( esc_html__( '', 'wozine' ), esc_html__( '1', 'wozine' ), __( '%', 'wozine' ) ); ?></span>
			<?php
			endif;
			?>
			</div>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->
	
	<div class="post-content">
		<?php dt_print_social_share(); ?>
		<div class="entry-content">
			<?php
				the_content();
	
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'wozine' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
					'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'wozine' ) . ' </span>%',
					'separator'   => '<span class="screen-reader-text">, </span>',
				) );
			?>
			<?php the_tags( '<footer class="tags-list"><span class="tag-title"><i class="fa fa-tags"></i> '.esc_html__('Tags:', 'wozine').' </span><span class="tag-links">', ' , ', '</span></footer>' ); ?>
		</div> <!-- .entry-content -->
	</div>
</article><!-- #post-## -->
