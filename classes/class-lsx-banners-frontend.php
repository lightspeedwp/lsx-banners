<?php
/**
 * Frontend actions for the LSX Banners Plugin
 *
 * @package   LSX_Banners_Frontend
 * @author    LightSpeed
 * @license   GPL-2.0+
 * @link      
 * @copyright 2016 LightSpeedDevelopment
 */

/**
 * Main plugin class.
 *
 * @package LSX_Banners_Frontend
 * @author  LightSpeed
 */
class LSX_Banners_Frontend extends LSX_Banners {

	/**
	 * This holds the class OBJ of LSX_Template_Redirects
	 */
	public $redirects = false;	

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
	 * Initate some boolean flags
	 */
	public function wp_head() {		
	}
}