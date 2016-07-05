<?php
wp_enqueue_script('vendor-theia-sticky-sidebar'); 
?>
<div id="content-sidebar" class="content-sidebar col-md-4 sidebar-wrap sticky_sidebar" role="complementary" data-sticky-sidebar="sticky_sidebar" data-container-selector="#main.site-main .sticky_sidebar">
	<div class="main-sidebar">
		<?php 
		$main_sidebar = dt_get_post_meta('main-sidebar');
		if(!empty($main_sidebar) && is_active_sidebar($main_sidebar)):
				dynamic_sidebar($main_sidebar);
		else:
			if(defined('WOOCOMMERCE_VERSION') && is_woocommerce()){
				if(is_active_sidebar('sidebar-shop'))
					dynamic_sidebar('sidebar-shop');
			}else{
				if(is_active_sidebar('main-sidebar'))
					dynamic_sidebar('main-sidebar');
			}
		endif;
		?>
	</div>
</div>
