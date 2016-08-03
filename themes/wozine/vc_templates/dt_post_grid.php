<?php
$output = '';
extract(shortcode_atts(array(
	'columns'			=>'1',
	'layout_style'      =>'list',
	'posts_per_page'	=>'12',
	'orderby'			=>'latest',
	'hide_pagination'   =>'',
	'hide_date'			=>'',
	'hide_author'		=>'',
	'hide_comment'		=>'',
	'hide_category'		=>'',
	'hide_excerpt'      =>'',
	'excerpt_length'    =>'15',
	'categories'		=>'',
	'visibility'		=>'',
	'el_class'			=>'',
), $atts));


$show_date = empty($hide_date) ? true : false;
$show_author = empty($hide_author)  ? true : false;
$show_category = empty($hide_category) ? true : false;
$show_comment = empty($hide_comment) ? true : false;
$show_pagination = empty($hide_pagination)  ? true : false;
$show_excerpt = empty($hide_excerpt)  ? true : false;


$class          = !empty($el_class) ?  ' '.esc_attr( $el_class ) : '';
switch ($visibility) {
	case 'hidden-phone':
		$class .= ' hidden-xs';
		break;
	case 'hidden-tablet':
		$class .= ' hidden-sm hidden-md';
		break;
	case 'hidden-pc':
		$class .= ' hidden-lg';
		break;
	case 'visible-phone':
		$class .= ' visible-xs-inline';
		break;
	case 'visible-tablet':
		$class .= ' visible-sm-inline visible-md-inline';
		break;
	case 'visible-pc':
		$class .= ' visible-lg-inline';
		break;
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
		$order = 'ASC';
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
	'posts_per_page'  => "12"
);
if(!empty($posts_per_page))
	$args['posts_per_page'] = $posts_per_page;

if(!empty($categories)){
	$args['category_name'] = $categories;
}
$r = new WP_Query($args);
$post_col = '';
$post_col = 'col-sm-'.(12/$columns).' ';
if($r->have_posts()):
	ob_start();
	?>
	<div class="post-grid-wrap">
		<ul class="row <?php echo ($layout_style == 'list') ?  'list' : 'grid col-'.$columns.''; ?>">
			<?php while ($r->have_posts()): $r->the_post(); global $post;?>
				<li class="<?php echo esc_attr($post_col) ?>">
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
						<?php if(get_post_format() == 'link'):?>
						<?php $link = dh_get_post_meta('link'); ?>
						<div class="hentry-wrap hentry-wrap-link">
							<div class="entry-content">
								<div class="link-content">
									<a target="_blank" href="<?php echo esc_url($link) ?>">
										<span><?php the_title()?></span>
										<cite><?php echo esc_url($link) ?></cite>
									</a>
								</div>
							</div>
						</div>
						<?php elseif (get_post_format() == 'quote'):?>
						<div class="hentry-wrap hentry-wrap-link">
							<div class="entry-content">
								<div class="quote-content">
									<a href="<?php the_permalink()?>">
										<span>
											<?php echo dh_get_post_meta('quote'); ?>
										</span>
										<cite><i class="fa fa-quote-left"></i> <?php the_title(); ?></cite>
									</a>
								</div>
							</div>
						</div>
						<?php else:?>
						<div class="hentry-wrap">
							<?php if($layout_style == 'list') : ?>
								<?php 
									$entry_featured_class = '';
									dh_post_featured('','',true,false,$entry_featured_class,'', false);
								?>
							<?php else: ?>
								<?php 
									$entry_featured_class = '';
									dh_post_featured('','',true,false,$entry_featured_class,'', true);
								?>
							<?php endif; ?>

							<div class="entry-info">

								<div class="entry-header">
									<h3 class="entry-title" data-itemprop="name">
										<a href="<?php the_permalink()?>" title="<?php echo esc_attr(get_the_title())?>">
											<?php the_title()?>
										</a>
									</h3>
									<div class="entry-meta icon-meta">
										<?php 
											dh_post_meta($show_date,$show_comment,$show_category,$show_author,true,false,null,true);  
										?>
									</div>
								</div>
								
								<div class="entry-content">
									<?php 
										if($show_excerpt == 'true'){
											$excerpt = $post->post_excerpt;
											if(empty($excerpt))
												$excerpt = $post->post_content;
											
											$excerpt = strip_shortcodes($excerpt);
											$excerpt = wp_trim_words($excerpt,$excerpt_length,'...');
											echo '<p>' . $excerpt . '</p>';
										}
									?>

								</div>

								<?php if($layout_style == 'list') : ?>
									<a class="read-more btn btn-outline" href="<?php the_permalink()?>" title="<?php echo esc_attr(get_the_title())?>">
										<?php esc_html_e( 'Read more', 'wozine'); ?> <i>+</i>
									</a>
								<?php endif; ?>

							</div>
						</div>
						<?php endif;?>
					</article>
				</li>
			<?php endwhile;?>
		</ul>
	</div>
	<?php
	$html = ob_get_clean();
	echo $html;
endif;
wp_reset_postdata();