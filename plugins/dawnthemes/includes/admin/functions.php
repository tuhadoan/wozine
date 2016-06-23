<?php

function dt_render_meta_boxes($post, $meta_box) {
	$args = $meta_box ['args'];
	if(!defined('DT_META_BOX_NONCE')):
		define('DT_META_BOX_NONCE', 1);
	
	wp_nonce_field ('dt_meta_box_nonce', 'dt_meta_box_nonce',false);
	endif;
		
	if (! is_array ( $args ))
		return false;
		
	echo '<div class="dt-metaboxes">';
	if (isset ( $args ['description'] ) && $args ['description'] != '') {
		echo '<p>' . $args ['description'] . '</p>';
	}
	$count = 0;
	foreach ( $args ['fields'] as $field ) {
		if(!isset($field['type']) )
			continue;
	
		$field['name']          = isset( $field['name'] ) ? $field['name'] : '';
		$field['name'] 	= strstr( $field['name'], '_dt_' ) ? sanitize_title( $field['name'] ) : '_dt_' . sanitize_title( $field['name'] );
		
		$value = get_post_meta( $post->ID,$field['name'], true );
	
		$field['value']         = isset( $field['value'] ) ? $field['value'] : '';
		if($value !== '' && $value !== null && $value !== array() && $value !== false)
			$field['value'] = $value;
	
	
		$field['id'] 			= isset( $field['id'] ) ? $field['id'] : $field['name'];
		$field['description'] 	= isset($field['description']) ? $field['description'] : '';
		$field['label'] 		= isset( $field['label'] ) ? $field['label'] : '';
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : $field['label'];
	
		$field['name'] = 'dt_meta['.$field['name'].']';
		if( isset($field['callback']) && !empty($field['callback']) ) {
			call_user_func($field['callback'], $post,$field);
		} else {
			switch ($field['type']){
				case 'heading':
					echo '<h4>'.$field['heading'].'</h4>';
					break;
				break;
				case 'hr':
					echo '<div style="margin-top:20px;margin-bottom:20px;">';
					echo '<hr>';
					echo '</div>';
					break;
				case 'text':
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label><input type="text" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" style="width: 99%;" /> ';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					if(isset($field['hidden']) && $field['hidden'] == true){
						$field['name'] = 'dt_meta['.$field['name'].'_hidden]';
						echo '<input type="hidden" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field['value'] ) . '">';
					}
					echo '</div>';
					break;
				case 'color':
					wp_enqueue_style( 'wp-color-picker');
					wp_enqueue_script( 'wp-color-picker');
					
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label><input type="text" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" /> ';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					echo '<script type="text/javascript">
						jQuery(document).ready(function($){
						    $("#'. esc_attr( $field['id'] ).'").wpColorPicker();
						});
					 </script>
					';
					echo '</div>';
					break;
				case 'textarea':
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label><textarea name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="5" cols="20" style="width: 99%;">' . esc_textarea( $field['value'] ) . '</textarea> ';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					echo '</div>';
					break;
				case 'checkbox':
					$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : '1';
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label><input type="checkbox" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="0"  checked="checked" style="display:none" /><input class="checkbox" type="checkbox" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . ' /> ';
					if ( ! empty( $field['description'] ) ) echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
						
					echo '</div>';
					break;
				case 'categories':
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label>';
					wp_dropdown_categories(array(
						'name'=>esc_attr( $field['name'] ),
						'id'=>esc_attr( $field['id'] ),
						'hierarchical'=>1,
						'selected'=>$field['value']
					));
					echo '</div>';
				break;
				case 'widgetised_sidebars':
					$sidebars = $GLOBALS['wp_registered_sidebars'];
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '">';
					echo '<option value=""' . $selected . '>' . __('Select a sidebar...','dawnthemes') . '</option>';
					foreach ( $sidebars as $sidebar ) {
						$selected = '';
						if ( $sidebar["id"] == $field['value'] ) $selected = ' selected="selected"';
						$sidebar_name = $sidebar["name"];
						echo '<option value="' . $sidebar["id"] . '"' . $selected . '>' . $sidebar_name . '</option>';
					}
					echo '</select> ';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					echo '</div>';
					break;
					break;
				case 'select':
					$field['options']       = isset( $field['options'] ) ? $field['options'] : array();
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '">';
					foreach ( $field['options'] as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
					}
					echo '</select> ';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					echo '</div>';
					break;
				case 'radio':
					$field['options']       = isset( $field['options'] ) ? $field['options'] : array();
					echo '<fieldset '.$data_dependency.' class="form-field ' . esc_attr( $field['id'] ) . '_field"><legend>' . esc_html( $field['label'] ) . '</legend><ul>';
					foreach ( $field['options'] as $key => $value ) {
						echo '<li><label><input
					        		name="' . esc_attr( $field['name'] ) . '"
					        		value="' . esc_attr( $key ) . '"
					        		type="radio"
									class="radio"
					        		' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
					        		/> ' . esc_html( $value ) . '</label>
					    	</li>';
					}
					echo '</ul>';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					echo '</fieldset>';
					break;
				case 'gallery':
					if(function_exists( 'wp_enqueue_media' )){
						wp_enqueue_media();
					}else{
						wp_enqueue_style('thickbox');
						wp_enqueue_script('media-upload');
						wp_enqueue_script('thickbox');
					}
					
					if(!defined('_DT_META_GALLERY_JS')):
					define('_DT_META_GALLERY_JS', 1);
					?>
					<script type="text/javascript">
						jQuery(document).ready(function($) {
							$('.dt-meta-gallery-select').on('click',function(e){
								e.stopPropagation();
								e.preventDefault();
								
								var $this = $(this),
									dt_meta_gallery_list = $this.closest('.dt-meta-box-field').find('.dt-meta-gallery-list'),
									dt_meta_gallery_frame,
									dt_meta_gallery_ids = $this.closest('.dt-meta-box-field').find('#dt_meta_gallery_ids'),
									_ids = dt_meta_gallery_ids.val();
	
								if(dt_meta_gallery_frame){
									dt_meta_gallery_frame.open();
									return false;
								}
								
								dt_meta_gallery_frame = wp.media({
									title: '<?php echo __('Add Images to Gallery','dawnthemes')?>',
									button: {
										text: '<?php echo __('Add to Gallery','dawnthemes')?>',
									},
									library: { type: 'image' },
									multiple: true
								});
	
								dt_meta_gallery_frame.on('select',function(){
									var selection = dt_meta_gallery_frame.state().get('selection');
									selection.map( function( attachment ) {
										attachment = attachment.toJSON();
										if ( attachment.id ) {
											_ids = _ids ? _ids + "," + attachment.id : attachment.id;
											dt_meta_gallery_list.append('\
												<li data-id="' + attachment.id +'">\
													<div class="thumbnail">\
														<div class="centered">\
															<img src="' + attachment.url + '" />\
														</div>\
														<a href="#" title="<?php echo __('Delete','dawnthemes')?>"><?php echo __('x','dawnthemes')?></a></li>\
													</div>\
												</li>'
											);
										}
										dt_meta_gallery_ids.val( dt_trim(_ids,',') );
										dt_meta_gallery_fn();
									});
								});
	
								dt_meta_gallery_frame.open();
							});
							var dt_meta_gallery_fn = function(){
								if($('.dt-meta-gallery-list').length){
									$('.dt-meta-gallery-list').each(function(){
										var $this = $(this);
										$this.sortable({
											items: 'li',
											cursor: 'move',
											forcePlaceholderSize: true,
											forceHelperSize: false,
											helper: 'clone',
											opacity: 0.65,
											placeholder: 'li-placeholder',
											start:function(event,ui){
												ui.item.css('background-color','#f6f6f6');
											},
											update: function(event, ui) {
												var _ids = '';
												$this.find('li').each(function() {
													var _id = $(this).data( 'id' );
													_ids = _ids + _id + ',';
												});
									
												$this.closest('.dt-meta-box-field').find('#dt_meta_gallery_ids').val( dt_trim(_ids,',') );
											}
										});
	
										$this.find('a').on( 'click',function(e) {
											e.stopPropagation();
											e.preventDefault();
											$(this).closest('li').remove();
											var _ids = '';
											$this.find('li').each(function() {
												var _id = $(this).data( 'id' );
												_ids = _ids + _id + ',';
											});
	
											$this.closest('.dt-meta-box-field').find('#dt_meta_gallery_ids').val( dt_trim(_ids,',') );
	
											return false;
										});
										
									});
								}
							}
							dt_meta_gallery_fn();
						});
					</script>
					<?php
					endif;
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field">';
					echo '<label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label>';
					echo '<div class="dt-meta-gallery-wrap"><ul class="dt-meta-gallery-list">';
					if($field['value']){
						$value_arr = explode(',', $field['value']);
						if(!empty($value_arr) && is_array($value_arr)){
							foreach ($value_arr as $attachment_id ){
								if($attachment_id):
							?>
								<li data-id="<?php echo esc_attr( $attachment_id ) ?>">
									<div class="thumbnail">
										<div class="centered">
											<?php echo wp_get_attachment_image( $attachment_id, array(120,120) ); ?>						
										</div>
										<a title="<?php echo __('Delete','dawnthemes') ?>" href="#"><?php echo __('x','dawnthemes') ?></a>
									</div>						
								</li>
							<?php
								endif;
							}
						}
					}
					echo '</ul></div>';
					echo '<input type="hidden" name="' . $field['name'] . '" id="dt_meta_gallery_ids" value="' . $field['value'] . '" />';
					echo '<input type="button" class="button button-primary dt-meta-gallery-select" name="' . $field['id'] . '_button_upload" id="' . $field['id'] . '_upload" value="' . __('Add Gallery Images','dawnthemes') . '" /> ';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					echo '</div>';
				break;
				case 'media':
					if(function_exists( 'wp_enqueue_media' )){
						wp_enqueue_media();
					}else{
						wp_enqueue_style('thickbox');
						wp_enqueue_script('media-upload');
						wp_enqueue_script('thickbox');
					}
					$btn_text = !empty(  $field['value'] ) ? __( 'Change Media', 'dawnthemes' ) : __( 'Select Media', 'dawnthemes' );
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field">';
					echo '<label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label>';
					echo '<input type="text" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" style="width: 99%;margin-bottom:5px" />';
					echo '<input type="button" class="button button-primary" name="' . $field['id'] . '_button_upload" id="' . $field['id'] . '_upload" value="' . $btn_text . '" /> ';
					echo '<input type="button" class="button" name="' . $field['id'] . '_button_clear" id="' . $field['id'] . '_clear" value="' . __( 'Clear', 'dawnthemes' ) . '" />';				
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					echo '</div>';
					?>
					<script type="text/javascript">
						jQuery(document).ready(function($) {
							<?php if ( empty ( $field['value'] ) ) : ?> $('#<?php echo esc_attr($field['id']); ?>_clear').css('display', 'none'); <?php endif; ?>
							$('#<?php echo esc_attr($field['id']) ?>_upload').on('click', function(event) {
								event.preventDefault();
								var $this = $(this);
		
								// if media frame exists, reopen
								if(dt_<?php echo esc_attr($field['id']); ?>_media_frame) {
									dt_<?php echo esc_attr($field['id']); ?>_media_frame.open();
					                return;
					            }
		
								// create new media frame
								// I decided to create new frame every time to control the selected images
								var dt_<?php echo esc_attr($field['id']); ?>_media_frame = wp.media.frames.wp_media_frame = wp.media({
									title: "<?php echo __( 'Select or Upload your Media', 'dawnthemes' ); ?>",
									button: {
										text: "<?php echo __( 'Select', 'dawnthemes' ); ?>"
									},
									library: { type: 'video,audio' },
									multiple: false
								});
		
								// when image selected, run callback
								dt_<?php echo esc_attr($field['id']); ?>_media_frame.on('select', function(){
									var attachment = dt_<?php echo esc_attr($field['id']); ?>_media_frame.state().get('selection').first().toJSON();
									$this.closest('.dt-meta-box-field').find('input#<?php echo esc_attr($field['id']); ?>').val(attachment.url);
									
									$this.attr('value', '<?php echo __( 'Change Media', 'dawnthemes' ); ?>');
									$('#<?php echo esc_attr($field['id']); ?>_clear').css('display', 'inline-block');
								});
		
								// open media frame
								dt_<?php echo esc_attr($field['id']); ?>_media_frame.open();
							});
		
							$('#<?php echo esc_attr($field['id']) ?>_clear').on('click', function(event) {
								var $this = $(this);
								$this.hide();
								$('#<?php echo esc_attr($field['id']) ?>_upload').attr('value', '<?php echo __( 'Select Media', 'dawnthemes' ); ?>');
								$this.closest('.dt-meta-box-field').find('#<?php echo esc_attr($field['id']); ?>').val('');
							});
						});
					</script>
					<?php
				break;
				
				case 'image':
					if(function_exists( 'wp_enqueue_media' )){
						wp_enqueue_media();
					}else{
						wp_enqueue_style('thickbox');
						wp_enqueue_script('media-upload');
						wp_enqueue_script('thickbox');
					}
					$image_id = $field['value'];
					$image = wp_get_attachment_image( $image_id,array(120,120));
					$output = !empty( $image_id ) ? $image : '';
					$btn_text = !empty( $image_id ) ? __( 'Change Image', 'dawnthemes' ) : __( 'Select Image', 'dawnthemes' );
					echo '<div  class="dt-meta-box-field ' . esc_attr( $field['id'] ) . '_field">';
					echo '<label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label>';
					echo '<div class="dt-meta-image-thumb">' . $output . '</div>';
					echo '<input type="hidden" name="' . $field['name'] . '" id="' . $field['id'] . '" value="' . $field['value'] . '" />';
					echo '<input type="button" class="button button-primary" name="' . $field['id'] . '_button_upload" id="' . $field['id'] . '_upload" value="' . $btn_text . '" /> ';
					echo '<input type="button" class="button" name="' . $field['id'] . '_button_clear" id="' . $field['id'] . '_clear" value="' . __( 'Clear Image', 'dawnthemes' ) . '" />';
					if ( ! empty( $field['description'] ) ) {
						echo '<span class="description">' . dt_echo( $field['description'] ) . '</span>';
					}
					?>
					<script type="text/javascript">
						jQuery(document).ready(function($) {
							<?php if ( empty ( $field['value'] ) ) : ?> $('#<?php echo esc_attr($field['id']) ?>_clear').css('display', 'none'); <?php endif; ?>
							$('#<?php echo esc_attr($field['id']) ?>_upload').on('click', function(event) {
								event.preventDefault();
								var $this = $(this);
		
								// if media frame exists, reopen
								if(dt_<?php echo esc_attr($field['id']); ?>_image_frame) {
									dt_<?php echo esc_attr($field['id']); ?>_image_frame.open();
					                return;
					            }
		
								// create new media frame
								// I decided to create new frame every time to control the selected images
								var dt_<?php echo esc_attr($field['id']); ?>_image_frame = wp.media.frames.wp_media_frame = wp.media({
									title: "<?php echo __( 'Select or Upload your Image', 'dawnthemes' ); ?>",
									button: {
										text: "<?php echo __( 'Select', 'dawnthemes' ); ?>"
									},
									library: { type: 'image' },
									multiple: false
								});
		
								// when open media frame, add the selected image
								dt_<?php echo esc_attr($field['id']); ?>_image_frame.on('open',function() {
									var selected_id = $this.closest('.dt-meta-box-field').find('#<?php echo esc_attr($field['id']); ?>').val();
									if (!selected_id)
										return;
									var selection = dt_<?php echo esc_attr($field['id']); ?>_image_frame.state().get('selection');
									var attachment = wp.media.attachment(selected_id);
									attachment.fetch();
									selection.add( attachment ? [ attachment ] : [] );
								});
		
								// when image selected, run callback
								dt_<?php echo esc_attr($field['id']); ?>_image_frame.on('select', function(){
									var attachment = dt_<?php echo esc_attr($field['id']); ?>_image_frame.state().get('selection').first().toJSON();
									$this.closest('.dt-meta-box-field').find('input#<?php echo esc_attr($field['id']); ?>').val(attachment.id);
									var thumbnail = $this.closest('.dt-meta-box-field').find('.dt-meta-image-thumb');
									thumbnail.html('');
									thumbnail.append('<img src="' + attachment.url + '" alt="" />');
		
									$this.attr('value', '<?php echo __( 'Change Image', 'dawnthemes' ); ?>');
									$('#<?php echo esc_attr($field['id']); ?>_clear').css('display', 'inline-block');
								});
		
								// open media frame
								dt_<?php echo esc_attr($field['id']); ?>_image_frame.open();
							});
		
							$('#<?php echo esc_attr($field['id']); ?>_clear').on('click', function(event) {
								var $this = $(this);
								$this.hide();
								$('#<?php echo esc_attr($field['id']); ?>_upload').attr('value', '<?php echo __( 'Select Image', 'dawnthemes' ); ?>');
								$this.closest('.dt-meta-box-field').find('input#<?php echo esc_attr($field['id']); ?>').val('');
								$this.closest('.dt-meta-box-field').find('.dt-meta-image-thumb').html('');
							});
						});
					</script>
								
					<?php
					echo '</div>';
				break;
			}
		}
	}
	
	echo '</div>';
}