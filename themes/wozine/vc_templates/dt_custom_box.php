<?php
$output = '';
$icon = '';
extract( shortcode_atts( array(
	'icon_type' 			=> 'fontawesome',
	'icon_fontawesome' 		=> 'fa fa-adjust',
	'icon_openiconic' 		=> '',
	'icon_typicons' 		=> '',
	'icon_entypo' 			=> '',
	'icon_linecons' 		=> 'vc_li vc_li-heart',
	'icon_color'			=> '#73bb67',
	'icon_font_size'		=>'32',
	'link' 					=> '',
	'target'				=>'_self',
	'title' 				=> esc_html__("Your Title Here ...",'wozine'),
	'text_align' 			=> '',
	'desc'					=> '',
	'visibility'			=>'',
	'el_class'				=>'',
), $atts ) );

switch ($icon_type){
	case 'openiconic':
		$icon = $icon_openiconic;
		break;
	case 'typicons':
		$icon = $icon_typicons;
		break;
	case 'entypo':
		$icon = $icon_entypo;
		break;
	case 'linecons':
		$icon = $icon_linecons;
		break;
	default: //'fontawesome':
		$icon = $icon_fontawesome;
		break;
}

vc_icon_element_fonts_enqueue( $icon_type );

$el_class  = !empty($el_class) ? ' '.esc_attr( $el_class ) : '';
$el_class .= dt_visibility_class($visibility);

if ( $target == 'same' || $target == '_self' ) {
	$target = '';
}
$target = ( $target != '' ) ? ' target="' . $target . '"' : '';
$inline_style = '';
$inline_style .= (!empty($icon_color) ? 'color: '.$icon_color.';' : '');
$inline_style .= (!empty($icon_font_size) ? 'font-size: '.$icon_font_size. 'px;' : '');



$output .='<div class="dt-custom-box feature-item '.$el_class.'" '.(!empty($text_align) ? ' style="text-align:'.$text_align.'"' : '').'>';
$output .='<a href="'.esc_url($link).'" '.$target.' class="dt-custom-link"></a>';
$output .='<i class="'.esc_attr($icon).' icon" '.(!empty($inline_style) ? ' style="'.$inline_style.'"' : '' ).'></i>';
$output .='<h3 class="feature-item-title">'.esc_attr( $title ).'</h3>';
$output .='<div class="feature-item-desc">'.esc_html( $desc ).'</div>';
$output .='</div>';

echo $output."\n";