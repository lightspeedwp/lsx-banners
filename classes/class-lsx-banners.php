<?php
/**
 * Main Class for the LSX Banners Plugin
 *
 * @package   LSX_Banners_Admin
 * @author    LightSpeed
 * @license   GPL-3+
 * @link      
 * @copyright 2016 LightSpeedDevelopment
 */

/**
 * Main plugin class.
 *
 * @package LSX_Banners_Admin
 * @author  LightSpeed
 */
class LSX_Banners {

	/**
	 * Holds class instance
	 *
	 * @var      object|Lsx_Banners
	 */
	protected static $instance = null;
	
	/**
	 * Holds the name of the theme
	 *
	 * @var      string|Lsx_Banners
	 */
	public $theme = null;
	
	/**
	 * Holds a boolean weather or not to use placeholdit.
	 *
	 * @var      string|Lsx_Banners
	 */
	public $placeholder = false;	
	
	/**
	 * Holds the current objects ID
	 *
	 * @var      string|Lsx_Banners
	 */
	public $post_id = false;
	
	/**
	 * Holds the current banner ID
	 *
	 * @var      string|Lsx_Banners
	 */
	public $banner_id = false;	
	
	/**
	 * Runs on the body_class, to let you know if there is a banner or not.
	 *
	 * @var      string|Lsx_Banners
	 */
	public $has_banner = false;		

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->options = get_option('_lsx_lsx-settings',false);	
		$this->set_vars();	
	}

	/**
	 * Return an instance of this class.
	 * 
	 * @return    object
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}	
}