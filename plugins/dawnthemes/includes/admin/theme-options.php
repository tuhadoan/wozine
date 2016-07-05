<?php
if ( ! class_exists( 'DTThemeOptions' ) ) :

	class DTThemeOptions {

		protected $_sections = array(); // Sections and fields

		protected static $_option_name;

		public function __construct() {
			$this->_sections = $this->get_sections();
			
			self::$_option_name = dt_get_theme_option_name();
			
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			// Download theme option
			add_action( "wp_ajax_dt_download_theme_option", array( &$this, "download_theme_option" ) );
		}

		public static function get_options( $key, $default = null ) {
			global $dt_theme_options;
			if ( empty( $dt_theme_options ) ) {
				$dt_theme_options = get_option( self::$_option_name );
			}
			if ( isset( $dt_theme_options[$key] ) && $dt_theme_options[$key] !== '' ) {
				return $dt_theme_options[$key];
			} else {
				return $default;
			}
		}

		public function admin_init() {
			register_setting( self::$_option_name, self::$_option_name, array( &$this, 'register_setting_callback' ) );
			$_opions = get_option( self::$_option_name );
			if ( empty( $_opions ) ) {
				$default_options = array();
				foreach ( $this->_sections as $key => $sections ) {
					if ( is_array( $sections['fields'] ) && ! empty( $sections['fields'] ) ) {
						foreach ( $sections['fields'] as $field ) {
							if ( isset( $field['name'] ) && isset( $field['value'] ) ) {
								$default_options[$field['name']] = $field['value'];
							}
						}
					}
				}
				if ( ! empty( $default_options ) ) {
					$options = array();
					foreach ( $default_options as $key => $value ) {
						$options[$key] = $value;
					}
				}
				$r = update_option( self::$_option_name, $options );
			}
		}

		protected static function getFileSystem( $url = '' ) {
			if ( empty( $url ) ) {
				$url = wp_nonce_url( 'admin.php?page=theme-options', 'register_setting_callback' );
			}
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
				_e( 'This is required to enable file writing', 'dawnthemes' );
				exit(); // stop processing here
			}
			$assets_dir = get_template_directory();
			if ( ! WP_Filesystem( $creds, $assets_dir ) ) {
				request_filesystem_credentials( $url, '', true, false, null );
				_e( 'This is required to enable file writing', 'dawnthemes' );
				exit();
			}
		}

		public function register_setting_callback( $options ) {
			$less_flag = false;
			
			do_action( 'dt_theme_option_before_setting_callback', $options );
			
			$update_options = array();
			foreach ( $this->_sections as $key => $sections ) {
				if ( is_array( $sections['fields'] ) && ! empty( $sections['fields'] ) ) {
					foreach ( $sections['fields'] as $field ) {
						if ( isset( $field['name'] ) && isset( $options[$field['name']] ) ) {
							$option_value = $options[$field['name']];
							$option_value = wp_unslash( $option_value );
							if ( is_array( $option_value ) ) {
								$option_value = array_filter( 
									array_map( 'sanitize_text_field', (array) $option_value ) );
							} else {
								if ( $field['type'] == 'textarea' ) {
									$option_value = wp_kses_post( trim( $option_value ) );
								} elseif ( $field['type'] == 'ace_editor' ) {
									$option_value = $option_value;
								} else {
									$option_value = wp_kses_post( trim( $option_value ) );
								}
							}
							$update_options[$field['name']] = $option_value;
						}
					}
				}
			}
			if ( ! empty( $update_options ) ) {
				foreach ( $update_options as $key => $value ) {
					$options[$key] = $value;
				}
			}
			
			if ( isset( $options['dt_opt_import'] ) ) {
				$import_code = $options['import_code'];
				if ( ! empty( $import_code ) ) {
					$imported_options = json_decode( $import_code, true );
					if ( ! empty( $imported_options ) && is_array( $imported_options ) ) {
						foreach ( $imported_options as $key => $value ) {
							$options[$key] = $value;
						}
					}
				}
			}
			if ( isset( $options['dt_opt_reset'] ) ) {
				$default_options = array();
				foreach ( $this->_sections as $key => $sections ) {
					if ( is_array( $sections['fields'] ) && ! empty( $sections['fields'] ) ) {
						foreach ( $sections['fields'] as $field ) {
							if ( isset( $field['name'] ) && isset( $field['value'] ) ) {
								$default_options[$field['name']] = $field['value'];
							}
						}
					}
				}
				if ( ! empty( $default_options ) ) {
					$options = array();
					foreach ( $default_options as $key => $value ) {
						$options[$key] = $value;
					}
				}
			}
			
			unset( $options['import_code'] );
			do_action( 'dt_theme_option_after_setting_callback', $options );
			return $options;
		}

		public function get_default_option() {
			return apply_filters( 'dt_theme_option_default', '' );
		}

		public function option_page() {
			?>
<div class="clear"></div>
<div class="wrap">
	<input type="hidden" id="security" name="security"
		value="<?php echo wp_create_nonce( 'dt_theme_option_ajax_security' ) ?>" />
	<form method="post" action="options.php" enctype="multipart/form-data">
				<?php settings_fields( self::$_option_name ); ?>
				<div class="dt-opt-header">
			<div class="dt-opt-heading">
				<h2><?php echo DT_THEME_NAME?> <span><?php echo DT_THEME_VERSION?></span>
				</h2>
				<a target="_blank"
					href="http://dawnthemes.com/<?php echo basename(get_template_directory())?>/document"><?php _e('Online Document','dawnthemes')?></a>
			</div>
		</div>
		<div class="clear"></div>
		<div class="dt-opt-actions">
			<em style="float: left; margin-top: 5px;"><?php echo esc_html('Theme customizations are done here. Happy styling!','dawnthemes')?></em>
			<button id="dt-opt-submit" name="dt_opt_save" class="button-primary"
				type="submit"><?php echo esc_html__('Save All Change','dawnthemes') ?></button>
		</div>
		<div class="clear"></div>
		<div id="dt-opt-tab" class="dt-opt-wrap">
			<div class="dt-opt-sidebar">
				<ul id="dt-opt-menu" class="dt-opt-menu">
							<?php $i = 0;?>
							<?php foreach ((array) $this->_sections as $key=>$sections):?>
								<li <?php echo ($i == 0 ? ' class="current"': '')?>><a
						href="#<?php
				
				echo esc_attr( $key )?>"
						title="<?php echo esc_attr($sections['title']) ?>"><?php echo (isset($sections['icon']) ? '<i class="'.$sections['icon'].'"></i> ':'')?><?php echo esc_html($sections['title']) ?></a>
					</li>
							<?php $i++?>
							<?php endforeach;?>
						</ul>
			</div>
			<div id="dt-opt-content" class="dt-opt-content">
						<?php $i = 0;?>
						<?php foreach ((array) $this->_sections as $key=>$sections):?>
							<div id=<?php echo esc_attr($key)?> class="dt-opt-section"
					<?php echo ($i == 0 ? ' style="display:block"': '') ?>>
					<h3><?php echo esc_html($sections['title']) ?></h3>
								<?php if(isset($sections['desc'])):?>
								<div class="dt-opt-section-desc">
									<?php echo dt_echo($sections['desc'])?>
								</div>
								<?php endif;?>
								<table class="form-table">
						<tbody>
										<?php foreach ( (array) $sections['fields'] as $field ) { ?>
										<tr>
											<?php if ( !empty($field['label']) ): ?>
											<th scope="row">
									<div class="dt-opt-label">
													<?php echo esc_html($field['label'])?>
													<?php if ( !empty($field['desc']) ): ?>
													<span class="description"><?php echo dt_echo($field['desc'])?></span>
													<?php endif;?>
												</div>
								</th>
											<?php endif;?>
											<td <?php if(empty($field['label'])):?> colspan="2"
									<?php endif;?>>
									<div class="dt-opt-field-wrap">
													<?php
					if ( isset( $field['callback'] ) )
						call_user_func( $field['callback'], $field );
					?>
													<?php echo dt_echo($this->_render_field($field))?>
												</div>
								</td>
							</tr>
										<?php } ?>
									</tbody>
					</table>
				</div>
						<?php $i++?>
						<?php endforeach;?>
					</div>
		</div>
		<div class="clear"></div>
		<div class="dt-opt-actions2">
			<button id="dt-opt-submit2" name="dt_opt_save" class="button-primary"
				type="submit"><?php echo esc_html__('Save All Change','dawnthemes') ?></button>
			<button id="dt-opt-reset"
				name="<?php echo self::$_option_name?>[dt_opt_reset]" class="button"
				type="submit"><?php echo esc_html__('Reset Options','dawnthemes') ?></button>
		</div>
		<div class="clear"></div>
	</form>
</div>
<?php
		}

		public function _render_field( $field = array() ) {
			if ( ! isset( $field['type'] ) )
				echo '';
			
			$field['name'] = isset( $field['name'] ) ? esc_attr( $field['name'] ) : '';
			
			$value = self::get_options( $field['name'] );
			$field['value'] = isset( $field['value'] ) ? $field['value'] : '';
			
			$field['value'] = apply_filters( 'dt_theme_option_field_std', $field['value'], $field );
			$field['default_value'] = $field['value'];
			if ( $value !== '' && $value !== null && $value !== array() && $value !== false ) {
				$field['value'] = $value;
			}
			
			$field['id'] = isset( $field['id'] ) ? esc_attr( $field['id'] ) : $field['name'];
			$field['desc'] = isset( $field['desc'] ) ? $field['desc'] : '';
			$field['label'] = isset( $field['label'] ) ? $field['label'] : '';
			$field['placeholder'] = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : esc_attr( 
				$field['label'] );
			
			$data_name = ' data-name="' . $field['name'] . '"';
			$field_name = self::$_option_name . '[' . $field['name'] . ']';
			
			$dependency_cls = isset( $field['dependency'] ) ? ' dt-dependency-field' : '';
			$dependency_data = '';
			if ( ! empty( $dependency_cls ) ) {
				$dependency_default = array( 'element' => '', 'value' => array() );
				$field['dependency'] = wp_parse_args( $field['dependency'], $dependency_default );
				if ( ! empty( $field['dependency']['element'] ) && ! empty( $field['dependency']['value'] ) )
					$dependency_data = ' data-master="' . esc_attr( $field['dependency']['element'] ) .
						 '" data-master-value="' . esc_attr( implode( ',', $field['dependency']['value'] ) ) . '"';
			}
			
			if ( isset( $field['field-label'] ) ) {
				echo '<p class="field-label">' . $field['field-label'] . '</p>';
			}
			
			switch ( $field['type'] ) {
				case 'heading' :
					echo '<h4>' . ( isset( $field['text'] ) ? $field['text'] : '' ) . '</h4>';
					break;
				case 'hr' :
					echo '<hr/>';
					break;
				case 'datetimepicker' :
					wp_enqueue_script( 'vendor-datetimepicker' );
					wp_enqueue_style( 'vendor-datetimepicker' );
					echo '<div class="dt-field-text ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<input type="text" readonly class="dt-opt-value input_text" name="' . $field_name . '" id="' .
						 $field['id'] . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' .
						 $field['placeholder'] . '" style="width:99%" /> ';
					echo '</div>';
					?>
<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('#<?php echo esc_attr($field['id']); ?>').datetimepicker({
						 scrollMonth: false,
						 scrollTime: false,
						 scrollInput: false,
						 step:15,
						 format:'m/d/Y H:i'
						});
					});
				</script>
<?php
					break;
				case 'image' :
					if ( function_exists( 'wp_enqueue_media' ) ) {
						wp_enqueue_media();
					} else {
						wp_enqueue_style( 'thickbox' );
						wp_enqueue_script( 'media-upload' );
						wp_enqueue_script( 'thickbox' );
					}
					
					$image = $field['value'];
					$output = ! empty( $image ) ? '<img src="' . $image . '" with="200">' : '';
					
					$btn_text = ! empty( $image_id ) ? esc_html__( 'Change Image', 'dawnthemes' ) : esc_html__( 
						'Select Image', 
						'dawnthemes' );
					echo '<div  class="dt-field-image ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<div class="dt-field-image-thumb">' . $output . '</div>';
					echo '<input type="hidden" class="dt-opt-value" name="' . $field_name . '" id="' . $field['id'] .
						 '" value="' . esc_attr( $field['value'] ) . '" />';
					echo '<input type="button" class="button button-primary" id="' . $field['id'] . '_upload" value="' .
						 $btn_text . '" /> ';
					echo '<input type="button" class="button" id="' . $field['id'] . '_clear" value="' .
						 esc_html__( 'Clear Image', 'dawnthemes' ) . '" ' .
						 ( empty( $field['value'] ) ? ' style="display:none"' : '' ) . ' />';
					?>
<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('#<?php echo esc_attr($field['id']); ?>_upload').on('click', function(event) {
							event.preventDefault();
							var $this = $(this);
	
							// if media frame exists, reopen
							if(dt_meta_image_frame) {
								dt_meta_image_frame.open();
				                return;
				            }
	
							// create new media frame
							// I decided to create new frame every time to control the selected images
							var dt_meta_image_frame = wp.media.frames.wp_media_frame = wp.media({
								title: "<?php echo esc_html__( 'Select or Upload your Image', 'dawnthemes' ); ?>",
								button: {
									text: "<?php echo esc_html__( 'Select', 'dawnthemes' ); ?>"
								},
								library: { type: 'image' },
								multiple: false
							});
	
							// when image selected, run callback
							dt_meta_image_frame.on('select', function(){
								var attachment = dt_meta_image_frame.state().get('selection').first().toJSON();
								$this.closest('.dt-field-image').find('input#<?php echo esc_attr($field['id']); ?>').val(attachment.url);
								var thumbnail = $this.closest('.dt-field-image').find('.dt-field-image-thumb');
								thumbnail.html('');
								thumbnail.append('<img src="' + attachment.url + '" alt="" />');
	
								$this.attr('value', '<?php echo esc_html__( 'Change Image', 'dawnthemes' ); ?>');
								$('#<?php echo esc_attr($field['id']); ?>_clear').css('display', 'inline-block');
							});
	
							// open media frame
							dt_meta_image_frame.open();
						});
	
						$('#<?php echo esc_attr($field['id']); ?>_clear').on('click', function(event) {
							var $this = $(this);
							$this.hide();
							$('#<?php echo esc_attr($field['id']); ?>_upload').attr('value', '<?php echo esc_html__( 'Select Image', 'dawnthemes' ); ?>');
							$this.closest('.dt-field-image').find('input#<?php echo esc_attr($field['id']); ?>').val('');
							$this.closest('.dt-field-image').find('.dt-field-image-thumb').html('');
						});
					});
				</script>

<?php
					echo '</div>';
					break;
				case 'color' :
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
					
					echo '<div  class="dt-field-color ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<input type="text" class="dt-opt-value" name="' . $field_name . '" id="' . $field['id'] .
						 '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . $field['placeholder'] . '" /> ';
					echo '<script type="text/javascript">
					jQuery(document).ready(function($){
					    $("#' . ( $field['id'] ) . '").wpColorPicker();
					});
				 </script>
				';
					echo '</div>';
					break;
				case 'muitl-select' :
				case 'select' :
					if ( $field['type'] == 'muitl-select' ) {
						
						$field_name = $field_name . '[]';
					}
					$field['options'] = isset( $field['options'] ) ? $field['options'] : array();
					echo '<div  class="dt-field-select ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<select ' . ( $field['type'] == 'muitl-select' ? 'multiple="multiple"' : $data_name ) .
						 ' data-placeholder="' . $field['label'] . '" class="dt-opt-value dt-chosen-select"  id="' .
						 $field['id'] . '" name="' . $field_name . '">';
					foreach ( $field['options'] as $key => $value ) {
						if ( $field['type'] == 'muitl-select' ) {
							echo '<option value="' . esc_attr( $key ) . '" ' .
								 ( in_array( esc_attr( $key ), $field['value'] ) ? 'selected="selected"' : '' ) . '>' .
								 esc_html( $value ) . '</option>';
						} else {
							echo '<option value="' . esc_attr( $key ) . '" ' .
								 selected( ( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) .
								 '</option>';
						}
					}
					echo '</select> ';
					echo '</div>';
					break;
				case 'textarea' :
					echo '<div class="dt-field-textarea ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<textarea class="dt-opt-value" name="' . $field_name . '" id="' . $field['id'] .
						 '" placeholder="' . $field['placeholder'] . '" rows="5" cols="20" style="width: 99%;">' .
						 esc_textarea( $field['value'] ) . '</textarea> ';
					echo '</div>';
					break;
				case 'ace_editor' :
					echo '<div class="dt-field-textarea ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<pre id="' . $field['id'] .
						 '-editor" class="dt-opt-value" style="height: 205px;border:1px solid #ccc">' . $field['value'] .
						 '</pre>';
					echo '<textarea class="dt-opt-value" id="' . $field['id'] . '" name="' . $field_name .
						 '" placeholder="' . $field['placeholder'] . '" style="width: 99%;display:none">' .
						 $field['value'] . '</textarea> ';
					echo '</div>';
					break;
				case 'switch' :
					$cb_enabled = $cb_disabled = ''; // no errors, please
					if ( (int) $field['value'] == 1 ) {
						$cb_enabled = ' selected';
					} else {
						$field['value'] = 0;
						$cb_disabled = ' selected';
					}
					// Label On
					if ( ! isset( $field['on'] ) ) {
						$on = esc_html__( 'On', 'dawnthemes' );
					} else {
						$on = $field['on'];
					}
					
					// Label OFF
					if ( ! isset( $field['off'] ) ) {
						$off = esc_html__( 'Off', 'dawnthemes' );
					} else {
						$off = $field['off'];
					}
					
					echo '<div class="dt-field-switch ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<label class="cb-enable' . $cb_enabled . '" data-id="' . $field['id'] . '">' . $on . '</label>';
					echo '<label class="cb-disable' . $cb_disabled . '" data-id="' . $field['id'] . '">' . $off .
						 '</label>';
					echo '<input ' . $data_name . ' type="hidden"  class="dt-opt-value switch-input" id="' . $field['id'] .
						 '" name="' . $field_name . '" value="' . esc_attr( $field['value'] ) . '" />';
					echo '</div>';
					break;
				case 'buttonset' :
					$field['options'] = isset( $field['options'] ) ? $field['options'] : array();
					echo '<div class="dt-field-buttonset ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<div class="dt-buttonset">';
					foreach ( $field['options'] as $key => $value ) {
						echo '<input ' . $data_name . ' name="' . $field_name . '"
								id="' . esc_attr( $field['name'] . '-' . $key ) . '"
				        		value="' . esc_attr( $key ) . '"
				        		type="radio"
								class="dt-opt-value"
				        		' . checked( $field['value'], esc_attr( $key ), false ) . '
				        		/><label for="' . esc_attr( $field['name'] . '-' . $key ) . '">' . esc_html( $value ) . '</label>';
					}
					echo '</div>';
					echo '</div>';
					break;
				case 'radio' :
					$field['options'] = isset( $field['options'] ) ? $field['options'] : array();
					echo '<div class="dt-field-radio ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<ul>';
					foreach ( $field['options'] as $key => $value ) {
						echo '<li><label><input
				        		name="' . $field_name . '"
				        		value="' . esc_attr( $key ) . '"
				        		type="radio"
								' . $data_name . '
								class="dt-opt-value radio"
				        		' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
				        		/> ' . esc_html( $value ) . '</label>
				    	</li>';
					}
					echo '</ul>';
					echo '</div>';
					break;
				case 'text' :
					echo '<div class="dt-field-text ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<input type="text" class="dt-opt-value input_text" name="' . $field_name . '" id="' .
						 $field['id'] . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' .
						 $field['placeholder'] . '" style="width:99%" /> ';
					echo '</div>';
					break;
				case 'background' :
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
					if ( function_exists( 'wp_enqueue_media' ) ) {
						wp_enqueue_media();
					} else {
						wp_enqueue_style( 'thickbox' );
						wp_enqueue_script( 'media-upload' );
						wp_enqueue_script( 'thickbox' );
					}
					$value_default = array( 
						'background-color' => '', 
						'background-repeat' => '', 
						'background-attachment' => '', 
						'background-position' => '', 
						'background-image' => '', 
						'background-clip' => '', 
						'background-origin' => '', 
						'background-size' => '', 
						'media' => array() );
					$values = wp_parse_args( $field['value'], $value_default );
					echo '<div class="dt-field-background ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					// background color
					echo '<div  class="dt-background-color">';
					echo '<input type="text" class="dt-opt-value" name="' . $field_name . '[background-color]" id="' .
						 $field['id'] . '_background_color" value="' . esc_attr( $values['background-color'] ) . '" /> ';
					echo '<script type="text/javascript">
					jQuery(document).ready(function($){
					    $("#' . ( $field['id'] ) . '_background_color").wpColorPicker();
					});
				 </script>
				';
					echo '</div>';
					echo '<br>';
					// background repeat
					echo '<div  class="dt-background-repeat">';
					$bg_repeat_options = array( 
						'no-repeat' => 'No Repeat', 
						'repeat' => 'Repeat All', 
						'repea-x' => 'Repeat Horizontally', 
						'repeat-y' => 'Repeat Vertically', 
						'inherit' => 'Inherit' );
					echo '<select class="dt-opt-value dt-chosen-select-nostd" id="' . $field['id'] .
						 '_background_repeat" data-placeholder="' . esc_html__( 'Background Repeat', 'dawnthemes' ) .
						 '" name="' . $field_name . '[background-repeat]">';
					echo '<option value=""></option>';
					foreach ( $bg_repeat_options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ' .
							 selected( $values['background-repeat'], esc_attr( $key ), false ) . '>' . esc_html( 
								$value ) . '</option>';
					}
					echo '</select> ';
					echo '</div>';
					// background size
					echo '<div  class="dt-background-size">';
					$bg_size_options = array( 'inherit' => 'Inherit', 'cover' => 'Cover', 'contain' => 'Contain' );
					echo '<select class="dt-opt-value dt-chosen-select-nostd" id="' . $field['id'] .
						 '_background_size" data-placeholder="' . esc_html__( 'Background Size', 'dawnthemes' ) . '" name="' .
						 $field_name . '[background-size]">';
					echo '<option value=""></option>';
					foreach ( $bg_size_options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ' .
							 selected( $values['background-size'], esc_attr( $key ), false ) . '>' . esc_html( $value ) .
							 '</option>';
					}
					echo '</select> ';
					echo '</div>';
					// background attachment
					echo '<div  class="dt-background-attachment">';
					$bg_attachment_options = array( 'fixed' => 'Fixed', 'scroll' => 'Scroll', 'inherit' => 'Inherit' );
					echo '<select class="dt-opt-value dt-chosen-select-nostd" id="' . $field['id'] .
						 '_background_attachment" data-placeholder="' . esc_html__( 'Background Attachment', 'dawnthemes' ) .
						 '"  name="' . $field_name . '[background-attachment]">';
					echo '<option value=""></option>';
					foreach ( $bg_attachment_options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ' .
							 selected( $values['background-attachment'], esc_attr( $key ), false ) . '>' .
							 esc_html( $value ) . '</option>';
					}
					echo '</select> ';
					echo '</div>';
					// background position
					echo '<div  class="dt-background-position">';
					$bg_position_options = array( 
						'left top' => 'Left Top', 
						'left center' => 'Left center', 
						'left bottom' => 'Left Bottom', 
						'center top' => 'Center Top', 
						'center center' => 'Center Center', 
						'center bottom' => 'Center Bottom', 
						'right top' => 'Right Top', 
						'right center' => 'Right center', 
						'right bottom' => 'Right Bottom' );
					echo '<select class="dt-opt-value dt-chosen-select-nostd"  id="' . $field['id'] .
						 '_background_position" data-placeholder="' . esc_html__( 'Background Position', 'dawnthemes' ) .
						 '" name="' . $field_name . '[background-position]">';
					echo '<option value=""></option>';
					foreach ( $bg_position_options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ' .
							 selected( $values['background-position'], esc_attr( $key ), false ) . '>' .
							 esc_html( $value ) . '</option>';
					}
					echo '</select> ';
					echo '</div>';
					// background image
					
					$image = $values['background-image'];
					$output = ! empty( $image ) ? '<img src="' . $image . '" with="100">' : '';
					$btn_text = ! empty( $image_id ) ? esc_html__( 'Change Image', 'dawnthemes' ) : esc_html__( 
						'Select Image', 
						'dawnthemes' );
					echo '<br>';
					echo '<div  class="dt-background-image">';
					echo '<div class="dt-field-image-thumb">' . $output . '</div>';
					echo '<input type="hidden" class="dt-opt-value" name="' . $field_name . '[background-image]" id="' .
						 $field['id'] . '_background_image" value="' . esc_attr( $values['background-image'] ) . '" />';
					echo '<input type="button" class="button button-primary" id="' . $field['id'] .
						 '_background_image_upload" value="' . $btn_text . '" /> ';
					echo '<input type="button" class="button" id="' . $field['id'] . '_background_image_clear" value="' .
						 esc_html__( 'Clear Image', 'dawnthemes' ) . '" ' .
						 ( empty( $field['value'] ) ? ' style="display:none"' : '' ) . ' />';
					?>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('#<?php echo esc_attr($field['id']); ?>_background_image_upload').on('click', function(event) {
							event.preventDefault();
							var $this = $(this);
	
							// if media frame exists, reopen
							if(dt_meta_image_frame) {
								dt_meta_image_frame.open();
				                return;
				            }
	
							// create new media frame
							// I decided to create new frame every time to control the selected images
							var dt_meta_image_frame = wp.media.frames.wp_media_frame = wp.media({
								title: "<?php echo esc_html__( 'Select or Upload your Image', 'dawnthemes' ); ?>",
								button: {
									text: "<?php echo esc_html__( 'Select', 'dawnthemes' ); ?>"
								},
								library: { type: 'image' },
								multiple: false
							});
	
							// when image selected, run callback
							dt_meta_image_frame.on('select', function(){
								var attachment = dt_meta_image_frame.state().get('selection').first().toJSON();
								$this.closest('.dt-background-image').find('input#<?php echo esc_attr($field['id']); ?>_background_image').val(attachment.url);
								var thumbnail = $this.closest('.dt-background-image').find('.dt-field-image-thumb');
								thumbnail.html('');
								thumbnail.append('<img src="' + attachment.url + '" alt="" />');
	
								$this.attr('value', '<?php echo esc_html__( 'Change Image', 'dawnthemes' ); ?>');
								$('#<?php echo esc_attr($field['id']); ?>_background_image_clear').css('display', 'inline-block');
							});
	
							// open media frame
							dt_meta_image_frame.open();
						});
	
						$('#<?php echo esc_attr($field['id']); ?>_background_image_clear').on('click', function(event) {
							var $this = $(this);
							$this.hide();
							$('#<?php echo esc_attr($field['id']); ?>_background_image_upload').attr('value', '<?php echo esc_html__( 'Select Image', 'dawnthemes' ); ?>');
							$this.closest('.dt-background-image').find('input#<?php echo esc_attr($field['id']); ?>_background_image').val('');
							$this.closest('.dt-background-image').find('.dt-field-image-thumb').html('');
						});
					});
				</script>

<?php
					echo '</div>';
					echo '</div>';
					break;
				case 'custom_font' :
					$value_default = array( 
						'font-family' => '', 
						'font-size' => '', 
						'font-style' => '', 
						'text-transform' => '', 
						'letter-spacing' => '', 
						'subset' => '' );
					$values = wp_parse_args( $field['value'], $value_default );
					global $google_fonts;
					if ( empty( $google_fonts ) )
						include_once ( DTINC_DIR . '/lib/google-fonts.php' );
					
					$google_fonts_object = json_decode( $google_fonts );
					$google_faces = array();
					foreach ( $google_fonts_object as $obj => $props ) {
						$google_faces[$props->family] = $props->family;
					}
					echo '<div class="dt-field-custom-font ' . ( $field['id'] ) . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					// font-family
					echo '<div  class="custom-font-family">';
					echo '<select data-placeholder="' . esc_html__( 'Select a font family', 'dawnthemes' ) .
						 '" class="dt-opt-value dt-chosen-select-nostd"  id="' . $field['id'] . '" name="' . $field_name .
						 '[font-family]">';
					echo '<option value=""></option>';
					foreach ( $google_faces as $key => $value ) {
						echo '<option value="' . ( $key ) . '" ' .
							 selected( ( $values['font-family'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) .
							 '</option>';
					}
					echo '</select> ';
					echo '</div>';
					// font-size
					echo '<div  class="custom-font-size">';
					echo '<select data-placeholder="' . esc_html__( 'Font size', 'dawnthemes' ) .
						 '" class="dt-opt-value dt-chosen-select-nostd"  id="' . $field['id'] . '" name="' . $field_name .
						 '[font-size]">';
					echo '<option value=""></option>';
					foreach ( (array) dt_custom_font_size( true ) as $key => $value ) {
						echo '<option value="' . ( $key ) . '" ' .
							 selected( ( $values['font-size'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) .
							 '</option>';
					}
					echo '</select> ';
					echo '</div>';
					// font-style
					echo '<div  class="custom-font-style">';
					echo '<select data-placeholder="' . esc_html__( 'Font style', 'dawnthemes' ) .
						 '" class="dt-opt-value dt-chosen-select-nostd"  id="' . $field['id'] . '" name="' . $field_name .
						 '[font-style]">';
					echo '<option value=""></option>';
					foreach ( (array) dt_custom_font_style( true ) as $key => $value ) {
						echo '<option value="' . ( $key ) . '" ' .
							 selected( ( $values['font-style'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) .
							 '</option>';
					}
					echo '</select> ';
					echo '</div>';
					
					// subset
					$subset = array( 
						"latin" => "Latin", 
						"latin-ext" => "Latin Ext", 
						"cyrillic" => "Cyrillic", 
						"cyrillic-ext" => "Cyrillic Ext", 
						"greek" => "Greek", 
						"greek-ext" => "Greek Ext", 
						"vietnamese" => "Vietnamese" );
					echo '<div  class="custom-font-subset">';
					echo '<select data-placeholder="' . esc_html__( 'Subset', 'dawnthemes' ) .
						 '" class="dt-opt-value dt-chosen-select-nostd"  id="' . $field['id'] . '" name="' . $field_name .
						 '[subset]">';
					echo '<option value=""></option>';
					foreach ( (array) $subset as $key => $value ) {
						echo '<option value="' . ( $key ) . '" ' .
							 selected( ( $values['subset'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) .
							 '</option>';
					}
					echo '</select> ';
					echo '</div>';
					
					echo '</div>';
					break;
				case 'list_color' :
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
					echo '<div class="dt-field-list-color ' . ( $field['id'] ) . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					$field['options'] = isset( $field['options'] ) ? $field['options'] : array();
					foreach ( $field['options'] as $key => $label ) {
						$values[$key] = isset( $field['value'][$key] ) ? $field['value'][$key] : '';
						echo '<div>' . $label . '<br>';
						echo '<input type="text" class="dt-opt-value" name="' . $field_name . '[' . $key . ']" id="' .
							 $field['id'] . '_' . $key . '" value="' . esc_attr( $values[$key] ) . '" /> ';
						echo '<script type="text/javascript">
						jQuery(document).ready(function($){
						    $("#' . $field['id'] . '_' . $key . '").wpColorPicker();
						});
					 </script>
					';
						echo '</div>';
					}
					echo '</div>';
					break;
				case 'image_select' :
					$field['options'] = isset( $field['options'] ) ? $field['options'] : array();
					echo '<div class="dt-field-image-select ' . ( $field['id'] ) . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<ul class="dt-image-select">';
					foreach ( $field['options'] as $key => $value ) {
						echo '<li' . ( $field['value'] == $key ? ' class="selected"' : '' ) . '><label for="' .
							 esc_attr( $key ) . '"><input
			        		name="' . $field_name . '"
							id="' . esc_attr( $key ) . '"
			        		value="' . esc_attr( $key ) . '"
			        		type="radio"
							' . $data_name . '
							class="dt-opt-value"
			        		' . checked( $field['value'], esc_attr( $key ), false ) . '
			        		/><img title="' . esc_attr( @$value['alt'] ) . '" alt="' . esc_attr( @$value['alt'] ) . '" src="' .
							 esc_url( @$value['img'] ) . '"></label>
				    </li>';
					}
					echo '</ul>';
					echo '</div>';
					break;
				case 'import' :
					echo '<div class="dt-field-import ' . $field['id'] . '-field' . $dependency_cls . '"' .
						 $dependency_data . '>';
					echo '<textarea id="' . $field['id'] . '" name="' . self::$_option_name .
						 '[import_code]" placeholder="' . $field['placeholder'] .
						 '" rows="5" cols="20" style="width: 99%;"></textarea><br><br>';
					echo '<button id="dt-opt-import" class="button-primary" name="' . self::$_option_name .
						 '[dt_opt_import]" type="submit">' . esc_html__( 'Import', 'dawnthemes' ) . '</button>';
					echo ' <em style="font-size:11px;color:#f00">' . esc_html__( 
						'WARNING! This will overwrite all existing option values, please proceed with caution!', 
						'dawnthemes' ) . '</em>';
					echo '</div>';
					break;
				case 'export' :
					$secret = md5( AUTH_KEY . SECURE_AUTH_KEY );
					$link = admin_url( 'admin-ajax.php?action=dt_download_theme_option&secret=' . $secret );
					echo '<a id="dt-opt-export" class="button-primary" href="' . esc_url( $link ) . '">' .
						 esc_html__( 'Export', 'dawnthemes' ) . '</a>';
					break;
				default :
					break;
			}
		}

		public function get_sections() {
			$section = array( 
				'general' => array( 
					'icon' => 'fa fa-home', 
					'title' => esc_html__( 'General', 'dawnthemes' ), 
					'desc' => __( 
						'<p class="description">Here you will set your site-wide preferences.</p>', 
						'dawnthemes' ), 
					'fields' => array( 
						array( 
							'name' => 'logo', 
							'type' => 'image', 
							'value' => get_template_directory_uri() . '/assets/images/logo.png', 
							'label' => esc_html__( 'Logo', 'dawnthemes' ), 
							'desc' => esc_html__( 'Upload your own logo.', 'dawnthemes' ) ), 
						array( 
							'name' => 'logo-fixed', 
							'type' => 'image', 
							'value' => get_template_directory_uri() . '/assets/images/logo.png', 
							'label' => esc_html__( 'Fixed Menu Logo', 'dawnthemes' ), 
							'desc' => esc_html__( 'Upload your own logo.This is optional use when fixed menu', 'dawnthemes' ) ), 
						array( 
							'name' => 'logo-transparent', 
							'type' => 'image', 
							'value' => get_template_directory_uri() . '/assets/images/logo-dark.png', 
							'label' => esc_html__( 'Transparent Menu Logo', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Upload your own logo.This is optional use for menu transparent', 
								'dawnthemes' ) ), 
						array( 
							'name' => 'logo-mobile', 
							'type' => 'image', 
							'value' => get_template_directory_uri() . '/assets/images/logo-mobile.png', 
							'label' => esc_html__( 'Mobile Version Logo', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Use this option to change your logo for mobile devices if your logo width is quite long to fit in mobile device screen.', 
								'dawnthemes' ) ), 
						array( 
							'name' => 'favicon', 
							'type' => 'image', 
							'value' => get_template_directory_uri() . '/assets/images/favicon.ico', 
							'label' => esc_html__( 'Favicon', 'dawnthemes' ), 
							'desc' => esc_html__( 'Image that will be used as favicon (32px32px).', 'dawnthemes' ) ), 
						array( 
							'name' => 'apple57', 
							'type' => 'image', 
							'label' => esc_html__( 'Apple Iphone Icon', 'dawnthemes' ), 
							'desc' => esc_html__( 'Apple Iphone Icon (57px 57px).', 'dawnthemes' ) ), 
						array( 
							'name' => 'apple72', 
							'type' => 'image', 
							'label' => esc_html__( 'Apple iPad Icon', 'dawnthemes' ), 
							'desc' => esc_html__( 'Apple Iphone Retina Icon (72px 72px).', 'dawnthemes' ) ), 
						array( 
							'name' => 'apple114', 
							'type' => 'image', 
							'label' => esc_html__( 'Apple Retina Icon', 'dawnthemes' ), 
							'desc' => esc_html__( 'Apple iPad Retina Icon (144px 144px).', 'dawnthemes' ) ), 
						array( 
							'name' => 'back-to-top', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Back To Top Button', 'dawnthemes' ), 
							'value' => 1, 
							'desc' => esc_html__( 
								'Toggle whether or not to enable a back to top button on your pages.', 
								'dawnthemes' ) ) ) ), 
				
				'design_layout' => array( 
					'icon' => 'fa fa-columns', 
					'title' => esc_html__( 'Design and Layout', 'dawnthemes' ), 
					'desc' => __( '<p class="description">Customize Design and Layout.</p>', 'dawnthemes' ), 
					'fields' => array( 
						array( 
							'name' => 'body-bg', 
							'type' => 'color', 
							'label' => esc_html__( 'Body background', 'dawnthemes' ), 
							'value' => '' ) ) ), 
				'color_typography' => array( 
					'icon' => 'fa fa-font', 
					'title' => esc_html__( 'Color and Typography', 'dawnthemes' ), 
					'desc' => __( '<p class="description">Customize Color and Typography.</p>', 'dawnthemes' ), 
					'fields' => array( 
						array( 
							'name' => 'brand-primary', 
							'type' => 'color', 
							'label' => esc_html__( 'Brand primary', 'dawnthemes' ), 
							'value' => '#262626' ), 
						array( 
							'name' => 'brand-success', 
							'type' => 'color', 
							'label' => esc_html__( 'Brand success', 'dawnthemes' ), 
							'value' => '#57bb58' ), 
						array( 
							'name' => 'brand-info', 
							'type' => 'color', 
							'label' => esc_html__( 'Brand info', 'dawnthemes' ), 
							'value' => '#5788bb' ), 
						array( 
							'name' => 'brand-warning', 
							'type' => 'color', 
							'label' => esc_html__( 'Brand warning', 'dawnthemes' ), 
							'value' => '#f0ad4e' ), 
						array( 
							'name' => 'brand-danger', 
							'type' => 'color', 
							'label' => esc_html__( 'Brand danger', 'dawnthemes' ), 
							'value' => '#bb5857' ), 
						array( 
							'name' => 'text-color', 
							'type' => 'color', 
							'label' => esc_html__( 'Text color', 'dawnthemes' ), 
							'value' => '#525252' ), 
						array( 
							'name' => 'link-color', 
							'type' => 'color', 
							'label' => esc_html__( 'Link color', 'dawnthemes' ), 
							'value' => '#262626' ), 
						array( 
							'name' => 'link-hover-color', 
							'type' => 'color', 
							'label' => esc_html__( 'Link hover color', 'dawnthemes' ), 
							'value' => '#262626' ), 
						array( 
							'name' => 'headings-color', 
							'type' => 'color', 
							'label' => esc_html__( 'Headings Color', 'dawnthemes' ), 
							'value' => '#262626' ), 
						array( 
							'name' => 'body-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Body', 'dawnthemes' ), 
							'value' => array() ), 
						array( 
							'name' => 'navbar-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Navigation', 'dawnthemes' ), 
							'value' => array() ), 
						array( 
							'name' => 'h1-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Heading H1', 'dawnthemes' ), 
							'value' => array() ), 
						array( 
							'name' => 'h2-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Heading H2', 'dawnthemes' ), 
							'value' => array() ), 
						array( 
							'name' => 'h3-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Heading H3', 'dawnthemes' ), 
							'value' => array() ), 
						array( 
							'name' => 'h4-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Heading H4', 'dawnthemes' ), 
							'value' => array() ), 
						array( 
							'name' => 'h5-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Heading H5', 'dawnthemes' ), 
							'value' => array() ), 
						array( 
							'name' => 'h6-typography', 
							'type' => 'custom_font', 
							'field-label' => esc_html__( 'Heading H6', 'dawnthemes' ), 
							'value' => array() ) ) ), 
				'header' => array( 
					'icon' => 'fa fa-header', 
					'title' => esc_html__( 'Header', 'dawnthemes' ), 
					'desc' => __( '<p class="description">Customize Header.</p>', 'dawnthemes' ), 
					'fields' => array( 
						array( 
							'name' => 'header-style', 
							'type' => 'select', 
							'label' => esc_html__( 'Header Style', 'dawnthemes' ), 
							'desc' => esc_html__( 'Please select your header style here.', 'dawnthemes' ), 
							'options' => array( 
								'center' => esc_html__( 'Center', 'dawnthemes' ), 
								'below' => esc_html__( 'Below', 'dawnthemes' ) ), 
							'value' => 'below' ), 
						array( 
							'name' => 'topbar_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'Topbar Settings', 'dawnthemes' ) ), 
						array( 
							'name' => 'show-topbar', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Display top bar', 'dawnthemes' ), 
							'desc' => __( 
								'Enable or disable the top bar.<br> See Social icons tab to enable the social icons inside it.<br> Set a Top menu from  Appearance - Menus ', 
								'dawnthemes' ), 
							'value' => '0' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'left-topbar-content', 
							'type' => 'buttonset', 
							'dependency' => array( 'element' => 'show-topbar', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Left topbar content', 'dawnthemes' ), 
							'options' => array( 
								'none' => esc_html__( 'None', 'dawnthemes' ), 
								'menu_social' => esc_html__( 'Social', 'dawnthemes' ), 
								'info' => esc_html__( 'Site Info', 'dawnthemes' ), 
								'custom' => esc_html__( 'Custom HTML', 'dawnthemes' ) ), 
							'value' => 'info' ), 
						array( 
							'name' => 'left-topbar-social', 
							'type' => 'muitl-select', 
							'label' => esc_html__( 'Top Social Icon', 'dawnthemes' ), 
							'dependency' => array( 
								'element' => 'left-topbar-content', 
								'value' => array( 'menu_social' ) ), 
							'value' => array( 'facebook', 'twitter', 'google-plus', 'pinterest', 'rss', 'instagram' ), 
							'options' => array( 
								'facebook' => 'Facebook', 
								'twitter' => 'Twitter', 
								'google-plus' => 'Google Plus', 
								'pinterest' => 'Pinterest', 
								'linkedin' => 'Linkedin', 
								'rss' => 'Rss', 
								'instagram' => 'Instagram', 
								'github' => 'Github', 
								'behance' => 'Behance', 
								'stack-exchange' => 'Stack Exchange', 
								'tumblr' => 'Tumblr', 
								'soundcloud' => 'SoundCloud', 
								'dribbble' => 'Dribbble' ) ), 
						array( 
							'name' => 'left-topbar-phone', 
							'type' => 'text', 
							'dependency' => array( 'element' => 'left-topbar-content', 'value' => array( 'info' ) ), 
							'label' => esc_html__( 'Phone number', 'dawnthemes' ), 
							'value' => '(123) 456 789' ), 
						array( 
							'name' => 'left-topbar-email', 
							'type' => 'text', 
							'dependency' => array( 'element' => 'left-topbar-content', 'value' => array( 'info' ) ), 
							'label' => esc_html__( 'Email', 'dawnthemes' ), 
							'value' => 'info@domain.com' ), 
						array( 
							'name' => 'left-topbar-skype', 
							'type' => 'text', 
							'dependency' => array( 'element' => 'left-topbar-content', 'value' => array( 'info' ) ), 
							'label' => esc_html__( 'Skype', 'dawnthemes' ), 
							'value' => 'skype.name' ), 
						array( 
							'name' => 'left-topbar-custom-content', 
							'type' => 'textarea', 
							'dependency' => array( 'element' => 'left-topbar-content', 'value' => array( 'custom' ) ), 
							'label' => esc_html__( 'Left Topbar Content Custom HTML', 'dawnthemes' ) ), 
						
						array( 
							'name' => 'right-topbar-content', 
							'type' => 'buttonset', 
							'dependency' => array( 'element' => 'show-topbar', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Right topbar content', 'dawnthemes' ), 
							'options' => array( 
								'none' => esc_html__( 'None', 'dawnthemes' ), 
								'menu' => esc_html__( 'Navigation', 'dawnthemes' ), 
								'menu_social' => esc_html__( 'Social', 'dawnthemes' ), 
								'custom' => esc_html__( 'Custom HTML', 'dawnthemes' ) ), 
							'value' => 'menu' ), 
						array( 
							'name' => 'right-topbar-account', 
							'type' => 'switch', 
							'label' => esc_html__( 'Use Account Url', 'dawnthemes' ), 
							'dependency' => array( 'element' => 'right-topbar-content', 'value' => array( 'menu' ) ), 
							'desc' => esc_html__( 'Use account url in right topbar menu', 'dawnthemes' ), 
							'value' => '0' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'topbar-social', 
							'type' => 'muitl-select', 
							'label' => esc_html__( 'Top Social Icon', 'dawnthemes' ), 
							'dependency' => array( 
								'element' => 'right-topbar-content', 
								'value' => array( 'menu_social' ) ), 
							'value' => array( 'facebook', 'twitter', 'google-plus', 'pinterest', 'rss', 'instagram' ), 
							'options' => array( 
								'facebook' => 'Facebook', 
								'twitter' => 'Twitter', 
								'google-plus' => 'Google Plus', 
								'pinterest' => 'Pinterest', 
								'linkedin' => 'Linkedin', 
								'rss' => 'Rss', 
								'instagram' => 'Instagram', 
								'github' => 'Github', 
								'behance' => 'Behance', 
								'stack-exchange' => 'Stack Exchange', 
								'tumblr' => 'Tumblr', 
								'soundcloud' => 'SoundCloud', 
								'dribbble' => 'Dribbble' ) ), 
						array( 
							'name' => 'right-topbar-custom-content', 
							'type' => 'textarea', 
							'dependency' => array( 'element' => 'right-topbar-content', 'value' => array( 'custom' ) ), 
							'label' => esc_html__( 'Right Topbar Content Custom HTML', 'dawnthemes' ) ), 
						
						array( 
							'name' => 'main_navbar_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'Main Navbar Settings', 'dawnthemes' ) ), 
						array( 
							'name' => 'sticky-menu', 
							'type' => 'switch', 
							'label' => esc_html__( 'Sticky Top menu', 'dawnthemes' ), 
							'desc' => esc_html__( 'Enable or disable the sticky menu.', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'custom-sticky-color', 
							'type' => 'switch', 
							'label' => esc_html__( 'Custom Sticky Color', 'dawnthemes' ), 
							'dependency' => array( 'element' => 'sticky-menu', 'value' => array( '1' ) ), 
							'desc' => esc_html__( 'Custom sticky menu color scheme ?', 'dawnthemes' ), 
							'value' => '0' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'sticky-menu-bg', 
							'type' => 'color', 
							'label' => esc_html__( 'Sticky menu background', 'dawnthemes' ), 
							'dependency' => array( 'element' => 'custom-sticky-color', 'value' => array( '1' ) ), 
							'value' => '' ), 
						array( 
							'name' => 'sticky-menu-color', 
							'type' => 'color', 
							'label' => esc_html__( 'Sticky menu color', 'dawnthemes' ), 
							'dependency' => array( 'element' => 'custom-sticky-color', 'value' => array( '1' ) ), 
							'value' => '' ), 
						array( 
							'name' => 'sticky-menu-hover-color', 
							'type' => 'color', 
							'label' => esc_html__( 'Sticky menu hover color', 'dawnthemes' ), 
							'dependency' => array( 'element' => 'custom-sticky-color', 'value' => array( '1' ) ), 
							'value' => '' ), 
						array( 
							'name' => 'menu-transparent', 
							'type' => 'switch', 
							'label' => esc_html__( 'Transparent Main Menu', 'dawnthemes' ), 
							'desc' => esc_html__( 'Enable or disable main menu background transparency', 'dawnthemes' ), 
							'value' => '0' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'ajaxsearch', 
							'type' => 'switch', 
							'label' => esc_html__( 'Ajax Search in menu', 'dawnthemes' ), 
							'desc' => esc_html__( 'Enable or disable ajax search in menu.', 'dawnthemes' ), 
							'value' => '1' ),  // 1
							                                                                                                     // = checked
							                                                                                                     // | 0 =
							                                                                                                     // unchecked
						array( 
							'name' => 'heading-bg', 
							'type' => 'image', 
							'desc' => esc_html__( 'Change Heading background', 'dawnthemes' ), 
							'label' => esc_html__( 'Heading background', 'dawnthemes' ) ), 
						array( 
							'name' => 'breadcrumb', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Show breadcrumb', 'dawnthemes' ), 
							'desc' => esc_html__( 'Enable or disable the site path under the page title.', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'header_color_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'Header Color Scheme', 'dawnthemes' ) ), 
						array( 
							'name' => 'header-color', 
							'type' => 'select', 
							'label' => esc_html__( 'Header Color Scheme', 'dawnthemes' ), 
							'desc' => esc_html__( 'Custom Topbar and Main menu color scheme .', 'dawnthemes' ), 
							'options' => array( 
								'default' => esc_html__( 'Default', 'dawnthemes' ), 
								'custom' => esc_html__( 'Custom', 'dawnthemes' ) ), 
							'value' => 'default' ), 
						array( 
							'name' => 'header-custom-color', 
							'type' => 'list_color', 
							'dependency' => array( 'element' => 'header-color', 'value' => array( 'custom' ) ), 
							'options' => array( 
								'topbar-bg' => esc_html__( 'Topbar Background', 'dawnthemes' ), 
								'topbar-font' => esc_html__( 'Topbar Color', 'dawnthemes' ), 
								'topbar-link' => esc_html__( 'Topbar Link Color', 'dawnthemes' ), 
								'header-bg' => esc_html__( 'Header Background', 'dawnthemes' ), 
								'header-color' => esc_html__( 'Header Color', 'dawnthemes' ), 
								'header-hover-color' => esc_html__( 'Header Hover Color', 'dawnthemes' ), 
								'navbar-bg' => esc_html__( 'Navbar Background', 'dawnthemes' ), 
								'navbar-font' => esc_html__( 'Navbar Color', 'dawnthemes' ), 
								'navbar-font-hover' => esc_html__( 'Navbar Color Hover', 'dawnthemes' ), 
								'navbar-dd-bg' => esc_html__( 'Navbar Dropdown Background', 'dawnthemes' ), 
								'navbar-dd-hover-bg' => esc_html__( 'Navbar Dropdown Hover Background', 'dawnthemes' ), 
								'navbar-dd-font' => esc_html__( 'Navbar Dropdown Color', 'dawnthemes' ), 
								'navbar-dd-font-hover' => esc_html__( 'Navbar Dropdown Color Hover', 'dawnthemes' ), 
								'navbar-dd-mm-title' => esc_html__( 'Navbar Dropdown Mega Menu Title', 'dawnthemes' ) ) ) ) ), 
				'footer' => array( 
					'icon' => 'fa fa-list-alt', 
					'title' => esc_html__( 'Footer', 'dawnthemes' ), 
					'desc' => __( '<p class="description">Customize Footer.</p>', 'dawnthemes' ), 
					'fields' => array( 
						array( 
							'name' => 'footer-area', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Footer Widget Area', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Do you want use the main footer that contains all the widgets areas.', 
								'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'footer-area-columns', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Footer Area Columns', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Please select the number of columns you would like for your footer.', 
								'dawnthemes' ), 
							'dependency' => array( 'element' => 'footer-area', 'value' => array( '1' ) ), 
							'options' => array( 
								'2' => array( 'alt' => '2 Column', 'img' => DTINC_ASSETS_URL . '/images/2col.png' ), 
								'3' => array( 'alt' => '3 Column', 'img' => DTINC_ASSETS_URL . '/images/3col.png' ), 
								'4' => array( 'alt' => '4 Column', 'img' => DTINC_ASSETS_URL . '/images/4col.png' ), 
								'5' => array( 'alt' => '5 Column', 'img' => DTINC_ASSETS_URL . '/images/5col.png' ) ), 
							'value' => '5' ), 
						array( 
							'name' => 'footer-featured', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Footer Featured', 'dawnthemes' ), 
							'desc' => esc_html__( 'Do you want show featured in footer.', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'footer-featured-1', 
							'type' => 'textarea', 
							'dependency' => array( 'element' => 'footer-featured', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Footer Featured Column 1', 'dawnthemes' ), 
							'desc' => esc_html__( 'Footer featured column 1 text.', 'dawnthemes' ), 
							'value' => '<h4 class="footer-featured-title">FREE UK STANDARD DELIVERY</h4>' . "\n" .
								 'on order over $ 85 - use code ukfre75' ), 
						array( 
							'name' => 'footer-featured-2', 
							'type' => 'textarea', 
							'dependency' => array( 'element' => 'footer-featured', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Footer Featured Column 2', 'dawnthemes' ), 
							'desc' => esc_html__( 'Footer featured column 2 text.', 'dawnthemes' ), 
							'value' => '<h4 class="footer-featured-title">COLLECT FROM STORE</h4>' . "\n" .
							 '$2 next day delivery at over 250 store' ), 
						array( 
							'name' => 'footer-featured-3', 
							'type' => 'textarea', 
							'dependency' => array( 'element' => 'footer-featured', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Footer Featured Column 3', 'dawnthemes' ), 
							'desc' => esc_html__( 'Footer featured column 3 text.', 'dawnthemes' ), 
							'value' => '<h4 class="footer-featured-title">FREE INTERNATIONAL DELIVERY</h4>' . "\n" .
							 'onorder over $100 - use code free100' ), 
						array( 
							'name' => 'footer-logo', 
							'type' => 'image', 
							'value' => get_template_directory_uri() . '/assets/images/logo.png', 
							'label' => esc_html__( 'Footer Logo', 'dawnthemes' ), 
							'desc' => esc_html__( 'Upload your footer logo.', 'dawnthemes' ) ), 
						array( 
							'name' => 'footer-info', 
							'type' => 'textarea', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Footer Info', 'dawnthemes' ), 
							'value' => 'Copyright© 2014 the Phoenix  Store. All Rights Reserved.' . "\n" .
							 'Mobile: (00) 123 456 789' . "\n" .
							 'Email: <a href="maito:thephoenix@info.com">thephoenix@info.com</a>' ), 
						array( 
							'name' => 'footer-menu', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Footer Menu', 'dawnthemes' ), 
							'desc' => esc_html__( 'Do you want use menu in main footer.', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'footer-copyright', 
							'type' => 'text', 
							'label' => esc_html__( 'Footer Copyright Text', 'dawnthemes' ), 
							'desc' => esc_html__( 'Please enter the copyright section text.', 'dawnthemes' ), 
							'value' => 'Copyright 2015 - Powered by <a href="http://dawnthemes.com/">DawnThemes</a>' ), 
						array( 
							'name' => 'footer_color_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'Footer Color Scheme', 'dawnthemes' ) ), 
						array( 
							'name' => 'footer-color', 
							'type' => 'switch', 
							'label' => esc_html__( 'Custom Footer Color Scheme', 'dawnthemes' ), 
							'value' => '0' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'footer-custom-color', 
							'type' => 'list_color', 
							'dependency' => array( 'element' => 'footer-color', 'value' => array( '1' ) ), 
							'options' => array( 
								'footer-widget-bg' => esc_html__( 'Footer Widget Area Background', 'dawnthemes' ), 
								'footer-widget-color' => esc_html__( 'Footer Widget Area Color', 'dawnthemes' ), 
								'footer-widget-link' => esc_html__( 'Footer Widget Area Link', 'dawnthemes' ), 
								'footer-widget-link-hover' => esc_html__( 'Footer Widget Area Link Hover', 'dawnthemes' ), 
								'footer-bg' => esc_html__( 'Footer Copyright Background', 'dawnthemes' ), 
								'footer-color' => esc_html__( 'Footer Copyright Color', 'dawnthemes' ), 
								'footer-link' => esc_html__( 'Footer Copyright Link', 'dawnthemes' ), 
								'footer-link-hover' => esc_html__( 'Footer Copyright Link Hover', 'dawnthemes' ) ) ) ) ), 
				'blog' => array( 
					'icon' => 'fa fa-pencil', 
					'title' => esc_html__( 'Blog', 'dawnthemes' ), 
					'desc' => __( '<p class="description">Customize Blog.</p>', 'dawnthemes' ), 
					'fields' => array( 
						array( 
							'name' => 'list_blog_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'List Blog Settings', 'dawnthemes' ) ), 
						array( 
							'name' => 'blog-layout', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Main Blog Layout', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Select main blog layout. Choose between 1, 2 or 3 column layout.', 
								'dawnthemes' ), 
							'options' => array( 
								'full-width' => array( 
									'alt' => 'No sidebar', 
									'img' => DTINC_ASSETS_URL . '/images/1col.png' ), 
								'left-sidebar' => array( 
									'alt' => '2 Column Left', 
									'img' => DTINC_ASSETS_URL . '/images/2cl.png' ), 
								'right-sidebar' => array( 
									'alt' => '2 Column Right', 
									'img' => DTINC_ASSETS_URL . '/images/2cr.png' ) ), 
							'value' => 'right-sidebar' ), 
						array( 
							'name' => 'archive-layout', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Archive Layout', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Select Archive layout. Choose between 1, 2 or 3 column layout.', 
								'dawnthemes' ), 
							'options' => array( 
								'full-width' => array( 
									'alt' => 'No sidebar', 
									'img' => DTINC_ASSETS_URL . '/images/1col.png' ), 
								'left-sidebar' => array( 
									'alt' => '2 Column Left', 
									'img' => DTINC_ASSETS_URL . '/images/2cl.png' ), 
								'right-sidebar' => array( 
									'alt' => '2 Column Right', 
									'img' => DTINC_ASSETS_URL . '/images/2cr.png' ) ), 
							'value' => 'right-sidebar' ), 
						array( 
							'name' => 'blog_style', 
							'type' => 'select', 
							'label' => esc_html__( 'Style', 'dawnthemes' ), 
							'desc' => esc_html__( 'How your blog posts will display.', 'dawnthemes' ), 
							'options' => array( 
								'default' => esc_html__( 'Default', 'dawnthemes' ), 
								'grid' => esc_html__( 'Grid', 'dawnthemes' ),
								'classic' => esc_html__( 'Classic', 'dawnthemes' ),
								'masonry' => esc_html__( 'Masonry', 'dawnthemes' ),
								), 
							'value' => 'default' ), 
						array( 
							'name' => 'blog-columns', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Blogs Columns', 'dawnthemes' ), 
							'desc' => esc_html__( 'Select blogs columns.', 'dawnthemes' ), 
							'dependency' => array( 'element' => 'blog_style', 'value' => array( 'grid', 'masonry' ) ), 
							'options' => array( 
								'2' => array( 'alt' => '2 Column', 'img' => DTINC_ASSETS_URL . '/images/2col.png' ), 
								'3' => array( 'alt' => '3 Column', 'img' => DTINC_ASSETS_URL . '/images/3col.png' ), 
								'4' => array( 'alt' => '4 Column', 'img' => DTINC_ASSETS_URL . '/images/4col.png' ) ), 
							'value' => '3' ), 
						array( 
							'type' => 'select', 
							'label' => esc_html__( 'Pagination', 'dawnthemes' ), 
							'name' => 'blog-pagination', 
							'options' => array( 
								'wp_pagenavi' => esc_html__( 'WP PageNavi', 'dawnthemes' ), 
								'loadmore' => esc_html__( 'Ajax Load More', 'dawnthemes' ),
								'infinite_scroll' => esc_html__( 'Infinite Scrolling', 'dawnthemes' ),
							), 
							'value' => 'wp_pagenavi',
							'dependency' => array( 'element' => 'blog_style', 'value' => array( 'default', 'grid', 'masonry' ) ),
							'desc' => esc_html__( 'Choose pagination type.', 'dawnthemes' ) ), 
						array( 
							'type' => 'text', 
							'label' => esc_html__( 'Load More Button Text', 'dawnthemes' ), 
							'name' => 'blog-loadmore-text', 
							'dependency' => array( 'element' => "blog-pagination", 'value' => array( 'loadmore' ) ), 
							'value' => esc_html__( 'Load More', 'dawnthemes' ) ),
						array( 
							'name' => 'blog-excerpt-length', 
							'type' => 'text', 
							'label' => esc_html__( 'Excerpt Length', 'dawnthemes' ), 
							'dependency' => array( 
								'element' => "blog_style", 
								'value' => array( 'default', 'medium', 'grid', 'masonry' ) ), 
							'desc' => esc_html__( 'In Archive Blog. Enter the number words excerpt', 'dawnthemes' ), 
							'value' => 55 ), 
						array( 
							'name' => 'blog-show-date', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Date Meta', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Archive Blog. Show/Hide the date meta', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-comment', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Comment Meta', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Archive Blog. Show/Hide the comment meta', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-category', 
							'type' => 'switch', 
							'label' => esc_html__( 'Show/Hide Category', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Archive Blog. Show/Hide the category meta', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-author', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'dependency' => array( 
								'element' => "blog_style", 
								'value' => array( 'default', 'medium', 'grid', 'masonry' ) ), 
							'label' => esc_html__( 'Author Meta', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Archive Blog. Show/Hide the author meta', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-tag', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'dependency' => array( 
								'element' => "blog_style", 
								'value' => array( 'default', 'medium', 'grid', 'masonry' ) ), 
							'label' => esc_html__( 'Tags', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Archive Blog. If enabled it will show tag.', 'dawnthemes' ), 
							'value' => '0' ),  // 1 = checked | 0 = unchecked
						
						array( 
							'name' => 'blog_show_readmore', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'dependency' => array( 
								'element' => "blog_style", 
								'value' => array( 'default', 'medium', 'grid', 'masonry' ) ), 
							'label' => esc_html__( 'Show/Hide Readmore', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Archive Blog. Show/Hide the post readmore', 'dawnthemes' ), 
							'value' => '' ),  // 1 = checked | 0 = unchecked
						
						array( 
							'name' => 'single_blog_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'Single Blog Settings', 'dawnthemes' ) ), 
						array( 
							'name' => 'single-layout', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Single Blog Layout', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Select single content and sidebar alignment. Choose between 1, 2 or 3 column layout.', 
								'dawnthemes' ), 
							'options' => array( 
								'full-width' => array( 
									'alt' => 'No sidebar', 
									'img' => DTINC_ASSETS_URL . '/images/1col.png' ), 
								'left-sidebar' => array( 
									'alt' => '2 Column Left', 
									'img' => DTINC_ASSETS_URL . '/images/2cl.png' ), 
								'right-sidebar' => array( 
									'alt' => '2 Column Right', 
									'img' => DTINC_ASSETS_URL . '/images/2cr.png' ) ), 
							'value' => 'right-sidebar' ), 
						
						// as---
						array( 
							'name' => 'blog-show-date', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Date Meta', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Single Blog. Show/Hide the date meta', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-comment', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Comment Meta', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Single Blog. Show/Hide the comment meta', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-category', 
							'type' => 'switch', 
							'label' => esc_html__( 'Show/Hide Category', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Single Blog. Show/Hide the category', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-author', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Author Meta', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Single Blog. Show/Hide the author meta', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'blog-show-tag', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Show/Hide Tag', 'dawnthemes' ), 
							'desc' => esc_html__( 'In Single Blog. If enabled it will show tag.', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
							                 
						// as--
						array( 
							'name' => 'show-authorbio', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Show Author Bio', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Display the author bio at the bottom of post on single post page ?', 
								'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'show-postnav', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Show Next/Prev Post Link On Single Post Page', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Using this will add a link at the bottom of every post page that leads to the next/prev post.', 
								'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'show_related_posts', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Show Related Post On Single Post Page', 'dawnthemes' ), 
							'desc' => esc_html__( 'Display related post the bottom of posts?', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'show_post_share', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Show Sharing Button', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'Activate this to enable social sharing buttons on single post page.', 
								'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'sharing_facebook', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show_post_share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Facebook', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'sharing_twitter', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show_post_share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Twitter', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array(
							'name' => 'sharing_linkedIn',
							'type' => 'switch',
							'dependency' => array( 'element' => 'show_post_share', 'value' => array( '1' ) ),
							'label' => esc_html__( 'Share on LinkedIn', 'dawnthemes' ),
							'value' => '0' ), // 1 = checked | 0 = unchecked
						array(
							'name' => 'sharing_tumblr',
							'type' => 'switch',
							'dependency' => array( 'element' => 'show_post_share', 'value' => array( '1' ) ),
							'label' => esc_html__( 'Share on Tumblr', 'dawnthemes' ),
							'value' => '0' ), // 1 = checked | 0 = unchecked
						array( 
							'name' => 'sharing_google', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show_post_share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Google+', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'sharing_pinterest', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show_post_share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Pinterest', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'sharing_email', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show_post_share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Email', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						) 
 					)
				 );
			if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
				$section['woocommerce'] = array( 
					'icon' => 'fa fa-shopping-cart', 
					'title' => esc_html__( 'Woocommerce', 'dawnthemes' ), 
					'desc' => __( '<p class="description">Customize Woocommerce.</p>', 'dawnthemes' ), 
					'fields' => array( 
						array( 
							'name' => 'woo-cart-nav', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Cart In header', 'dawnthemes' ), 
							'desc' => esc_html__( 'This will show cat in header.', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'woo-cart-mobile', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'label' => esc_html__( 'Mobile Cart Icon', 'dawnthemes' ), 
							'desc' => esc_html__( 
								'This will show on mobile menu a shop icon with the number of cart items.', 
								'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'list_product_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'List Product Settings', 'dawnthemes' ) ), 
						array( 
							'name' => 'woo-shop-layout', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Shop Layout', 'dawnthemes' ), 
							'desc' => esc_html__( 'Select shop layout.', 'dawnthemes' ), 
							'options' => array( 
								'full-width' => array( 
									'alt' => 'No sidebar', 
									'img' => DTINC_ASSETS_URL . '/images/1col.png' ), 
								'left-sidebar' => array( 
									'alt' => '2 Column Left', 
									'img' => DTINC_ASSETS_URL . '/images/2cl.png' ), 
								'right-sidebar' => array( 
									'alt' => '2 Column Right', 
									'img' => DTINC_ASSETS_URL . '/images/2cr.png' ) ), 
							'value' => 'right-sidebar' ), 
						array( 
							'name' => 'woo-category-layout', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Product Category Layout', 'dawnthemes' ), 
							'desc' => esc_html__( 'Select product category layout.', 'dawnthemes' ), 
							'options' => array( 
								'full-width' => array( 
									'alt' => 'No sidebar', 
									'img' => DTINC_ASSETS_URL . '/images/1col.png' ), 
								'left-sidebar' => array( 
									'alt' => '2 Column Left', 
									'img' => DTINC_ASSETS_URL . '/images/2cl.png' ), 
								'right-sidebar' => array( 
									'alt' => '2 Column Right', 
									'img' => DTINC_ASSETS_URL . '/images/2cr.png' ) ), 
							'value' => 'right-sidebar' ), 
						array( 
							'name' => 'dt_woocommerce_view_mode', 
							'type' => 'buttonset', 
							'label' => esc_html__( 'Default View Mode', 'dawnthemes' ), 
							'desc' => esc_html__( 'Select default view mode', 'dawnthemes' ), 
							'value' => 'grid', 
							'options' => array( 
								'grid' => esc_html__( 'Grid', 'dawnthemes' ), 
								'list' => esc_html__( 'List', 'dawnthemes' ) ) ), 
						array( 
							'name' => 'woo-per-page', 
							'type' => 'text', 
							'value' => 12, 
							'label' => esc_html__( 'Number of Products per Page', 'dawnthemes' ), 
							'desc' => esc_html__( 'Enter the products of posts to display per page.', 'dawnthemes' ) ), 
						array( 
							'name' => 'single_product_setting', 
							'type' => 'heading', 
							'text' => esc_html__( 'Single Product Settings', 'dawnthemes' ) ), 
						array( 
							'name' => 'woo-product-layout', 
							'type' => 'image_select', 
							'label' => esc_html__( 'Single Product Layout', 'dawnthemes' ), 
							'desc' => esc_html__( 'Select single product layout.', 'dawnthemes' ), 
							'options' => array( 
								'full-width' => array( 
									'alt' => 'No sidebar', 
									'img' => DTINC_ASSETS_URL . '/images/1col.png' ), 
								'left-sidebar' => array( 
									'alt' => '2 Column Left', 
									'img' => DTINC_ASSETS_URL . '/images/2cl.png' ), 
								'right-sidebar' => array( 
									'alt' => '2 Column Right', 
									'img' => DTINC_ASSETS_URL . '/images/2cr.png' ) ), 
							'value' => 'right-sidebar' ), 
						array( 
							'name' => 'show-woo-share', 
							'type' => 'switch', 
							'label' => esc_html__( 'Show Sharing Button', 'dawnthemes' ), 
							'desc' => esc_html__( 'Activate this to enable social sharing buttons.', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'woo-fb-share', 
							'type' => 'switch', 
							'on' => esc_html__( 'Show', 'dawnthemes' ), 
							'off' => esc_html__( 'Hide', 'dawnthemes' ), 
							'dependency' => array( 'element' => 'show-woo-share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Facebook', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'woo-tw-share', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show-woo-share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Twitter', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'woo-go-share', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show-woo-share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Google+', 'dawnthemes' ), 
							'value' => '1' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'woo-pi-share', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show-woo-share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on Pinterest', 'dawnthemes' ), 
							'value' => '0' ),  // 1 = checked | 0 = unchecked
						array( 
							'name' => 'woo-li-share', 
							'type' => 'switch', 
							'dependency' => array( 'element' => 'show-woo-share', 'value' => array( '1' ) ), 
							'label' => esc_html__( 'Share on LinkedIn', 'dawnthemes' ), 
							'value' => '1' ) ) // 1 = checked | 0 = unchecked
 				);
			}
			
			$section['social'] = array( 
				'icon' => 'fa fa-twitter', 
				'title' => esc_html__( 'Social Profile', 'dawnthemes' ), 
				'desc' => __( 
					'<p class="description">Enter in your profile media locations here.<br><strong>Remember to include the "http://" in all URLs!</strong></p>', 
					'dawnthemes' ), 
				'fields' => array( 
					array( 'name' => 'facebook-url', 'type' => 'text', 'label' => esc_html__( 'Facebook URL', 'dawnthemes' ) ), 
					array( 'name' => 'twitter-url', 'type' => 'text', 'label' => esc_html__( 'Twitter URL', 'dawnthemes' ) ), 
					array( 
						'name' => 'google-plus-url', 
						'type' => 'text', 
						'label' => esc_html__( 'Google+ URL', 'dawnthemes' ) ), 
					array( 
						'name' => 'pinterest-url', 
						'type' => 'text', 
						'label' => esc_html__( 'Pinterest URL', 'dawnthemes' ) ), 
					array( 'name' => 'linkedin-url', 'type' => 'text', 'label' => esc_html__( 'LinkedIn URL', 'dawnthemes' ) ), 
					array( 'name' => 'rss-url', 'type' => 'text', 'label' => esc_html__( 'RSS URL', 'dawnthemes' ) ), 
					array( 
						'name' => 'instagram-url', 
						'type' => 'text', 
						'label' => esc_html__( 'Instagram URL', 'dawnthemes' ) ), 
					array( 'name' => 'github-url', 'type' => 'text', 'label' => esc_html__( 'GitHub URL', 'dawnthemes' ) ), 
					array( 'name' => 'behance-url', 'type' => 'text', 'label' => esc_html__( 'Behance URL', 'dawnthemes' ) ), 
					array( 
						'name' => 'stack-exchange-url', 
						'type' => 'text', 
						'label' => esc_html__( 'Stack Exchange URL', 'dawnthemes' ) ), 
					array( 'name' => 'tumblr-url', 'type' => 'text', 'label' => esc_html__( 'Tumblr URL', 'dawnthemes' ) ), 
					array( 
						'name' => 'soundcloud-url', 
						'type' => 'text', 
						'label' => esc_html__( 'SoundCloud URL', 'dawnthemes' ) ), 
					array( 'name' => 'dribbble-url', 'type' => 'text', 'label' => esc_html__( 'Dribbble URL', 'dawnthemes' ) ) ) );
			$section['import_export'] = array( 
				'icon' => 'fa fa-refresh', 
				'title' => esc_html__( 'Import and Export', 'dawnthemes' ), 
				'fields' => array( 
					array( 
						'name' => 'import', 
						'type' => 'import', 
						'field-label' => esc_html__( 
							'Input your backup file below and hit Import to restore your sites options from a backup.', 
							'dawnthemes' ) ), 
					array( 
						'name' => 'export', 
						'type' => 'export', 
						'field-label' => esc_html__( 
							'Here you can download your current option settings.You can use it to restore your settings on this site (or any other site).', 
							'dawnthemes' ) ) ) );
			$section['custom_code'] = array( 
				'icon' => 'fa fa-code', 
				'title' => esc_html__( 'Custom Code', 'dawnthemes' ), 
				'fields' => array( 
					array( 
						'name' => 'custom-css', 
						'type' => 'ace_editor', 
						'label' => esc_html__( 'Custom Style', 'dawnthemes' ), 
						'desc' => esc_html__( 'Place you custom style here', 'dawnthemes' ) ) ) )
			// array(
			// 'name' => 'custom-js',
			// 'type' => 'ace_editor',
			// 'label' => esc_html__('Custom Javascript','dawnthemes'),
			// 'desc'=>esc_html__('Place you custom javascript here','dawnthemes'),
			// ),
			;
			return apply_filters( 'dt_theme_option_sections', $section );
		}

		public function enqueue_scripts() {
			wp_enqueue_style( 'vendor-chosen' );
			wp_enqueue_style( 'vendor-font-awesome' );
			// wp_enqueue_style('vendor-jquery-ui-bootstrap');
			wp_enqueue_style( 'dt-theme-options', DTINC_ASSETS_URL . '/css/theme-options.css', null, DTINC_VERSION );
			wp_register_script( 
				'dt-theme-options', 
				DTINC_ASSETS_URL . '/js/theme-options.js', 
				array( 
					'jquery', 
					'underscore', 
					'jquery-ui-button', 
					'jquery-ui-tooltip', 
					'vendor-chosen', 
					'vendor-ace-editor' ), 
				DTINC_VERSION, 
				true );
			$dtthemeoptionsL10n = array( 'reset_msg' => esc_js( 'You want reset all options ?', 'dawnthemes' ) );
			wp_localize_script( 'dt-theme-options', 'dtthemeoptionsL10n', $dtthemeoptionsL10n );
			wp_enqueue_script( 'dt-theme-options' );
		}

		public function admin_menu() {
			$option_page = add_theme_page( 
				esc_html__( 'Theme Options', 'dawnthemes' ), 
				esc_html__( 'Theme Options', 'dawnthemes' ), 
				'edit_theme_options', 
				'theme-options', 
				array( &$this, 'option_page' ) );
			
			// Add framework functionaily to the head individually
			// add_action("admin_print_scripts-$option_page", array(&$this,'enqueue_scripts'));
			add_action( "admin_print_styles-$option_page", array( &$this, 'enqueue_scripts' ) );
		}

		public function admin_bar_render() {
			global $wp_admin_bar;
			$wp_admin_bar->add_menu( 
				array( 'parent' => 'site-name', // use 'false' for a root menu, or pass the ID of the parent menu
						'id' => 'dt_theme_options', // link ID, defaults to a sanitized title value
						'title' => esc_html__( 'Theme Options', 'dawnthemes' ), // link title
						'href' => admin_url( 'themes.php?page=theme-option' ), // name of file
						'meta' => false ) ) // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' =>
				         // '', target => '', title => '' );
			;
		}

		public function download_theme_option() {
			if ( ! isset( $_GET['secret'] ) || $_GET['secret'] != md5( AUTH_KEY . SECURE_AUTH_KEY ) ) {
				wp_die( 'Invalid Secret for options use' );
				exit();
			}
			$options = get_option( self::$_option_name );
			$content = json_encode( $options );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/txt' );
			header( 
				'Content-Disposition: attachment; filename="' . self::$_option_name . '_backup_' . date( 'd-m-Y' ) .
					 '.json"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			echo $content;
			exit();
		}
	}
	new DTThemeOptions();


endif;