<?php
class DT_Widget extends WP_Widget {
	public $widget_cssclass;
	public $widget_description;
	public $widget_id;
	public $widget_name;
	public $settings;
	public $cached = true;
	/**
	 * Constructor
	 */
	public function __construct() {
	
		$widget_ops = array(
				'classname'   => $this->widget_cssclass,
				'description' => $this->widget_description
		);
		
		parent::__construct(
			$this->widget_id,
			$this->widget_name,
			$widget_ops
		);
		
		if($this->cached){
			add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		}
	}
	
	/**
	 * get_cached_widget function.
	 */
	function get_cached_widget( $args ) {
	
		$cache = wp_cache_get( apply_filters( 'dt_cached_widget_id', $this->widget_id ), 'widget' );
	
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}
	
		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return true;
		}
	
		return false;
	}
	
	/**
	 * Cache the widget
	 * @param string $content
	 */
	public function cache_widget( $args, $content ) {
		$cache[ $args['widget_id'] ] = $content;
	
		wp_cache_set( apply_filters( 'dt_cached_widget_id', $this->widget_id ), $cache, 'widget' );
	}
	
	/**
	 * Flush the cache
	 *
	 * @return void
	 */
	public function flush_widget_cache() {
		wp_cache_delete( apply_filters( 'dt_cached_widget_id', $this->widget_id ), 'widget' );
	}
	
	/**
	 * Output the html at the start of a widget
	 *
	 * @param  array $args
	 * @return string
	 */
	public function widget_start( $args, $instance ) {
		echo $args['before_widget'];
	
		if ( $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
	}
	
	/**
	 * Output the html at the end of a widget
	 *
	 * @param  array $args
	 * @return string
	 */
	public function widget_end( $args ) {
		echo $args['after_widget'];
	}
	
	/**
	 * update function.
	 *
	 * @see WP_Widget->update
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
	
		$instance = $old_instance;
	
		if ( ! $this->settings ) {
			return $instance;
		}
	
		foreach ( $this->settings as $key => $setting ) {
			
			if(isset($setting['multiple'])):
				$instance[ $key ] = implode ( ',', $new_instance [$key] );
			else:
				if ( isset( $new_instance[ $key ] ) ) {
					$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
				} elseif ( 'checkbox' === $setting['type'] ) {
					$instance[ $key ] = 0;
				}
			endif;
		}
		if($this->cached){
			$this->flush_widget_cache();
		}
	
		return $instance;
	}
	
	/**
	 * form function.
	 *
	 * @see WP_Widget->form
	 * @param array $instance
	 */
	public function form( $instance ) {
	
		if ( ! $this->settings ) {
			return;
		}
		foreach ( $this->settings as $key => $setting ) {
			$value   = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];
			switch ( $setting['type'] ) {
				
			case "text" :
			?>
				<p>
					<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
				</p>
				<?php
			break;
			
			case "textarea" :
				?>
				<p>
					<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
					<textarea class="widefat" rows="10" cols="20" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
				</p>
				<?php
			break;
			
			case "number" :
				?>
				<p>
					<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
				</p>
				<?php
			break;
			
			case "select" :
				if(isset($setting['multiple'])):
				$value = explode(',', $value);
				endif;
				?>
				<p>
					<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" <?php if(isset($setting['multiple'])):?> multiple="multiple"<?php endif;?> name="<?php echo $this->get_field_name( $key ); ?><?php if(isset($setting['multiple'])):?>[]<?php endif;?>">
						<?php foreach ( $setting['options'] as $option_key => $option_value ) : ?>
							<option value="<?php echo esc_attr( $option_key ); ?>" <?php if(isset($setting['multiple'])): selected( in_array ( $option_key, $value ) , true ); else: selected( $option_key, $value ); endif; ?>><?php echo esc_html( $option_value ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<?php
			break;

			case "checkbox" :
				?>
				<p>
					<input id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="checkbox" value="1" <?php checked( $value, 1 ); ?> />
					<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
				</p>
				<?php
			break;
			}
		}
	}
}

class DT_About_US extends DT_Widget{
	public function __construct(){
		$this->widget_cssclass		= 'about-us__widget';
		$this->widget_description	= esc_html__( 'Display About US widget which contains information and social accounts.', 'dawnthemes' );
		$this->widget_id			= 'DT_AboutUS_Widget';
		$this->widget_name        	= esc_html__( 'DT About US', 'dawnthemes' );
		
		$this->settings				= array(
			'title'		=> array(
					'type'	=> 'text',
					'std'	=> esc_html__( 'About us', 'dawnthemes' ),
					'label' => esc_html__( 'Title', 'dawnthemes' )
			),
			'content'		=> array(
				'type'	=> 'textarea',
				'std'	=> '',
				'label' => esc_html__( 'Content', 'dawnthemes' )
			),
			'social' => array(
				'type'  => 'select',
				'std'   => '',
				'multiple'=>true,
				'label'=>esc_html__('Social','dawnthemes'),
				'desc' => esc_html__( 'Select socials', 'dawnthemes' ),
				'options' => array(
					'facebook'=>'Facebook',
					'twitter'=>'Twitter',
					'google-plus'=>'Google Plus',
					'pinterest'=>'Pinterest',
					'linkedin'=>'Linkedin',
					'rss'=>'Rss',
					'instagram'=>'Instagram',
					'github'=>'Github',
					'behance'=>'Behance',
					'stack-exchange'=>'Stack Exchange',
					'tumblr'=>'Tumblr',
					'soundcloud'=>'SoundCloud',
					'dribbble'=>'Dribbble'
				),
			),
		);
		
		parent::__construct();
	}
	
	public function widget($args, $instance){
		ob_start();
		extract( $args );
		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$info = isset($instance['content']) ? wp_kses_post( stripslashes( $instance['content'] ) ) : '';
		$social = isset($instance['social']) ? explode(',',$instance['social']) : array();
		
		if(!empty($social)){
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			echo '<div class="about-us-widget">';
				echo '<div class="about-us-info">'.$info.'</div>';
				echo '<div class="wg-social">';
				$hover = false;
				$soild_bg = true;
				$outlined = false;
				dt_social($social,$hover,$soild_bg,$outlined);
				echo '</div>';
			echo '</div>';
			echo $after_widget;
			$content = ob_get_clean();
			echo $content;
		}
	}
}

class DT_Instagram_Widget extends DT_Widget{
	public function __construct(){
		$this->widget_cssclass		= 'dt-instagram__widget';
		$this->widget_description	= esc_html__( 'Display Instagram photos.', 'dawnthemes' );
		$this->widget_id			= 'DT_Instagram_Widget';
		$this->widget_name        	= esc_html__( 'DT Instagram', 'dawnthemes' );
		
		$this->settings				= array(
			'title'		=> array(
					'type'	=> 'text',
					'std'	=> esc_html__( 'Instagram', 'dawnthemes' ),
					'label' => esc_html__( 'Title', 'dawnthemes' )
			),
			'username'		=> array(
					'type'	=> 'text',
					'std'	=> esc_html__( 'DawnThemes', 'dawnthemes' ),
					'label' => esc_html__( 'Instagram Username', 'dawnthemes' )
			),
			'images_number'		=> array(
					'type'	=> 'text',
					'std'	=> esc_html__( '8', 'dawnthemes' ),
					'label' => esc_html__( 'Number of Images to Show', 'dawnthemes' )
			),
			'refresh_hour'		=> array(
					'type'	=> 'text',
					'std'	=> esc_html__( '5', 'dawnthemes' ),
					'label' => esc_html__( 'Check for new images on every (hours)', 'dawnthemes' )
			),
		);
		
		parent::__construct();
	}
	
	public function widget($args, $instance){
		ob_start();
		extract( $args );
		$title      	= apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$username		= isset($instance['username']) ? esc_html($instance['username']) : '' ;
		$images_number 	= isset($instance['images_number']) ? absint($instance['images_number']) : 8 ;
		$refresh_hour 	= isset($instance['refresh_hour']) ? absint($instance['refresh_hour']) : 5 ;
		
		if(!empty($username)){
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			?>
			<div class="dt-instagram__widget_wrap">
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
						echo esc_html__( 'Instagram did not return any images.', 'woow' );
					}
					echo '</div>';
				};
				?>
			</div>
			<?php
			echo $after_widget;
			$content = ob_get_clean();
			echo $content;
		}
	}
}

class DT_Posts extends DT_Widget{
	public function __construct(){
		$this->widget_cssclass    = 'dt-posts__wg';
		$this->widget_description = esc_html__( "A list of Your site’s Posts.", 'dawnthemes' );
		$this->widget_id          = 'DT_Posts_Widget';
		$this->widget_name        = esc_html__( 'DT Posts', 'dawnthemes' );
		
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'	=>'',
				'label' => esc_html__( 'Title', 'dawnthemes' )
			),
			'orderby' => array(
				'type'  => 'select',
				'std'   => 'date',
				'label' => esc_html__( 'Order by', 'dawnthemes' ),
				'options' => array(
					'date'   => esc_html__( 'Recent posts', 'dawnthemes' ),
					'featured'   => esc_html__( 'Featured posts', 'dawnthemes' ),
					'rand'  => esc_html__( 'Random', 'dawnthemes' ),
				)
			),
			'number'  => array(
				'type'  => 'number',
				'std'	=> '4',
				'label' => esc_html__( 'Number of posts to show:', 'dawnthemes' )
			),
			'show_date'  => array(
				'type'  => 'checkbox',
				'std'	=>'',
				'label' => esc_html__( 'Display post date?', 'dawnthemes' )
			),
		);
		parent::__construct();
	}
	
	public function widget($args, $instance){
		ob_start();
		extract( $args );
		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$orderby     	= sanitize_title( $instance['orderby'] );
		$number = isset($instance['number']) ? absint($instance['number']) : 4 ;
		$show_date = isset($instance['show_date']) ? $instance['show_date'] : '';
		
		echo $before_widget;
		
		if($title) {
			echo wp_kses( $before_title . esc_html($title) . $after_title, array(
				'h3' => array(
					'class' => array()
				),
				'h4' => array(
					'class' => array()
				),
				'span' => array(
					'class' => array()
				),
			) );
		}
		
		switch ($orderby) {
			case 'date':
				$orderby = 'date';
				break;
		
			case 'featured':
				$orderby = 'meta_value';
				break;
				
			case 'rand':
				$orderby = 'rand';
				break;
		
			default:
				$orderby = 'date';
				break;
		}
		
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => $number ,
			'order'          => 'DESC',
			'orderby'        => "{$orderby}",
			'ignore_sticky_posts' => true,
			'post_status'    => 'publish'
		);
		
		if( $orderby == 'meta_value' ){
			$args['meta_key'] = '_dt_post_meta_featured_post';
			$args['meta_value'] = 'yes';
		}
		
		$posts = new WP_Query($args);
		
		if($posts->have_posts()):
			?>
	        <ul class="dt-recent-posts-wg">
			<?php while($posts->have_posts()): $posts->the_post(); ?>
		        <li class="post-item">
					<?php if(has_post_thumbnail()): ?>
					<div class="post-img dt-effect1">
						<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
					    <?php the_post_thumbnail('dtwozine-recent-posts-wg-thumb'); ?>
						</a>
					</div>
					<?php endif; ?>
			        <div class="post-content">
			        	<h3 class="post-title">
			        		<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title() ?></a>
			        	</h3>
			        	<?php if( $show_date ) :?>
			        	<div class="post-date"><?php echo get_the_date();?></div>
			        	<?php endif; ?>
			        	<div class="post-author"><?php echo sprintf( __( 'Posts by %s' ), get_the_author_posts_link() );?></div>
			        </div>
		        </li>
	        <?php endwhile; ?>
	        </ul>
		<?php
			wp_reset_postdata();
			endif;
		echo $after_widget;
	}
}

class DT_Post_Slider extends DT_Widget{
	public function __construct(){
		$this->widget_cssclass    = 'dt-post-slider-wg';
		$this->widget_description = esc_html__( "Your site’s most recent Posts.", 'dawnthemes' );
		$this->widget_id          = 'DT_Post_Slider_Widget';
		$this->widget_name        = esc_html__( 'DT Post Slider', 'dawnthemes' );
		
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'	=>'',
				'label' => esc_html__( 'Title', 'dawnthemes' )
			),
			'orderby' => array(
				'type'  => 'select',
				'std'   => 'date',
				'label' => esc_html__( 'Order by', 'dawnthemes' ),
				'options' => array(
						'date'   => esc_html__( 'Recent First', 'dawnthemes' ),
						'oldest'  => esc_html__( 'Older First', 'dawnthemes' ),
						'alphabet'  => esc_html__( 'Title Alphabet', 'dawnthemes' ),
						'ralphabet'  => esc_html__( 'Title Reversed Alphabet', 'dawnthemes' ),
						'rand'  => esc_html__( 'Random', 'dawnthemes' ),
				)
			),
			'posts_per_page' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 5,
				'label' => esc_html__( 'Number posts to query', 'dawnthemes' )
			),
		);
		parent::__construct();
	}
	
	public function widget($args, $instance){
		ob_start();
		extract( $args );
		$title       	= apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$orderby     	= sanitize_title( $instance['orderby'] );
		$posts_per_page = absint( $instance['posts_per_page'] );
		
		echo $before_widget;
		
		if($title) {
			echo wp_kses( $before_title . esc_html($title) . $after_title, array(
				'h3' => array(
					'class' => array()
				),
				'h4' => array(
					'class' => array()
				),
				'span' => array(
					'class' => array()
				),
			) );
		}
		
		switch ($orderby) {
			case 'date':
				$orderby = 'date';
				break;
		
			case 'oldest':
				$orderby = 'date';
				$order = 'ASC';
				break;
		
			case 'alphabet':
				$orderby = 'title';
				$orderby = 'ASC';
				break;
		
			case 'ralphabet':
				$orderby = 'title';
				break;
				
			case 'rand':
				$orderby = 'rand';
				break;
		
			default:
				$orderby = 'date';
				break;
		}
		
		$args = array(
			'orderby'         => "{$orderby}",
			'order'           => "DESC",
			'post_type'       => "post",
			'posts_per_page'  => $posts_per_page,
		);
		
		if(is_single()){
			$args['post__not_in'] = array(get_the_ID());
		}
		
		$p = new WP_Query($args);
		
		if($p->have_posts()):
		wp_enqueue_style('slick');
		wp_enqueue_script('slick');
			?>
		<div class="dt-posts-slider dt-preload single_mode" data-mode="single_mode" data-visible="1" data-scroll="1" data-infinite="1" data-autoplay="1" data-arrows="1" data-dots="false">
			<div class="dt-posts-slider__wrap">
		        <div class="posts-slider single_mode">
				<?php while($p->have_posts()): $p->the_post(); ?>
			        <div class="post-item-slide">
						<article id="post-<?php the_ID(); ?>" class="post">
							<?php 
							if( has_post_thumbnail() ):?>
								<div class="post-thumbnail">
									<a href="<?php echo esc_url(get_permalink()); ?>" title="<?php the_title();?>">
									<?php the_post_thumbnail('wozine-post-slider-widget');?>
									</a>
								</div>
								<?php
							endif;
							?>
							<div class="post-content">
								<?php
								$category = get_the_category();
								$cat_ID = $category[0]->term_id;
								if ($category) {
									echo '<a class="dt-post-category" href="' . get_category_link( $cat_ID ) . '" title="' . sprintf( __( "View all posts in %s", "wozine" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> ';
								}
								?>
								<?php the_title( sprintf('<h3 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink()) ), '</a></h3>' ); ?>
								
								<div class="entry-meta">
									<?php
									printf('<div class="byline"><span class="author vcard">%1$s <a class="url fn n" href="%2$s" rel="author">%3$s</a></span></div>',
										esc_html__('By', 'wozine'),
										esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
										get_the_author()
									);
									?>
									<?php
									dt_posted_on();
									?>
								</div>
							</div>
						</article>
					</div>
		        <?php endwhile; ?>
		        </div>
			</div>
	    </div>
		<?php
			wp_reset_postdata();
			endif;
		
		echo $after_widget;
	}
}

class DT_Social_Widget extends DT_Widget {
	public function __construct(){
		$this->widget_cssclass    = 'social-widget';
		$this->widget_description = esc_html__( "Display Social Icon.", 'dawnthemes' );
		$this->widget_id          = 'DT_Social_Widget';
		$this->widget_name        = esc_html__( 'DT Social', 'dawnthemes' );
		
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'	=>'',
				'label' => esc_html__( 'Title', 'dawnthemes' )
			),
			'social' => array(
					'type'  => 'select',
					'std'   => '',
					'multiple'=>true,
					'label'=>esc_html__('Social','dawnthemes'),
					'desc' => esc_html__( 'Select socials', 'dawnthemes' ),
					'options' => array(
						'facebook'=>'Facebook',
						'twitter'=>'Twitter',
						'google-plus'=>'Google Plus',
						'pinterest'=>'Pinterest',
						'linkedin'=>'Linkedin',
						'rss'=>'Rss',
						'instagram'=>'Instagram',
						'github'=>'Github',
						'behance'=>'Behance',
						'stack-exchange'=>'Stack Exchange',
						'tumblr'=>'Tumblr',
						'soundcloud'=>'SoundCloud',
						'dribbble'=>'Dribbble'
					),
			),
			'style' => array(
				'type'  => 'select',
				'std'   => '',
				'label' => esc_html__( 'Style', 'dawnthemes' ),
				'options' => array(
					'square' =>  esc_html__('Square', 'dawnthemes' ),
					'round' =>  esc_html__('Round', 'dawnthemes' ),
					'outlined' =>  esc_html__('Outlined', 'dawnthemes' ),
				)
			),
		);
		parent::__construct();
	}
	
	public function widget($args, $instance){
		ob_start();
		extract( $args );
		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$social = isset($instance['social']) ? explode(',',$instance['social']) : array();
		$style = isset($instance['style']) ? $instance['style'] : 'square';
		if(!empty($social)){
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			echo '<div class="social-widget-wrap social-widget-'.$style.'">';
			$hover = false;
			$soild_bg = true;
			$outlined = false;
			if($style == 'outlined'){
				$hover = true;
				$soild_bg = false;
				$outlined = true;
			}
			dt_social($social,$hover,$soild_bg,$outlined);
			echo '</div>';
			echo $after_widget;
			$content = ob_get_clean();
			echo $content;
		}
	}
	
}

class DT_Tweets extends WP_Widget {
	public function __construct() {
		parent::__construct (
			'dt_tweets', 		// Base ID
			'DT Recent Tweets', 		// Name
			array ('classname'=>'tweets-widget','description' => __ ( 'Display recent tweets', 'dawnthemes' ) )
		);
	}

	public function widget($args, $instance) {
		extract($args);
		if(!empty($instance['title'])){ $title = apply_filters( 'widget_title', $instance['title'] ); }
		echo $before_widget;
		if ( ! empty( $title ) ){ echo $before_title . $title . $after_title; }

		//check settings and die if not set
		if(empty($instance['consumerkey']) || empty($instance['consumersecret']) || empty($instance['accesstoken']) || empty($instance['accesstokensecret']) || empty($instance['cachetime']) || empty($instance['username'])){
			echo '<strong>'.esc_html__('Please fill all widget settings!' , 'dawnthemes').'</strong>' . $after_widget;
			return;
		}

		$dt_widget_recent_tweets_cache_time = get_option('dt_widget_recent_tweets_cache_time');
		$diff = time() - $dt_widget_recent_tweets_cache_time;

		$crt = (int) $instance['cachetime'] * 3600;

		if($diff >= $crt || empty($dt_widget_recent_tweets_cache_time)){
			
			if(!require_once(DTINC_DIR . '/lib/twitteroauth.php')){
				echo '<strong>'.esc_html__('Couldn\'t find twitteroauth.php!','dawnthemes').'</strong>' . $after_widget;
				return;
			}
				
			function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
				$connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
				return $connection;
			}
				
			$connection = getConnectionWithAccessToken($instance['consumerkey'], $instance['consumersecret'], $instance['accesstoken'], $instance['accesstokensecret']);
			$tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$instance['username']."&count=10&exclude_replies=".$instance['excludereplies']);

			if(!empty($tweets->errors)){
				if($tweets->errors[0]->message == 'Invalid or expired token'){
					echo '<strong>'.$tweets->errors[0]->message.'!</strong><br/>'.esc_html__('You\'ll need to regenerate it <a href="https://dev.twitter.com/apps" target="_blank">here</a>!', 'dawnthemes' ) . $after_widget;
				}else{
					echo '<strong>'.$tweets->errors[0]->message.'</strong>' . $after_widget;
				}
				return;
			}
				
			$tweets_array = array();
			for($i = 0;$i <= count($tweets); $i++){
				if(!empty($tweets[$i])){
					$tweets_array[$i]['created_at'] = $tweets[$i]->created_at;

					//clean tweet text
					$tweets_array[$i]['text'] = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $tweets[$i]->text);

					if(!empty($tweets[$i]->id_str)){
						$tweets_array[$i]['status_id'] = $tweets[$i]->id_str;
					}
				}
			}
			update_option('dt_widget_recent_tweets',serialize($tweets_array));
			update_option('dt_widget_recent_tweets_cache_time',time());
		}

		$dt_widget_recent_tweets = maybe_unserialize(get_option('dt_widget_recent_tweets'));
		if(!empty($dt_widget_recent_tweets)){
			echo '<div class="recent-tweets"><ul>';
			$i = '1';
			foreach($dt_widget_recent_tweets as $tweet){
				if(!empty($tweet['text'])){
					if(empty($tweet['status_id'])){ $tweet['status_id'] = ''; }
					if(empty($tweet['created_at'])){ $tweet['created_at'] = ''; }
						
					echo '<li><span>'.$this->_convert_links($tweet['text']).'</span><a class="twitter_time" target="_blank" href="http://twitter.com/'.$instance['username'].'/statuses/'.$tweet['status_id'].'">'.ucfirst($this->_relative_time($tweet['created_at'])).'</a></li>';
					if($i == $instance['tweetstoshow']){ break; }
					$i++;
				}
			}
				
			echo '</ul></div>';
		}

		echo $after_widget;
	}

	protected function _convert_links($status, $targetBlank = true, $linkMaxLen=50){
		// the target
		$target=$targetBlank ? " target=\"_blank\" " : "";

		// convert link to url
		$status = preg_replace("/((http:\/\/|https:\/\/)[^ )]+)/i", "<a href=\"$1\" title=\"$1\" $target >$1</a>", $status);

		// convert @ to follow
		$status = preg_replace("/(@([_a-z0-9\-]+))/i","<a href=\"http://twitter.com/$2\" title=\"Follow $2\" $target >$1</a>",$status);

		// convert # to search
		$status = preg_replace("/(#([_a-z0-9\-]+))/i","<a href=\"https://twitter.com/search?q=$2\" title=\"Search $1\" $target >$1</a>",$status);

		// return the status
		return $status;
	}

	protected function _relative_time($a=''){
		//get current timestampt
		$b = strtotime("now");
		//get timestamp when tweet created
		$c = strtotime($a);
		//get difference
		$d = $b - $c;
		//calculate different time values
		$minute = 60;
		$hour = $minute * 60;
		$day = $hour * 24;
		$week = $day * 7;

		if(is_numeric($d) && $d > 0) {
			//if less then 3 seconds
			if($d < 3) return "right now";
			//if less then minute
			if($d < $minute) return sprintf(esc_html__("%s seconds ago",'dawnthemes'),floor($d));
			//if less then 2 minutes
			if($d < $minute * 2) return esc_html__("about 1 minute ago",'dawnthemes');
			//if less then hour
			if($d < $hour) return sprintf(esc_html__('%s minutes ago','dawnthemes'), floor($d / $minute));
			//if less then 2 hours
			if($d < $hour * 2) return esc_html__("about 1 hour ago",'dawnthemes');
			//if less then day
			if($d < $day) return sprintf(esc_html__("%s hours ago", 'dawnthemes'),floor($d / $hour));
			//if more then day, but less then 2 days
			if($d > $day && $d < $day * 2) return esc_html__("yesterday",'dawnthemes');
			//if less then year
			if($d < $day * 365) return sprintf(esc_html__('%s days ago','dawnthemes'),floor($d / $day));
			//else return more than a year
			return esc_html__("over a year ago",'dawnthemes');
		}
	}

	public function form($instance) {
		$defaults = array (
			'title' => '',
			'consumerkey' => '',
			'consumersecret' => '',
			'accesstoken' => '',
			'accesstokensecret' => '',
			'cachetime' => '',
			'username' => '',
			'tweetstoshow' => ''
		);
		$instance = wp_parse_args ( ( array ) $instance, $defaults );

		echo '
		<p>
			<label>' . __ ( 'Title' , 'dawnthemes' ) . ':</label>
			<input type="text" name="' . $this->get_field_name ( 'title' ) . '" id="' . $this->get_field_id ( 'title' ) . '" value="' . esc_attr ( $instance ['title'] ) . '" class="widefat" />
		</p>
		<p>
			<label>' . __ ( 'Consumer Key' , 'dawnthemes' ) . ':</label>
			<input type="text" name="' . $this->get_field_name ( 'consumerkey' ) . '" id="' . $this->get_field_id ( 'consumerkey' ) . '" value="' . esc_attr ( $instance ['consumerkey'] ) . '" class="widefat" />
		</p>
		<p>
			<label>' . __ ( 'Consumer Secret' , 'dawnthemes' ) . ':</label>
			<input type="text" name="' . $this->get_field_name ( 'consumersecret' ) . '" id="' . $this->get_field_id ( 'consumersecret' ) . '" value="' . esc_attr ( $instance ['consumersecret'] ) . '" class="widefat" />
		</p>
		<p>
			<label>' . __ ( 'Access Token' , 'dawnthemes' ) . ':</label>
			<input type="text" name="' . $this->get_field_name ( 'accesstoken' ) . '" id="' . $this->get_field_id ( 'accesstoken' ) . '" value="' . esc_attr ( $instance ['accesstoken'] ) . '" class="widefat" />
		</p>
		<p>
			<label>' . __ ( 'Access Token Secret' , 'dawnthemes' ) . ':</label>
			<input type="text" name="' . $this->get_field_name ( 'accesstokensecret' ) . '" id="' . $this->get_field_id ( 'accesstokensecret' ) . '" value="' . esc_attr ( $instance ['accesstokensecret'] ) . '" class="widefat" />
		</p>
		<p>
			<label>' . __ ( 'Cache Tweets in every' , 'dawnthemes' ) . ':</label>
			<input type="text" name="' . $this->get_field_name ( 'cachetime' ) . '" id="' . $this->get_field_id ( 'cachetime' ) . '" value="' . esc_attr ( $instance ['cachetime'] ) . '" class="small-text" />'.esc_html__('hours','dawnthemes').'
		</p>
		<p>
			<label>' . __ ( 'Twitter Username' , 'dawnthemes' ) . ':</label>
			<input type="text" name="' . $this->get_field_name ( 'username' ) . '" id="' . $this->get_field_id ( 'username' ) . '" value="' . esc_attr ( $instance ['username'] ) . '" class="widefat" />
		</p>
		<p>
			<label>' . __ ( 'Tweets to display' , 'dawnthemes' ) . ':</label>
			<select type="text" name="' . $this->get_field_name ( 'tweetstoshow' ) . '" id="' . $this->get_field_id ( 'tweetstoshow' ) . '">';
		$i = 1;
		for(i; $i <= 10; $i ++) {
			echo '<option value="' . $i . '"';
			if ($instance ['tweetstoshow'] == $i) {
				echo ' selected="selected"';
			}
			echo '>' . $i . '</option>';
		}
		echo '
			</select>
		</p>
		<p>
			<label>' . __ ( 'Exclude replies', 'dawnthemes' ) . ':</label>
			<input type="checkbox" name="' . $this->get_field_name ( 'excludereplies' ) . '" id="' . $this->get_field_id ( 'excludereplies' ) . '" value="true"';
		if (! empty ( $instance ['excludereplies'] ) && esc_attr ( $instance ['excludereplies'] ) == 'true') {
			echo ' checked="checked"';
		}
		echo '/></p>';
	}

	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['consumerkey'] = strip_tags( $new_instance['consumerkey'] );
		$instance['consumersecret'] = strip_tags( $new_instance['consumersecret'] );
		$instance['accesstoken'] = strip_tags( $new_instance['accesstoken'] );
		$instance['accesstokensecret'] = strip_tags( $new_instance['accesstokensecret'] );
		$instance['cachetime'] = strip_tags( $new_instance['cachetime'] );
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['tweetstoshow'] = strip_tags( $new_instance['tweetstoshow'] );
		$instance['excludereplies'] = strip_tags( $new_instance['excludereplies'] );

		if($old_instance['username'] != $new_instance['username']){
			delete_option('dt_widget_recent_tweets_cache_time');
		}

		return $instance;
	}
}



class DT_Post_Thumbnail_Widget extends DT_Widget {

	public function __construct() {
		$this->widget_cssclass    = 'widget-post-thumbnail';
		$this->widget_description = esc_html__( "Widget post with thumbnail.", 'dawnthemes' );
		$this->widget_id          = 'dt_widget_post_thumbnail';
		$this->widget_name        = esc_html__( 'DT Post Thumbnail', 'dawnthemes' );
		$this->cached = false;
		$categories = get_categories( array(
				'orderby' => 'NAME',
				'order' => 'ASC'
		));
		$categories_options = array();
		foreach ((array)$categories as $category) {
			$categories_options[$category->term_id] = $category->name;
		}
		$this->settings           = array(
				'title'  => array(
					'type'  => 'text',
					'std'	=>'',
					'label' => esc_html__( 'Title', 'dawnthemes' )
				),
				'posts_per_page' => array(
					'type'  => 'number',
					'step'  => 1,
					'min'   => 1,
					'max'   => '',
					'std'   => 5,
					'label' => esc_html__( 'Number of posts to show', 'dawnthemes' )
				),
				'orderby' => array(
					'type'  => 'select',
					'std'   => 'date',
					'label' => esc_html__( 'Order by', 'dawnthemes' ),
					'options' => array(
							'latest'   => esc_html__( 'Latest', 'dawnthemes' ),
							'comment'  => esc_html__( 'Most Commented', 'dawnthemes' ),
					)
				),
				'categories' => array(
						'type'  => 'select',
						'std'   => '',
						'multiple'=>true,
						'label'=>esc_html__('Categories','dawnthemes'),
						'desc' => esc_html__( 'Select a category or leave blank for all', 'dawnthemes' ),
						'options' => $categories_options,
				),
				'hide_date' => array(
						'type'  => 'checkbox',
						'std'   => 0,
						'label' => esc_html__( 'Hide date in post meta info', 'dawnthemes' )
				),
				'hide_comment' => array(
						'type'  => 'checkbox',
						'std'   => 0,
						'label' => esc_html__( 'Hide comment in post meta info', 'dawnthemes' )
				),
		);
		parent::__construct();
	}
	
	public function widget($args, $instance){
		ob_start();
		extract( $args );
		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$posts_per_page      = absint( $instance['posts_per_page'] );
		$orderby     = sanitize_title( $instance['orderby'] );
		$hide_date = isset($instance['hide_date']) && $instance['hide_date'] === '1' ? true : false;
		$hide_comment = isset($instance['hide_comment']) && $instance['hide_comment'] === '1' ? true : false;
		$categories  = $instance['categories'];
		$query_args  = array(
				'posts_per_page' => $posts_per_page,
				'post_status' 	 => 'publish',
				'ignore_sticky_posts' => 1,
				'orderby' => 'date',
				"meta_key" => "_thumbnail_id",
				'order' => 'DESC',
		);
		if($orderby == 'comment'){
			$query_args['orderby'] = 'comment_count';
		}
		if(!empty($categories)){
			$query_args['cat'] = $categories;
		}
		$r = new WP_Query($query_args);
		if($r->have_posts()):
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
				echo '<ul class="posts-thumbnail-list">';
				while ($r->have_posts()): $r->the_post();global $post;
					echo '<li>';
					echo '<div class="posts-thumbnail-image">';
					echo '<a href="'.esc_url(get_the_permalink()).'">'.get_the_post_thumbnail(null,'dt-thumbnail-square', array('title' => strip_tags(get_the_title()))).'</a>';
					echo '</div>';
					echo '<div class="posts-thumbnail-content">';
						echo '<h4><a href="'.esc_url(get_the_permalink()).'" title="'.esc_attr(get_the_title()).'">'.get_the_title().'</a></h4>';
						echo '<div class="posts-thumbnail-meta">';
						if(!$hide_date)
							echo '<time datetime="'.get_the_date('c').'">'.get_the_date().'</time>';
						
						if(!$hide_date && !$hide_comment)
							echo ', ';
						
						if(!$hide_comment){
							$output = '';
							$number = get_comments_number($post->ID);
							if ( $number > 1 ) {
								$output = str_replace( '%', number_format_i18n( $number ), ( false === false ) ? esc_html__( '% Comments' ,'dawnthemes') : false );
							} elseif ( $number == 0 ) {
								$output = ( false === false ) ? esc_html__( '0 Comments', 'dawnthemes') : false;
							} else { // must be one
								$output = ( false === false ) ? esc_html__( '1 Comment', 'dawnthemes' ) : false;
							}
							echo '<span class="comment-count"><a href="'.esc_url(get_comments_link()).'">'.$output.'</a></span>';	
						}
						echo '</div>';
					echo '</div>';
					echo '</li>';
				endwhile;
				echo  '</ul>';
			echo $after_widget;
		endif;
		$content = ob_get_clean();
		wp_reset_query();
		echo $content;
	}
	
}

class DT_Mailchimp_Widget extends DT_Widget {
	public function __construct(){
		$this->widget_cssclass    = 'widget-mailchimp';
		$this->widget_description = esc_html__( "Widget Mailchimp Subscribe.", 'dawnthemes' );
		$this->widget_id          = 'dt_widget_mailchimp';
		$this->widget_name        = esc_html__( 'DT Mailchimp Subscribe', 'dawnthemes' );
		$this->cached = false;
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'	=>'',
				'label' => esc_html__( 'Title', 'dawnthemes' )
			),
		);
		parent::__construct();
	}
	
	public function widget($args, $instance){
		ob_start();
		extract( $args );
		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		ob_start();
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		dt_mailchimp_form();
		echo $after_widget;
		$content = ob_get_clean();
		echo $content;
	}
}

add_action( 'widgets_init', 'dt_register_widget');
function dt_register_widget(){
	register_widget('DT_About_US');
	register_widget('DT_Instagram_Widget');
	register_widget('DT_Posts');
	register_widget('DT_Post_Slider');
	//register_widget('DT_Post_Thumbnail_Widget');
	register_widget('DT_Social_Widget');
	register_widget( 'DT_Tweets' );
	register_widget( 'DT_Mailchimp_Widget' );
}