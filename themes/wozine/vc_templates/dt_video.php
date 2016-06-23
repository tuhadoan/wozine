<?php
$output = '';
extract(shortcode_atts(array(
	'type'					=>'inline',
	'background'			=>'',
	'video_embed'			=>'',
), $atts));
if(!empty($video_embed)){
	
	$video_id = uniqid('video-featured-');
	$video = '';
	$video .= '<div class="video-embed-shortcode'.($type == 'popup'?' mfp-hide ':'').'">';
	$video .= '<div id="'.esc_attr($video_id).'" class="embed-wrap">';
	$video .= apply_filters('dh_embed_video', $video_embed);
	$video .= '</div>';
	$video .= '</div>';
	if($type == 'inline'){
		echo ($video);
	}elseif($type == 'popup'){
		/**
		 * script
		 * {{
		 */
		wp_enqueue_style('vendor-magnific-popup');
		wp_enqueue_script('vendor-magnific-popup');
		
		$background_url = '';
		if(!empty($background)){
			$background_url =wp_get_attachment_url($background);
		}
		if(empty($background_url))
			$background_url = dh_placeholder_img_src();
		
		$background_image = '<img class="video-embed-shortcode-bg" src="'.esc_attr($background_url).'" alt=""/>';
		
		echo '<div class="video-embed-shortcode">';
		echo $background_image;
		echo ($video);
		echo '<a class="video-embed-action" data-video-inline="'.esc_attr($video).'" href="#'.esc_attr($video_id).'" data-rel="magnific-popup-video"><i class="elegant_arrow_triangle-right_alt2"></i></a>';
		echo '</div>';
	}
}