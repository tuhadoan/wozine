<?php
global $post, $wp_query;
$category = get_the_category();

// Settings
$separator  = '<i class="fa fa-angle-right" aria-hidden="true"></i>';
$id         = 'breadcrumbs';
$class      = 'breadcrumbs';
$home_title = esc_html__('Home', 'wozine');

$breadcrumbs_title = '';
if(is_home()){
	$breadcrumbs_title = esc_html__('blog', 'wozine');

}elseif(class_exists('WooCommerce') &&is_shop()){
	$breadcrumbs_title = esc_html__('shop', 'wozine');
}elseif( class_exists('WooCommerce') && is_woocommerce() ){

	if(is_product()){
		$product_cats = wp_get_post_terms( get_the_ID(), 'product_cat' );
		$single_cat = array_shift( $product_cats );
		$breadcrumbs_title = $single_cat->name;
	}else{
		$breadcrumbs_title = get_cat_name($wp_query->get_queried_object()->term_id);
	}

}elseif (is_single() && isset($category[0])){
	$breadcrumbs_title = $category[0]->cat_name;

}else if ( is_category() ) {
	$breadcrumbs_title = $category[0]->cat_name;

}else if ( is_page() ) {
	$breadcrumbs_title = get_the_title();

}else if ( is_tag() ) {
	// Tag page
	// Get tag information
	$term_id = get_query_var('tag_id');
	$taxonomy = 'post_tag';
	$args ='include=' . $term_id;
	$terms = get_terms( $taxonomy, $args );

	$breadcrumbs_title = $terms[0]->name;
} else if ( is_author() ) {
	// Get the author information
	global $author;
	$userdata = get_userdata( $author );
	$breadcrumbs_title = $userdata->display_name;
}

// Build the breadcrums
echo '<div id="' . $id . '" class="' . $class . '">';
    // Home page
    echo '<a class="home" href="' . esc_url( home_url('/') ) . '" title="' . $home_title . '">'.$home_title.'</a>';
    echo '<span class="navigation-pipe">' . $separator . '</span>';
    
    if ( class_exists('WooCommerce') && is_woocommerce() ) {
        $args = '';
        $args = wp_parse_args( $args, apply_filters( 'woocommerce_breadcrumb_defaults', array(
            'delimiter'   => '<span class="navigation-pipe">' . $separator . '</span>',
            'wrap_before' => '',
            'wrap_after'  => '',
            'before'      => '',
            'after'       => '',
            'home'        => ''
        ) ) );

        $breadcrumbs = new WC_Breadcrumb();

        if ( $args['home'] ) {
            $breadcrumbs->add_crumb( $args['home'], apply_filters( 'woocommerce_breadcrumb_home_url', esc_url( home_url('/') ) ) );
        }
		
        $args['breadcrumb'] = $breadcrumbs->generate();
		
       wc_get_template( 'global/breadcrumb.php', $args );
        
    }
    elseif ( is_single() && isset($category[0])) {
        // Single post (Only display the first category)
        echo '<a class="bread-cat bread-cat-' . $category[0]->term_id . ' bread-cat-' . $category[0]->category_nicename . '" href="' . esc_url( get_category_link($category[0]->term_id ) ) . '" title="' . $category[0]->cat_name . '">' . $category[0]->cat_name . '</a>';
        echo '<span class="navigation-pipe">' . $separator . '</span>';
        echo '<span class="item-current item-' . $post->ID . '">' . get_the_title() . '</span>';
    } else if ( is_category() ) {
        // Category page
        echo '<span class="bread-current bread-cat-' . $category[0]->term_id . ' bread-cat-' . $category[0]->category_nicename . '">' . $category[0]->cat_name . '</span>';
    } else if ( is_page() ) {
        // Standard page
        if( $post->post_parent ){
            $parents = '';
            // If child page, get parents 
            $anc = get_post_ancestors( $post->ID );
            // Get parents in the right order
            $anc = array_reverse($anc);
            // Parent page loop
            foreach ( $anc as $ancestor ) {
                $parents .= '<a class="bread-parent bread-parent-' . $ancestor . '" href="' . esc_url( get_permalink($ancestor) ) . '" title="' . get_the_title($ancestor) . '">' . get_the_title($ancestor) . '</a>';
                $parents .= '<span class="navigation-pipe">' . $separator . '</span>';
            }
            // Display parent pages
            echo $parents;
            // Current page
            echo '<span title="' . get_the_title() . '"> ' . get_the_title() . '</span>';
        } else {
            // Just display current page if not parents
            echo '<span class="bread-current bread-' . $post->ID . '"> ' . get_the_title() . '</span>'; 
        }
    } else if ( is_tag() ) {
        // Tag page
        // Get tag information
        $term_id = get_query_var('tag_id');
        $taxonomy = 'post_tag';
        $args ='include=' . $term_id;
        $terms = get_terms( $taxonomy, $args );
        // Display the tag name
        echo '<span class="bread-current bread-tag-' . $terms[0]->term_id . ' bread-tag-' . $terms[0]->slug . '">' . $terms[0]->name . '</span>';
    } elseif ( is_day() ) {
        // Day archive
        // Year link
        echo '<a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . esc_url( get_year_link( get_the_time('Y') ) ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a>';
        echo '<span class="navigation-pipe">' . $separator . ' </span>';
        // Month link
        echo '<a class="bread-month bread-month-' . get_the_time('m') . '" href="' . esc_url( get_month_link( get_the_time('Y'), get_the_time('m') ) ) . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</a>';
        echo '<span class="navigation-pipe">' . $separator . '';
        // Day display
        echo '<span class="bread-current bread-' . get_the_time('j') . '"> ' . get_the_time('jS') . ' ' . get_the_time('M') . ' Archives</span>';
    } else if ( is_month() ) {
        // Month Archive
        // Year link
        echo '<a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . esc_url( get_year_link( get_the_time('Y') ) ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a>';
        echo '<span class="navigation-pipe">' . $separator . '</span>';
        // Month display
        echo '<span class="bread-month bread-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</span>';
    } else if ( is_year() ) {
        // Display year archive
        echo '<span class="bread-current bread-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</span>';
    } else if ( is_author() ) {
        // Auhor archive
        // Get the author information
        global $author;
        $userdata = get_userdata( $author );
        // Display author name
        echo '<span class="bread-current bread-current-' . $userdata->user_nicename . '" title="' . $userdata->display_name . '">' . 'Author: ' . $userdata->display_name . '</span>';
    } else if ( get_query_var('paged') ) {
        // Paginated archives
        echo '<span class="bread-current bread-current-' . get_query_var('paged') . '" title="Page ' . get_query_var('paged') . '">'.esc_html__('Page', 'wozine') . ' ' . get_query_var('paged') . '</span>';
    } else if ( is_search() ) {
        // Search results page
        echo '<span class="bread-current bread-current-' . get_search_query() . '" title="Search results for: ' . get_search_query() . '">Search results for: ' . get_search_query() . '</span>';
    } elseif ( is_404() ) {
        // 404 page
        echo '<span>' . 'Error 404' . '</span>';
    }else{
    	echo $breadcrumbs_title;
    }
echo '</div>';

?>