<?php
/*
Plugin Name: LSX Banners
Plugin URI: {add_in}
Description: A full width responsive banner solution. Compatabile with LSX, Storefront and Sage themes
Author: Warwick
Author URI: http://wordpress.org/
Version: 1.1
Text Domain: lsx-banners
Tags: LSX, Storefront, Sage
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if(!function_exists('CMB_Meta_Box')){
	require 'vendor/Custom-Meta-Boxes/custom-meta-boxes.php';
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
	 * Holds class instance
	 *
	 * @var      string|Lsx_Banners
	 */
	public $theme = null;

	
	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {	
		//Enqueue the scrips
		add_filter( 'cmb_meta_boxes', array($this,'metaboxes') );	
		
		add_filter('body_class', array($this,'body_class'));

		add_action('init',array($this,'init'));	
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
	 * Initializes the variables we need.
	 *
	 */
	function init() {
		//$theme = wp_get_theme();
		if(defined('LSX_VERSION')){
			$this->theme = 'lsx';
			remove_action( 'lsx_header_after', 'lsx_page_banner' );
			add_action('lsx_header_after',array($this,'banner'));
		}elseif(function_exists('storefront_setup')){
			$this->theme = 'storefront';
			add_action('storefront_before_content',array($this,'banner'));
		}else{
			$this->theme = 'other';
		}
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
				array( 'id' => 'image_group', 'name' => '', 'type' => 'group', 'cols' => 4, 'fields' => array(
						array( 'id' => 'banner_image', 'name' => 'Image', 'type' => 'image', 'repeatable' => false, 'show_size' => false, 'size' => array(350,200))
				) ),
				array( 'id' => 'image_bg_group', 'name' => '', 'type' => 'group', 'cols' => 8, 'fields' => array(
						array( 'id' => 'banner_height',  'name' => 'Height', 'type' => 'text' ),
						array( 'id' => 'banner_x', 'name' => 'X Position', 'type' => 'select', 'options' => array( 'left' => 'Left', 'right' => 'Right', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
						array( 'id' => 'banner_y', 'name' => 'Y Position', 'type' => 'select', 'options' => array( 'top' => 'Top', 'bottom' => 'Bottom', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
				) ),				
				/*array( 'id' => 'banner_title',  'name' => 'Title', 'type' => 'text' ),
				array( 'id' => 'banner_subtitle',  'name' => 'Sub Title', 'type' => 'text' ),*/

		);
		
		$meta_boxes[] = array(
				'title' => 'Banner',
				'pages' => array('post','page'),
				'fields' => $fields
		);			

		return $meta_boxes;
	}
	
	
	function banner(){ 
		$img_group = get_post_meta(get_the_ID(),'image_group',true);
		
		$banner_image = false;
		if(false !== $img_group && is_array($img_group) && isset($img_group['banner_image']) && '' !== $img_group['banner_image']){
			$banner_image_id = $img_group['banner_image'];
	        $banner_image = wp_get_attachment_image_src($banner_image_id,'full');
	        $banner_image = $banner_image[0];
		}
		
		if('lsx' === $this->theme && false === $banner_image && has_post_thumbnail()){
			$banner_image = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()),'full');
			$banner_image = $banner_image[0];			
		}
		
		$image_bg_group = get_post_meta(get_the_ID(),'image_bg_group',true);
		if(false !== $image_bg_group && is_array($image_bg_group)){
			
			$size = 'cover';
			if(isset($image_bg_group['banner_height'])){
				$size = $image_bg_group['banner_height'];
			}
			$x_position = 'center';
			if(isset($image_bg_group['banner_x'])){
				$x_position = $image_bg_group['banner_x'];
			}			
			$y_position = 'center';
			if(isset($image_bg_group['banner_y'])){
				$y_position = $image_bg_group['banner_y'];
			}
		}
		
		if(false !== $banner_image){
		?>
			<div class="page-banner" style="background-position: <?php echo $x_position; ?> <?php echo $y_position; ?>; background-image:url(<?php echo $banner_image; ?>); background-size:<?php echo $size; ?>;">
	        	<div class="container">
		            <header class="page-header">
		            	<h1 class="page-title"><?php the_title(); ?></h1> 
		            	<?php echo $this->banner_content(); ?>
		            </header><!-- .entry-header -->
		        </div>
	        </div>		
	<?php 
		}
	}
	
	/**
	 * Add <body> classes
	 */
	function body_class($classes) {
		// Add page slug if it doesn't exist
		
				

		$banner_image = false;
		$img_group = get_post_meta(get_the_ID(),'image_group',true);
		
		if(false !== $img_group && is_array($img_group) && isset($img_group['banner_image']) && '' !== $img_group['banner_image']){
			$classes[] = 'has-banner';
		}	
		return $classes;
	}
	
	/**
	 * Outputs the banner content, usually a short tagline.
	 */
	function banner_content() {
		switch($this->theme){
			case 'lsx':
				ob_start();
				lsx_banner_content();
				$retval = ob_get_clean();
			break;
			
			default:
				$retval = '';
			break;
		}	
	}
	
}
$lst_banners = Lsx_Banners::get_instance();