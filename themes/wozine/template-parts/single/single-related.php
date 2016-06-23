<?php
/**
 * The Template for displaying all related posts by Category & tag.
 *
 * @package Dawn
 */
?>
<?php
	
	$show_related_post = dt_get_theme_option('show_related_posts','1');
	
	$number = dt_get_theme_option('related_posts_count', 6);
		
	if($show_related_post == '0') return;
		
	$related_items = dt_get_related_posts();
	
	if(!$related_items->have_posts()) return;
	
	wp_enqueue_style('slick');
	wp_enqueue_script('slick');
	
?>
	<div class="related_posts">
		<div class="related_posts__wrapper">
			<div class="related-posts__heading">
				<h5 class="related-posts__title"><?php esc_html_e('Related Posts', 'wozine');?></h5>
			</div>
	      	<div class="related_posts-slider dt-preload">
					<?php
				
					while ( $related_items->have_posts() ) : $related_items->the_post();
						get_template_part( 'template-parts/single/content','related');
					
					endwhile;
				?>
			</div>
		</div>
	</div>
<?php
    wp_reset_postdata(); 
