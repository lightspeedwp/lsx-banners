var LSX_Banners = {
    initThis: function(){
    	if(jQuery('body').hasClass('has-banner')){
    		var bannerObj = false;
            bannerObj = jQuery('#lsx-banner .page-banner.rotating').attr('data-banners').split(',');
            console.log(bannerObj[Math.floor(Math.random() * bannerObj.length)]);
    		jQuery('#lsx-banner .page-banner.rotating').css('background-image','url(' + bannerObj[Math.floor(Math.random() * bannerObj.length)] + ')');
    	}
    }
}
jQuery(document).ready( function() {
	LSX_Banners.initThis(); 
});