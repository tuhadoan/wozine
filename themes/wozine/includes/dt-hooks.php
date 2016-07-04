<?php
add_action('wozine_breadcrumbs', 'dt_breadcrumbs', 10);

// change the width of an automatic WordPress embed
add_filter( 'dt_embed_defaults', 'dt_embed_size', 10, 2);

add_action('wp_ajax_dt_nav_content', 'dt_nav_content');
add_action('wp_ajax_nopriv_dt_nav_content', 'dt_nav_content');
function dt_nav_content(){
	$category			= $_POST['cat'];
	$orderby			= $_POST['orderby'];
	$order				= $_POST['order'];
	$hover_thumbnail	= $_POST['hover_thumbnail'];
	$offset				= $_POST['offset'];
	$paged				= $_POST['current_page'];
	$posts_per_page		= $_POST['posts_per_page'];
	$template			= isset($_POST['template']) ? $_POST['template'] : 'ajax_nav';

	$orderby    		= sanitize_title( $orderby );
	$order       		= sanitize_title( $order );

	$query_args = array(
		'posts_per_page' 	=> $posts_per_page,
		'post_status' 	 	=> 'publish',
		'post_type' 	 	=> 'post',
		'offset'            => $offset,
		'orderby'          	=> $orderby == '' ? 'date' : $orderby,
		'order'          	=> $order == 'asc' ? 'ASC' : 'DESC',
		'paged'				=> $paged,
	);

	$query_args['tax_query'][] =
	array(
		'taxonomy'			=> 'category',
		'field'				=> 'slug',
		'terms'				=> $category,
		'operator'			=> 'IN'
	);

	$p = new WP_Query( $query_args  );

	switch ($template){
		case 'ajax_nav':
			while ( $p->have_posts() ) : $p->the_post(); $limit = $p->found_posts;
			?>
						
				<?php
				if($offset + $posts_per_page >= $limit){
		    		// there are no more product
		    		// print a flag to detect
		    		echo '<div id="dt-ajax-no-p" class=""><!-- --></div>';
		    	}
	    	endwhile;
	    break;
		case 'post-category':
				$i = 0;
				while ( $p->have_posts() ) : $p->the_post(); $limit = $p->found_posts; global $post;
					$i++;
					dt_get_template('tpl-post-category.php',
						array(
							'post' => $post,
							'i' => $i,
						),
						'vc_templates/tpl', 'vc_templates/tpl'
					);
					
					if($offset + $posts_per_page >= $limit){
						// there are no more product
						// print a flag to detect
						echo '<div id="dt-ajax-no-p" class=""><!-- --></div>';
					}
		    	endwhile;
			break;
		default: break;
	}
	wp_reset_postdata();
	wp_die();
}