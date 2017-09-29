/*
 * lsx-banners-admin.js
 */

jQuery(document).ready(function() {

    var lsx_tax_frame;
	/*
	 * Choose Image
	 */
	if (undefined === window.lsx_thumbnail_image_add) {
		jQuery(document).on('click', '.lsx-thumbnail-image-add', function(e) {
			e.preventDefault();
			e.stopPropagation();

			//tb_show('Choose a Featured Image', 'media-upload.php?type=image&feature_image_text_button=1&TB_iframe=1');

			//Save the current object for use in the
			var $this = jQuery(this),
				$td = $this.parent('td');

            if ( lsx_tax_frame ) {
                lsx_tax_frame.open();
                return;
            }

            lsx_tax_frame = wp.media({
                title: 'Select your imageimage',
                button: {
                    text: 'Insert image'
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // When an image is selected in the media frame...
            lsx_tax_frame.on( 'select', function() {

                // Get media attachment details from the frame state
                var attachment = lsx_tax_frame.state().get('selection').first().toJSON();

                // Send the attachment URL to our custom image input field.
                $td.find('.thumbnail-preview, .banner-preview').append('<img width="150" src="' + attachment.url + '" />');

                // Send the attachment id to our hidden input
                $td.find('input.input_image_id').val( attachment.id );
                $td.find('input.input_image').val( attachment.url );

                // Hide the add image link
                $this.hide();

                // Unhide the remove image link
                $td.find('.lsx-thumbnail-image-delete, .lsx-thumbnail-image-remove').show();

            });

            // Finally, open the modal on click
            lsx_tax_frame.open();

            return false;

		});

		window.lsx_thumbnail_image_add = true;
	}

	/*
	 * Delete Image
	 */
	if (undefined === window.lsx_thumbnail_image_delete) {
		jQuery(document).on('click', '.lsx-thumbnail-image-delete, .lsx-thumbnail-image-remove', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var $this = jQuery(this),
				$td = $this.parent('td');

			$td.find('input.input_image_id').val('');
			$td.find('input.input_image').val('');
			$td.find('.thumbnail-preview, .banner-preview' ).html('');
			$this.hide();
			$td.find('.lsx-thumbnail-image-add' ).show();

			return false;
		});

		window.lsx_thumbnail_image_delete = true;
	}

	/*
	 * Subtabs navigation
	 */
	if (undefined === window.lsx_thumbnail_subtabs_nav) {
		jQuery(document).on('click', '.ui-tab-nav a', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var $this = jQuery(this);

			jQuery('.ui-tab-nav a.active').removeClass('active');
			$this.addClass('active');
			jQuery('.ui-tab.active').removeClass('active');
			$this.closest('.uix-field-wrapper').find($this.attr('href')).addClass('active');

			return false;
		});

		window.lsx_thumbnail_subtabs_nav = true;
	}

});
