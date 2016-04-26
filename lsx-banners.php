<?php
/*
Plugin Name: LSX Banners
Plugin URI: {add_in}
Description: A full width responsive banner solution. Compatabile with LSX, Storefront and Sage themes
Author: Warwick
Author URI: http://wordpress.org/
Version: 0.7.0
Text Domain: lsx-banners
Tags: LSX, Storefront, Sage
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

define('LSX_BANNERS_PATH',  plugin_dir_path( __FILE__ ) );
define('LSX_BANNERS_CORE',  __FILE__ );
define('LSX_BANNERS_URL',  plugin_dir_url( __FILE__ ) );
define('LSX_BANNERS_VER',  '1.0.0' );

if(!function_exists('cmb_init')){
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
	 * Holds the name of the theme
	 *
	 * @var      string|Lsx_Banners
	 */
	public $theme = null;
	
	/**
	 * Holds a boolean weather or not to use placeholdit.
	 *
	 * @var      string|Lsx_Banners
	 */
	public $placeholder = false;	
	
	/**
	 * Holds the current items ID
	 *
	 * @var      string|Lsx_Banners
	 */
	public $post_id = false;
	
	/**
	 * Runs on the body_class, to let you know if there is a banner or not.
	 *
	 * @var      string|Lsx_Banners
	 */
	public $has_banner = false;	

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {	
		
		// init UIX
		// include the library
		if(!class_exists('\lsx\ui\uix')){
			include_once LSX_BANNERS_PATH . 'uix/uix.php';
		}

		// get the pages
		$pages = include LSX_BANNERS_PATH . 'includes/pages.php';

		// initialize admin UI
		$uix = \lsx\ui\uix::get_instance( 'lsx' );
		$uix->register_pages( $pages );

		//Enqueue the scrips
		add_filter( 'cmb_meta_boxes', array($this,'metaboxes') );	
		add_action('wp_head',array($this,'init'));
		add_action('admin_init',array($this,'admin_init'));
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
	public function admin_init() {
		$allowed_taxonomies = $this->get_allowed_taxonomies();
		if(is_array($allowed_taxonomies)){
			foreach($allowed_taxonomies as $taxonomy){
				add_action( "{$taxonomy}_add_form_fields",  array( $this, 'add_form_field'  ),1 );
				add_action( "{$taxonomy}_edit_form_fields", array( $this, 'add_form_field' ),1 );
			}
		}
	}

	/**
	 * Initializes the variables we need.
	 *
	 */
	public function init() {
		$allowed_post_types = $this->get_allowed_post_types();
		$allowed_taxonomies = $this->get_allowed_taxonomies();
		
		$post_type = get_post_type();	
		$this->post_id = get_queried_object_id();
		
		if(is_singular($allowed_post_types) || is_post_type_archive($allowed_post_types) || is_tax($allowed_taxonomies) || is_404() ) {
			//$theme = wp_get_theme();
			if(function_exists('lsx_setup')){
				$this->theme = 'lsx';
				remove_action( 'lsx_header_after', 'lsx_page_banner' );
				add_action('lsx_header_after',array($this,'banner'));
			}elseif(function_exists('storefront_setup')){
				$this->theme = 'storefront';
				add_action('storefront_before_content',array($this,'banner'));
			}else{
				$this->theme = 'other';
			}
			
			add_filter('lsx_banner_title', array($this,'banner_title') );
			add_filter('lsx_banner_meta_boxes',array($this,'subtitle_metabox'));
			add_filter('body_class', array($this,'body_class'));
			
			
			
			$this->placeholder = apply_filters('lsx_banner_enable_placeholder', false);
			if(false !== $this->placeholder){
				add_filter('lsx_banner_placeholder_url', array($this,'default_placeholder') );
			}
		}
	}	

	/**
	 * retreives the allowed post types
	 *
	 * @return array
	 */
	public function get_allowed_post_types() {
		// Example of all available fields
		$allowed_post_types = array('page','post');
		if(in_array('jetpack-portfolio', get_post_types())){
			$allowed_post_types[] = 'jetpack-portfolio';
		}
		return apply_filters( 'lsx_banner_allowed_post_types', $allowed_post_types );
	}	
	
	/**
	 * retreives the allowed taxonomies
	 *
	 * @return array
	 */
	public function get_allowed_taxonomies() {
		// Example of all available fields
		$allowed_taxonomies = array('category');
		return apply_filters( 'lsx_banner_allowed_taxonomies', $allowed_taxonomies );
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
			array( 'id' => 'image_group', 'name' => '', 'type' => 'group', 'cols' => 4, 'fields' => array(
					array( 'id' => 'banner_image', 'name' => 'Image', 'type' => 'image', 'repeatable' => true, 'show_size' => false, 'size' => array(350,200))
			) );
		$fields[] = 
			array( 'id' => 'image_bg_group', 'name' => '', 'type' => 'group', 'cols' => 8, 'fields' => array(
					array( 'id' => 'banner_height',  'name' => 'Height', 'type' => 'text' ),
					array( 'id' => 'banner_x', 'name' => 'X Position', 'type' => 'select', 'options' => array( 'left' => 'Left', 'right' => 'Right', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
					array( 'id' => 'banner_y', 'name' => 'Y Position', 'type' => 'select', 'options' => array( 'top' => 'Top', 'bottom' => 'Bottom', 'Center' => 'Center' ), 'allow_none' => true, 'sortable' => false, 'repeatable' => false ),
			) );

		$meta_boxes[] = array(
				'title' => 'Banners',
				'pages' => $allowed_post_types,
				'fields' => $fields
		);
		return $meta_boxes;
	}
	
	/**
	 * Outputs the Banner HTML
	 */
	
	public function banner(){ 
		/*
		 * This section gets actualy banner url.
		*/
		$post_id = $this->post_id;
				
		//If we are using placeholders then the baner section shows all the time,  this is when the banner disabled checkbox comes into play.
		if(true === $this->placeholder && get_post_meta($post_id,'banner_disabled',true)) { return ''; }

		//We change the id to the page with a matching slug ar the post_type archive.
		$img_group = get_post_meta($post_id,'image_group',true);
		$banner_image = false;
		$show_slider = false;
		if(false !== $img_group && is_array($img_group) && isset($img_group['banner_image']) && !empty($img_group['banner_image'])){
			if(!is_array($img_group['banner_image'])){
				$banner_image_id = $img_group['banner_image'];
			}else{
				$banners_length = count($img_group['banner_image'])-1;
				
				$banner_ids = array_values($img_group['banner_image']);
				if('lsx' !== $this->theme && $banners_length > 0){
					$banner_index = rand('0', $banners_length);
					$banner_image_id = $banner_ids[$banner_index];
				}else{				
					$banner_image_id = $banner_ids[0];
				}	
			}
			$banner_image = wp_get_attachment_image_src($banner_image_id,'full');
			$banner_image = $banner_image[0];
		}
		
		if('lsx' === $this->theme && 0 !== $post_id && false === $banner_image && has_post_thumbnail($post_id)){
			$banner_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),'full');
			$banner_image = $banner_image[0];			
		}
		
		//Check if the slider code should show
		if('lsx' === $this->theme && isset($img_group['banner_image']) && is_array($img_group['banner_image']) && 1 < count($img_group['banner_image'])) {
			$show_slider = true;
		}
		
		//If we have enabled the placeholders,  then force a placeholdit url
		if(true === $this->placeholder && false === $banner_image){
			$banner_image = apply_filters('lsx_banner_placeholder_url','https://placeholdit.imgix.net/~text?txtsize=33&txt=1920x600&w=1920&h=600');
		}		
		
		/*
		 * This section gets the image meta, size etc.
		 */
		$image_bg_group = get_post_meta($post_id,'image_bg_group',true);
		$size = 'cover';
		
		$x_position = 'center';
		$y_position = 'center';
		if(false !== $image_bg_group && is_array($image_bg_group)){
			
			if(isset($image_bg_group['banner_size']) && '' !== $image_bg_group['banner_size']){
				$size = $image_bg_group['banner_size'];
			}
			
			if(isset($image_bg_group['banner_x']) && '' !== $image_bg_group['banner_x']){
				$x_position = $image_bg_group['banner_x'];
			}			
			
			if(isset($image_bg_group['banner_y']) && '' !== $image_bg_group['banner_y']){
				$y_position = $image_bg_group['banner_y'];
			}
		}

		//Check if the content should be disabled or not
		$text_disable = get_post_meta($post_id,'banner_text_disabled',true);		

		if(false !== $banner_image){
			
			//if its the lsx theme and there are more than 1 banner, then output a bootstrap carousel.
			if($show_slider) { 
				?>
				<div id="page-slider" class="carousel slide" data-ride="carousel" data-interval="6000">
					<div class="carousel-inner">
				<?php
			}
			?>
				<div class="page-banner <?php if($show_slider){ echo 'item active'; }else{}  ?>" style="background-position: <?php echo $x_position; ?> <?php echo $y_position; ?>; background-image:url(<?php echo $banner_image; ?>); background-size:<?php echo $size; ?>;">
		        	<div class="container">
		        		
		        		<?php do_action('lsx_banner_container_top'); ?>
		        		
			            <header class="page-header">
			            		<?php echo apply_filters('lsx_banner_title','<h1 class="page-title">'.get_the_title($post_id).'</h1>'); ?>
			            </header><!-- .entry-header -->
			            <?php if(true !== $text_disable && '1' !== $text_disable) { ?><?php echo $this->banner_content(); ?><?php } ?>
			            
			            <?php do_action('lsx_banner_container_bottom'); ?>
			        </div>
		        </div>		
			<?php
			//if its the lsx theme and there are more than 1 banner, then output a bootstrap carousel.
			if($show_slider) {	?>
						<?php 
						foreach($img_group['banner_image'] as $key => $slide_id){ if('cmb-field-0' === $key){continue;}
							$slide = wp_get_attachment_image_src($slide_id,'full');
							?>
							<div class="page-banner item" style="background-position: <?php echo $x_position; ?> <?php echo $y_position; ?>; background-image:url(<?php echo $slide[0]; ?>); background-size:<?php echo $size; ?>;">
					        	<div class="container">
						            <header class="page-header">
						            	<?php echo apply_filters('lsx_banner_title','<h1 class="page-title">'.get_the_title($post_id).'</h1>'); ?>
						            </header><!-- .entry-header -->
						            <?php echo $this->banner_content(); ?>
						        </div>
					        </div>
						<?php }	?>
					</div>					
				</div>
				<?php
			}			
		
		}
	}
	
	/**
	 * Add <body> classes
	 */
	public function body_class($classes) {
		// Add page slug if it doesn't exist		
		//Test is the banner has been disabled.
			//see if there is a banner image
		$banner_image = false;
		if(0 !== get_the_ID()){
			$img_group = get_post_meta(get_the_ID(),'image_group',true);
			if(false !== $img_group && is_array($img_group) && isset($img_group['banner_image']) && '' !== $img_group['banner_image'] && !empty($img_group['banner_image'])){
				$classes[] = 'has-banner';
				$this->has_banner = true;
			}
		}	
		if(true === $this->placeholder){
			$classes[] = 'has-banner';
			$this->has_banner = true;
		}
		return $classes;
	}
	
	/**
	 * a filter to check if a custom title has been added, if so, use that instead of the post title
	 */
	public function banner_title($post_title) {	
		if(is_post_type_archive($this->get_allowed_post_types())){
			$post_title = '<h1 class="page-title">'.get_the_archive_title().'</h1>';
		}		
		if(is_tax($this->get_allowed_taxonomies())){
			$post_title = '<h1 class="page-title">'.single_term_title("", false).'</h1>';
		}		
		if(apply_filters('lsx_banner_enable_title', false) && 0 !== $this->post_id){
			$new_title = get_post_meta($this->post_id,'banner_title',true);
			if(false !== $new_title && '' !== $new_title){
				$post_title = '<h1 class="page-title">'.$new_title.'</h1>';
			}
		}
		return $post_title;
	}	
	
	/**
	 * Outputs the banner content, usually a short tagline.
	 */
	public function banner_content() {
		switch($this->theme){
			case 'lsx':
				ob_start();
				lsx_banner_content();
				$retval = ob_get_clean();
			break;
			
			default:
				$retval = apply_filters('lsx_banner_content','');	
			break;
		}	
		
		return $retval;
	}
	
	/**
	 * Returns the defulat placeholder url
	 */
	public function default_placeholder($url) {
		$post_type = get_post_type();
		if(false !== $post_type && !is_array($post_type)){
			$default_id = \lsx\ui\uix::get_setting('lsx-general.general.'.$post_type.'.id');
		}
		if( empty( $default_id ) ){
			$default_id = \lsx\ui\uix::get_setting('lsx-general.general.selection.id');
		}
		if( !empty( $default_id ) ){
			$banner_image = wp_get_attachment_image_src( $default_id,'full');
			if( !empty( $banner_image ) ){
				$url = $banner_image[0];
			}
		}
		return $url;
	}
	
	
	/**
	 * Output the form field for this metadata when adding a new term
	 *
	 * @since 0.1.0
	 */
	public function add_form_field() {
		?>
		<tr class="form-field form-required term-banner-wrap">
			<th scope="row"><label for="banner"><?php _e('Banner','lsx-banners');?></label></th>
			<td>
				<input style="display:none;" name="banner" id="banner" type="text" value="" size="40" aria-required="true">
				<div class="banner-preview">
				</div>				
				<a class="button-secondary lsx-banner-image-add"><?php _e('Choose Image','lsx-banners');?></a>
				<a style="display:none;" class="button-secondary lsx-banner-image-remove"><?php _e('Remove Image','lsx-banners');?></a>
			</td>
		</tr>
		
		<script type="text/javascript">
			(function( $ ) {
				$( '.lsx-banner-image-add' ).on( 'click', function() {
					tb_show('test', 'media-upload.php?type=image&TB_iframe=1');
					var image_thumbnail = '';
					window.send_to_editor = function( html ) 
					{
						var image_thumbnail = $( 'img',html ).html();
						$( '.banner-preview' ).append(html);
						var imgClasses = $( 'img',html ).attr( 'class' );
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
			
	
}
$lsx_banners = Lsx_Banners::get_instance();


/**
 * A template tag to output the banner bg src
 * 
 * @return		String
 */
function lsx_banner_src(){
	global $lsx_banners;
	$lsx_banners->banner();
}

/**
 * Returns a true or false if there is a banner.
 *
 * @return		String
 */
function lsx_has_banner(){
	global $lsx_banners;
	return $lsx_banners->has_banner;
}