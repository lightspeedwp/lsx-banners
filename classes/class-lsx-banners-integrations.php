<?php
/**
 * LSX Banners Plugin Integrations
 *
 * @package   LSX Banners
 * @author    LightSpeed
 * @license   GPL3
 * @link
 * @copyright 2016 LightSpeed
 */
class LSX_Banners_Integrations {

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct( ) {
		add_filter( 'lsx_banner_allowed_post_types', array( $this, 'lsx_banner_allowed_post_types' ), 10, 1 );
	}

	/**
	 * Enable project custom post type on LSX Banners.
	 */
	public function lsx_banner_allowed_post_types( $post_types ) {
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$post_types[] = 'tribe_events';
		}
		return $post_types;
	}
}
