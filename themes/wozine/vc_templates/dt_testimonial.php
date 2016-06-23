<?php
$output = '';
extract(shortcode_atts(array(
	'background_transparent'=>'',
	'color'					=>'',
	'style'                 =>'style-1',
	'fx'					=>'scroll',
	'visibility'			=>'',
	'el_class'				=>'',
), $atts));
$el_class  = !empty($el_class) ? ' '.esc_attr( $el_class ) : '';
$el_class .= dh_visibility_class($visibility);
$el_class .= $style;
/**
 * script
 * {{
 */
wp_enqueue_script('vendor-carouFredSel');
$color = dh_format_color($color);
$output .='<div class="testimonial '.(!empty($background_transparent)?' bg-transparent':'').$el_class.'">';
$output .='<div class="caroufredsel" data-visible-min="1" data-visible-max="2" data-scroll-fx="'.$fx.'"  data-speed="5000" data-responsive="1" data-infinite="1" data-autoplay="0">';
$output .='<div class="caroufredsel-wrap">';
$output .='<ul class="caroufredsel-items"'.(!empty($color) ? ' style="color:'.$color.'"':'').'>';
$output .= wpb_js_remove_wpautop( $content );
$output .='</ul>';
$output .='<a href="#" class="caroufredsel-prev hide"></a>';
$output .='<a href="#" class="caroufredsel-next hide"></a>';
$output .='</div>';
$output .='</div>';
$output .='</div>';
echo $output;
