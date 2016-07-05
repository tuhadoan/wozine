<?php
/**
 * @package dawn
 */

$output = array();
extract(shortcode_atts(array(
	'title'				=>'',
	'sub_title'			=>'',
	'icon'				=>'',
	'icon_color'		=> dt_get_theme_option('main_color', '#54af7d'),
	'layout'			=>'default',
	'columns'			=>2,
	'posts_per_page'	=>'10',
	'orderby'			=>'latest',
	'categories'		=>'',
	'exclude_categories'=>'',
	'pagination'		=>'wp_pagenavi',
	'loadmore_text'		=>__('Load More','wozine'),
	'visibility'		=>'',
	'el_class'			=>'',
), $atts));
if($layout == 'masonry'){
	wp_enqueue_script('vendor-isotope');
}
if($pagination === 'infinite_scroll'){
	wp_enqueue_script('vendor-infinitescroll');
}
$sc_id = dt_sc_get_id();
$class = !empty($el_class) ?  ' '.esc_attr( $el_class ) : '';
$class .= dt_visibility_class($visibility);

if( is_front_page() || is_home()) {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
} else {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
}

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
	'posts_per_page'  => "-1",
	'paged'			  => $paged
);
if(!empty($posts_per_page))
	$args['posts_per_page'] = $posts_per_page;

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

$itemSelector = '';
$itemSelector .= (($pagination === 'infinite_scroll') ? '.post.infinite-scroll-item':'');
$itemSelector .= (($pagination === 'loadmore') ? '.post.loadmore-item':'');


if($r->have_posts()):
?>
<div id="<?php echo esc_attr($sc_id);?>" class="dt-blog-sc wpb_content_element <?php echo esc_attr( $class );?>">
	<div class="dt-blog-sc__wrap">
		<?php if( $title != '' ):?>
		<div class="dt-blog-sc__heading">
			<?php if( $icon != '' ):?>
			<?php 
			// icon color style inline
			$icon_style = '';
			if($icon_color != ''){
				$iconColor = dt_format_color($icon_color);
				$icon_style = 'style="border-color: '.$iconColor.'; color: '.$iconColor.';"';
			}
			?>
			<div class="dt-blog-sc__icon" <?php echo $icon_style;?>>
				<i class="<?php echo esc_attr($icon);?>" aria-hidden="true"></i>
			</div>
			<?php endif;?>
			<div class="dt-blog-sc__title">
				<h5 class="dt-title"><?php echo esc_html($title);?></h5>
				<?php if( $sub_title != '' ):?>
				<span><?php echo esc_html($sub_title);?></span>
				<?php endif;?>
			</div>
		</div>
		<?php endif;?>
		<div class="dt-content__wrap">
			<div data-itemselector="<?php echo esc_attr($itemSelector)  ?>"  class="posts <?php echo (($pagination === 'loadmore') ? ' loadmore':''); ?><?php echo (($pagination === 'infinite_scroll') ? ' infinite-scroll':'') ?><?php echo (($layout === 'masonry') ? ' masonry':'') ?>" data-paginate="<?php echo esc_attr($pagination) ?>" data-layout="<?php echo esc_attr($layout) ?>"<?php echo ($layout === 'masonry') ? ' data-masonry-column="'.$columns.'"':''?>>
				<div class="posts-wrap<?php echo (($pagination === 'loadmore') ? ' loadmore-wrap':'') ?><?php echo (($pagination === 'infinite_scroll') ? ' infinite-scroll-wrap':'') ?><?php echo (($layout === 'masonry') ? ' masonry-wrap':'') ?> posts-layout-<?php echo esc_attr($layout)?><?php if( $layout == 'default' || $layout == 'grid' || $layout == 'masonry') echo' row' ?>">
				<?php
				// Start the Loop.
				$i = 0;
				while ($r->have_posts() ) : $r->the_post();?>
					<?php
					$post_class = '';
					$post_class .= (($pagination === 'infinite_scroll') ? ' infinite-scroll-item':'');
					$post_class .= (($pagination === 'loadmore') ? ' loadmore-item':'');
					if($layout == 'masonry')
						$post_class.=' masonry-item';
					?>
					<?php
						$blog_layout = ($layout == 'default')  ? '' : '-'.$layout;
						$layout_class = '';
						if($layout == 'classic'){
							if($i == 0 || $i == 4){
								$layout_class = ' post_classic_full';
								$i = 1;
							}else{
								$layout_class = ' post_classic';
							}
						}
						
						dt_get_template('content'.$blog_layout.'.php', array(
							'post_class' => $post_class,
							'columns' => $columns,
							'layout_class' => $layout_class,
						),
						'template-parts/loop', 'template-parts/loop'
						);
						$i++;
					?>
				<?php
				endwhile;
				?>
				</div>
				<?php
				// Previous/next post navigation.
				// this paging nav should be outside .posts-wrap
				$paginate_args = array();
				switch ($pagination){
					case 'loadmore':
						dt_paging_nav_ajax($loadmore_text, $r);
						$paginate_args = array('show_all'=>true);
						break;
					case 'infinite_scroll':
						$paginate_args = array('show_all'=>true);
						break;
				}
				if($pagination != 'no') dt_paginate_links($paginate_args, $r);
				?>
			</div>
		</div>
	</div>
</div>
<?php
endif;
wp_reset_postdata();
?>