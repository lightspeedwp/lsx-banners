<?php
/**
 * Plugin Pages Structures
 *
 * @package   LSX Banners
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */


// This array is for the Admin Pages. each element defines a page that is seen in the admin
return array(
	'lsx-general' 	  => array(                                                         // this is the settings array. The key is the page slug
		'page_title'  =>  'Banners',                                                  // title of the page
		'menu_title'  =>  'Banners',                                                  // title seen on the menu link
		'capability'  =>  'manage_options',                                              // required capability to access page
		'icon'        =>  'dashicons-book-alt',                                          // Icon or image to be used on admin menu
		'parent'      =>  'themes.php',                                         // Position priority on admin menu)
		'save_button' =>  'Save Changes',                                                // If the page required saving settings, Set the text here.
		'scripts'	  => array(
			'picker' => LSX_BANNERS_URL . 'includes/templates/image-picker.js'
		),
		'tabs'        =>  array(                                                        // tabs array are for setting the tab / section templates
			// each array element is a tab with the key as the slug that will be the saved object property
			'general'		=> array(
				'page_title'        => 'Default Banner',                                  // the tab page title 
				'page_description'  => 'Setup Default Banner for LSX Banners',                   // the tab description
				'menu_title'        => 'Default',                                    // the title of the tab menu item
				'template'          => 'includes/templates/general.php',           // the template to define the tab content and values
				'default'	 => true                                                 // defines which is the default tab
			)
		),
		'help'	=> array(	// the wordpress contextual help is also included
			// key is the help slug
			'default-help' => array(
				'title'		=> 	esc_html__( 'Easy to add Help' , 'uix' ),
				'content'	=>	"Just add more items to this array with a unique slug/key."
			),
			'more-help' => array(
				'title'		=> 	esc_html__( 'Makes things Easy' , 'uix' ),
				'content'	=>	"the content can also be a file path to a template"
			)
		),
	),
);