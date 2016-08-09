<?php
/*
 * Walker for the Front End Mega menu
 * @package DawnThemes
 */
class DawnThemes_Mega_Menu_Walker_Core extends Walker_Nav_Menu{

	protected $index = 0;
	protected $menuItemOptions;
	protected $noMegaMenu;

	/**
	 * Traverse elements to create list from elements.
	 *
	 * Calls parent function in wp-includes/class-wp-walker.php
	 */
	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
		if ( !$element )
			return;

		//Add indicators for top level menu items with submenus
		$id_field = $this->db_fields['id'];
		$element->classes[] = 'level' . $depth;
		if ( $depth == 0 && !empty( $children_elements[ $element->$id_field ] ) ) {
			$element->classes[] = 'menu-item-has-sub-content';
		}
		
		$id_field = $this->db_fields['id'];

		//display this element
		if ( is_array( $args[0] ) )
			$args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
		if($this->getMegaMenuOption($element->menu_item_parent,'menu_style') == 'preview'){
		//if($this->getMegaMenuOption($element->menu_item_parent,'isMega') != 'off'){
			if($depth == 1 && is_array($args[0]))
				$args[0]['parentMega'] = 'preview';
		} elseif(is_array($args[0])){
				$args[0]['parentMega'] = $this->getMegaMenuOption($element->menu_item_parent,'menu_style');
		}
		
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		
		call_user_func_array(array($this, 'start_el'), $cb_args);

		$id = $element->$id_field;
				
		// descend only when the depth is right and there are childrens for this element
		if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id])) {
			if(isset( $children_elements[$id])){
				foreach( $children_elements[ $id ] as $child ){

					if ( !isset($newlevel) ) {
						$newlevel = true;
						//start the child delimiter
						
						$sidebar_name = $this->getMegaMenuOption($element->$id_field,'addSidebar');
						$args = array(array("id"=>$element->$id_field,"title"=>$element->title,'addSidebar'=>$sidebar_name));
						
						
						if($depth == 0)
							$args[0]["parentMega"] = $this->getMegaMenuOption($element->$id_field,'menu_style') ;
						else
							$args[0]["parentMega"] = $this->getMegaMenuOption($element->menu_item_parent,'menu_style') ;
							
						
						$cb_args = array_merge( array(&$output, $depth), $args);
												
						call_user_func_array(array($this, 'start_lvl'), $cb_args);
					}
					$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
				}
				unset( $children_elements[ $id ] );
			}
		}

		if ( isset($newlevel) && $newlevel ){
			//end the child delimiter
			$args = array(array("id"=>$element->$id_field,"title"=>$element->title));
			
			$args[0]["parentMega"] = $this->getMegaMenuOption($element->$id_field,'menu_style');
			
			$cb_args = array_merge( array(&$output, $depth), $args);
			call_user_func_array(array($this, 'end_lvl'), $cb_args);
		}

		//end this element
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		call_user_func_array(array($this, 'end_el'), $cb_args);
	}
	
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		if($depth == 0){
			if(isset($args["parentMega"]) && $args["parentMega"] == 'preview'){
				$output .= "\n$indent<div class=\"sub-content sub-preview\"><ul class=\"sub-grid-tabs\">";
			} elseif(isset($args["parentMega"]) && $args["parentMega"] == 'columns') {
				$output .= "\n$indent<div class=\"sub-content sub-menu-box-grid sub-columns\"><ul class=\"columns\">\n";
			} else {
				$output .= "\n$indent<ul class=\"sub-menu sub-menu-list level0\">\n";
			}
		} else {
			
			if(isset($args["parentMega"]) && $args["parentMega"] == 'columns'){
				$output .= "\n$indent<li><ul class=\"list\"><li class=\"header\">".$args["title"]."</li>\n";				
			} else {
				$output .= "\n$indent<ul class=\"sub-menu level" . $depth . "\">\n";
			}
		}
	}
	
	function end_lvl( &$output, $depth = 0, $args = array() ){
		$indent = str_repeat( "\t", $depth );
		if($depth == 0){
			if(isset($args["parentMega"]) && $args["parentMega"] == 'preview'){
				$output .= "\n$indent</ul></div>"; // end <ul class="sub-grid-tabs">
			} elseif(isset($args["parentMega"]) && $args["parentMega"] == 'columns') {
				$output .= "\n$indent</ul></div>\n"; // end <ul class="columns">
			} else {
				$output .= "</ul>";
			}
		} else {
			if(isset($args["parentMega"]) && $args["parentMega"] == 'columns')
				$output .= "\n$indent</ul></li>\n";
			else
				$output .= "</ul>";
		}
	}

	function getMegaMenuOption( $item_id , $id ){
		$option_id = 'menu-item-'.$id;

		//Initialize array
		if( !is_array( $this->menuItemOptions ) ){
			$this->menuItemOptions = array();
			$this->noMegaMenu = array();
		}

		//We haven't investigated this item yet
		if( !isset( $this->menuItemOptions[ $item_id ] ) ){
			
			$megamenu_options = false;
			if( empty( $this->noMegaMenu[ $item_id ] ) ) {
				$megamenu_options = get_post_meta( $item_id , '_megamenu_options', true );
				if( !$megamenu_options ) $this->noMegaMenu[ $item_id ] = true; //don't check again for this menu item
			}

			//If $megamenu_options are set, use them
			if( $megamenu_options ){
				$this->menuItemOptions[ $item_id ] = $megamenu_options;
			} 
			//Otherwise get the old meta
			else{
				$option_id = '_menu_item_'.$id;
				return get_post_meta( $item_id, $option_id , true );
			}
		}
		return isset( $this->menuItemOptions[ $item_id ][ $option_id ] ) ? stripslashes( $this->menuItemOptions[ $item_id ][ $option_id ] ) : '';
	}
	
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ){
		$args = (object)$args;
		
		// check display logic
		$display_logic = $this->getMegaMenuOption( $item->ID, 'displayLogic' );
		if(($display_logic == 'guest' && is_user_logged_in()) || ($display_logic == 'member' && !is_user_logged_in())){
			return;
		}
		if(isset($classes)){
			unset($classes['list-style']);
		}
		global $wp_query;
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
 
		//Handle class names depending on menu item settings
		$class_names = $value = '';
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		
		if($depth == 1 && $args->parentMega == 'preview'){
			$classes[] = 'grid-title';
		}
		if($depth == 0 && $opt_menu_style = $this->getMegaMenuOption( $item->ID, 'menu_style' )== 'list'){
			$classes[] = 'list-style';
		}
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
		$class_names = ' '. esc_attr( $class_names ) . '';

		$options = get_option('megamenu_options');
		
		if($depth == 1 && $args->parentMega == 'preview'){
			$post_type = 'any';
			/* if you want exactly what kind of post types which belong to this category
			 * uncomment & edit code below
			 * ====================
			 * if($item->object = 'custom-taxonomy') $post_type = 'custom-post-type';
			 * ====================
			 */

			if($options['ajax_loading'] != 'on'){
				$output .= '<div class="sub-grid-content" id="grid-'.$item->ID.'">';
				
				$helper = new DawnThemes_Mega_Menu_Content_Helper();
				
				switch($item->object){
					case 'category':
						$output .= $helper->get_latest_category_items($item->object_id);
						break;
					case 'post_tag':
						$output .= $helper->get_latest_items_by_tag($item->object_id);
						break;
					case 'page':
						$output .= $helper->get_page_content($item->object_id);
						break;
					case 'post':
						$output .= $helper->get_post_content($item->object_id);
						break;
					case 'product_cat':
						$output .= $helper->get_woo_product_items($item->object_id);
						break;
					default:						
						$output .= $helper->get_latest_custom_category_items($item->object_id, $item->object,$post_type);
						break;
				}
				
				
				$output .= '</div>';
			}
			
			$output .= /*$indent . */'<li id="mega-menu-item-'. $item->ID . '"' . $value .' class="'. $class_names .'" data-target="grid-'.$item->ID.'" data-type="'.$item->type.'" data-post="'.$post_type.'" data-object="'.$item->object.'" data-id="'.$item->object_id.'">';
		} else if($depth != 1){
			$output .= /*$indent . */'<li id="mega-menu-item-'. $item->ID . '"' . $value .' class="'.$class_names.'">';
		}

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		//$attributes .= ! empty( $item->class )      ? ' class="'  . esc_attr( $item->class      ) .'"' : '';
		
		$item_output = '';
		
		/* Add title and normal link content - skip altogether if nolink and notext are both checked */
		if( !empty( $item->title ) && trim( $item->title ) != '' ){
			
			//Determine the title
			$title = apply_filters( 'the_title', $item->title, $item->ID );
			
			if(!empty($args->before)){
				$item_output = $args->before;
			}
			if(!in_array("header",$classes)){
				$item_output.= '<a'. $attributes .'>';
			}
			
			
			$opt_icon = $this->getMegaMenuOption( $item->ID, 'icon' );
			$opt_iconPos = $this->getMegaMenuOption( $item->ID, 'iconPos' );
			$opt_caretDownPos = $this->getMegaMenuOption( $item->ID, 'caretDownPos' );
			
			
			
			if($depth == 0 && $opt_caretDownPos == 'left'){
				if($options['icon_mainmenu_parent'] != ''){
					$item_output .= "<i class='fa " . $options['icon_mainmenu_parent'] . "'></i>";
				} else {
					$item_output .= "<i class='fa fa-caret-down'></i>";
				}
			}
				if($depth == 1){
					if($options['icon_subchannel_item_left'] != ''){
						$item_output .= "<i class='fa " . $options['icon_subchannel_item_left'] . "'></i>";
					} else {
						$item_output.= '<i class="fa fa-plus"><!-- --></i>';
					}
				}
			if(!empty( $args->link_before)){
				$item_output.= $args->link_before;
			}
			// add menu icon
			if($opt_icon != '' && $opt_iconPos == 'left'){
				$title = "<i class='fa " . $opt_icon . "'></i>" . $title;
			} else if($opt_icon != '' && $opt_iconPos == 'right'){
				$title .= "<i class='fa " . $opt_icon . "'></i>";
			}
			
			//Text - Title
			$prepend='';
			$append='';
			$item_output.= $prepend . $title . $append;
			
			//Description
			$description ='';
			$item_output.= $description;
			
			//Link After
			if(!empty($args->link_after)){ 
				$item_output.= $args->link_after;
			}
			//Close Link or emulator
			if($depth == 0){
				if($opt_caretDownPos == 'right'){
					if($options['icon_mainmenu_parent'] != ''){
						$item_output .= "<i class='fa " . $options['icon_mainmenu_parent'] . "'></i>";
					} else {
						$item_output .= "<i class='fa fa-caret-down'></i>";
					}
				}
			} else if($depth == 1 && $args->parentMega == 'preview'){
				if($options['icon_subchannel_item_right'] != ''){
					$item_output .= "<i class='fa " . $options['icon_subchannel_item_right'] . "'></i>";
				} else {
					$item_output.= '<i class="fa fa-chevron-right"><!-- --></i>';
				}
			}
			
			if(!in_array("header",$classes)){
				$item_output.= '</a>';
			}
			
			//Append after Link
			if(!empty($args->after)){
				$item_output .= $args->after;
			}
		}
		$with_child ='';
		if (in_array("parent", $classes)){
			$with_child ='parent';	
		}
		if($depth == 1 && isset($args->parentMega) && $args->parentMega == 'columns'){
			$sidebar = $this->getMegaMenuOption( $item->ID, 'addSidebar' );
			if($sidebar != '0'){
				ob_start();
				dynamic_sidebar($sidebar);
				$html = ob_get_contents();
				ob_end_clean();
				$output .= '<li><ul class="list"><li class="header">' . $item->title . '</li><li class="cactus-widgets">'. $html .'</li></ul>';
			} else {				
				$output .= '';
			}
		} else {
			if((!isset($args->parentMega) || $args->parentMega == 'list') && $depth == 1){
				$output .= apply_filters( 'walker_nav_menu_start_el', '<li class="menu-item level'.($depth+1).' '.$with_child.''.$class_names.'">'.$item_output, $item, $depth, $args );
			} else 
				$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	function end_el(&$output, $item, $depth = 0, $args = array()) {
		$output .= "</li>";
	}
}