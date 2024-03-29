<?php

    // Only process POST reqeusts.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
	    
	     if ( !empty($_POST) ) {
	        $codedb_user = "clecocel_formUse";
	        $password = "2RshO7iuSaoR";
	        $database = "clecocel_formDB";
	        $server = "localhost";
	        $link = mysqli_connect($server, $codedb_user, $password, $database);
	    }
	    	    
	    // Add Submission to DB \\
	    
	    $signupEmail = mysqli_real_escape_string($link, $_POST['signup-email']);
	    $now = new DateTime();
	    $submitted_on = $now->format('Y-m-d H:i:s');
	
	    $sql = "INSERT INTO newsletterSubmit (Email,submitted_on) VALUES ('$signupEmail', '$submitted_on')";
	    mysqli_query($link, $sql);

	    
	    
        // Get the form fields and remove whitespace.
        $email = filter_var(trim($_POST["signup-email"]), FILTER_SANITIZE_EMAIL);

        // Check that data was sent to the mailer.
        if ( !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set a 400 (bad request) response code and exit.
            http_response_code(400);
            echo "Es tut uns leid, aber die von Ihnen übersandten Angaben scheinen fehlerhaft zu sein.";
            exit;
        }

        // Set the recipient email address.
        $recipient = "CustomerService-Lexi@apextoolgroup.com,Christina.schreiter@apextoolgroup.com,Ramona.Voglgsang@apextoolgroup.com,siegmar.schreyer@clecotools.com,jrhodes@webbmason.com";

        // Set the email subject.
        $subject = "New Email Signup for CellCore [German]";

        // Build the email content.
        $email_content = "Email: $email";
      
        // Build the email headers.
        $email_headers = "From: CellCore Signup Form <$email>";
        
        
        // Send the email.
        if (mail($recipient, $subject, $email_content, $email_headers)) {
            // Set a 200 (okay) response code.
            http_response_code(200);
            echo "Vielen Dank für Ihre Registrierung.";
        } else {
            // Set a 500 (internal server error) response code.
            http_response_code(500);
            echo "Es tut uns leid, aber die von Ihnen übersandten Angaben scheinen fehlerhaft zu sein.";
        }

    } else {
        // Not a POST request, set a 403 (forbidden) response code.
        http_response_code(403);
        echo "Es tut uns leid, aber die von Ihnen übersandten Angaben scheinen fehlerhaft zu sein.";
    }

?>