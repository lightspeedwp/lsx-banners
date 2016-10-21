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
		add_filter( 'lsx_framework_settings_tabs', array( $this, 'register_additional_tabs'),100,1 );
		add_action( 'lsx_framework_dashboard_tab_bottom', array( $this, 'settings_page_scripts' ), 100 );
		add_action( 'init', array( $this, 'create_settings_page'),100 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts') );		
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
	function admin_scripts() {
		if(isset($_GET['page']) && 'lsx-lsx-settings' === $_GET['page']){
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

		$fields[] = array( 'id' => 'banner_video',  'name' => __('Video (mp4)','lsx-banners'), 'type' => 'file' );			

		// Envira Gallery
		if ( class_exists( 'Envira_Gallery' ) && !class_exists('Tour_Operator')) {
			$fields[] = array( 'id' => 'envira_gallery', 'name' => __( 'Envira Gallery', 'lsx-banners' ), 'type' => 'post_select', 'use_ajax' => false, 'query' => array( 'post_type' => 'envira', 'nopagin' => true, 'posts_per_page' => '-1', 'orderby' => 'title', 'order' => 'ASC' ) , 'allow_none' => true );
		}

		// Soliloquy
		if ( class_exists( 'Soliloquy' ) ) {
			$fields[] = array( 'id' => 'soliloquy_slider', 'name' => __( 'Soliloquy Slider', 'lsx-banners' ), 'type' => 'post_select', 'use_ajax' => false, 'query' => array( 'post_type' => 'soliloquy', 'nopagin' => true, 'posts_per_page' => '-1', 'orderby' => 'title', 'order' => 'ASC' ) , 'allow_none' => true );
		}
		
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
	 * Adds in the settings neccesary for the archives
	 */
	public function archive_settings($post_type=false){ ?>
		{{#unless disable_archives}}	
			<tr class="form-field banner-wrap">
				<th scope="row">
					<label for="banner"> Banner Image</label>
				</th>
				<td>
					<input type="hidden" {{#if banner_id}} value="{{banner_id}}" {{/if}} name="banner_id" />
					<input type="hidden" {{#if banner}} value="{{banner}}" {{/if}} name="banner" />
					<div class="thumbnail-preview">
						{{#if banner}}<img src="{{banner}}" width="150" height="150" />{{/if}}	
					</div>

					<a {{#if banner}}style="display:none;"{{/if}} class="button-secondary lsx-thumbnail-image-add" data-slug="banner">Choose Image</a>

					<a {{#unless banner}}style="display:none;"{{/unless}} class="button-secondary lsx-thumbnail-image-delete" data-slug="banner">Delete</a>
					
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="banner_video"><?php _e('Banner Video URL (mp4)','lsx-banners'); ?></label>
				</th>
				<td>
					<input type="text" {{#if banner_video}} value="{{banner_video}}" {{/if}} name="banner_video" />
				</td>
			</tr>
		{{/unless}}
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
	public function create_settings_page(){
		if(is_admin()){
			if(!class_exists('\lsx\ui\uix')){
				include_once LSX_BANNERS_PATH.'vendor/uix/uix.php';
			}
			$pages = $this->settings_page_array();
			$uix = \lsx\ui\uix::get_instance( 'lsx' );
			$uix->register_pages( $pages );

			$post_types = $this->get_allowed_post_types();

			if(false !== $post_types){
				foreach($post_types as $post_type){
					add_action( 'lsx_framework_'.$post_type.'_tab_content_top', array( $this, 'archive_settings' ), 11 , 1 );
				}	
			}
		}
	}	

	/**
	 * Returns the array of settings to the UIX Class
	 */
	public function settings_page_array(){
		// This array is for the Admin Pages. each element defines a page that is seen in the admin
	
		$tabs = array( // tabs array are for setting the tab / section templates
				// each array element is a tab with the key as the slug that will be the saved object property
				'general'		=> array(
						'page_title'        => '',
						'page_description'  => '',
						'menu_title'        => 'General',
						'template'          => LSX_BANNERS_PATH.'includes/settings/general.php',
						'default'	 		=> true
				)
		);

		$posts_page = get_option('page_for_posts',false);
		if(false === $posts_page){
			$tabs['post'] = array(
				'page_title'        => 'Posts',
				'page_description'  => '',
				'menu_title'        => 'Posts',
				'template'          => LSX_BANNERS_PATH.'includes/settings/post.php',
				'default'	 		=> false
			);
		}
	
		$additional_tabs = false;
		$additional_tabs = apply_filters('lsx_framework_settings_tabs',$additional_tabs);
		if(false !== $additional_tabs && is_array($additional_tabs) && !empty($additional_tabs)){
			$tabs = array_merge($tabs,$additional_tabs);
		}
		return array(
				'lsx-settings'  => array(                                                         // this is the settings array. The key is the page slug
						'page_title'  =>  'LSX Settings',                                                  // title of the page
						'menu_title'  =>  'LSX Settings',                                                  // title seen on the menu link
						'capability'  =>  'manage_options',                                              // required capability to access page
						'icon'        =>  'dashicons-book-alt',                                          // Icon or image to be used on admin menu
						'parent'      =>  'options-general.php',                                         // Position priority on admin menu)
						'save_button' =>  'Save Changes',                                                // If the page required saving settings, Set the text here.
						'tabs'        =>  $tabs,
						/*'help'	=> array(	// the wordpress contextual help is also included
								// key is the help slug
								'default-help' => array(
										'title'		=> 	esc_html__( 'Easy to add Help' , 'uix' ),
										'content'	=>	"Just add more items to this array with a unique slug/key."
								),
								'more-help' => array(
										'title'		=> 	esc_html__( 'Makes things Easy' , 'uix' ),
										'content'	=>	"the content can also be a file path to a template"
								)
						),*/
				),
		);
	}

	/**
	 * Runs through the registered post types, and does a generic settings page for them.
	 */	
	public function register_additional_tabs($tabs){
		// This array is for the Admin Pages. each element defines a page that is seen in the admin
		$post_types = $this->get_allowed_post_types();
		if(false !== $post_types && !empty($post_types)){
			foreach($post_types as $index){

				$disabled = false;
				if(is_array($tabs) && !array_key_exists($index,$tabs) && !in_array($index,array('post','page'))){
					$tabs[$index] = array(
						'page_title'        => 'General',
						'page_description'  => '',
						'menu_title'        => ucwords(str_replace('-',' ',$index)),
						'template'          => LSX_BANNERS_PATH.'includes/settings/placeholder.php',
						'default'	 		=> false,
						'disabled'			=> $disabled
					);
				}
			}
			if(is_array($tabs)) {
				ksort($tabs);
			}
		}
		return $tabs;
	}

	/**
	 * Allows the settings pages to upload images
	 */
	public function settings_page_scripts(){ ?>
	{{#script}}
		jQuery( function( $ ){
			$( '.lsx-thumbnail-image-add' ).on( 'click', function() {

				var slug = $(this).attr('data-slug');
				tb_show('Choose a Featured Image', 'media-upload.php?type=image&TB_iframe=1');
				var image_thumbnail = '';
				var thisObj = $(this);
				window.send_to_editor = function( html ) 
				{

					var image_thumbnail = $( 'img',html ).html();

					$( thisObj ).parent('td').find('.thumbnail-preview' ).append('<img width="150" height="150" src="'+$( 'img',html ).attr( 'src' )+'" />');
					$( thisObj ).parent('td').find('input[name="'+slug+'"]').val($( 'img',html ).attr( 'src' ));
					
					var imgClasses = $( 'img',html ).attr( 'class' );
					imgClasses = imgClasses.split('wp-image-');
					
					$( thisObj ).parent('td').find('input[name="'+slug+'_id"]').val(imgClasses[1]);
					$( thisObj ).hide();
					$( thisObj ).parent('td').find('.lsx-thumbnail-image-delete' ).show();
					tb_remove();
					$( this ).hide();
				}
				return false;
			});
			$( '.lsx-thumbnail-image-delete' ).on( 'click', function() {
				var slug = $(this).attr('data-slug');
				$( this ).parent('td').find('input[name="'+slug+'_id"]').val('');
				$( this ).parent('td').find('input[name="'+slug+'"]').val('');
				$( this ).parent('td').find('.thumbnail-preview' ).html('');
				$( this ).hide();
				$( this ).parent('td').find('.lsx-thumbnail-image-add' ).show();
			});		
		});
	{{/script}}
	<?php
	}					
}