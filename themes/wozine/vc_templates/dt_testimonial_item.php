<?php
$output = '';
extract(shortcode_atts(array(
	'text'=>'I am testimonial. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
	'author'=>'',
	'company'=>'',
	'avatar'=>'',
), $atts));

$output .='<li class="caroufredsel-item col-sm-6">';
$output .='<div class="testimonial-wrap">';
$output .='<div class="testimonial-text">';
$output .= '<span>&ldquo;</span>'.trim( vc_value_from_safe( $text ) ).'<span>&rdquo;</span>';
$output .='</div>';
$output .='<div class="clearfix">';
if(!empty($avatar)){
	$avatar_image = wp_get_attachment_image_src($avatar,'thumbnail');
	$output .='<div class="testimonial-avatar">';
	$output .= '<img src="'.$avatar_image[0].'" alt="'.$avatar.'"/>'; 
	// $output .='<a href="#" class="caroufredsel-prev"></a>';
	// $output .='<a href="#" class="caroufredsel-next"></a>';
	$output .="</div>";
}
if(!empty($author)){
	$output .='<span class="testimonial-author">';
	$output .=esc_html($author);
	$output .='</span>';
}
if(!empty($company)){
	$output .='<span class="testimonial-company">';
	$output .=esc_html($company);
	$output .='</span>';
}
$output .='</div>';
$output .='</div>';
$output .='</li>';
echo $output;
