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
		$this->options = get_option('_lsx_settings',false);
		if ( false === $this->options ) {
			$this->options = get_option('_lsx_lsx-settings',false);
		}
		$this->set_vars();	

		add_action('admin_init',array($this,'admin_init'));
		add_filter('cmb_meta_boxes', array($this,'metaboxes') );
		add_filter('lsx_taxonomy_admin_taxonomies', array( $this, 'add_taxonomies' ),10,1 );	
		
		add_action( 'init', array( $this, 'create_settings_page' ), 200 );
		add_filter( 'lsx_framework_settings_tabs', array( $this, 'register_tabs' ), 200, 1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'assets') );		
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
	 * Enques the assets
	 */
	public function assets() {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			$min = '';
		 }else {
			$min = '.min';
		}

		//wp_enqueue_script( 'lsx-banners-admin', LSX_BANNERS_URL . 'assets/js/lsx-banners-admin' . $min . '.js', array( 'jquery' ), LSX_BANNERS_VER, true );
		wp_enqueue_style( 'lsx-banners-admin', LSX_BANNERS_URL . 'assets/css/lsx-banners-admin.css', array(), LSX_BANNERS_VER );

		if ( isset( $_GET['page'] ) && 'lsx-settings' === $_GET['page'] ) {
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}
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
			$fields[] = array( 'id' => 'banner_disabled',  'name' => esc_html__( 'Disable banner', 'lsx-banners' ), 'type' => 'checkbox' );
		}	
		if(true === $text_disable) {
			$fields[] = array( 'id' => 'banner_text_disabled',  'name' => esc_html__( 'Disable banner text', 'lsx-banners' ), 'type' => 'checkbox' );
		}
		if($title_enabled){
			$fields[] = array( 'id' => 'banner_title',  'name' => esc_html__( 'Title', 'lsx-banners' ), 'type' => 'text' );
		}
		if($subtitle_enabled){
			$fields[] = array( 'id' => 'banner_subtitle',  'name' => esc_html__( 'Tagline', 'lsx-banners' ), 'type' => 'text' );
		}		
		$fields[] = 
			array( 'id' => 'image_bg_group', 'name' => '', 'type' => 'group', 'cols' => 12, 'fields' => array(
					array( 'id' => 'banner_height',  'name' => esc_html__( 'Height', 'lsx-banners' ), 'type' => 'text' ),
					array( 'id' => 'banner_x', 'name' => esc_html__( 'X Position', 'lsx-banners' ), 'type' => 'select', 'options' => array( 'left' => esc_html__( 'Left', 'lsx-banners' ), 'right' => esc_html__( 'Right', 'lsx-banners' ), 'Center' => esc_html__( 'Center', 'lsx-banners' ) ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
					array( 'id' => 'banner_y', 'name' => esc_html__( 'Y Position', 'lsx-banners' ), 'type' => 'select', 'options' => array( 'top' => esc_html__( 'Top', 'lsx-banners' ), 'bottom' => esc_html__( 'Bottom', 'lsx-banners' ), 'Center' => esc_html__( 'Center', 'lsx-banners' ) ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
			) );		
		$fields[] = 
			array( 'id' => 'image_group', 'name' => '', 'type' => 'group', 'cols' => 12, 'fields' => array(
					array( 'id' => 'banner_image', 'name' => esc_html__( 'Image', 'lsx-banners' ), 'type' => 'image', 'repeatable' => true, 'show_size' => false, 'size' => array(185,130))
			) );

		$fields[] = array( 'id' => 'banner_video',  'name' => esc_html__('Video (mp4)','lsx-banners'), 'type' => 'file' );			

		// Envira Gallery
		if ( class_exists( 'Envira_Gallery' ) && !class_exists('Tour_Operator')) {
			$fields[] = array( 'id' => 'envira_gallery', 'name' => esc_html__( 'Envira Gallery', 'lsx-banners' ), 'type' => 'post_select', 'use_ajax' => false, 'query' => array( 'post_type' => 'envira', 'nopagin' => true, 'posts_per_page' => '-1', 'orderby' => 'title', 'order' => 'ASC' ) , 'allow_none' => true );
		}

		// Soliloquy
		if ( class_exists( 'Soliloquy' ) ) {
			$fields[] = array( 'id' => 'soliloquy_slider', 'name' => esc_html__( 'Soliloquy Slider', 'lsx-banners' ), 'type' => 'post_select', 'use_ajax' => false, 'query' => array( 'post_type' => 'soliloquy', 'nopagin' => true, 'posts_per_page' => '-1', 'orderby' => 'title', 'order' => 'ASC' ) , 'allow_none' => true );
		}
		
		$meta_boxes[] = array(
				'title' => esc_html__( 'Banners', 'lsx-banners' ),
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
			<th scope="row"><label for="banner"><?php esc_html_e('Banner','lsx-banners');?></label></th>
			<td>
				<input style="display:none;" name="banner" id="banner" type="text" value="<?php echo $value; ?>" size="40" aria-required="true">
				<div class="banner-preview">
					<?php echo $image_preview; ?>
				</div>				

				<a style="<?php if('' !== $value && false !== $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-add"><?php esc_html_e('Choose Image','lsx-banners');?></a>				
				<a style="<?php if('' === $value || false === $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-remove"><?php esc_html_e('Remove Image','lsx-banners');?></a>
			</td>
		</tr>
		
		<script>
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
		<h2><?php esc_html_e('Cover Image','lsx-banners');?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="banner"><?php esc_html_e('Banner','lsx-banners');?></label></th>
					<td>
						<input style="display:none;" name="banner" id="banner" type="text" value="<?php echo $value; ?>" size="40" aria-required="true">
						<div class="banner-preview">
							<?php echo $image_preview; ?>
						</div>				

						<a style="<?php if('' !== $value && false !== $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-add"><?php esc_html_e('Choose Image','lsx-banners');?></a>				
						<a style="<?php if('' === $value || false === $value) { ?>display:none;<?php } ?>" class="button-secondary lsx-banner-image-remove"><?php esc_html_e('Remove Image','lsx-banners');?></a>
					</td>
				</tr>
			</tbody>
		</table>		
		
		<script type="text/javascript">
			(function( $ ) {
				$( '.lsx-banner-image-add' ).on( 'click', function() {
					tb_show('<?php esc_html_e( 'Choose a Banner', 'lsx-banners' ); ?>', 'media-upload.php?type=image&TB_iframe=1');
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
	 * Adds in the settings neccesary for the archives
	 *
	 * @return null
	 */
	public function archive_settings() {
		?>
		<tr class="form-field banner-wrap">
			<th scope="row">
				<label for="banner"> <?php esc_html_e( 'Banner Image', 'lsx-banners' ); ?></label>
			</th>
			<td>
				<input type="hidden" {{#if banner_id}} value="{{banner_id}}" {{/if}} name="banner_id" />
				<input type="hidden" {{#if banner}} value="{{banner}}" {{/if}} name="banner" />
				<div class="thumbnail-preview">
					{{#if banner}}<img src="{{banner}}" width="150" height="150" />{{/if}}
				</div>
				<a {{#if banner}}style="display:none;"{{/if}} class="button-secondary lsx-thumbnail-image-add" data-slug="banner"><?php esc_html_e( 'Choose Image', 'lsx-banners' ); ?></a>
				<a {{#unless banner}}style="display:none;"{{/unless}} class="button-secondary lsx-thumbnail-image-delete" data-slug="banner"><?php esc_html_e( 'Delete', 'lsx-banners' ); ?></a>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row">
				<label for="banner_video"><?php esc_html_e( 'Banner Video URL (mp4)', 'lsx-banners' ); ?></label>
			</th>
			<td>
				<input type="text" {{#if banner_video}} value="{{banner_video}}" {{/if}} name="banner_video" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row">
				<label for="tagline"> <?php esc_html_e( 'Tagline', 'lsx-banners' ); ?></label>
			</th>
			<td>
				<input type="text" {{#if tagline}} value="{{tagline}}" {{/if}} name="tagline" />
			</td>
		</tr>
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

	/**
	 * Returns the array of settings to the UIX Class
	 */
	public function create_settings_page() {
		if ( is_admin() ) {
			if ( ! class_exists( '\lsx\ui\uix' ) && ! class_exists( 'Tour_Operator' ) ) {
				include_once LSX_BANNERS_PATH . 'vendor/uix/uix.php';
				$pages = $this->settings_page_array();
				$uix = \lsx\ui\uix::get_instance( 'lsx' );
				$uix->register_pages( $pages );
			}

			$post_types = $this->get_allowed_post_types();
			if ( false !== $post_types ) {
				foreach ( $post_types as $post_type ) {
					if ( class_exists( 'Tour_Operator' ) ) {
						add_action( 'to_framework_' . $post_type . '_tab_archive_settings_top', array( $this, 'archive_settings' ), 20 );
						add_action( 'to_framework_dashboard_tab_bottom', array( $this, 'settings_scripts' ), 200 );
					} else {
						add_action( 'lsx_framework_' . $post_type . '_tab_content_top', array( $this, 'archive_settings' ), 20 );
						add_action( 'lsx_framework_dashboard_tab_bottom', array( $this, 'settings_scripts' ), 200 );
					}
				}	
			}
		}
	}	

	/**
	 * Returns the array of settings to the UIX Class
	 */
	public function settings_page_array() {
		$tabs = apply_filters( 'lsx_framework_settings_tabs', array() );
		
		return array(
			'settings'  => array(
				'page_title'  =>  esc_html__( 'Theme Options', 'lsx-banners' ),
				'menu_title'  =>  esc_html__( 'Theme Options', 'lsx-banners' ),
				'capability'  =>  'manage_options',
				'icon'        =>  'dashicons-book-alt',
				'parent'      =>  'themes.php',
				'save_button' =>  esc_html__( 'Save Changes', 'lsx-banners' ),
				'tabs'        =>  $tabs,
			),
		);
	}

	/**
	 * Register tabs
	 */	
	public function register_tabs( $tabs ) {
		$default = true;

		if ( false !== $tabs && is_array( $tabs ) && count( $tabs ) > 0 ) {
			$default = false;
		}

		if ( ! array_key_exists( 'display', $tabs ) ) {
			$tabs['display'] = array(
				'page_title'        => '',
				'page_description'  => '',
				'menu_title'        => esc_html__( 'Display', 'lsx-currencies' ),
				'template'          => LSX_CURRENCY_PATH . 'includes/settings/display.php',
				'default'           => $default
			);

			$default = false;
		}

		if ( ! array_key_exists( 'api', $tabs ) ) {
			$tabs['api'] = array(
				'page_title'        => '',
				'page_description'  => '',
				'menu_title'        => esc_html__( 'API', 'lsx-currencies' ),
				'template'          => LSX_CURRENCY_PATH . 'includes/settings/api.php',
				'default'           => $default
			);

			$default = false;
		}

		$post_types = $this->get_allowed_post_types();

		if ( false !== $post_types && ! empty( $post_types ) ) {
			foreach( $post_types as $index ) {
				if ( ! array_key_exists( $index, $tabs ) && ! in_array( $index, array( 'page' ) ) ) {
					$tabs[$index] = array(
						'page_title'        => esc_html__( 'Placeholders', 'lsx-banners' ),
						'page_description'  => '',
						'menu_title'        => ucwords( str_replace( '-', ' ', $index ) ),
						'template'          => LSX_BANNERS_PATH . 'includes/settings/placeholder.php',
						'default'	 		=> $default
					);

					$default = false;
				}
			}
		}

		return $tabs;
	}

	/**
	 * Allows the settings pages to upload images
	 */
	public function settings_scripts() {
		?>
		<script>
			jQuery(function($) {
				$('.lsx-thumbnail-image-add').on('click', function() {
					var slug = $(this).attr('data-slug');
					tb_show('<?php esc_html_e( 'Choose a Featured Image', 'lsx-banners' ); ?>', 'media-upload.php?type=image&TB_iframe=1');
					var image_thumbnail = '';
					var thisObj = $(this);
					window.send_to_editor = function(html) {
						var image_thumbnail = $('img', html).html();
						$(thisObj).parent('td').find('.thumbnail-preview' ).append('<img width="150" height="150" src="'+$( 'img',html ).attr( 'src' )+'" />');
						$(thisObj).parent('td').find('input[name="'+slug+'"]').val($( 'img',html ).attr( 'src' ));
						var imgClasses = $('img', html).attr('class');
						imgClasses = imgClasses.split('wp-image-');
						$(thisObj).parent('td').find('input[name="'+slug+'_id"]').val(imgClasses[1]);
						$(thisObj).hide();
						$(thisObj).parent('td').find('.lsx-thumbnail-image-delete' ).show();
						tb_remove();
						$( this ).hide();
					}
					return false;
				});
				$('.lsx-thumbnail-image-delete').on('click', function() {
					var slug = $(this).attr('data-slug');
					$( this ).parent('td').find('input[name="'+slug+'_id"]').val('');
					$( this ).parent('td').find('input[name="'+slug+'"]').val('');
					$( this ).parent('td').find('.thumbnail-preview' ).html('');
					$( this ).hide();
					$( this ).parent('td').find('.lsx-thumbnail-image-add' ).show();
				});
				jQuery(document).on('click', '.ui-tab-nav a', function(event) {
					event.preventDefault();
					jQuery('.ui-tab-nav a.active').removeClass('active');
					jQuery(this).addClass('active');
					jQuery('.ui-tab.active').removeClass('active');
					jQuery(jQuery(this).attr('href')).addClass('active');
				});
			});
		</script>
		<?php
	}

}