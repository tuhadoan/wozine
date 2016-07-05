<?php
/**
 * The template for displaying Archive pages
 *
 * @package dawn
 */

get_header(); ?>
<div class="container">
	<div class="row">
		<div id="primary" class="content-area col-md-8">
			<div id="content" class="site-content" role="main">
				<?php if ( have_posts() ) : ?>
				<header class="page-header">
					<h1 class="page-title">
						<?php
							if ( is_day() ) :
								printf( __( 'Daily Archives: %s', 'wozine' ), get_the_date() );

							elseif ( is_month() ) :
								printf( __( 'Monthly Archives: %s', 'wozine' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'wozine' ) ) );

							elseif ( is_year() ) :
								printf( __( 'Yearly Archives: %s', 'wozine' ), get_the_date( _x( 'Y', 'yearly archives date format', 'wozine' ) ) );

							else :
								esc_html_e( 'Archives', 'wozine' );

							endif;
						?>
					</h1>
				</header><!-- .page-header -->
				<?php
					// Start the Loop.
					while ( have_posts() ) : the_post();
						get_template_part( 'template-parts/loop/content', get_post_format() );
					endwhile;
				else :
					// If no content, include the "No posts found" template.
					get_template_part( 'template-parts/loop/content', 'none' );
				endif;
				?>
			</div><!-- #content -->
			<?php 
			// Previous/next page navigation.
			// this paging nav should be outside #content
			dt_paging_nav();
			?>
		</div><!-- #primary -->
		<?php do_action('dt_left_sidebar');?>
		<?php do_action('dt_right_sidebar') ?>
	</div><!-- .row -->
</div><!-- #container -->
<?php
get_footer();
