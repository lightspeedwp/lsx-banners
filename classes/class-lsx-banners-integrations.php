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
	 * Holds the plugins integration post types
	 *
	 * @var      array|Lsx_Banners
	 */
	public $post_types = array( 'tribe_events' );

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct( ) {
		add_filter( 'lsx_banner_allowed_post_types', array( $this, 'lsx_banner_allowed_post_types' ), 10, 1 );

		if ( false !== $this->post_types ) {
			foreach ( $this->post_types as $post_type ) {
				if ( function_exists( 'tour_operator' ) ) {
					add_action( 'lsx_to_framework_' . $post_type . '_tab_archive_settings_top', array( $this, 'enable_banners_setting' ), 20 );
				} else {
					add_action( 'lsx_framework_' . $post_type . '_tab_content_top', array( $this, 'enable_banners_setting' ), 20 );
				}
			}
		}
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

	public function enable_banners_setting( $tab = 'general' ) {
		if ( in_array( $tab, $this->post_types ) ) {
			?>
			<tr class="form-field">
				<th scope="row">
					<label for="banners_enabled"><?php esc_html_e( 'Enable Banners', 'lsx-banners' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if banners_enabled}} checked="checked" {{/if}} name="banners_enabled" />
				</td>
			</tr>
			<?php
		}
	}

	public function enable_banners( $enable, $post_type ) {
		print_r('hello');

		if ( in_array( $post_type, $this->post_types ) ) {

		}
		return $enable;
	}
}
