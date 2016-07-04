<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$output = '';
extract(shortcode_atts(array(
	'layout_style'      =>'layout_1',
	'orderby'			=>'latest',
	'order'   			=>'asc',
	'visibility'		=>'',
	'el_class'			=>'',
), $atts));

$class = !empty($el_class) ?  ' '.esc_attr( $el_class ) : '';
$class .= dt_visibility_class($visibility);

$order = 'DESC';
switch ($orderby) {
	case 'latest':
		$orderby = 'date';
		break;
		
	case 'featured':
		$orderby = 'meta_value';
		break;
		
	case 'random':
		$orderby = 'rand';
		break;

	case 'oldest':
		$orderby = 'date';
		$order = 'ASC';
		break;

	case 'alphabet':
		$orderby = 'title';
		$order = 'ASC';
		break;

	case 'ralphabet':
		$orderby = 'title';
		break;

	default:
		$orderby = 'date';
		break;
}

$posts_per_page = 4;
$args = array(
	'orderby'         => "{$orderby}",
	'order'           => "{$order}",
	'post_type'       => "post",
	'posts_per_page'  => $posts_per_page
);
// if($orderby == 'meta_value'){
// 	$args['meta_key'] = 'post_meta_featured_post',
// 	$args['meta_value'] = 'yes'
// }

if(!empty($categories)){
	$args['category_name'] = $categories;
}
$r = new WP_Query($args);
if($r->have_posts() && $r->post_count >= 4):
	ob_start();
	$i = 0;
	?>
	<div class="dt-smart-content-box wpb_content_element <?php echo esc_attr( $class );?>">
		<div class="smart-content-box__wrap <?php echo $layout_style; ?>">
			<?php while ($r->have_posts()): $r->the_post();?>
					<?php if($i == 0):?>
						<div class="dt-smcb-block1 dt-smcb-post dt-big-grid-post-0">
							<div class="dt-module-thumb">
								<a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>">
									<?php the_post_thumbnail('wozine-smart-content-box-big-thumb');?>
								</a>
							</div>
							<div class="dt-smcb-info-container">
								<div class="dt-smcb-info_wrap">
									<?php $category = get_the_category();
									$cat_ID = $category[0]->term_id;
									$representative_color = get_option( "dt_category_representative_color$cat_ID");
									$style_inline = '';
									if( !empty($representative_color) ){
										$style_inline = 'style="background-color:'. $representative_color .';"';
									}
									if ($category) {
										echo '<a '.$style_inline.' class="dt-post-category" href="' . get_category_link( $cat_ID ) . '" title="' . sprintf( __( "View all posts in %s", "wozine" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> ';
									}
									?>
									<h3 class="entry-title dt-module-title"><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
								</div>
							</div>
						</div>
					<?php else: ?>
						<?php if($i == 1): echo '<div class="dt-smcb-block2">'; ?>
							<div class="dt-big-grid-post-<?php echo $i;?> dt-smcb-post">
								<div class="dt-module-thumb">
									<a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>">
										<?php $size = ($i == 1) ? 'wozine-smart-content-box-type2-02' : 'wozine-smart-content-box-type2-03';
										?>
										<?php the_post_thumbnail($size);?>
									</a>
								</div>
								<div class="dt-smcb-info-container">
									<div class="dt-smcb-info_wrap">
										<?php $category = get_the_category();
										$cat_ID = $category[0]->term_id;
										$representative_color = get_option( "dt_category_representative_color$cat_ID");
										$style_inline = '';
										if( !empty($representative_color) ){
											$style_inline = 'style="background-color:'. $representative_color .';"';
										}
										if ($category) {
											echo '<a '.$style_inline.' class="dt-post-category" href="' . get_category_link( $category[0]->term_id ) . '" title="' . sprintf( __( "View all posts in %s", "wozine" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> ';
										}
										?>
										<h3 class="entry-title dt-module-title"><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
									</div>
								</div>
							</div>
						<?php echo '</div>'; endif; ?>
						<?php if($i == 2): echo '<div class="dt-smcb-block3">'; ?>
							<div class="dt-big-grid-post-<?php echo $i;?> dt-smcb-post">
								<div class="dt-module-thumb">
									<a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>">
										<?php $size = ($i == 1) ? 'wozine-smart-content-box-type2-02' : 'wozine-smart-content-box-type2-03';
										?>
										<?php the_post_thumbnail($size);?>
									</a>
								</div>
								<div class="dt-smcb-info-container">
									<div class="dt-smcb-info_wrap">
										<?php $category = get_the_category();
										$cat_ID = $category[0]->term_id;
										$representative_color = get_option( "dt_category_representative_color$cat_ID");
										$style_inline = '';
										if( !empty($representative_color) ){
											$style_inline = 'style="background-color:'. $representative_color .';"';
										}
										if ($category) {
											echo '<a '.$style_inline.' class="dt-post-category" href="' . get_category_link( $category[0]->term_id ) . '" title="' . sprintf( __( "View all posts in %s", "wozine" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> ';
										}
										?>
										<h3 class="entry-title dt-module-title"><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if($i == 3): ?>
							<div class="dt-big-grid-post-<?php echo $i;?> dt-smcb-post">
								<div class="dt-module-thumb">
									<a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>">
										<?php $size = ($i == 1) ? 'wozine-smart-content-box-type2-02' : 'wozine-smart-content-box-type2-03';
										?>
										<?php the_post_thumbnail($size);?>
									</a>
								</div>
								<div class="dt-smcb-info-container">
									<div class="dt-smcb-info_wrap">
										<?php $category = get_the_category();
										$cat_ID = $category[0]->term_id;
										$representative_color = get_option( "dt_category_representative_color$cat_ID");
										$style_inline = '';
										if( !empty($representative_color) ){
											$style_inline = 'style="background-color:'. $representative_color .';"';
										}
										if ($category) {
											echo '<a '.$style_inline.' class="dt-post-category" href="' . get_category_link( $category[0]->term_id ) . '" title="' . sprintf( __( "View all posts in %s", "wozine" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> ';
										}
										?>
										<h3 class="entry-title dt-module-title"><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
									</div>
								</div>
							</div>
						<?php echo '</div>'; endif; ?>
					<?php endif; // $i = 1?>
					
					<?php $i++; ?>
			<?php endwhile;?>
		</div>
	</div>
	<?php
	$html = ob_get_clean();
	echo $html;
endif;
wp_reset_postdata();