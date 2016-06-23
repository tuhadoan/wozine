<div id="content-sidebar" class="content-sidebar col-md-4 sidebar-wrap" role="complementary">
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
