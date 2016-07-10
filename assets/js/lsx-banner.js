var LSX_Banners = {
	initThis: function(){
		if (jQuery('body').hasClass('page-has-banner')) {
			var $bannerImage = jQuery('#lsx-banner .page-banner.rotating .page-banner-image'),
				bannerImageObj = $bannerImage.attr('data-banners').split(',');
			$bannerImage.css('background-image','url(' + bannerImageObj[Math.floor(Math.random() * bannerImageObj.length)] + ')');
		}
	}
}
jQuery(document).ready(function() {
	LSX_Banners.initThis(); 
});