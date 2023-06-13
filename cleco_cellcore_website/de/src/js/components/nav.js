$(document).ready(function() {

	$("html").on("click", ".hamburger", function(e) {
		$( e.delegateTarget ).toggleClass("has-nav");
		$( e.delegateTarget ).removeClass("has-lang");
		$(this).toggleClass("is-active"); 
		$(".language-toggle").removeClass("is-active");
	});
	
	$("html").on("click", ".language-toggle", function(e) {
		$( e.delegateTarget ).toggleClass("has-lang");
		$( e.delegateTarget ).removeClass("has-nav");	
		$(".hamburger").removeClass("is-active"); 
		$(this).toggleClass("is-active");
	});

});