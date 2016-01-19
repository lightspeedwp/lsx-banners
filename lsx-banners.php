<?php
/*
Plugin Name: LSX Banners
Plugin URI: {add_in}
Description: A full width responsive banner solution. Compatabile with LSX and StoreFront themes
Author: Warwick
Author URI: http://wordpress.org/
Version: 1.0
Text Domain: lsx-banners
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//require 'inc/template-tags.php';

/**
 * Main plugin class.
 *
 * @package Lsx_Banners
 * @author  Warwick
 */

class Lsx_Banners {
	
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
		//Enqueue the scrips
		add_action( 'wp_enqueue_scripts', array($this,'scripts') ,100 );
	}
	
	/**
	 * Return an instance of this class.
	 * 
	 * @return    object|Lsx_Restrict_Access    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Enqueue scripts and styles.
	 *
	 */
	function scripts() {
		if(!is_user_logged_in()){
			wp_enqueue_style('lsx_login_style', plugin_dir_url(__FILE__) . 'assets/css/lsx-login.css');
			wp_enqueue_script('lsx_login_script', plugin_dir_url(__FILE__) . 'assets/js/lsx-login.js', array('jquery'), null, false);
			$param_array = array(
					'ajax_url' 			=> admin_url('admin-ajax.php'),
					'empty_username'	=> __('The username field is empty.','lsx-login'),
					'empty_password'	=> __('The password field is empty.','lsx-login'),
					'empty_reset'		=> __('Enter a username or e-mail address.','lsx-login'),
					'no_match'		=> __('Passwords do not match','lsx-login'),
					'ajax_spinner'		=> plugin_dir_url( __FILE__ ) . "assets/images/ajax-spinner.gif"
			);
			wp_localize_script( 'lsx_login_script', 'lsx_login_params', $param_array );
		}		
	}
}
$lst_banners = Lsx_Banners::get_instance();