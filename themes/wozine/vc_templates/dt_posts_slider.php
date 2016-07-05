<?php
$output = array();
extract(shortcode_atts(array(
	'mode'				=>'',
	'title'				=>'',
	'sub_title'			=>'',
	'icon'				=>'',
	'icon_color'		=> dt_get_theme_option('main_color', '#54af7d'),
	'posts_to_show'		=>'3',
	'posts_per_page'	=>'9',
	'orderby'			=>'latest',
	'categories'		=>'',
	'exclude_categories'=>'',
	'show_cat'			=>'show',
	'show_excerpt'		=>'show',
	'visibility'		=>'',
	'el_class'			=>'',
), $atts));

$class          = !empty($el_class) ?  ' '.esc_attr( $el_class ) : '';
$class .= dt_visibility_class($visibility);

$order = 'DESC';
switch ($orderby) {
	case 'latest':
		$orderby = 'date';
		break;

	case 'oldest':
		$orderby = 'date';
		$order = 'ASC';
		break;

	case 'alphabet':
		$orderby = 'title';
		$orderby = 'ASC';
		break;

	case 'ralphabet':
		$orderby = 'title';
		break;

	default:
		$orderby = 'date';
		break;
}

$args = array(
	'orderby'         => "{$orderby}",
	'order'           => "{$order}",
	'post_type'       => "post",
	'posts_per_page'  => $posts_per_page,
);

if(!empty($categories)){
	$args['category_name'] = $categories;
}
if(!empty($exclude_categories)){
	$args['tax_query'][] =  array(
			'taxonomy' => 'category',
			'terms'    => explode(',',$exclude_categories),
			'field'    => 'slug',
			'operator' => 'NOT IN'
	);
}
$r = new WP_Query($args);

if($r->have_posts()):
wp_enqueue_style('slick');
wp_enqueue_script('slick');
$data_arrows = ($title != '') ? '1' : '0';
$dots = 'true';
if($mode == 'single_mode'){
	$dots = 'false';
	$data_arrows = '1';
	$posts_to_show = '1';
	$posts_to_show = '1';
}
?>
<div class="dt-posts-slider wpb_content_element dt-preload <?php echo esc_attr( $class . $mode );?>" data-mode="<?php echo esc_attr($mode);?>" data-visible="<?php echo esc_attr($posts_to_show)?>" data-scroll="<?php echo esc_attr($posts_to_show)?>" data-infinite="true" data-autoplay="false" data-arrows="<?php echo esc_attr($data_arrows);?>" data-dots="<?php echo esc_attr($dots);?>">
	<div class="dt-posts-slider__wrap">
		<?php if($title !=''):?>
		<div class="dt-post-slider__heading">
			<?php if( $icon != '' ):?>
			<?php 
			// icon color style inline
			$icon_style = '';
			if($icon_color != ''){
				$iconColor = dt_format_color($icon_color);
				$icon_style = 'style="border-color: '.$iconColor.'; color: '.$iconColor.';"';
			}
			?>
			<div class="dt-post-slider__icon" <?php echo $icon_style;?>>
				<i class="<?php echo esc_attr($icon);?>" aria-hidden="true"></i>
			</div>
			<?php endif;?>
			<?php if( $title != '' ):?>
			<div class="dt-post-slider__title">
				<h5 class="dt-slider-title"><?php echo esc_html($title);?></h5>
				<?php if( $sub_title != '' ):?>
				<span><?php echo esc_html($sub_title);?></span>
				<?php endif;?>
			</div>
			<?php endif;?>
		</div>
		<?php endif; ?>
		<div class="posts-slider <?php echo esc_attr($mode);?>">
			<?php
			switch ($mode){
				case 'single_mode':
					while ($r->have_posts()): $r->the_post();
					?>
						<div class="post-item-slide">
							<article id="post-<?php the_ID(); ?>" class="post">
								<?php 
								if( has_post_thumbnail() ):?>
									<div class="post-thumbnail">
										<a href="<?php echo esc_url(get_permalink()); ?>" title="<?php the_title();?>">
										<?php the_post_thumbnail('wozine-posts-slider-single_mode_thumb');?>
										</a>
									</div>
									<?php
								endif;
								?>
								<div class="post-content">
									<?php
									$category = get_the_category();
									$cat_ID = $category[0]->term_id;
									if ($category) {
										echo '<a class="dt-post-category" href="' . get_category_link( $cat_ID ) . '" title="' . sprintf( __( "View all posts in %s", "wozine" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> ';
									}
									?>
									<?php the_title( sprintf('<h3 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink()) ), '</a></h3>' ); ?>
									
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
					<?php endwhile;
					break;
				default:
					while ($r->have_posts()): $r->the_post(); global $post;
					$featured_class = !empty(get_post_format()) ? ' '.get_post_format().'-featured' : '';
					?>
						<div class="post-item-slide">
							<article id="post-<?php the_ID(); ?>" class="post">
								<?php 
								if( has_post_thumbnail() ):?>
									<div class="post-thumbnail entry-featured dt-effect0 <?php echo esc_attr($featured_class);?>">
										<a href="<?php echo esc_url(get_permalink()); ?>" title="<?php the_title();?>">
										<?php the_post_thumbnail('wozine-posts-slider-thumb');?>
										<?php 
										if(get_post_format() == 'video'){
											echo '<i class="fa fa-play-circle-o" aria-hidden="true"></i>';
										}
										?>
										</a>
									</div>
									<?php
								endif;
								?>
								<?php 
								if( $show_cat != 'hide'):
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
								if( $show_cat != 'hide'):?>
								<div class="post-excerpt">
									<?php 
									$excerpt = $post->post_excerpt;
									if(empty($excerpt))
										$excerpt = $post->post_content;
									$excerpt = strip_shortcodes($excerpt);
									$excerpt = wp_trim_words($excerpt, 10,'...');
									echo ( $excerpt );
									?>
								</div>
								<?php endif;?>
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
					<?php endwhile;
					break;
			}
			?>
		</div>
	</div>
</div>
<?php
endif;
wp_reset_postdata();
?>