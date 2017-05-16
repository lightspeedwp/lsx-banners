<?php
/*
 * Plugin Name: LSX Banners
 * Plugin URI:  https://www.lsdev.biz/product/lsx-banners
 * Description: The LSX Banners extension adds advanced banner configuration options to your WordPress site running LSX theme.
 * Version:     1.1.0
 * Author:      LightSpeed
 * Author URI:  https://www.lsdev.biz/
 * License:     GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: lsx-banners
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('LSX_BANNERS_PATH',  plugin_dir_path( __FILE__ ) );
define('LSX_BANNERS_CORE',  __FILE__ );
define('LSX_BANNERS_URL',  plugin_dir_url( __FILE__ ) );
define('LSX_BANNERS_VER',  '1.1.0' );

if(!function_exists('cmb_init')){
	if (is_file(LSX_BANNERS_PATH.'vendor/Custom-Meta-Boxes/custom-meta-boxes.php')) {
		require LSX_BANNERS_PATH.'vendor/Custom-Meta-Boxes/custom-meta-boxes.php';
	}
}

/* ======================= The API Classes ========================= */

if(!class_exists('LSX_API_Manager')){
	require_once('classes/class-lsx-api-manager.php');
}

/**
 * Runs once when the plugin is activated.
 */
function lsx_banners_activate_plugin() {
    $lsx_to_password = get_option('lsx_api_instance',false);
    if(false === $lsx_to_password){
    	update_option('lsx_api_instance',LSX_API_Manager::generatePassword());
    }
}
register_activation_hook( __FILE__, 'lsx_banners_activate_plugin' );

/**
 *	Grabs the email and api key from the LSX Search Settings.
 */
function lsx_banners_options_pages_filter($pages){
	$pages[] = 'lsx-settings';
	$pages[] = 'lsx-to-settings';
	return $pages;
}
add_filter('lsx_api_manager_options_pages','lsx_banners_options_pages_filter',10,1);

function lsx_banners_api_admin_init(){
	global $lsx_banners_api_manager;

	if(class_exists('Tour_Operator')) {
		$options = get_option('_lsx-to_settings', false);
	}else{
		$options = get_option('_lsx_settings', false);
		if (false === $options) {
			$options = get_option('_lsx_lsx-settings', false);
		}
	}

	$data = array('api_key'=>'','email'=>'');

	if(false !== $options && isset($options['api'])){
		if(isset($options['api']['lsx-banners_api_key']) && '' !== $options['api']['lsx-banners_api_key']){
			$data['api_key'] = $options['api']['lsx-banners_api_key'];
		}
		if(isset($options['api']['lsx-banners_email']) && '' !== $options['api']['lsx-banners_email']){
			$data['email'] = $options['api']['lsx-banners_email'];
		}
	}

	$instance = get_option( 'lsx_api_instance', false );
	if(false === $instance){
		$instance = LSX_API_Manager::generatePassword();
	}

	$api_array = array(
		'product_id'	=>		'LSX Banners',
		'version'		=>		'1.1.0',
		'instance'		=>		$instance,
		'email'			=>		$data['email'],
		'api_key'		=>		$data['api_key'],
		'file'			=>		'lsx-banners.php'
	);
	$lsx_banners_api_manager = new LSX_API_Manager($api_array);
}
add_action('admin_init','lsx_banners_api_admin_init');

require_once( LSX_BANNERS_PATH . 'classes/class-lsx-banners.php' );
$lsx_banners = new LSX_Banners();

/* ======================= Deprecated Plugin Class ========================= */

class Lsx_Banners_old {

	/**
	 * Holds class instance
	 *
	 * @var      object|Lsx_Banners
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {
		global $lsx_banners;
		if(null === $lsx_banners){
			$lsx_banners = LSX_Banners::get_instance();
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
}
