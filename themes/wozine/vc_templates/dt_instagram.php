<?php
extract( shortcode_atts( array(
	'username'			=> '',
	'images_number'		=> '12',
	'refresh_hour'		=> '5',
	'visibility'        => '',
	'el_class'             => '',
), $atts ) );
/**
 * script
 * {{
 */
$el_class  = !empty($el_class) ? ' '.esc_attr( $el_class ) : '';
$el_class .= dt_visibility_class($visibility);
$username = strtolower($username);
ob_start();
?>
<div class="instagram">
	<div class="instagram-wrap">
		<?php ;
		$images_data = dt_instagram($username,$images_number, $refresh_hour);

		if ( !is_wp_error($images_data) && ! empty( $images_data ) ) {
			?>
			<ul class="dt-instagram__list">
				<?php foreach ((array)$images_data as $item):?>
				<li class="dt-instagram__item">
					<a href="<?php echo esc_attr( $item['link'])?>" title="<?php echo esc_attr($item['description'])?>" target="_blank">
						<img src="<?php echo esc_attr($item['thumbnail'])?>"  alt="<?php echo esc_attr($item['description'])?>"/>
					</a>
				</li>
				<?php endforeach;?>
			</ul>
			<?php
		} else {
			echo '<div class="text-center" style="margin-bottom:30px">';
			if(is_wp_error($images_data)){
				echo implode($images_data->get_error_messages());
			}else{
				echo esc_html__( 'Instagram did not return any images.', 'wozine' );
			}
			echo '</div>';
		};
		?>
	</div>
</div>
<?php
echo ob_get_clean();