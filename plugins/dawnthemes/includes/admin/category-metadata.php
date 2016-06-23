<?php
/* Category custom field */
add_action( 'category_add_form_fields', 'dawnthemes_extra_category_fields', 10 );
add_action ( 'edit_category_form_fields', 'dawnthemes_extra_category_fields');
function dawnthemes_extra_category_fields( $tag ) {    //check for existing featured ID
	$t_id 									= is_object($tag) && $tag->term_id?$tag->term_id:'';
	$main_color 							= str_replace('#', '', dt_get_theme_option('main_color', '#9CBA75') );
	$dt_category_representative_color 			= get_option( "dt_category_representative_color$t_id") ? get_option( "dt_category_representative_color$t_id"): '';
	$category_sidebar_options 				= get_option( "category_sidebar_options$t_id") ? get_option( "category_sidebar_options$t_id"):'';
	$category_feature_post_carousel_options = get_option( "category_feature_post_carousel_options$t_id") ? get_option( "category_feature_post_carousel_options$t_id"):'';
	?>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="category_sidebar"><?php esc_html_e('Sidebar','dawnthemes'); ?></label>
		</th>
		<td>
			<select name="category_sidebar_options">
                <option value="default"<?php echo  $category_sidebar_options == 'default' ? 'selected="selected"' : '';?>><?php esc_html_e('Default','dawnthemes'); ?></option>
                <option value="left"<?php echo  $category_sidebar_options == 'left' ? 'selected="selected"' : '';?>><?php esc_html_e('Left','dawnthemes'); ?></option>
                <option value="right"<?php echo  $category_sidebar_options == 'right' ? 'selected="selected"' : '';?>><?php esc_html_e('Right','dawnthemes'); ?></option>
			</select>
			</select>
			<p class="description"><?php esc_html_e('Choose "default" to use Theme Options setting.','dawnthemes'); ?></p>
		</td>
	</tr>
    <tr class="form-field">
		<th scope="row" valign="top">
			<label for="dt_category_representative_color"><?php esc_html_e('Representative Color','dawnthemes'); ?></label>
		</th>
		<td>
            <input type="text" class="colorpicker dt-colorpicker" value="<?php echo esc_attr($dt_category_representative_color == '' ? $main_color : $dt_category_representative_color);?>" name="dt_category_representative_color"/>
			<p class="description"><?php esc_html_e('Choose representative color for this category.','dawnthemes'); ?></p>
		</td>
	</tr>

<?php
}
//save extra category extra fields hook
add_action ( 'edited_category', 'dawnthemes_save_extra_category_fileds');
add_action( 'created_category', 'dawnthemes_save_extra_category_fileds', 10, 2 );
function dawnthemes_save_extra_category_fileds( $term_id ) {
    if ( isset( $_POST[sanitize_key('dt_category_representative_color')] ) ) {
        $dt_category_representative_color = $_POST['dt_category_representative_color'];
        update_option( "dt_category_representative_color$term_id", $dt_category_representative_color );
    }
	 if ( isset( $_POST['category_sidebar_options'] ) ) {
        $category_sidebar_options = $_POST['category_sidebar_options'];
        update_option( "category_sidebar_options$term_id", $category_sidebar_options );
    }
}