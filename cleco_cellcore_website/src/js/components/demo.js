$(function() {
	
	$('#demo-form').validate();
	
    // Get the form.
    var form = $('#demo-form');

    // Get the messages div.
    var formMessages = $('#demo-messages');

    // Set up an event listener for the contact form.
	$(form).submit(function(event) {
	    // Stop the browser from submitting the form.
	    event.preventDefault();
	    
	    $('.submit-btn').prop('disabled',true);
	    
		if($('.tripwire').val() != ""){
			console.log("gotcha");
			return false;
		}
		
	    // Serialize the form data.
		var formData = $(form).serialize();
	
		// Submit the form using AJAX.
		$.ajax({
		    type: 'POST',
		    url: $(form).attr('action'),
		    data: formData
		})
		
		.done(function(response) {
		    // Make sure that the formMessages div has the 'success' class.
		    $(formMessages).removeClass('error');
		    $(formMessages).addClass('success');
		
		    // Set the message text.
		    $(formMessages).text(response);
		
		    // Clear the form.
		    $('#first_name').val('').parent().removeClass('is-focused');
		    $('#last_name').val('').parent().removeClass('is-focused');
		    $('#company_name').val('').parent().removeClass('is-focused');
		    $('#zip').val('').parent().removeClass('is-focused');
		    $('#email').val('').parent().removeClass('is-focused');
		    $('#phone').val('').parent().removeClass('is-focused');
			$('.submit-btn').prop('disabled', false);

		})
		
		.fail(function(data) {
		    // Make sure that the formMessages div has the 'error' class.
		    $('.submit-btn').prop('disabled', false);
		    $(formMessages).removeClass('success');
		    $(formMessages).addClass('error');
		
		    // Set the message text.
		    if (data.responseText !== '') {
		        $(formMessages).text(data.responseText);
		    } else {
		        $(formMessages).text('Oops! An error occured and your message could not be sent.');
		    }
		});
		
		

		
	});
	
	    
});