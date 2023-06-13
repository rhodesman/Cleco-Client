$(document).ready(function() {

	//On field focus move label up
	
	
	$('.form-field').on('focus', '.form-control', function(e) {
		$( e.delegateTarget ).addClass('is-focused'); 
	});
	
	//on de-focus check if there is text entered. If not, move label back.
	
	$('.form-field').on('blur', '.form-control', function(e) {
		var fieldValue = $(this).val();
		if(fieldValue === ""){
			$( e.delegateTarget ).removeClass('is-focused');
			$( e.delegateTarget ).removeClass('is-filled'); 
		}
	});
			

	//on load check any that have text entered and check select box
	
	$('.form-control').each(function (index, value){
		var fieldValue = $(this).val();
		if(fieldValue != ""){
			$(this).parents('.form-field').addClass('is-filled');
		}
	});
	

	

});