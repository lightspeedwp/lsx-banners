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

		add_action('admin_init',array($this,'admin_init'));
		add_filter( 'cmb_meta_boxes', array($this,'metaboxes') );
		add_filter('lsx_taxonomy_admin_taxonomies', array( $this, 'add_taxonomies' ),10,1 );	
	}

	/**
	 * Initializes the variables we need.
	 *
	 */
	public function admin_init() {

		$this->taxonomy_admin = new LSX_Taxonomy_Admin();

		$allowed_taxonomies = $this->get_allowed_taxonomies();

		if(is_array($allowed_taxonomies)){
			foreach($allowed_taxonomies as $taxonomy){
				//add_action( "{$taxonomy}_add_form_fields",  array( $this, 'add_form_field'  ),1 );
				add_action( "{$taxonomy}_edit_form_fields", array( $this, 'add_form_field' ),1,1 );
			}
		}
		add_action( 'create_term', array( $this, 'save_meta' ), 10, 2 );
		add_action( 'edit_term',   array( $this, 'save_meta' ), 10, 2 );

		add_action( 'show_user_profile', array( $this, 'user_profile_fields' ), 1);
		add_action( 'edit_user_profile', array( $this, 'user_profile_fields' ), 1);
		add_action( 'personal_options_update', array( $this, 'save_profile_fields' ));
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ));		
	}

	/**
	 * Output the form field for this metadata when adding a new term
	 *
	 * @since 0.1.0
	 */
	public function add_taxonomies($taxonomies) {

		$allowed_taxonomies = $this->get_allowed_taxonomies();
		if(false !== $taxonomies && is_array($taxonomies)){
			$taxonomies = array_merge($taxonomies,$allowed_taxonomies);
		}else{
			$taxonomies = $allowed_taxonomies;
		}
		return $taxonomies;
	}		

	/**
	 * Define the metabox and field configurations.
	 *
	 * @param  array $meta_boxes
	 * @return array
	 */
	public function metaboxes( array $meta_boxes ) {		
		
		// allowed post types 
		$allowed_post_types = $this->get_allowed_post_types();
		
		//Allowed Meta_boxes
		$title_enabled = apply_filters('lsx_banner_enable_title', false);
		$subtitle_enabled = apply_filters('lsx_banner_enable_subtitle', false);

		//If you only want to be able to disable content per banner
		$text_disable = apply_filters('lsx_banner_disable_text', false);
		
		//This runs twice in the plugin,  this is the only time it runs in the backend.
		$this->placeholder = apply_filters('lsx_banner_enable_placeholder', false);
		$fields = array();
		

		//Create the Field array

		if(true === $this->placeholder) {
			$fields[] = array( 'id' => 'banner_disabled',  'name' => 'Disable banner', 'type' => 'checkbox' );
		}	
		if(true === $text_disable) {
			$fields[] = array( 'id' => 'banner_text_disabled',  'name' => 'Disable banner text', 'type' => 'checkbox' );
		}
		if($title_enabled){
			$fields[] = array( 'id' => 'banner_title',  'name' => 'Title', 'type' => 'text' );
		}
		if($subtitle_enabled){
			$fields[] = array( 'id' => 'banner_subtitle',  'name' => 'Tagline', 'type' => 'text' );
		}		
		$fields[] = 
			array( 'id' => 'image_bg_group', 'name' => '', 'type' => 'group', 'cols' => 12, 'fields' => array(
					array( 'id' => 'banner_height',  'name' => 'Height', 'type' => 'text' ),
					array( 'id' => 'banner_x', 'name' => 'X Position', 'type' => 'select', 'options' => array( 'left' => 'Left', 'right' => 'Right', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
					array( 'id' => 'banner_y', 'name' => 'Y Position', 'type' => 'select', 'options' => array( 'top' => 'Top', 'bottom' => 'Bottom', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
			) );		
		$fields[] = 
			array( 'id' => 'image_group', 'name' => '', 'type' => 'group', 'cols' => 12, 'fields' => array(
					array( 'id' => 'banner_image', 'name' => 'Image', 'type' => 'image', 'repeatable' => true, 'show_size' => false, 'size' => array(185,130))
			) );


		$meta_boxes[] = array(
				'title' => 'Banners',
				'pages' => $allowed_post_types,
				'fields' => $fields
		);
		return $meta_boxes;
	}

	/**
	 * Output the form field for this metadata when adding a new term
	 *
	 * @since 0.1.0
	 */
	public function add_form_field($term = false) {

		if(apply_filters('lsx_banners_disable_taxonomy_field',false)){
			return true;
		}

		if(is_object($term)){
			$value = get_term_meta( $term->term_id, 'banner', true );
			$image_preview = wp_get_attachment_image_src($value,'thumbnail');
			if(is_array($image_preview)){
				$image_preview = '<img src="'.$image_preview[0].'" width="'.$image_preview[1].'" height="'.$image_preview[2].'" class="alignnone size-thumbnail wp-image-'.$value.'" />';
			}
		}else{
			$image_preview = false;
			$value = false;
		}
		?>
		<tr class="form-field form-required term-banner-wrap">
			<th scope="row"><label for="banner"><?php _e('Banner','lsx-banners');?></label></th>
			<td>
				<input style="display:none;" name="banner" id="banner" type="text" value="<?php echo $value; ?>" size="40" aria-required="true">
				<div class="banner-preview">
					<?php echo $image_preview; ?>
				</div>				

				<a style="<?php if('' !== $value && false !== $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-add"><?php _e('Choose Image','lsx-banners');?></a>				
				<a style="<?php if('' === $value || false === $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-remove"><?php _e('Remove Image','lsx-banners');?></a>
			</td>
		</tr>
		
		<script type="text/javascript">
			(function( $ ) {
				$( '.lsx-banner-image-add' ).on( 'click', function() {
					tb_show('Choose a Banner', 'media-upload.php?type=image&TB_iframe=1');
					var image_thumbnail = '';
					window.send_to_editor = function( html ) 
					{
						var image_thumbnail = $(html).find('img').length > 0 ? $('img',html) : $(html);
						$( '.banner-preview' ).append(html);
						var imgClasses = image_thumbnail.attr( 'class' );
						imgClasses = imgClasses.split('wp-image-');
						$( '#banner' ).val(imgClasses[1]);
						tb_remove();
					}
					$( this ).hide();
					$( '.lsx-banner-image-remove' ).show();
					
					return false;
				});

				$( '.lsx-banner-image-remove' ).on( 'click', function() {
					$( '.banner-preview' ).html('');
					$( '#banner' ).val('');
					$( this ).hide();
					$( '.lsx-banner-image-add' ).show();					
					return false;
				});	
			})(jQuery);
		</script>		
		<?php
	}
	
	/**
	 * Saves the Taxnomy term banner image
	 *
	 * @since 0.1.0
	 *
	 * @param  int     $term_id
	 * @param  string  $taxonomy
	 */
	public function save_meta( $term_id = 0, $taxonomy = '' ) {
		// Bail if not updating meta_key
		$meta = ! empty( $_POST[ 'banner' ] ) ? $_POST[ 'banner' ]	: '';
		if ( empty( $meta ) ) {
			delete_term_meta( $term_id, 'banner' );
		} else {
			update_term_meta( $term_id, 'banner', $meta );
		}
	}


	/**
	 * Displays the user banner field
	 *
	 * @since 0.1.0
	 */
	public function user_profile_fields( $user ) {
		if(is_object($user)){
			$value = get_user_meta( $user->ID, 'banner', true );
			$image_preview = wp_get_attachment_image_src($value,'thumbnail');
			if(is_array($image_preview)){
				$image_preview = '<img src="'.$image_preview[0].'" width="'.$image_preview[1].'" height="'.$image_preview[2].'" class="alignnone size-thumbnail wp-image-'.$value.'" />';
			}
		}else{
			$image_preview = false;
			$value = false;
		}
		?>
		<h2><?php _e('Cover Image','lsx-banners');?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="banner"><?php _e('Banner','lsx-banners');?></label></th>
					<td>
						<input style="display:none;" name="banner" id="banner" type="text" value="<?php echo $value; ?>" size="40" aria-required="true">
						<div class="banner-preview">
							<?php echo $image_preview; ?>
						</div>				

						<a style="<?php if('' !== $value && false !== $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-add"><?php _e('Choose Image','lsx-banners');?></a>				
						<a style="<?php if('' === $value || false === $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-remove"><?php _e('Remove Image','lsx-banners');?></a>
					</td>
				</tr>
			</tbody>
		</table>		
		
		<script type="text/javascript">
			(function( $ ) {
				$( '.lsx-banner-image-add' ).on( 'click', function() {
					tb_show('Choose a Banner', 'media-upload.php?type=image&TB_iframe=1');
					var image_thumbnail = '';
					window.send_to_editor = function( html ) 
					{
						var image_thumbnail = $(html).find('img').length > 0 ? $('img',html) : $(html);
						$( '.banner-preview' ).append(html);
						var imgClasses = image_thumbnail.attr( 'class' );
						imgClasses = imgClasses.split('wp-image-');
						$( '#banner' ).val(imgClasses[1]);
						tb_remove();
					}
					$( this ).hide();
					$( '.lsx-banner-image-remove' ).show();
					
					return false;
				});

				$( '.lsx-banner-image-remove' ).on( 'click', function() {
					$( '.banner-preview' ).html('');
					$( '#banner' ).val('');
					$( this ).hide();
					$( '.lsx-banner-image-add' ).show();					
					return false;
				});	
			})(jQuery);
		</script>
		<?php 	
	}


	/**
	 * Saves the Taxnomy term banner image
	 *
	 * @since 0.1.0
	 *
	 * @param  int     $term_id
	 * @param  string  $taxonomy
	 */
	public function save_profile_fields( $user_id ) {
		// Bail if not updating meta_key
		$meta = ! empty( $_POST[ 'banner' ] ) ? $_POST[ 'banner' ]	: '';
		if ( empty( $meta ) ) {
			delete_user_meta( $user_id, 'banner' );
		} else {
			update_user_meta( $user_id, 'banner', $meta );
		}
	}		
}