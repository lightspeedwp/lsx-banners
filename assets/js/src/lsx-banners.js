var LSX_Banners = {
	initThis: function() {
		if (jQuery('body').hasClass('page-has-banner')) {
			var $bannerImage = jQuery('#lsx-banner .page-banner.rotating .page-banner-image'),
				bannerImageObj;

			if ($bannerImage.length > 0) {
				bannerImageObj = $bannerImage.attr('data-banners').split(',');
				$bannerImage.css('background-image','url(' + bannerImageObj[Math.floor(Math.random() * bannerImageObj.length)] + ')');
			}
		}
	},

	initScrollable: function() {
		jQuery('.banner-easing a').on('click',function(e) {
			e.preventDefault();

			var $from = jQuery(this),
				$to = jQuery($from.attr('href')),
				top = parseInt($to.offset().top),
				extra = parseInt($from.data('extra-top') ? $from.data('extra-top') : '-160');

			jQuery('html, body').animate({
				scrollTop: (top+extra)
			}, 1200);

			return false;
		});

		jQuery('.banner-easing a i').on('click',function(e) {
			e.stopPropagation();
			jQuery(this).parent().trigger('click');
			return false;
		});
	},

	initSliderSwiper: function() {
		jQuery('#page-banner-slider').swipe({
			swipeLeft:function(event, direction, distance, duration, fingerCount) {
				jQuery(this).carousel('next');
			},

			swipeRight: function() {
				jQuery(this).carousel('prev');
			},

			threshold: 0,
			allowPageScroll: 'vertical'
		});
	}
};

jQuery(document).ready(function() {
	LSX_Banners.initThis();
	LSX_Banners.initScrollable();
	LSX_Banners.initSliderSwiper();
});
