$(document).ready(function() {

	$('.spec-tab-accordion-title').on('click', function(e) {
		$(this).toggleClass('is-active').next().slideToggle(200);
		
	});	

});