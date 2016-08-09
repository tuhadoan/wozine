<?php
/**
 * @package DawnThemes
 */
class DawnThemes_Mega_Menu_Content_Helper{
	/*
	 * Get 3 Latest posts in custom category (taxonomy)
	 *
	 * $post_type: post type to return
	 * $tax: type of custom taxonomy
	 * $cat_id: custom taxonomy ID
	 * Return HTML
	 */
	function get_latest_custom_category_items($cat_id, $tax, $post_type = 'any'){

		$term = get_term_by('id',$cat_id,$tax);
		if($term === false){
			return;
		}

		$args = array('posts_per_page'=>3,'post_type'=>$post_type,$tax=>$term->slug);

		$query = new WP_Query($args);

		$html = '';

		ob_start();

		$tmp_post = $post;
		$options = get_option('megamenu_options');
		$sizes = $options['thumbnail_size'];
		$width = 200;$height = 200;

		if($sizes != '') {
			$sizes = explode('x',$sizes);
			if(count($sizes) == 2){
				$width = intval($sizes[0]);
				$height = intval($sizes[1]);
				if($width == 0) $width = 200;
				if($height == 0) $height = 200;
			}
		}

		while($query->have_posts()) : $query->the_post();

		?>
		<div class="grid-item">
			<?php $options['image_link'] = 'on'; if($options['image_link'] == 'on'){?>
			<a href="<?php the_permalink(); ?>" title="<?php the_title();?>">
				<?php the_post_thumbnail(array($width,$height));?>
			</a>
			<?php } else {?>
			<?php the_post_thumbnail(array($width,$height));?>
			<?php }?>
			<h3 class="title"><a href="<?php the_permalink(); ?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
		</div>
		<?php
		endwhile;

		$html = ob_get_contents();
		ob_end_clean();

		wp_reset_postdata();

		$post = $temp_post;

		return $html;
	}

	/*
	 * Get 3 Latest posts in category
	 *
	 * Return HTML
	 */
	function get_latest_category_items($cat_id, $post_type = 'post'){
		$args = array('posts_per_page'=>3,'category'=>$cat_id,'post_type'=>$post_type);

		$posts = get_posts($args);
		$html = '';

		ob_start();

		global $post;
		$tmp_post = $post;
		$options = get_option('megamenu_options');
		$sizes = $options['thumbnail_size'];
		$width = 520;$height = 354;

		if($sizes != '') {
			$sizes = explode('x',$sizes);
			if(count($sizes) == 2){
				$width = intval($sizes[0]);
				$height = intval($sizes[1]);
				if($width == 0) $width = 200;
				if($height == 0) $height = 200;
			}
		}

		foreach($posts as $post) : setup_postdata($post);
		?>
		<div class="grid-item col-md-4">
			<div class="grid-item-post">
				<div class="entry-item-wrap">
					<div class="img-wrap">
						<a href="<?php the_permalink(); ?>" class="image" title="<?php the_title();?>">
							
							<?php the_post_thumbnail( apply_filters( 'wozine-megamenu-thumbnail', 'wozine-megamenu-preview-thumbnail' ) );?>
						</a>
					</div>
				</div>
					<h3 class="title"><a href="<?php the_permalink(); ?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
			</div>
		</div>
		<?php
		endforeach;
		$html = ob_get_contents();
		ob_end_clean();

		$temp_post='';
		$post = $temp_post;

		return $html;
	}

	/*
	 * Get 3 Latest WooCommerce/JigoShop Products in category
	 *
	 * Return HTML
	 */
	function get_woo_product_items($cat_id){
		$html = '';

		// get slug by ID
		$term = get_term_by('id',$cat_id,'product_cat');
		if($term){
			$args = array('posts_per_page'=>3,'product_cat'=>$term->slug,'post_type'=>'product');
			$posts = get_posts($args);
			ob_start();
			global $post;
			$tmp_post = $post;
			$options = get_option('megamenu_options');

			$sizes = $options['thumbnail_size'];
			$width = 200;$height = 200;
			if($sizes != '') {
				$sizes = explode('x',$sizes);
				if(count($sizes) == 2){
					$width = intval($sizes[0]);
					$height = intval($sizes[1]);
					if($width == 0) $width = 200;
					if($height == 0) $height = 200;
				}
			}

			foreach($posts as $post) : setup_postdata($post);

				//$product = WC_Product($post->ID);
				if (class_exists('WC_Product')) {
					// WooCommerce Installed
					global $product;
				} else if(class_exists('jigoshop_product')){
					$product = new jigoshop_product( $post->ID ); // JigoShop
				}
			?>
			<div class="grid-item">
				<?php $options['image_link'] = 'on'; if($options['image_link'] == 'on'){?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title();?>">
					<?php the_post_thumbnail(array($width,$height));?>
				</a>
				<?php } else {?>
				<?php the_post_thumbnail(array($width,$height));?>
				<?php }?>
				<h3 class="title"><a href="<?php the_permalink(); ?>" title="<?php the_title();?>"><?php if ( ($options['show_price'] == 'left') && $price_html = $product->get_price_html() ) { echo $price_html; } ?> <?php the_title();?> <?php if ( (!isset($options['show_price']) || $options['show_price'] == '') && $price_html = $product->get_price_html() ) { echo $price_html; } ?></a></h3>
			</div>
			<?php
			endforeach;
			$html = ob_get_contents();
			ob_end_clean();

			$post = $temp_post;
		}
		return $html;
	}

	/*
	 * Get page content
	 *
	 * Return HTML
	 */
	function get_page_content($page_id){
		$page = get_page($page_id);

		$html = '';
		if($page){
			ob_start();
			?>
			<div class="page-item">
				<h3 class="title"><a href="<?php echo get_permalink($page->ID); ?>" title="<?php echo esc_attr($page->post_title);?>"><?php echo apply_filters('the_title', $page->post_title);?></a></h3>
				<?php
					$morepos = strpos($page->post_content,'<!--more-->');
					if($morepos === false){
						echo apply_filters('the_content',$page->post_content);
					} else {
						echo apply_filters('the_content',substr($page->post_content,0,$morepos));
					}
				?>
			</div>
			<?php
		}

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/*
	 * Get post content
	 *
	 * Return HTML
	 */
	function get_post_content($post_id){
		$page = get_post($post_id);

		$html = '';

		$options = get_option('megamenu_options');
		$sizes = $options['thumbnail_size'];

		$width = 200;$height = 200;
		if($sizes != '') {
			$sizes = explode('x',$sizes);
			if(count($sizes) == 2){
				$width = intval($sizes[0]);
				$height = intval($sizes[1]);
				if($width == 0) $width = 200;
				if($height == 0) $height = 200;
			}
		}

		if($page){
			ob_start();
			?>
			<div class="page-item">
				<h3 class="title"><a href="<?php echo get_permalink($page->ID); ?>" title="<?php echo esc_attr($page->post_title);?>"><?php echo apply_filters('the_title', $page->post_title);?></a></h3>
				<div>
					<div class="thumb">
					<?php echo get_the_post_thumbnail( $page->ID, array($width,$height));?>
					</div>
				<?php
					$morepos = strpos($page->post_content,'<!--more-->');
					if($morepos === false){
						echo apply_filters('the_content',$page->post_content);
					} else {
						echo apply_filters('the_content',substr($page->post_content,0,$morepos));
					}
				?>
				</div>
			</div>
			<?php
		}

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/*
	 * Get 3 Latest posts that has tag id
	 *
	 * Return HTML
	 */
	function get_latest_items_by_tag($tag_id, $post_type = 'post'){
		$tag = get_term($tag_id,'post_tag');
		$args = array('showposts'=>3,'tag'=>$tag->slug,'caller_get_posts'=>1,'post_status'=>'publish','post_type'=>$post_type);
		$query = new WP_Query($args);

		$html = '';

		ob_start();
		$options = get_option('megamenu_options');

		$sizes = $options['thumbnail_size'];
		$width = 200;$height = 200;
		if($sizes != '') {
			$sizes = explode('x',$sizes);
			if(count($sizes) == 2){
				$width = intval($sizes[0]);
				$height = intval($sizes[1]);
				if($width == 0) $width = 200;
				if($height == 0) $height = 200;
			}
		}

		while($query->have_posts()) : $query->the_post();
		?>
		<div class="grid-item">
			<?php if($options['image_link'] == 'on'){?>
			<a href="<?php the_permalink(); ?>" title="<?php the_title();?>">
				<?php the_post_thumbnail(array($width,$height));?>
			</a>
			<?php } else {?>
			<?php the_post_thumbnail(array($width,$height));?>
			<?php }?>
			<h3 class="title"><a href="<?php the_permalink(); ?>" title="<?php the_title();?>"><?php the_title();?></a></h3>
		</div>
		<?php
		endwhile;
		$html = ob_get_contents();
		ob_end_clean();

		$post = $temp_post;
		wp_reset_query();
		return $html;
	}
}