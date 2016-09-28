<?php
/**
 * Backend actions for the LSX Banners Plugin
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
class LSX_Banners_Admin extends LSX_Banners {

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
}