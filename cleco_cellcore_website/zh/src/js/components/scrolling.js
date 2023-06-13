$(document).ready(function() {
	$('.scroll').on('click', function(e) {
		$('html').removeClass('has-nav');
        $('.hamburger').removeClass('is-active');
		var id = $(this).attr("href");
        var offset = 140;
        var target = $(id).offset().top - offset;
        
        $("html, body").animate({
	        scrollTop:target
	    }, 1000);
        e.preventDefault();    
	});
	
});