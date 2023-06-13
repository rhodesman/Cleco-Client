$(document).ready(function() {

	$('.tab-container').on('click', '.tab-trigger', function(e) {
		var tabTarget = $(this).data('tab');
		if(!$('#' + tabTarget).hasClass('is-active')){
			$( e.delegateTarget ).find('.tab-trigger.is-active').removeClass('is-active');
			$( e.delegateTarget ).find('.tab.is-active').removeClass('is-active');
			$('#' + tabTarget).addClass('is-active');
			$(this).addClass('is-active');
		}
	});	

});