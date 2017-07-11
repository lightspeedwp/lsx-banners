<?php
/**
 * LSX Banners Frontend Class
 *
 * @package   LSX Banners
 * @author    LightSpeed
 * @license   GPL3
 * @link
 * @copyright 2016 LightSpeed
 */
class LSX_Banners_Frontend extends LSX_Banners {

	/**
	 * This holds the class OBJ of LSX_Template_Redirects
	 */
	public $redirects = false;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->options = get_option( '_lsx_settings', false );

		if ( false === $this->options ) {
			$this->options = get_option( '_lsx_lsx-settings', false );
		}

		//Test to see if Tour Operators is active.
		$lsx_to_options = get_option( '_lsx-to_settings', false );

		if ( false !== $lsx_to_options ) {
			$this->options = $lsx_to_options;
		}

		$this->set_vars();

		add_action( 'wp_head', array( $this, 'init' ) );

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_stylescripts' ), 999 );
		} else {
			add_filter( 'lsx_customizer_colour_selectors_banner', array( $this, 'customizer_colours_handler' ), 15, 2 );
		}

		add_filter( 'lsx_fonts_css', array( $this, 'customizer_fonts_handler' ), 15 );
		add_shortcode( 'banner_navigation', 'lsx_banner_navigation' );
	}

	/**
	 * Enques the assets
	 */
	public function enqueue_stylescripts() {
		wp_enqueue_script( 'jquery-touchswipe', LSX_BANNERS_URL . 'assets/js/vendor/jquery.touchSwipe.min.js', array( 'jquery' ) , LSX_BANNERS_VER, true );

		wp_enqueue_script( 'lsx-banners', LSX_BANNERS_URL . 'assets/js/lsx-banners.min.js', array( 'jquery', 'jquery-touchswipe' ), LSX_BANNERS_VER, true );

		$params = apply_filters( 'lsx_banners_js_params', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		));

		wp_localize_script( 'lsx-banners', 'lsx_banners_params', $params );

		wp_enqueue_style( 'lsx-banners', LSX_BANNERS_URL . 'assets/css/lsx-banners.css', array(), LSX_BANNERS_VER );
		wp_style_add_data( 'lsx-banners', 'rtl', 'replace' );
	}

	/**
	 * Initializes the variables we need.
	 *
	 */
	public function init() {
		$allowed_post_types = $this->get_allowed_post_types();
		$allowed_taxonomies = $this->get_allowed_taxonomies();
		$post_type          = get_post_type();

		$this->post_id = get_queried_object_id();

		if ( is_singular( $allowed_post_types ) || is_post_type_archive( $allowed_post_types ) || is_tax( $allowed_taxonomies ) || is_category() || is_author() || is_404() || is_front_page() || is_home() ) {
			if ( function_exists( 'lsx_setup' ) ) {
				$this->theme = 'lsx';
				remove_action( 'lsx_header_after', 'lsx_page_banner' );
				add_action( 'lsx_header_after', array( $this, 'banner' ) );
			} elseif ( class_exists( 'Storefront' ) ) {
				$this->theme = 'storefront';
				add_action( 'storefront_before_content', array( $this, 'banner' ) );
			} else {
				$this->theme = 'other';
			}

			add_filter( 'lsx_banner_title', array( $this, 'banner_title' ) );
			add_filter( 'lsx_banner_meta_boxes',array( $this, 'subtitle_metabox' ) );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_action( 'lsx_banner_content', array( $this, 'banner_tagline' ), 20 );

			$this->placeholder = apply_filters( 'lsx_banner_enable_placeholder', true );

			if ( false !== $this->placeholder ) {
				add_filter( 'lsx_banner_placeholder_url', array( $this, 'default_placeholder' ) );
			}
		}
	}

	/**
	 * Outputs the Banner HTML
	 */
	public function banner() {
		$post_id = $this->post_id;
		$full_height = false;
		$height = '';
		$x_position = 'center';
		$y_position = 'center';
		$show_slider = false;
		$img_group = false;

		// If we are using placeholders then the baner section shows all the time,  this is when the banner disabled checkbox comes into play.
		if ( true === $this->placeholder && get_post_meta( $this->post_id, 'banner_disabled', true ) ) {
			return '';
		}

		/*
		 * This section gets the image meta, size etc.
		 */
		$image_bg_group = get_post_meta( $post_id, 'image_bg_group', true );

		if ( false !== $image_bg_group && is_array( $image_bg_group ) ) {
			if ( isset( $image_bg_group['banner_full_height'] ) ) {
				$full_height = $image_bg_group['banner_full_height'];
			}

			if ( empty( $full_height ) && isset( $image_bg_group['banner_height'] ) && '' !== $image_bg_group['banner_height'] ) {
				$height = $image_bg_group['banner_height'];
			}

			if ( isset( $image_bg_group['banner_x'] ) && '' !== $image_bg_group['banner_x'] ) {
				$x_position = $image_bg_group['banner_x'];
			}

			if ( isset( $image_bg_group['banner_y'] ) && '' !== $image_bg_group['banner_y'] ) {
				$y_position = $image_bg_group['banner_y'];
			}
		}

		$banner_image = false;

		// We change the id to the page with a matching slug ar the post_type archive.
		// Singular Banners
		if ( is_front_page() || is_home() || is_singular( $this->get_allowed_post_types() ) || in_array( 'blog', get_body_class(), true ) ) {
			$img_group   = get_post_meta( $this->post_id,'image_group', true );
			$show_slider = false;

			if ( false !== $img_group && is_array( $img_group ) && isset( $img_group['banner_image'] ) && ! empty( $img_group['banner_image'] ) ) {
				if ( ! is_array( $img_group['banner_image'] ) ) {
					$banner_image_id = $img_group['banner_image'];
				} else {
					$banners_length = count( $img_group['banner_image'] ) - 1;
					$banner_ids     = array_values( $img_group['banner_image'] );

					if ( 'lsx' !== $this->theme && $banners_length > 0 ) {
						$banner_index    = rand( '0', $banners_length );
						$banner_image_id = $banner_ids[ $banner_index ];
					} else {
						$banner_image_id = $banner_ids[0];
					}
				}

				$banner_image = wp_get_attachment_image_src( $banner_image_id, 'full' );

				if ( ! empty( $banner_image ) ) {
					$banner_image = $banner_image[0];
				} else {
					$banner_image = false;
				}
			}

			// If its the LSX theme, and there is no banner, but there is a featured image,  then use that for the banner.
			if ( 'lsx' === $this->theme && is_singular( array( 'post', 'page' ) ) && false === $banner_image && has_post_thumbnail( $this->post_id ) ) {
				$banner_image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->post_id ), 'full' );

				if ( ! empty( $banner_image ) ) {
					$banner_image = $banner_image[0];
				} else {
					$banner_image = false;
				}
			}
		}

		if ( is_post_type_archive( $this->get_allowed_post_types() ) ) {
			$archive_banner = apply_filters( 'lsx_banner_post_type_archive_url', false );

			if ( false !== $archive_banner ) {
				$banner_image = $archive_banner;
			}

			if ( isset( $this->options[ get_post_type() ] ) && ! empty( $this->options[ get_post_type() ]['banner'] ) ) {
				$banner_image = $this->options[ get_post_type() ]['banner'];
			}
		}

		// If its a taxonomy, then get the image from out term meta.
		if ( ( is_category() || is_tax( $this->get_allowed_taxonomies() ) ) && false !== $this->banner_id ) {
			$banner_image = wp_get_attachment_image_src( $this->banner_id, 'full' );

			if ( ! empty( $banner_image ) ) {
				$banner_image = $banner_image[0];
			} else {
				$banner_image = false;
			}
		} elseif ( is_tax( $this->get_allowed_taxonomies() ) || is_category() ) {
			$tax_banner = apply_filters( 'lsx_banner_post_type_archive_url', false );

			if ( false !== $tax_banner ) {
				$banner_image = $tax_banner;
			}
		}

		// If its a author archive, then get the image from out user meta.
		if ( is_author() && false !== $this->banner_id ) {
			$banner_image = wp_get_attachment_image_src( $this->banner_id, 'full' );

			if ( ! empty( $banner_image ) ) {
				$banner_image = $banner_image[0];
			} else {
				$banner_image = false;
			}
		} elseif ( is_author() ) {
			$tax_banner = apply_filters( 'lsx_banner_post_type_archive_url', false );

			if ( false !== $tax_banner ) {
				$banner_image = $tax_banner;
			}
		}

		// If we have enabled the placeholders,  then force a placeholdit url
		if ( true === $this->placeholder && false === $banner_image && ! is_404() ) {
			$banner_image = apply_filters( 'lsx_banner_placeholder_url', 'https://placeholdit.imgix.net/~text?txtsize=33&txt=1920x600&w=1920&h=600' );
		}

		// Check if the content should be disabled or not
		$title_disable = get_post_meta( $post_id, 'banner_title_disabled', true );
		$text_disable  = get_post_meta( $post_id, 'banner_text_disabled', true );

		// Embed video
		$embed_video = get_post_meta( $this->post_id, 'banner_video', true );

		if ( false !== $embed_video && ! empty( $embed_video ) ) {
			$embed_video = wp_get_attachment_url( $embed_video );
			$embed_video = '<video src="' . $embed_video . '" ' . ( ! empty( $banner_image ) ? ( 'poster="' . $banner_image . '"' ) : '' ) . ' width="auto" height="auto" autoplay loop preload muted>' . ( ! empty( $banner_image ) ? ( '<img class="disable-lazyload" src="' . $banner_image . '">' ) : '' ) . '</video>';
		}

		// Envira Gallery
		$envira_gallery_id = get_post_meta( $this->post_id, 'envira_gallery', true );

		if ( class_exists( 'Envira_Gallery' ) && apply_filters( 'lsx_banners_envira_enable', true ) ) {
			if ( false !== $envira_gallery_id && ! empty( $envira_gallery_id ) ) {
				$envira_gallery = Envira_Gallery::get_instance();
				$envira_gallery_images = $envira_gallery->get_gallery( $envira_gallery_id );

				if ( 'lsx' === $this->theme && is_array( $envira_gallery_images ) && count( $envira_gallery_images ) > 1 && apply_filters( 'lsx_banner_enable_sliders', true ) ) {
					$img_group = array(
						'banner_image' => array(),
					);

					$show_slider = true;

					foreach ( $envira_gallery_images['gallery'] as $key => $value ) {
						$img_group['banner_image'][] = array(
							'image_id'    => $key,
							'image_title' => $value['title'],
							'image_text' => $value['caption'],
						);
					}
				}
			}
		} else {
			$envira_gallery_id = false;
		}

		// Soliloquy Slider
		$soliloquy_slider_id = get_post_meta( $this->post_id, 'soliloquy_slider', true );

		if ( class_exists( 'Soliloquy' ) ) {
			if ( false !== $soliloquy_slider_id && ! empty( $soliloquy_slider_id ) ) {
				$soliloquy_slider = Soliloquy::get_instance();
				$soliloquy_slider_images = $soliloquy_slider->get_slider( $soliloquy_slider_id );

				if ( is_array( $soliloquy_slider_images ) && count( $soliloquy_slider_images ) > 1 && apply_filters( 'lsx_banner_enable_sliders', true ) ) {
					$img_group = array(
						'banner_image' => array(),
					);

					$show_slider = true;

					foreach ( $soliloquy_slider_images['slider'] as $key => $value ) {
						$img_group['banner_image'][] = array(
							'image_id'    => $key,
							'image_title' => $value['title'],
							'image_text' => $value['caption'],
						);
					}
				}
			}
		} else {
			$soliloquy_slider_id = false;
		}

		if ( $show_slider || ( false !== $banner_image && ! empty( $banner_image ) ) || ( false !== $embed_video && ! empty( $embed_video ) ) ) {
			?>
			<div id="lsx-banner">
			<?php
			do_action('lsx_banner_top');

			//if its the lsx theme and there are more than 1 banner, then output a bootstrap carousel.
			$banner_attribute = false;
			if ( $show_slider && ! ( class_exists( 'Soliloquy' ) && false !== $soliloquy_slider_id && ! empty( $soliloquy_slider_id ) ) && ! ( class_exists( 'Envira_Gallery' ) && false !== $envira_gallery_id && ! empty( $envira_gallery_id) )) {
				?>
				<div id="page-banner-slider" class="carousel slide" data-ride="carousel" data-interval="false">
					<div class="carousel-inner">
				<?php
			}elseif(is_array($img_group) && 1 < count($img_group['banner_image'])){
				$banner_attribute = array();
				foreach($img_group['banner_image'] as $key => $slide_id){
					$slide = wp_get_attachment_image_src($slide_id,'full');
					if ( ! empty( $slide ) ) {
						$banner_attribute[] = $slide[0];
					}
				}
			}elseif(false !== $banner_image){
				$banner_attribute = array($banner_image);
			}

			if(false !== $banner_attribute){
				$banner_attribute = implode(',',$banner_attribute);
			}

			if(!$show_slider) {
				?>

				<div class="page-banner-wrap">
					<div class="page-banner rotating" <?php if ( ! empty( $height ) ) echo 'style="min-height:' . $height . '"'; ?>>
			        	<?php if(false !== $banner_attribute): ?>
			        		<div class="page-banner-image" style="background-position: <?php echo $x_position; ?> <?php echo $y_position; ?>;" data-banners="<?php echo $banner_attribute; ?>"></div>
			        	<?php endif; ?>

			        	<?php if(false !== $embed_video && !empty($embed_video)): ?>
			        		<div class="video-background">
			        			<div class="video-foreground">
			        				<?php echo $embed_video; ?>
			        			</div>
			        		</div>
			        	<?php endif; ?>

			        	<div class="container">
			        		<?php do_action('lsx_banner_container_top'); ?>

				            <?php if ( true !== $title_disable && '1' !== $title_disable ) : ?>
				            	<?php $title = apply_filters('lsx_banner_title','<h1 class="page-title">'.get_the_title($post_id).'</h1>'); ?>

				            	<?php if ( ! empty( $title ) ) : ?>
						            <header class="page-header">
						            	<?php echo $title; ?>
						            </header>
					        	<?php endif; ?>
					        <?php endif; ?>

				            <?php if(true !== $text_disable && '1' !== $text_disable && !empty($this->banner_content())) { ?><div class="banner-content"><?php echo $this->banner_content(); ?></div><?php } ?>

				            <?php do_action('lsx_banner_container_bottom'); ?>
				        </div>

				        <?php lsx_banner_inner_bottom(); ?>
			        </div>
			    </div>
			<?php
			//if its the lsx theme and there are more than 1 banner, then output a bootstrap carousel.
			} elseif ( class_exists( 'Soliloquy' ) && ((false !== $soliloquy_slider_id && ! empty( $soliloquy_slider_id )) || (false !== $envira_gallery_id && ! empty( $envira_gallery_id )) )) {
				echo '<div class="page-banner page-banner-soliloquy">';
				if (false !== $soliloquy_slider_id && ! empty( $soliloquy_slider_id )) {
					echo do_shortcode( '[soliloquy id="' . $soliloquy_slider_id . '"]' );
				} elseif (false !== $envira_gallery_id && ! empty( $envira_gallery_id )) {
					echo do_shortcode( '[soliloquy_dynamic id="envira-' . $envira_gallery_id . '"]' );
				}
				echo '</div>';
			} else { ?>
						<?php
						$count = 0;
						foreach($img_group['banner_image'] as $key => $value){
							if('cmb-field-0' === $key){continue;}

							if ( is_array( $value ) ) {
								$slide   = wp_get_attachment_image_src( $value['image_id'], 'full' );
								$title   = $value['image_title'];
								$content = $value['image_text'];
							} else {
								$slide   = wp_get_attachment_image_src( $value, 'full' );
								$title   = get_the_title( $post_id );
								$content = $this->banner_content();
							}
							$title = apply_filters('lsx_banner_title','<h1 class="page-title">'.$title.'</h1>');
							?>
							<div class="item <?php if ( 0 === $count ) echo 'active'; ?>">
								<div class="page-banner-wrap">
									<div class="page-banner">
							        	<div class="page-banner-image" style="background-position: <?php echo $x_position; ?> <?php echo $y_position; ?>; background-image:url(<?php echo $slide[0]; ?>); background-size:<?php echo $size; ?>;"></div>

				        				<div class="container">
							        		<?php do_action('lsx_banner_container_top'); ?>

								            <?php if ( true !== $title_disable && '1' !== $title_disable && ! empty( $title ) ) : ?>
									            <header class="page-header">
									            	<?php echo $title; ?>
									            </header>
									        <?php endif; ?>

								            <?php if(true !== $text_disable && '1' !== $text_disable && !empty($content)) { ?><div class="banner-content"><?php echo $content; ?></div><?php } ?>

								            <?php do_action('lsx_banner_container_bottom'); ?>
								        </div>
							        </div>
							    </div>
							</div>
							<?php
							$count++;
						}
						?>
						<ol class="carousel-indicators">
							<?php
								$i = 0;

								while ( $i < count( $img_group['banner_image'] ) ) {
									$class = $i == 0 ? 'active' : '';
									echo '<li data-target="#page-banner-slider" data-slide-to="'. $i .'" class="'. $class .'"></li>';
									$i++;
								}
							?>
						</ol>
						<a class="left carousel-control" href="#page-banner-slider" role="button" data-slide="prev">
							<span class="fa fa-angle-left" aria-hidden="true"></span>
							<span class="sr-only"><?php esc_html_e( 'Previous', 'lsx-banners' ); ?></span>
						</a>
						<a class="right carousel-control" href="#page-banner-slider" role="button" data-slide="next">
							<span class="fa fa-angle-right" aria-hidden="true"></span>
							<span class="sr-only"><?php esc_html_e( 'Next', 'lsx-banners' ); ?></span>
						</a>
					</div>
				</div>
				<?php
			}
			do_action('lsx_banner_bottom');
			?>
			</div>
			<?php
		}
	}

	/**
	 * Add <body> classes
	 */
	public function body_class( $classes ) {
		$banner_disabled = false;
		$banner_image = false;

		if ( 0 !== get_the_ID() || is_front_page() || is_home() ) {
			$post_id = $this->post_id;

			if ( is_home() ) {
				$post_id = get_option( 'page_for_posts' );
			}

			$img_group = get_post_meta( $post_id, 'image_group', true );
			$banner_disabled = get_post_meta( $post_id, 'banner_disabled', true );

			if ( true !== $banner_disabled && '1' !== $banner_disabled && false !== $img_group && is_array( $img_group ) && isset( $img_group['banner_image'] ) && '' !== $img_group['banner_image'] && ! empty( $img_group['banner_image'] ) ) {
				$classes[] = 'page-has-banner';
				$this->has_banner = true;
			}
		}

		if ( is_author() ) {
			$term_banner_id = get_user_meta( $this->post_id, 'banner', true );

			if ( ! empty( $term_banner_id ) ) {
				$classes[] = 'page-has-banner';
				$this->has_banner = true;
				$this->banner_id = $term_banner_id;
			}
		}

		if ( is_category() || is_tax( $this->get_allowed_taxonomies() ) ) {
			$term_banner_id = get_term_meta( $this->post_id, 'banner', true );

			if ( ! empty( $term_banner_id ) ) {
				$classes[] = 'page-has-banner';
				$this->has_banner = true;
				$this->banner_id = $term_banner_id;
			}
		}

		if ( true === $this->placeholder && true !== $banner_disabled && '1' !== $banner_disabled && ! is_404() ) {
			$classes[] = 'page-has-banner';
			$this->has_banner = true;
		}

		if ( true === $this->has_banner ) {
			if ( ! empty( $this->post_id ) ) {
				$image_bg_group = get_post_meta( $this->post_id, 'image_bg_group', true );

				if ( false !== $image_bg_group && is_array( $image_bg_group ) ) {
					if ( isset( $image_bg_group['banner_full_height'] ) ) {
						$full_height = $image_bg_group['banner_full_height'];

						if ( ! empty( $full_height ) ) {
							$classes[] = 'page-has-banner-full';
						}
					}
				}
			}

			remove_action( 'lsx_content_wrap_before', 'lsx_global_header' );
		}

		return $classes;
	}

	/**
	 * a filter to check if a custom title has been added, if so, use that instead of the post title
	 */
	public function banner_title( $post_title ) {
		$allowed_post_types = $this->get_allowed_post_types();
		$allowed_taxonomies = $this->get_allowed_taxonomies();

		if ( is_post_type_archive( $allowed_post_types ) ) {
			$post_title = '<h1 class="page-title">' . get_the_archive_title() . '</h1>';
		} elseif ( is_tax( $allowed_taxonomies ) || is_category() ) {
			$post_title = '<h1 class="page-title">' . single_term_title( '', false ) . '</h1>';
		} elseif ( is_author() ) {
			$post_title = '<h1 class="page-title">' . get_the_archive_title() . '</h1>';
		} elseif ( apply_filters( 'lsx_banner_enable_title', true ) && ! empty( $this->post_id ) ) {
			$new_title = get_post_meta( $this->post_id, 'banner_title', true );

			if ( ! empty( $new_title ) ) {
				$post_title = '<h1 class="page-title">' . $new_title . '</h1>';
			}
		}

		return $post_title;
	}

	/**
	 * Outputs the banner content, usually a short tagline.
	 */
	public function banner_content() {
		switch ( $this->theme ) {
			case 'lsx':
				ob_start();
				lsx_banner_content();
				$retval = ob_get_clean();
			break;

			default:
				$retval = apply_filters( 'lsx_banner_content','' );
			break;
		}

		return $retval;
	}

	/**
	 * A filter that outputs the tagline for the current page.
	 */
	public function banner_tagline() {
		$allowed_post_types = $this->get_allowed_post_types();
		$allowed_taxonomies = $this->get_allowed_taxonomies();
		$tagline            = false;
		$content            = '';

		if ( is_front_page() ) {
			$content = get_post( $this->post_id );
			$content = $content->post_content;

			if ( ! empty( $content ) ) {
				$content = apply_filters( 'the_content', $content );
				$content = '<div class="banner-content-from-post">' . $content . '</div>';
			}

			if ( apply_filters( 'lsx_banner_enable_subtitle', true ) && ! empty( $this->post_id ) ) {
				$new_tagline = get_post_meta( $this->post_id, 'banner_subtitle', true );

				if ( ! empty( $new_tagline ) ) {
					$tagline = '<p class="tagline">' . $new_tagline . '</p>';
				}
			}
		} elseif ( is_post_type_archive( $allowed_post_types ) && isset( $this->options[ get_post_type() ] ) && isset( $this->options[ get_post_type() ]['tagline'] ) ) {
			$new_tagline = $this->options[ get_post_type() ]['tagline'];

			if ( ! empty( $new_tagline ) ) {
				$tagline = '<p class="tagline">' . $new_tagline . '</p>';
			}
		} elseif ( is_tax( $allowed_taxonomies ) || is_category() ) {
			$new_tagline = get_term_meta( $this->post_id, 'tagline', true );

			if ( ! empty( $new_tagline ) ) {
				$tagline = '<p class="tagline">' . $new_tagline . '</p>';
			}
		} elseif ( apply_filters( 'lsx_banner_enable_subtitle', true ) && ! empty( $this->post_id ) ) {
			$new_tagline = get_post_meta( $this->post_id, 'banner_subtitle', true );

			if ( ! empty( $new_tagline ) ) {
				$tagline = '<p class="tagline">' . $new_tagline . '</p>';
			}
		}

		if ( ! empty( $content ) ) {
			$tagline = $tagline . $content;
		}

		if ( ! empty( $tagline ) ) {
			echo wp_kses_post( $tagline );
		}
	}

	/**
	 * Handle fonts that might be change by LSX Customiser
	 */
	public function customizer_fonts_handler( $css_fonts ) {
		global $wp_filesystem;

		$css_fonts_file = LSX_BANNERS_PATH . '/assets/css/lsx-banners-fonts.css';

		if ( file_exists( $css_fonts_file ) ) {
			if ( empty( $wp_filesystem ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				WP_Filesystem();
			}

			if ( $wp_filesystem ) {
				$css_fonts .= $wp_filesystem->get_contents( $css_fonts_file );
			}
		}

		return $css_fonts;
	}

	/**
	 * Handle colours that might be change by LSX Customiser
	 */
	public function customizer_colours_handler( $css, $colors ) {
		$css .= '
			@import "' . LSX_BANNERS_PATH . '/assets/css/scss/customizer-banners-banner-colours";

			/**
			 * LSX Customizer - LSX Banners
			 */
			@include customizer-banners-banner-colours (
				$color: ' . $colors['banner_text_image_color'] . '
			);
		';

		return $css;
	}

}
