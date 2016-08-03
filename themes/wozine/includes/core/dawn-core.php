<?php
/**
 * Core features for all themes
 *
 * @package danwthemes
 * @version 1.0 - 02/07/2016
 */
if( ! class_exists('dawn_core') ){
	class dawn_core{
		public function __construct(){
			add_action('init', array(&$this, 'init'));
			
			// Add author social link meta
			add_action( 'show_user_profile', array(&$this,'dawn_show_extra_profile_fields') );
			add_action( 'edit_user_profile', array(&$this,'dawn_show_extra_profile_fields') );
			add_action( 'personal_options_update', array(&$this,'dawn_save_extra_profile_fields') );
			add_action( 'edit_user_profile_update', array(&$this,'dawn_save_extra_profile_fields') );
		}
		
		public function init(){
		}
		
		public function dawn_show_extra_profile_fields( $user ){
	 		$dawn_author_links = array('facebook', 'twitter', 'google', 'flickr', 'instagram', 'pinterest', 'envelope');
		 	?>
		 	<h3><?php esc_html_e('DT - Social informations','wozine') ?></h3>
		 	<table class="form-table wozine-social-info">
		 		<?php foreach( $dawn_author_links as $account ): ?>
					<tr>
						<th><label for="<?php echo $account; ?>"><?php echo $account == 'envelope' ? 'Email' : $account ; ?></label></th>
						<td>
							<span class="description"><?php esc_html_e('Account URL','wozine')?></span>
							<input type="text" name="<?php echo $account; ?>" id="<?php echo $account; ?>" value="<?php echo esc_attr( get_the_author_meta( $account, $user->ID ) ); ?>" class="regular-text" />
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		 	<?php
	 	}
	 	
	 	public function dawn_save_extra_profile_fields( $user_id ){
	 		if( !current_user_can( 'edit_user', $user_id ) )
	 			return false;
	 		
	 		$dawn_author_links = array('facebook', 'twitter', 'google', 'flickr', 'instagram', 'pinterest', 'envelope');
	 		foreach($dawn_author_links as $account){
	 			update_user_meta( $user_id, $account, sanitize_text_field($_POST[sanitize_key($account)]) );
	 		}
		}
		
		// END Class
	}
	new dawn_core();
}