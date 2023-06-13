$(document).ready(function() {

	$(".video-trigger").on("click", function(e) {

		//Launch the Modal
		e.preventDefault();
		$("body").addClass("has-modal");
		
		var videoSrc = $(this).data("video");
		
		//Video Modal
		$(".modal-content").html(
			'<div class="modal-video-container"><iframe src="' + videoSrc + '?autoplay=1&rel=0" frameborder="0" allowfullscreen></iframe></div>'
		)
				
		
		//Fade in the Modal Contents
		$(".modal-overlay").animate({
			opacity:1
		}, {duration: 300, queue: false});
		$(".modal-wrapper").animate({
			opacity:1
		}, {duration: 300, queue: false});
	});

	
	
	//Close the Modal 
	
	function closeModal(){
			$("body").removeClass("has-modal")
			$(".modal-overlay").animate({
				opacity:0
			}, {duration: 100, queue: false});
			$(".modal-wrapper").animate({
				opacity:0
			}, {duration: 100, queue: false});
			$(".modal-content").html("");
		}
		
		// Handle ESC key (key code 27)
		document.addEventListener('keyup', function(e) {
		    if (e.keyCode == 27) {
		        closeModal();
		    }
		});
		
		$(".modal-close").on("click", function(e){
			closeModal();	
		});
		
		$(".modal-wrapper")
	    	.on("click", function(e) {
		       closeModal();
		    })
		    .on("click", ".modal-close", function( e ) {
		       closeModal();
		    })
		    .on("click", ".modal-inner-wrap", function(e) {
		        e.stopPropagation();
	    });
	

});