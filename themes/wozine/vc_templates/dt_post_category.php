<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$output = array();
extract(shortcode_atts(array(
	'template'			=>'grid',
	'title'				=>'',
	'sub_title'			=>'',
	'icon'				=>'',
	'icon_color'		=> dt_get_theme_option('main_color', '#54af7d'),
	'posts_per_page'	=>'3',
	'orderby'			=>'latest',
	'category'			=>'',
	'visibility'		=>'',
	'el_class'			=>'',
), $atts));

if(empty($category)){
	return;
}
$template = ($template !== '') ? $template : 'grid';
$sc_id = dt_sc_get_id();
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

$posts_per_page = ($template == 'grid') ? '5' : $posts_per_page;

$args = array(
	'category_name'   => "{$category}",
	'orderby'         => "{$orderby}",
	'order'           => "{$order}",
	'post_type'       => "post",
	'posts_per_page'  => $posts_per_page,
);

$p = new WP_Query($args);

if($p->have_posts()):
?>
<div id="<?php echo esc_attr($sc_id);?>" class="dt-post-category wpb_content_element dt-preload <?php echo esc_attr( $class ) . ' tem-'.$template;?>">
	<div class="dt-post-category__wrap">
		<div class="dt-post-category__heading">
			<?php if( $icon != '' ):?>
			<?php 
			// icon color style inline
			$icon_style = '';
			if($icon_color != ''){
				$iconColor = dt_format_color($icon_color);
				$icon_style = 'style="border-color: '.$iconColor.'; color: '.$iconColor.';"';
			}
			?>
			<div class="dt-post-category__icon" <?php echo $icon_style;?>>
				<i class="<?php echo esc_attr($icon);?>" aria-hidden="true"></i>
			</div>
			<?php endif;?>
			<?php if( $title != '' ):?>
			<div class="dt-post-category__title">
				<h5 class="dt-title"><?php echo esc_html($title);?></h5>
				<?php if( $sub_title != '' ):?>
				<span><?php echo esc_html($sub_title);?></span>
				<?php endif;?>
			</div>
			<?php endif;?>
			<?php if($template == 'grid'):?>
			<div class="dt-next-prev-wrap" data-cat="<?php esc_attr_e($category)?>" data-orderby="<?php esc_attr_e($orderby)?>" data-order="<?php esc_attr_e($order)?>" data-posts-per-page="5" data-target="<?php echo esc_attr($sc_id);?>" data-template="post-category">
				<a href="#" class="dt-ajax-prev-page ajax-page-disabled" data-offset="0" data-current-page="1"><i class="fa fa-chevron-left"></i></a>
				<a href="#" class="dt-ajax-next-page <?php echo ($p->found_posts <= 5) ? 'ajax-page-disabled' : '';?>" data-offset="5" data-current-page="1"><i class="fa fa-chevron-right"></i></a>
			</div>
			<?php endif;?>
		</div>
		<div class="dt-post-category__grid dt-content__wrap">
			<?php if($template == 'grid'):?>
			<div class="dt-nav-ajax-loading">
				<div class="dt-nav-fade-loading"><i></i><i></i><i></i><i></i></div>
			</div>
			<?php endif;?>
			<div class="dt-content <?php echo ' tem-'.$template;?>">
			<?php
				$i = 0;
				global $post;
				switch ($template){
					case 'list_big'; case 'list_small':
						while ($p->have_posts()): $p->the_post();?>
							<?php 
							$i++;
							dt_get_template('tpl-post-category-list.php',
								array(
									'template' => $template,
									'post' => $post,
									'i' => $i,
								),
								'vc_templates/tpl', 'vc_templates/tpl'
							);
							?>
						<?php endwhile;
						break;
					default: // grid
						while ($p->have_posts()): $p->the_post();?>
							<?php 
							$i++;
							dt_get_template('tpl-post-category.php',
								array(
									'post' => $post,
									'i' => $i,
								),
								'vc_templates/tpl', 'vc_templates/tpl'
							);
							?>
						<?php endwhile;
						break;
				}
			?>
			</div>
		</div>
	</div>
</div>
<?php
endif;
wp_reset_postdata();
?>