<?php
if(!class_exists('DTImportDemo')):
class DTImportDemo{
	public function __construct(){
		if(is_admin()){
			add_action('admin_menu', array(&$this,'admin_menu'));
			add_action('wp_ajax_dt_import_demo_data', array(&$this,'ajax_import_demo'));
		}
	}
	public function admin_menu(){
		add_submenu_page( 'themes.php' , esc_html__('Import Demo','dawnthemes') , esc_html__('Import Demo','dawnthemes') , 'manage_options' , 'import-demo' , array(&$this,'output') );
	}
	
	public function output(){
		?>
		<div class="dt-message content" style="display:none;">
			<img src="<?php echo DTINC_ASSETS_URL.'/images/spinner.gif' ?>" alt="spinner">
			<h1 class="dt-message-title"><?php esc_html_e('Importing Demo Content...','dawnthemes')?></h1>
			<p class="dt-message-text"><?php _e('Please be patient and do not navigate away from this page while the import is in&nbsp;progress. This can take a while if your server is slow (inexpensive hosting).<br>You will be notified via this page when the import is completed.','dawnthemes')?></p>
		</div>
		<div class="dt-message error" style="display:none;">
			<h1 class="dt-message-title"><?php esc_html_e('Error has occured','dawnthemes')?></h1>
			<p class="dt-message-text"></p>
		</div>
		<div class="dt-message success" style="display:none;">
			<h1 class="dt-message-title"><?php esc_html_e('Import completed successfully!','dawnthemes')?></h1>
			<p class="dt-message-text"><?php _e('Now you can see the result at','dawnthemes')?> <a href="<?php echo site_url(); ?>" target="_blank"><?php _e('your site','dawnthemes')?></a><br><?php _e('or start customize via','dawnthemes')?> <a href="<?php echo admin_url('themes.php?page=theme-options'); ?>"><?php _e('Theme Options','dawnthemes')?></a></p>
		</div>

		<form class="dt-importer" action="?page=import-demo" method="post">
			<h1 class="dt-importer-title"><?php _e('Import Demo Data','dawnthemes')?></h1>
			<div class="dt-importer-list" style="display: none">
				<div class="dt-importer-item">
					
				</div>
			</div>
			<div class="dt-importer-options">
				<div class="dt-importer-option demo-data" style="display: none">
					<label class="dt-importer-option-check">
						<input id="demo_data" type="checkbox" value="1" name="demo_data" checked="checked">
						<span class="dt-importer-title"><?php _e('Import Demo Data','dawnthemes')?></span>
					</label>
				</div>
				<div class="dt-importer-note">
					<strong><?php esc_html_e('Important Notes:','dawnthemes')?></strong>
					<ol>
						<li><?php _e('We recommend to run Demo Import on a clean WordPress installation.','dawnthemes')?></li>
						<li><?php echo sprintf(__('To reset your installation we recommend %s plugin.','dawnthemes'),'<a href="http://wordpress.org/plugins/wordpress-database-reset/" target="_blank">Wordpress Database Reset</a>')?></li>
						<li><?php _e('The Demo Import will not import the images we have used in our live demo, due to copyright/license reasons.','dawnthemes')?></li>
						<li><?php _e('Do not run the Demo Import multiple times one after another, it will result in double content.','dawnthemes')?></li>
					</ol>
				</div>
				<input type="hidden" name="action" value="import-demo">
				<input class="button-primary" id="run_import_demo_data" type="submit" value="<?php _e('Import','dawnthemes')?>">
			</div>
		</form>
		<script>
			jQuery(document).ready(function() {
				var import_running = false;
				jQuery('#run_import_demo_data').click(function() {
					if ( ! import_running) {
						import_running = true;
						jQuery("html, body").animate({
							scrollTop: 0
						}, {
							duration: 300
						});
						jQuery('.dt-importer').slideUp(null, function(){
							jQuery('.dt-message.content').slideDown();
						});
						var demo = jQuery('input[name=demo]:checked').val();
						if (demo == undefined) {
							demo = 'main';
						}
						jQuery.ajax({
							type: 'POST',
							url: '<?php echo admin_url('admin-ajax.php'); ?>',
							data: {
								action: 'dt_import_demo_data',
								demo: demo
							},
							success: function(response, textStatus, XMLHttpRequest){
								jQuery('.dt-message.content').slideUp();
								if(response != 'imported'){
									jQuery('.dt-message.import-error .dt-message-text').html(response);
									jQuery('.dt-message.import-error').slideDown();
								}else{
									jQuery('.dt-message.success').slideDown();
								}
								import_running = false;
							},
							error: function(MLHttpRequest, textStatus, errorThrown){

							}
						});
					}
					return false;
				});
			});
		</script>
		<?php
	}
	
	protected function _get_new_widget_name( $widget_name, $widget_index ) {
		$current_sidebars = get_option( 'sidebars_widgets' );
		$all_widget_array = array( );
		foreach ( $current_sidebars as $sidebar => $widgets ) {
			if ( !empty( $widgets ) && is_array( $widgets ) && $sidebar != 'wp_inactive_widgets' ) {
				foreach ( $widgets as $widget ) {
					$all_widget_array[] = $widget;
				}
			}
		}
		while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array ) ) {
			$widget_index++;
		}
		$new_widget_name = $widget_name . '-' . $widget_index;
		return $new_widget_name;
	}
	
	protected function _parse_import_data( $import_array ) {
		global $wp_registered_sidebars;
		$sidebars_data = $import_array[0];
		$widget_data = $import_array[1];
		$current_sidebars = get_option( 'sidebars_widgets' );
		$new_widgets = array( );
	
		foreach ( $sidebars_data as $import_sidebar => $import_widgets ) :
	
		foreach ( $import_widgets as $import_widget ) :
		//if the sidebar exists
		if ( isset( $wp_registered_sidebars[$import_sidebar] ) ) :
		$title = trim( substr( $import_widget, 0, strrpos( $import_widget, '-' ) ) );
		$index = trim( substr( $import_widget, strrpos( $import_widget, '-' ) + 1 ) );
		$current_widget_data = get_option( 'widget_' . $title );
		$new_widget_name = $this->_get_new_widget_name( $title, $index );
		$new_index = trim( substr( $new_widget_name, strrpos( $new_widget_name, '-' ) + 1 ) );
	
		if ( !empty( $new_widgets[ $title ] ) && is_array( $new_widgets[$title] ) ) {
			while ( array_key_exists( $new_index, $new_widgets[$title] ) ) {
				$new_index++;
			}
		}
		$current_sidebars[$import_sidebar][] = $title . '-' . $new_index;
		if ( array_key_exists( $title, $new_widgets ) ) {
			$new_widgets[$title][$new_index] = $widget_data[$title][$index];
			$multiwidget = $new_widgets[$title]['_multiwidget'];
			unset( $new_widgets[$title]['_multiwidget'] );
			$new_widgets[$title]['_multiwidget'] = $multiwidget;
		} else {
			$current_widget_data[$new_index] = $widget_data[$title][$index];
			$current_multiwidget = isset($current_widget_data['_multiwidget']) ? $current_widget_data['_multiwidget'] : false;
			$new_multiwidget = isset($widget_data[$title]['_multiwidget']) ? $widget_data[$title]['_multiwidget'] : false;
			$multiwidget = ($current_multiwidget != $new_multiwidget) ? $current_multiwidget : 1;
			unset( $current_widget_data['_multiwidget'] );
			$current_widget_data['_multiwidget'] = $multiwidget;
			$new_widgets[$title] = $current_widget_data;
		}
	
		endif;
		endforeach;
		endforeach;
	
		if ( isset( $new_widgets ) && isset( $current_sidebars ) ) {
			update_option( 'sidebars_widgets', $current_sidebars );
	
			foreach ( $new_widgets as $title => $content )
				update_option( 'widget_' . $title, $content );
	
			return true;
		}
	
		return false;
	}
	
	protected function _import_widget_data( $widget_data ) {
		$json_data = $widget_data;
		$json_data = json_decode( $json_data, true );
	
		$sidebar_data = $json_data[0];
		$widget_data = $json_data[1];
	
		foreach ( $widget_data as $widget_data_title => $widget_data_value ) {
			$widgets[ $widget_data_title ] = '';
			foreach( $widget_data_value as $widget_data_key => $widget_data_array ) {
				if( is_int( $widget_data_key ) ) {
					$widgets[$widget_data_title][$widget_data_key] = 'on';
				}
			}
		}
		unset($widgets[""]);
	
		foreach ( $sidebar_data as $title => $sidebar ) {
			$count = count( $sidebar );
			for ( $i = 0; $i < $count; $i++ ) {
				$widget = array( );
				$widget['type'] = trim( substr( $sidebar[$i], 0, strrpos( $sidebar[$i], '-' ) ) );
				$widget['type-index'] = trim( substr( $sidebar[$i], strrpos( $sidebar[$i], '-' ) + 1 ) );
				if ( !isset( $widgets[$widget['type']][$widget['type-index']] ) ) {
					unset( $sidebar_data[$title][$i] );
				}
			}
			$sidebar_data[$title] = array_values( $sidebar_data[$title] );
		}
	
		foreach ( $widgets as $widget_title => $widget_value ) {
			foreach ( $widget_value as $widget_key => $widget_value ) {
				$widgets[$widget_title][$widget_key] = $widget_data[$widget_title][$widget_key];
			}
		}
	
		$sidebar_data = array( array_filter( $sidebar_data ), $widgets );
	
		$this->_parse_import_data( $sidebar_data );
	}
	
	public function ajax_import_demo(){
		@set_time_limit(0);
		
		if ( !defined('WP_LOAD_IMPORTERS') ) define('WP_LOAD_IMPORTERS', true);
		
		if ( ! class_exists( 'WP_Importer' ) ) { // if main importer class doesn't exist
			include ABSPATH . 'wp-admin/includes/class-wp-importer.php';
		}
		
		if ( ! class_exists('WP_Import') ) { // if WP importer doesn't exist
			include DTINC_DIR . '/lib/wordpress-importer/wordpress-importer.php';
		}
		
		if ( class_exists( 'WP_Importer' ) && class_exists( 'WP_Import' ) ) {
			$wp_import = new WP_Import();
			$wp_import->fetch_attachments = true;
			ob_start();
			$wp_import->import(get_template_directory().'/dummy-data/dummy-data.xml');
			ob_end_clean();
			
			$widgets_file = get_template_directory_uri().'/dummy-data/widget_data.json';
			$widgets_json = $widgets_file; // widgets data file
			$widgets_json = wp_remote_get( $widgets_json );
			$widget_data = $widgets_json['body'];
			$this->_import_widget_data($widget_data);
			
			// Set menu
			$locations = get_theme_mod('nav_menu_locations');
			$menus  = wp_get_nav_menus();
			
			if(!empty($menus))
			{
				$one_page = get_page_by_title( 'Home One Page' );
				foreach($menus as $menu)
				{
					if(is_object($menu))
					{
						if($menu->name == 'Primary Menu'){
							$locations['primary'] = $menu->term_id;
						}else if ($menu->name == 'Footer Menu'){
							$locations['footer'] = $menu->term_id;
						}else if ($menu->name == 'Top Menu'){
							$locations['top'] = $menu->term_id;
						}
						// Assign One Page Menu
						if(isset( $one_page ) && $one_page->ID && $menu->name == 'One Page Menu') {
							update_post_meta($one_page->ID, '_dt_main_menu', $menu->term_id);
						}
					}
				}
			}
			
			set_theme_mod('nav_menu_locations', $locations);
			
			//Set Front Page
			$front_page = get_page_by_title('Home');
			
			if(isset($front_page->ID)) {
				update_option('show_on_front', 'page');
				update_option('page_on_front',  $front_page->ID);
			}
			
			//import revslider
			if ( ! class_exists('RevSlider')) {
				return false;
			}
			
			ob_start();
			$_FILES["import_file"]["tmp_name"] = get_template_directory().'/dummy-data/home.zip';
			
			$slider = new RevSlider();
			$response = $slider->importSliderFromPost();
			unset($slider);
			ob_end_clean();
			
			echo 'imported';
			die();
		}
	}
}
new DTImportDemo();
endif;