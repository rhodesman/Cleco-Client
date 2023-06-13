$(function() {
	
	$('#signup-form').validate();
	
    // Get the form.
    var form = $('#signup-form');

    // Get the messages div.
    var formMessages = $('#signup-messages');

    // Set up an event listener for the contact form.
	$(form).submit(function(event) {
	    // Stop the browser from submitting the form.
	    event.preventDefault();
		if($(".tripwire").val() != ""){
			console.log("gotcha");
			return false;
		}
		 $('.btn-signup').prop('disabled',true);
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
		    $('#signup-email').val('');
		    $('.btn-signup').prop('disabled', false);
		})
		
		.fail(function(data) {
		    // Make sure that the formMessages div has the 'error' class.
		    $(formMessages).removeClass('success');
		    $(formMessages).addClass('error');
		    $('.btn-signup').prop('disabled', false);
		
		    // Set the message text.
		    if (data.responseText !== '') {
		        $(formMessages).text(data.responseText);
		    } else {
		        $(formMessages).text('Ein Verkaufsmitarbeiter wird sich in Kürze mit Ihnen in Verbindung setzen.');
		    }
		});
		
		

		
	});
	
	    
});