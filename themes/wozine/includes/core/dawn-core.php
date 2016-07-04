<?php
/**
 * Core features for all themes
 *
 * @package danwthemes
 * @version 1.0 - 02/07/2016
 */
if( ! class_exists('dawn_core') ){
	class dawn_core{
		public function __construct(){
			add_action('init', array(&$this, 'init'));
			
			//
		}
		
		public function init(){
			
		}
	}
	new dawn_core();
}