<?php
/*
Plugin Name: DawnThemes Core
Plugin URI: http://dawnthemes.com/
Description: DawnThemes Core Plugin for WOZINE theme
Version: 1.0.0
Author: DawnThemes Team
Author URI: http://dawnthemes.com/
Text Domain: dawnthemes
*/
if ( ! defined( 'ABSPATH' ) ) die( '-1' );

if(!defined('DAWN_CORE_VERSION'))
	define('DAWN_CORE_VERSION', '1.0.0');

if(!defined('DAWN_CORE_URL'))
	define('DAWN_CORE_URL',untrailingslashit( plugins_url( '/', __FILE__ ) ));

if(!defined('DAWN_CORE_DIR'))
	define('DAWN_CORE_DIR',untrailingslashit( plugin_dir_path( __FILE__ ) ));

class DawnThemesCore {
	
	public function __construct(){
		add_action('dawn_theme_includes', array($this,'includes'));
	}
	
	public function includes(){
		include_once (DAWN_CORE_DIR.'/includes/init.php');
	}
}
new DawnThemesCore();