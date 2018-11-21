=== LSX Banners ===
Contributors: feedmymedia
Tags: LSX Theme, custom header, featured image, header images, banner
Donate link: https://www.lsdev.biz/product/lsx-banners/
Requires at least: 4.3
Tested up to: 4.9.4
Requires PHP: 7.0
Stable tag: 1.1.6
License: GPLv3

LightSpeed’s LSX Banner Extension plugin is here to help you add advanced banner configuration options to your Wordpress site running LSX Theme.

== Description ==
LightSpeed’s LSX Banner Extension plugin is here to help you add advanced banner configuration options to your Wordpress site running LSX Theme.

The plugin features customized banner options for each page on your site. This feature makes adding text to your banner super simple, also you can add buttons that can be transformed in call to actions. 

The customization area allows you to set a custom height, change the X and Y position of the banner, add logos and background colors.  The extension also add the functionality of mp4 video banners, as well as the option for banner rotation, so that the page loads a different image each time it’s loaded.

Users will also find that LSX Banners works with Soliloquy Sliders, allowing you to embed sliders as your pages’ banners. The banners can be fully customizer or even disable individually on each page.

We offer premium support for this plugin. Premium support that can be purchased via [lsdev.biz](https://www.lsdev.biz/).

= Works with the LSX Theme =
LSX Banners is an [LSX Theme](https://www.lsdev.biz/product/lsx-wordpress-theme/) powered plugin. It integrates seamlessly with the core LSX functionality to provide you will powerful options for creating your online Projects.

= It's Free, and always ill be =
We’re firm believers in open source – that’s why we’re releasing the LSX Banners plugin for free, forever. 

= Support =
We offer premium support for this plugin. Premium support that can be purchased via [lsdev.biz](https://www.lsdev.biz/).

= Actively Developed =
The LSX Banners plugin is actively developed with new features and exciting enhancements all the time. Keep track on the LSX Banners GitHub repository.
Report any bugs via github issues.

== Installation ==
1. Log in to your WordPress website (www.yourwebsiteurl.com/wp-admin).
2. Navigate to “Plugins”, and select “Add New”.
3. Upload the .zip file you downloaded and click install now.
4. When the installation is complete, Activate your plugin.
5. After installation new Banners options will appear under your LSX Theme options.

== Frequently Asked Questions ==
= What does this plugin do? =
The LSX Banners plugin allows you to add images to posts and pages in the featured image section, and have that featured image display as a full-width hero banner at the top of the post/page along with the title of the content you are loading.
= How do I add a new banner? =
- Log in to the back-end of your website
- Select either the “Posts” or “Pages” menu item from the WordPress Dashboard
- Either select “add new” or select a previously uploaded post/page
- When the page content is added, select “preview” before adding a featured image or publishing the post/page
- This will take you to a live preview of your page/post
- You will notice that the title of the post/page is displayed in a full-width banner section at the top of the page
- Navigate back to the editing area of your post/page, and navigate to the field on the right of the screen titled “Featured Image”
- Select the “Set featured image” option
- Select a previously uploaded image or upload an image to your library
*Note: The image chosen is to be displayed as a full-width banner image, and must be equal or over 2000 pixels in width, and 800 pixels in height. - Do not worry about resizing the image if it is larger, as the image is automatically resized to fit the banner area.
- Once you have selected a suitable image, select the “Preview” option once again.
- You will notice that the title of the post/page is displayed in a full-width banner section above the banner image you selected.
= Where can I find LSX Banners plugin documentation and user guides? =
For help setting up and configuring the LSX Banners plugin please refer to our [user guide](https://www.lsdev.biz/documentation/lsx/banners-extension/). For extra support, please subscribe to our support plan.
= Where can I get support? =
For help with add-ons from LightSpeed, use our support package plan.
= Your plugin doesn’t work =
The problem is likely with your theme. This plugin only works with the [LSX Theme](https://www.lsdev.biz/product/lsx-wordpress-theme/).
= Will the LSX Banners plugin work with my theme? =
No; the LSX Banners plugin requires some styling and functionality only available for the [LSX Theme](https://www.lsdev.biz/product/lsx-wordpress-theme/). You need to install the [LSX Theme](https://www.lsdev.biz/product/lsx-wordpress-theme/) for this extension to work properly.
= Where can I report bugs or contribute to the project? =
Bugs can be reported either in our support account or preferably on the LSX Banners [GitHub repository](https://github.com/lightspeeddevelopment/lsx-banners).
= The LSX Banners plugin is awesome! Can I contribute? =
Yes you can! Join in on our [GitHub repository](https://github.com/lightspeeddevelopment/lsx-banners)  🙂
= What are the server requirements for running the LSX Theme and the LSX Banners plugin? =
Your WordPress website needs to be running PHP version 7.0 or higher in order to make use of the LSX theme and related plugins.
= I need custom functionality for this plugin. Can you build it? =
Yes. Just send us a message via [contact form](https://www.lsdev.biz/contact/) with precise information about what you require.

== Screenshots ==
1. The LSX Theme setting has a LSX Banners tab
2. LSX Banner pannel pt1
3. LSX Banner pannel pt2
4. LSX Banner pannel pt3
5. LSX Banner working on a page

== Changelog ==

### 1.1.4
* Dev - Added in an "image size" dropdown to the banner metabox panel so you can choose your image size for posts and page banners
* Dev - Changed the Taxonomy banners to call a full image by default.
* Dev - Making sure slick.min.js slider is present, this is usually when using LSX banners with a non LSX theme.
* Dev - Added in a way to allow the "CMB" field vendor to be excluded.  `define( 'LSX_BANNER_DISABLE_CMB', true );`

### 1.1.3
* Dev - Changed the "Banner" field nonce on the taxonomy term edit pages.
* Fix - Fixed the edit term "thumbnail" preview.
* Fix - Fixed the missing placeholder image settings.
* Dev - Added in integration for WP Forms

### 1.1.2
* Dev - Added in a template tag which returns if the current item is disabled - lsx_is_banner_disabled()

### 1.1.1
* Dev - Added compatibility with LSX Videos
* Dev - Added compatibility with LSX Search
* Dev - Set default banner background colour to black and text colour to white

### 1.1.0
* Added compatibility with LSX 2.0
* Dev - New project structure
* Dev - Added in a filter to allow you to disable the banner altogether
* Dev - Updated the "Add Image" JS for the term image selection (using wp.media)
* Dev - Added compatibility to Envira Gallery
* Dev - Added compatibility to Soliloquy
* Dev - UIX copied from TO 1.1 + Fixed issue with sub tabs click (settings)
* Dev - New image size: square ('lsx-thumbnail-square')
* Dev - New filter: banner image (lsx_banner_image)
* Fix - Updated the license class to work with the new settings button
* Fix - Multiple images - Randomize banners when there is more than one
* Fix - Text/tagline front-end and all fields back-end reviewed (made all visible from default)
* Fix - Scripts from CMB loading first than WC scripts (to avoid WC load its select2 script - it breaks the CMB select)
* Fix - LSX tabs working integrated with TO tabs (dashboard settings)
* Tweak - Added new option to make the banner full height or not
* Tweak - Made the banner slider option uses Slick Slider
* Tweak - Added new fields: button (text, link, class, link/anchor/modal), logo, background colour, text colour

### 1.0.6
* Fix - Added the missing "full" image size to the placeholder class.
* Fix - Before try use the attachment URL, test if it's available

### 1.0.5
* Fix - Display tagline in blog page
* Fix - Adjusted the plugin settings link inside the LSX API Class
* Fix - Fixed the "banner height" attribute on front-end
* Fix - Fixed banner title on archives
* Fix - Fixed the feature from add/remove images on WP term pages
* Updated the CMB class with custom Google Maps code.

### 1.0.4
* Feature - Use Soliloquy HTML in front-end slider when it's a Soliloquy slider selected in back-end
* Feature - New option to disable the banner title per page/post

### 1.0.3
* Fix - Init variable as array and not string to avoid PHP fatal error

### 1.0.2
* Fix - Fixed all prefixes replaces (to_ > lsx_to_, TO_ > LSX_TO_)

### 1.0.1
* Fix - Reduced the access to server (check API key status) using transients
* Fix - Made the API URLs dev/live dynamic using a prefix "dev-" in the API KEY

### 1.0.0
* First Version

== Upgrade Notice ==
Upgrade Notice
