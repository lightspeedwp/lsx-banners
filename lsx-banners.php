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

if(!function_exists('CMB_Meta_Box')){
	require 'vendor/Custom-Meta-Boxes.php';
}

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
		add_filter( 'cmb_meta_boxes', array($this,'metaboxes') );	
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
	 * Define the metabox and field configurations.
	 *
	 * @param  array $meta_boxes
	 * @return array
	 */
	function metaboxes( array $meta_boxes ) {		
		
		// Example of all available fields
		$fields = array(
				array( 'id' => 'banner_image', 'name' => 'Image', 'type' => 'image', 'repeatable' => false, 'show_size' => true ),
				array( 'id' => 'banner_title',  'name' => 'Title', 'type' => 'text' ),
				array( 'id' => 'banner_subtitle',  'name' => 'Sub Title', 'type' => 'text' ),
				array( 'id' => 'banner_height',  'name' => 'Height', 'type' => 'text' ),
				array( 'id' => 'banner_x', 'name' => 'X Position', 'type' => 'select', 'options' => array( 'left' => 'Left', 'right' => 'Right', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
				array( 'id' => 'banner_y', 'name' => 'Y Position', 'type' => 'select', 'options' => array( 'top' => 'Top', 'bottom' => 'Bottom', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false )		
		);

		return $meta_boxes;
	}
}
$lst_banners = Lsx_Banners::get_instance();