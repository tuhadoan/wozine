<?php
/**
 * The template for displaying Search Results pages
 *
 * @package dawn
 */
$main_class = dt_get_main_class();
$layout = dt_get_theme_option('blog_style', 'default');
$pagination = dt_get_theme_option('blog-pagination', 'wp_pagenavi'); // wp_pagenavi || def || loadmore || infinite_scroll
if($layout == 'classic') $pagination = 'wp_pagenavi';
$loadmore_text = dt_get_theme_option('blog-loadmore-text', __('Load More','wozine'));
$columns = dt_get_theme_option('blog-columns', 2);

if($layout == 'masonry'){
	wp_enqueue_script('vendor-isotope');
}
if($pagination === 'infinite_scroll'){
	wp_enqueue_script('vendor-infinitescroll');
}

get_header(); ?>
<div id="main-content" class="main-content">
	<div class="container">
		<div class="row">
			<?php do_action('dt_left_sidebar');?>
			<section id="primary" class="content-area <?php echo esc_attr($main_class)?>">
				<div id="content" class="site-content" role="main">
					<div class="row">
						<div class="col-md-12">
							<?php 
							$itemSelector = '';
							$itemSelector .= (($pagination === 'infinite_scroll') ? '.post.infinite-scroll-item':'');
							$itemSelector .= (($pagination === 'loadmore') ? '.post.loadmore-item':'');
							?>
							<?php
							if ( have_posts() ) :
								?>
								<header class="page-header">
									<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'wozine' ), get_search_query() ); ?></h1>
								</header><!-- .page-header -->
								
								<div data-itemselector="<?php echo esc_attr($itemSelector)  ?>"  class="posts <?php echo (($pagination === 'loadmore') ? ' loadmore':''); ?><?php echo (($pagination === 'infinite_scroll') ? ' infinite-scroll':'') ?><?php echo (($layout === 'masonry') ? ' masonry':'') ?>" data-paginate="<?php echo esc_attr($pagination) ?>" data-layout="<?php echo esc_attr($layout) ?>"<?php echo ($layout === 'masonry') ? ' data-masonry-column="'.$columns.'"':''?>>
									<div class="posts-wrap<?php echo (($pagination === 'loadmore') ? ' loadmore-wrap':'') ?><?php echo (($pagination === 'infinite_scroll') ? ' infinite-scroll-wrap':'') ?><?php echo (($layout === 'masonry') ? ' masonry-wrap':'') ?> posts-layout-<?php echo esc_attr($layout)?><?php if( $layout == 'default' || $layout == 'grid' || $layout == 'masonry') echo' row' ?>">
									<?php
									// Start the Loop.
									$i = 0;
									while ( have_posts() ) : the_post();?>
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
										case 'def':
											dt_paging_nav_default();
											break;
										case 'loadmore':
											dt_paging_nav_ajax($loadmore_text);
											$paginate_args = array('show_all'=>true);
											break;
										case 'infinite_scroll':
											$paginate_args = array('show_all'=>true);
											break;
									}
									dt_paginate_links($paginate_args);
									?>
								</div>
							<?php
							else :
								// If no content, include the "No posts found" template.
								get_template_part( 'content', 'none' );
							endif;
							?>
						</div>
					</div><!-- /.row -->
				</div><!-- #content -->
			</section><!-- #primary -->
		<?php do_action('dt_right_sidebar') ?>

	</div><!-- .row -->
</div><!-- #container -->

</div><!-- #main-content -->

<?php
get_footer();
