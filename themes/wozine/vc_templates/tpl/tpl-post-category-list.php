<?php
/**
 * The template part for displaying content
 *
 * @subpackage Dawn
 */
?>
<div class="post-item <?php echo 'item-'.$i;?>">
	<article id="post-<?php the_ID(); ?>" class="post">
		<?php 
		if( has_post_thumbnail() ):?>
			<div class="post-thumbnail entry-featured dt-effect0">
				<a href="<?php echo esc_url(get_permalink()); ?>" title="<?php the_title();?>">
				<?php
				if($i == 1){
					if($template == 'list_big'){
						the_post_thumbnail('wozine-post-category-tpllist-big-thumb');
					}else{
						the_post_thumbnail('wozine-posts-slider-thumb');
					}
				}else{
					the_post_thumbnail('wozine-posts-slider-thumb');
				}
				?>
				</a>
			</div>
			<?php
		endif;
		?>
		<div class="post-content">
			<?php 
			if($i == 1 ||  $template == 'list_big'):
			$category = get_the_category();
			$cat_ID = $category[0]->term_id;
			$representative_color = get_option( "dt_category_representative_color$cat_ID");
			$style_inline = '';
			if( !empty($representative_color) ){
				$style_inline = 'style="color:'. $representative_color .';"';
			}
			if ($category) {
				echo '<a '.$style_inline.' class="dt-post-category" href="' . get_category_link( $cat_ID ) . '" title="' . sprintf( __( "View all posts in %s", "wozine" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> ';
			}
			endif;
			?>
			<?php the_title( sprintf('<h3 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink()) ), '</a></h3>' ); ?>
			<?php 
			if($i == 1 || $template == 'list_big'):
			?>
			<div class="post-excerpt">
				<?php 
				$excerpt = $post->post_excerpt;
				if(empty($excerpt))
					$excerpt = $post->post_content;
				$excerpt = strip_shortcodes($excerpt);
				$excerpt = wp_trim_words($excerpt, 20,'...');
				echo ( $excerpt );
				?>
			</div>
			<?php endif; ?>
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
			</div>
		</div>
	</article>
</div>