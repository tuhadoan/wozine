<?php
/**
 * @package DawnThemes
 */
require_once 'includes/mega-menu-walker.php';
require_once 'includes/mega-menu-walker-edit.php';
require_once 'includes/mega-menu-content-helper.php';

define('MEGA_MENU_NAV_LOCS', 'wp-mash-menu-nav-locations');

class DawnThemes_Mega_Menu{
	protected $baseURL;

	protected $menu_item_options;
	protected $optionDefaults;

	protected $count = 0;

	function __construct(){
		
		$this->baseURL =  get_template_directory_uri().'/includes/megamenu/';
		$this->menu_item_options = array();

		//ADMIN
		if( is_admin() ){
			add_action( 'admin_menu' , array( &$this , 'admin_init' ) );

			add_filter( 'wp_edit_nav_menu_walker', array( &$this , 'edit_walker' ) , 2000);
			add_action( 'wp_ajax_dt_megamenu_update_nav_locs', array( $this , 'update_nav_locs' ) ); //For logged in users
			add_action( 'wp_ajax_dt_megamenu_add_menu_item', array( $this , 'megamenu_add_menu_item' ) );
			
			//Appearance > Menus : save custom menu options
			add_action( 'wp_update_nav_menu_item', array( &$this , 'update_nav_menu_item' ), 10, 3); //, $menu_id, $menu_item_db_id, $args;
			add_action( 'megamenu_menu_item_options', array( &$this , 'menu_item_custom_options' ), 10, 1);		//Must go here for AJAX purposes

			// front-end Ajax
			add_action( 'wp_ajax_dt_megaMenu_getGridContent', array( &$this , 'get_grid_content' ) );
			add_action( 'wp_ajax_nopriv_dt_megaMenu_getGridContent', array( &$this , 'get_grid_content' ));

			$this->optionDefaults = array(
				'menu-item-isMega'				=> 'off'
			);
		} else {
			$this->init();
		}

		add_action( 'wp_enqueue_scripts', array ($this, 'add_scripts'));
	}

	function init(){
		//Filters
		add_filter( 'wp_nav_menu_args' , array( $this , 'mega_menu_filter' ), 2000 );  	//filters arguments passed to wp_nav_menu
	}

	function admin_init(){
		
		//Appearance > Menus : load additional styles and scripts
		add_action( 'admin_print_styles-nav-menus.php', array( $this , 'load_admin_nav_menu_js' ) );
		add_action( 'admin_print_styles-nav-menus.php', array( $this , 'load_admin_nav_menu_css' ));
	}

	/*
	 * Save the Menu Item Options
	 */
	function update_nav_menu_item( $menu_id, $menu_item_db_id, $args ){
		$megamenu_options_string = isset( $_POST[sanitize_key('megamenu_options')][$menu_item_db_id] ) ? $_POST[sanitize_key('megamenu_options')][$menu_item_db_id] : '';
		$megamenu_options = array();
		parse_str( $megamenu_options_string, $megamenu_options );

		$megamenu_options = wp_parse_args( $megamenu_options, $this->optionDefaults );

		update_post_meta( $menu_item_db_id, '_megamenu_options', $megamenu_options );
	}
	
	function get_grid_content(){
		$data = $_POST[sanitize_key('data')];	 // Array(dataType, dataId, postType)
		$helper = new DawnThemes_Mega_Menu_Content_Helper();
		switch($data[0]){
			case 'category':
				echo $helper->get_latest_category_items($data[1]);
				break;
			case 'post_tag':
				echo $helper->get_latest_items_by_tag($data[1]);
				break;
			case 'page':
				echo $helper->get_page_content($data[1]);
				break;
			case 'post':
				echo $helper->get_post_content($data[1]);
				break;
			/* WooCommerce/JigoShop Product Category */
			case 'product_cat':
				echo $helper->get_woo_product_items($data[1]);
				break;
			/* Custom Taxonomy */
			default:
				echo $helper->get_latest_custom_category_items($data[1],$data[0],$data[2]);
				break;
		}

		die();
	}
	
	/*
	 * Update the Locations when the Activate Mega Menu Locations Meta Box is Submitted
	 */
	function update_nav_locs(){
	
		$data = $_POST[sanitize_key('data')];
		$data = explode(',', $data);
	
		update_option( MEGA_MENU_NAV_LOCS, $data);
	
		echo $data;
		die();
	}
	
	function megamenu_add_menu_item(){
	
		if ( ! current_user_can( 'edit_theme_options' ) )
			die('-1');
	
		check_ajax_referer( 'add-menu_item', 'menu-settings-column-nonce' );
	
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	
		// For performance reasons, we omit some object properties from the checklist.
		// The following is a hacky way to restore them when adding non-custom items.
	
		$menu_items_data = array();
		foreach ( (array) $_POST[sanitize_key('menu-item')] as $menu_item_data ) {
			if (
				! empty( $menu_item_data['menu-item-type'] ) &&
				'custom' != $menu_item_data['menu-item-type'] &&
				! empty( $menu_item_data['menu-item-object-id'] )
			) {
				switch( $menu_item_data['menu-item-type'] ) {
					case 'post_type' :
						$_object = get_post( $menu_item_data['menu-item-object-id'] );
						break;
	
					case 'taxonomy' :
						$_object = get_term( $menu_item_data['menu-item-object-id'], $menu_item_data['menu-item-object'] );
						break;
				}
	
				$_menu_items = array_map( 'wp_setup_nav_menu_item', array( $_object ) );
				$_menu_item = array_shift( $_menu_items );
	
				// Restore the missing menu item properties
				$menu_item_data['menu-item-description'] = $_menu_item->description;
			}
	
			$menu_items_data[] = $menu_item_data;
		}
	
		$item_ids = wp_save_nav_menu_items( 0, $menu_items_data );
		if ( is_wp_error( $item_ids ) )
			die('-1');
	
		foreach ( (array) $item_ids as $menu_item_id ) {
			$menu_obj = get_post( $menu_item_id );
			if ( ! empty( $menu_obj->ID ) ) {
				$menu_obj = wp_setup_nav_menu_item( $menu_obj );
				$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
				$menu_items[] = $menu_obj;
			}
		}
	
		if ( ! empty( $menu_items ) ) {
			$args = array(
				'after' => '',
				'before' => '',
				'link_after' => '',
				'link_before' => '',
				'walker' =>	new DawnThemes_Mega_Menu_Walker_Edit,
			);
			echo walk_nav_menu_tree( $menu_items, 0, (object) $args );
		}
	}

	function menu_item_custom_options( $item_id ){
		?>

			<!--  START MASHMENU ATTS -->
			<div>
				<div class="wpmega-atts wpmega-unprocessed" style="display:block">
					<input id="megamenu_options-<?php echo $item_id;?>" class="megamenu_options_input" name="megamenu_options[<?php echo $item_id;?>]" type="hidden" value="" />

					<?php $this->showMenuOptions( $item_id ); ?>

				</div>
				<!--  END MASHMENU ATTS -->
			</div>
	<?php
	}

	function showMenuOptions( $item_id ){
		if(dt_get_theme_option('megamenu', 'on')=='on'){
			$this->showCustomMenuOption(
				'menu_style',
				$item_id,
				array(
					'level'    => '0',
					'title' => esc_html__( 'Select style for Menu' , 'wozine' ),
					'label' => esc_html__( 'Menu Style' , 'wozine' ),
					'type'     => 'select',
					'default' => '',
					'ops'    => array('list'=>esc_html__('List','wozine'),'columns'=>esc_html__('Columns','wozine'), 'preview'=>esc_html__('Preview','wozine'))
				)
			);
		}
		
		/** Get Sidebar **/
		global  $wp_registered_sidebars;
			$arr = array("0"=>"No Sidebar");
			foreach ( $wp_registered_sidebars as $sidebar ) :
		         $arr = array_merge($arr, array($sidebar['id']=>$sidebar['name']));
		    endforeach;
		if(dt_get_theme_option('megamenu', 'on')=='on'){
			$this->showCustomMenuOption(
				'addSidebar',
				$item_id,
				array(
					'level'	=> '1',
					'title' => esc_html__( 'Select the widget area to display' , 'wozine' ),
					'label' => esc_html__( 'Display widgets area ' , 'wozine' ),
					'type' 	=> 'select',
					'default' => '0',
					'ops'	=> $arr
				)
			);
		}
		/** Get Sidebar **/

		if(dt_get_theme_option('megamenu', 'on')=='on'){
			$this->showCustomMenuOption(
				'displayLogic',
				$item_id,
				array(
					'level'	=> '0',
					'title' => esc_html__( 'Logic to display this menu item' , 'wozine' ),
					'label' => esc_html__( 'Display Logic' , 'wozine' ),
					'type' 	=> 'select',
					'default' => '',
					'ops'	=> array('both'=>esc_html__('Always visible','wozine'),'guest'=>esc_html__('Only Visible to Guests','wozine'),'member'=>esc_html__('Only Visible to Members','wozine'))
				)
			);
		}
	}

	function showCustomMenuOption( $id, $item_id, $args ){
		extract( wp_parse_args(
			$args, array(
				'level'	=> '0-plus',
				'title' => '',
				'label' => '',
				'type'	=> 'text',
				'ops'	=>	array(),
				'default'=> '',
			) )
		);

		$_val = $this->getMenuItemOption( $item_id , $id );

		$desc = '<span class="ss-desc">'.$label.'<span class="ss-info-container">?<span class="ss-info">'.$title.'</span></span></span>';
		?>
				<p class="field-description description description-wide wpmega-custom wpmega-l<?php echo $level;?> wpmega-<?php echo $id;?>">
					<label for="edit-menu-item-<?php echo $id;?>-<?php echo $item_id;?>">

						<?php

						switch($type) {
							case 'text':
								echo $desc;
								?>
								<input type="text" id="edit-menu-item-<?php echo $id;?>-<?php echo $item_id;?>"
									class="edit-menu-item-<?php echo $id;?>"
									name="menu-item-<?php echo $id;?>[<?php echo $item_id;?>]"
									size="30"
									value="<?php echo htmlspecialchars( $_val );?>" />
								<?php

								break;
							case 'checkbox':
								?>
								<input type="checkbox"
									id="edit-menu-item-<?php echo $id;?>-<?php echo $item_id;?>"
									class="edit-menu-item-<?php echo $id;?>"
									name="menu-item-<?php echo $id;?>[<?php echo $item_id;?>]"
									<?php
										if ( ( $_val == '' && $default == 'on' ) ||
												$_val == 'on')
											echo 'checked="checked"';
									?> />
								<?php
								echo $desc;
								break;
							case 'select':
								echo $desc;
								if( empty($_val) ) $_val = $default;
								?>
								<select
									id="edit-menu-item-<?php echo $id; ?>-<?php echo $item_id; ?>"
									class="edit-menu-item-<?php echo $id; ?>"
									name="menu-item-<?php echo $id;?>[<?php echo $item_id;?>]">
									<?php foreach( $ops as $opval => $optitle ): ?>
										<option value="<?php echo $opval; ?>" <?php if( $_val == $opval ) echo 'selected="selected"'; ?> ><?php echo $optitle; ?></option>
									<?php endforeach; ?>
								</select>
								<?php
								break;
						}
 						?>

					</label>
				</p>
	<?php
	}

	function getMenuItemOption( $item_id , $id ){

		$option_id = 'menu-item-'.$id;

		//We haven't investigated this item yet
		if( !isset( $this->menu_item_options[ $item_id ] ) ){

			$megamenu_options = get_post_meta( $item_id , '_megamenu_options', true );
			//If $megamenu_options are set, use them
			if( $megamenu_options ){
				//echo '<pre>'; print_r( $megamenu_options ); echo '</pre>';
				$this->menu_item_options[ $item_id ] = $megamenu_options;
			}
			//Otherwise get the old meta
			else{
				return get_post_meta( $item_id, '_menu_item_'.$id , true );
			}
		}
		return isset( $this->menu_item_options[ $item_id ][ $option_id ] ) ? $this->menu_item_options[ $item_id ][ $option_id ] : '';

	}

	/*
	 * Custom Walker Name - to be overridden by Standard
	 */
	function edit_walker( $className ){
		return 'DawnThemes_Mega_Menu_Walker_Edit';
	}

	/*
	 * Default walker, but this can be overridden
	 */
	function get_walker(){
		return new DawnThemes_Mega_Menu_Walker_Core();
	}

	function get_menu_args( $args ){

		ob_start();
		global $wpdb;
		$number_day         = dt_get_theme_option('lns_number_days');
		if($number_day != ''):
		$limit_latest_news  = dt_get_theme_option('lns_maxinum_articles');
		$latest_news_str    = '';
		$limit_date = is_numeric($number_day) ? date('Y-m-d', strtotime('-' . $number_day . ' day')) : date('Y-m-d');

		$options = array(
			'post_type'         => 'post',
			'posts_per_page'    => $limit_latest_news,
			'orderby'           => 'post_date',
			'post_status'       => 'publish',
			'date_query'        => array(
					'after'         => $limit_date
							),
			'ignore_sticky_posts'   => true
		);
		$the_query = new WP_Query( $options );
		$query_count = $wpdb->get_results('select a.ID, a.post_title from ' . $wpdb->prefix .'posts as a where a.post_date >="' . $limit_date . '" and a.post_status = "publish" and a.post_type = "post"');
		?>
            <li class="post-toggle">
                <a class="link toggle" href="javascript:void(0)">
                    <span class="post-count"><?php echo count($query_count);?></span>
                    <span class="post-heading"><?php echo esc_html__('NEWS','wozine');?> <i style="display:inline-block" class="fa fa-angle-down"></i></span>
                </a>
                <div class="sub-menu-box sub-menu-box-post article-dropdown item-post-menu item-list-post">
                    <?php
                        if($the_query->have_posts()): 
						$i = 0;
						$count = $the_query->post_count;
						$item_per_column = ceil($count / 3);
						$col = 1;
						while($the_query->have_posts()): $the_query->the_post();
                            if($i % $item_per_column == 0){
							?>
									<div class="col-md-4">
								<?php }?>
							<article class="item item-post item-post-menu-post article-content clearfix">
								<a class="thumb" href="<?php echo get_permalink();?>" title="<?php echo get_the_title();?>">
									<?php the_post_thumbnail('xsmall');?>
								</a>
								<h3><a href="<?php echo get_permalink();?>" title="<?php echo get_the_title();?>"><?php echo get_the_title();?></a></h3>
							</article>
						<?php 
							if($i % $item_per_column == ($item_per_column - 1) || $i == $count - 1){
							?>
									</div>
								<?php }
							$i++;
						endwhile; 
						wp_reset_query();
					endif;?>
                </div>
            </li>
        <?php endif;?>
        <?php
		$new_articles .= ob_get_contents();
		ob_end_clean();
		
		$header_layout = dt_get_theme_option('header_layout','1');
		$logo_html = '';
		$is_sticky_menu = dt_get_theme_option('sticky_menu','on');
		if($header_layout == '3' || $is_sticky_menu == 'on'){
			ob_start();

			if($is_sticky_menu == 'on' && dt_get_theme_option('logo_image_sticky') != ''){?>
				<a class="logo" href="<?php echo esc_url(get_home_url()); ?>" title="<?php wp_title( '|', true, 'right' ); ?>"><img src="<?php echo esc_url(dt_get_theme_option('logo_image_sticky')); ?>" alt="<?php wp_title( '|', true, 'right' ); ?>"/></a>
			<?php
			} elseif(dt_get_theme_option('logo_image') == ''){?>
				<a class="logo" href="<?php echo esc_url(home_url()); ?>"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.png');?>" alt="logo"></a>
			<?php } else{?>
				<a class="logo" href="<?php echo esc_url(get_home_url()); ?>" title="<?php wp_title( '|', true, 'right' ); ?>"><img src="<?php echo esc_url(dt_get_theme_option('logo_image')); ?>" alt="<?php wp_title( '|', true, 'right' ); ?>"/></a>
			<?php };
			
			$logo_html = ob_get_contents();
			
			ob_end_clean();
		}
		$args['walker'] 			= $this->get_walker();
		$args['container_id'] 		= 'dt-megamenu';
		$args['container_class'] 	= 'megamenu hidden-mobile';
		$args['menu_class']			= 'menu';
		$args['depth']				= 0;
		$args['items_wrap']			= (($is_sticky_menu == 'on') ? '<div class="logo-menu">'.$logo_html.'</div>':'').'<ul id="%1$s" class="%2$s main-menu" data-theme-location="">%3$s'. str_replace('%','%%',$new_articles).'</ul>'/*.$css*/;
		$args['link_before']		= '';
		$args['link_after']			= '';
		
		return $args;
	}
	/*
	 * Apply options to the Menu via the filter
	 */
	function mega_menu_filter( $args ){

		//Only print the menu once
		if( $this->count > 0 ) return $args;

		if( isset( $args['responsiveSelectMenu'] ) ) return $args;
		if( isset( $args['filter'] ) && $args['filter'] === false ) return $args;

		//Check to See if this Menu Should be Megafied
		if(!isset($args['is_megamenu']) || !$args['is_megamenu']){
			return $args;
		}
		
		$this->count++;

		$items_wrap 	= '<ul id="%1$s" class="%2$s" data-theme-location="primary-menu">%3$s</ul>'; //This is the default, to override any stupidity

		$args['items_wrap'] = $items_wrap;

		$args = $this->get_menu_args( $args );

		return $args;
	}

	function add_scripts(){
		wp_enqueue_script('megamenu-js', $this->baseURL.'js/megamenu.min.js', array('jquery'), '', true);
		
		wp_localize_script( 'megamenu-js', 'dt_megamenu', array( 'ajax_url' => admin_url( 'admin-ajax.php' ),'ajax_loader'=>'','ajax_enabled'=>0) );
	}

	function load_admin_nav_menu_js(){
		wp_enqueue_script('megamenu-admin-js', $this->baseURL.'js/megamenu.admin.js', array('jquery'), '', true);
	}

	function load_admin_nav_menu_css(){
		wp_enqueue_style('megamenu-admin-css',$this->baseURL.'css/megamenu.admin.css');
	}
}

new DawnThemes_Mega_Menu();




