<?php
/*
* Template Name: 2 Columns - Left Slidebar
* @subpackage Dawn
*/
?>
<?php get_header() ?>
	<div class="content-container">
		<div class="<?php dt_container_class() ?>">
			<div class="row">
				<div class="col-md-9 main-wrap" data-itemprop="mainContentOfPage" role="main">
					<div class="main-content">
						<?php if ( have_posts() ) : ?>
							<?php 
							 while (have_posts()): the_post();
								the_content();
							 endwhile;
							 ?>
							<?php 
							if(dt_get_theme_option('comment-page',0) && comments_open(get_the_ID()))
								comments_template( '', true ); 
							?>
						<?php endif;?>
					</div>
				</div>
				<?php get_sidebar()?>
			</div>
		</div>
	</div>
<?php get_footer() ?>