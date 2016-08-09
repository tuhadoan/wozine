<?php

if(!class_exists('DT_Admin')):
class DT_Admin {
	public function __construct(){
		
		/**
		 * Add metadata for categories
		 */
		include_once (dirname( __FILE__ ) . '/category-metadata.php');
		/**
		 * Add metadata (meta-boxes) for all post types
		 */
		include_once (dirname( __FILE__ ) . '/meta-boxes.php');
		
		/**
		 * Theme Options
		 */
		include_once (dirname( __FILE__ ) . '/theme-options.php');
		// Import Demo
		include_once (dirname( __FILE__ ) . '/import-demo.php');
			
		
		add_action( 'admin_init', array(&$this,'admin_init'));
		add_action('admin_enqueue_scripts',array(&$this,'enqueue_scripts'));
		//Disnable auto save
		add_action( 'admin_print_scripts', array( &$this, 'disable_autosave' ) );
	}
	
	public function disable_autosave(){
		global $post;
	
		//if ( $post && get_post_type( $post->ID ) === 'page-section' ) {
			wp_dequeue_script( 'autosave' );
		//}
	}
	
	public function admin_init(){
		
		if(post_type_exists('page-section')){
			$pt_array = ( $pt_array = get_option( 'wpb_js_content_types' ) ) ? ( $pt_array ) :  array( 'page' );
			if(!in_array('page-section', $pt_array)){
				array_push($pt_array,'page-section');
				update_option('wpb_js_content_types', $pt_array);
			}
		}
		
		if (get_user_option('rich_editing') == 'true') {
			add_filter('mce_external_plugins', array($this, 'mce_external_plugins'));
			add_filter('mce_buttons', array($this, 'mce_buttons'));
		}
	}
	
	public function mce_external_plugins($plugins){
		$plugins['dt_tooltip'] = DTINC_ASSETS_URL. '/js/tooltip_plugin.js';
		return $plugins;
	}
	public function mce_buttons($buttons){
		$buttons[] = 'dt_tooltip_button';
		return $buttons;
	}
	
	public function enqueue_scripts(){
		wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style('dt-admin',DTINC_ASSETS_URL.'/css/admin.css',false,DTINC_VERSION);
		
		wp_register_script('dt-admin',DTINC_ASSETS_URL.'/js/admin.js',array('jquery'),DTINC_VERSION,true);
		$dtAdminL10n = array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'theme_url'=>get_template_directory_uri(),
			'framework_assets_url'=>DTINC_ASSETS_URL,
			'i18n_tooltip_mce_button'=>esc_js(__('Tooltip Shortcode','dawnthemes')),
			'tooltip_form'=>$this->_tooltip_form()
		);
		wp_localize_script('dt-admin', 'dtAdminL10n', $dtAdminL10n);
		wp_enqueue_script('dt-admin');
	}
	
	protected function _tooltip_form(){
		ob_start();
		?>
		<div class="dt-tooltip-form">
			<div class="dt-tooltip-options">
				<div>
					<label>
						<span><?php _e('Text','dawnthemes')?></span>
						<input data-id="text" type="text" placeholder="<?php _e('Text','dawnthemes')?>">
					</label>
				</div>
				<div>
					<label>
						<span><?php _e('URL','dawnthemes')?></span>
						<input data-id="url" type="text" placeholder="<?php _e('http://','dawnthemes')?>">
					</label>
				</div>
				<div>
					<label>
						<span><?php _e('Type','dawnthemes')?></span>
						<select data-id="type">
							<option value="tooltip"><?php _e('Tooltip','dawnthemes') ?></option>
							<option value="popover"><?php _e('Popover','dawnthemes') ?></option>
						</select>
					</label>
				</div>
				<div>
					<label>
						<span><?php _e('Tip position','dawnthemes')?></span>
						<select data-id="position">
							<option value="top"><?php _e('Top','dawnthemes') ?></option>
							<option value="bottom"><?php _e('Bottom','dawnthemes') ?></option>
							<option value="left"><?php _e('Left','dawnthemes') ?></option>
							<option value="right"><?php _e('Right','dawnthemes') ?></option>
						</select>
					</label>
				</div>
				<div style="display: none;">
					<label>
						<span><?php _e('Title','dawnthemes')?></span>
						<input data-id="title" type="text" placeholder="<?php _e('Title','dawnthemes')?>">
					</label>
				</div>
				<div>
					<label>
						<span><?php _e('Content','dawnthemes')?></span>
						<textarea data-id="content" placeholder="<?php _e('Content','dawnthemes')?>"></textarea>
					</label>
				</div>
				<div>
					<label>
						<span><?php _e('Action to trigger','dawnthemes')?></span>
						<select data-id="trigger">
							<option value="hover"><?php _e('Hover','dawnthemes') ?></option>
							<option value="click"><?php _e('Click','dawnthemes') ?></option>
						</select>
					</label>
				</div>
			</div>
			<div class="submitbox">
				<div id="dt-tooltip-cancel">
					<a href="#"><?php _e('Cancel','dawnthemes')?></a>
				</div>
				<div id="dt-tooltip-update">
					<input type="button" class="button button-primary" value="<?php _e('Add Tooltip','dawnthemes')?>">
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
new DT_Admin();
endif;
