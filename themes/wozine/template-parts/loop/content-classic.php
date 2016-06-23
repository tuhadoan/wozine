<?php
/**
 * The template part for displaying content
 *
 * @subpackage Dawn
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class($post_class . $layout_class); ?> itemscope="">
	<?php
	if(get_post_format() == 'link'):
		$link = dt_get_post_meta('link');
		?>
		<div class="hentry-wrap hentry-wrap-link">
			<div class="entry-content">
				<div class="link-content">
					<a target="_blank" href="<?php echo esc_url($link) ?>">
						<cite><?php echo esc_url($link) ?></cite>
					</a>
				</div>
			</div>
		</div>
	<?php
	elseif (get_post_format() == 'quote'):?>
		<div class="hentry-wrap hentry-wrap-link">
			<div class="entry-content">
				<div class="quote-content">
					<?php if(has_post_thumbnail()):?>
					<div class="quote-thumb">
					<?php
					the_post_thumbnail();?>
					</div>
					<?php
					endif;
					?>
					<a href="<?php the_permalink()?>" class="quote-link">
						<cite><i class="fa fa-quote-left"></i></cite>
						<span class="quote">
							<?php echo dt_get_post_meta('quote'); ?>
						</span>
						<?php if(dt_get_post_meta('quote') != ''): ?>
						<div class="quote-author"><?php echo esc_html__('By ', 'wozine') . dt_get_post_meta('quote_author');?></div>
						<?php endif; ?>
					</a>
				</div>
			</div>
		</div>
	<?php
	else:?>
	<?php 
	$entry_featured_class = '';
	dt_post_featured('','',true,false,$entry_featured_class,'default');
	?>
	<?php endif;?>
	<div class="post-content">
		<header class="post-header">
			<?php if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) && dt_categorized_blog() ) : ?>
			<div class="post-category">
				<span class="cat-links"><?php echo get_the_category_list( esc_html_x( ' ', 'Used between list items, there is a space after the comma.', 'wozine' ) ); ?></span>
			</div>
			<?php
			endif;
			?>
			<?php	
			the_title( '<h2 class="post-title" data-itemprop="name"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			?>
		</header><!-- .entry-header -->
		
		<div class="post-excerpt">
			<?php the_excerpt(); ?>
		</div>
		
		<div class="post-meta">
			<?php
			printf('<span class="byline"><span class="author vcard">%1$s <a class="url fn n" href="%2$s" rel="author">%3$s</a></span></span>',
				__('By','wozine'),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);
			?>
			<?php 
			dt_posted_on();
			?>
			<?php edit_post_link( esc_html__( 'Edit', 'wozine' ), '<span class="edit-link">', '</span>' ); ?>
			<?php
				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) :
			?>
			<span class="comments-link"><i class="fa fa-comment-o"></i><?php comments_popup_link( esc_html__( 'Leave a comment', 'wozine' ), esc_html__( '1 Comment', 'wozine' ), esc_html__( '% Comments', 'wozine' ) ); ?></span>
			<?php
				endif;
	
			?>
		</div><!-- .entry-meta -->
		<?php the_tags( '<footer class="tags-list"><span class="tag-title"><i class="fa fa-tags"></i> '.esc_html__('Tags:', 'wozine').' </span><span class="tag-links">', ' , ', '</span></footer>' ); ?>
	</div>
</article><!-- #post-## -->
