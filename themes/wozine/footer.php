<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package dawn
 */

$copyright = dt_get_theme_option('footer-copyright', '');
$number_footer_sidebar = 0;
if( is_active_sidebar('footer-sidebar-1') ) $number_footer_sidebar = $number_footer_sidebar + 1;
if( is_active_sidebar('footer-sidebar-2') ) $number_footer_sidebar = $number_footer_sidebar + 1;
if( is_active_sidebar('footer-sidebar-3') ) $number_footer_sidebar = $number_footer_sidebar + 1;
	
?>
		</div><!-- #main -->

		<footer id="footer" class="site-footer">
			<?php 
			if( $number_footer_sidebar > 0 ): ?>
			<div id="footer-primary">
				<div id="footer-sidebar" class="footer-sidebar widget-area" role="complementary">
					<div class="container">
						<div class="row footer-primary__columns__<?php echo absint($number_footer_sidebar);?>">
							<?php
							for( $i = 0; $i <= 3 ; $i++):
								if( is_active_sidebar("footer-sidebar-$i") ): ?>
								<div class="footer-primary__group">
									<?php dynamic_sidebar( "footer-sidebar-$i" ); ?>
								</div>
								<?php
								endif;
							endfor;
							?>
						</div>
					</div>
				</div><!-- #footer-sidebar -->
			</div><!-- #footer-primary -->
			<?php
			endif;
			?>
			<div class="footer-bottom">
				<div class="copyright-section">
						<div class="container">
								<div class="site-info">
									<?php do_action( 'dt_credits' ); ?>
									<?php 
									echo ( isset( $copyright ) && $copyright !='' ) ? wp_kses($copyright, array(
									'a' => array(
										'href' => array(),
										'class' => array(),
										'data-original-title' => array(),
										'data-toggle' => array(),
										'title' => array()
									),
									'br' => array(),
									)) : 'Copyright 2016 &#169; <a href="#" title="Wozine">Wozine.com</a>. All Rights Reserved'; ?>
								</div><!-- .site-info -->
						</div>
				</div><!-- .copyright-section -->
			</div><!-- /.footer-bottom -->
		</footer><!-- #footer -->
	</div><!-- #page -->

	<?php wp_footer(); ?>
</body>
</html>