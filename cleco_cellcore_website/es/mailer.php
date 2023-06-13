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
	   	    
	   	if (mysqli_connect_errno()) {
		    printf("Connect failed: %s\n", mysqli_connect_error());
		    exit();
		}    
		
	    // Add Submission to DB \\
	    
	    $firstName = mysqli_real_escape_string($link, $_POST['first_name']);
	    $lastName = mysqli_real_escape_string($link, $_POST['last_name']);
	    $democompany = mysqli_real_escape_string($link, $_POST['company_name']);
	    $demoZip = mysqli_real_escape_string($link, $_POST['zip']);
	    $demoPhone = mysqli_real_escape_string($link, $_POST['phone']);
	    $demoEmail = mysqli_real_escape_string($link, $_POST['email']);
	    $newsLetter = mysqli_real_escape_string($link, $_POST['optinNews']);
	    $now = new DateTime();
	    $submitted_on = $now->format('Y-m-d H:i:s');
		
		
		
 	    $sql = "INSERT INTO formSubmit (FName,LName,Company,ZipCode,PhoneNum,Email,submitted_on,NewsLetter) VALUES ('$firstName', '$lastName', '$democompany', '$demoZip', '$demoPhone', '$demoEmail', '$submitted_on', '$newsLetter')";

	    mysqli_query($link, $sql);
	   
		
	    
        // Get the form fields and remove whitespace.
        $fname = strip_tags(trim($_POST["first_name"]));
		$fname = str_replace(array("\r","\n"),array(" "," "),$fname);
		
		$lname = strip_tags(trim($_POST["last_name"]));
		$lname = str_replace(array("\r","\n"),array(" "," "),$lname);
		
		$cname = strip_tags(trim($_POST["company_name"]));
		$cname = str_replace(array("\r","\n"),array(" "," "),$cname);
		
		$zip = strip_tags(trim($_POST["zip"]));
		$zip = str_replace(array("\r","\n"),array(" "," "),$zip);
		
		$phone = strip_tags(trim($_POST["phone"]));
		$phone = str_replace(array("\r","\n"),array(" "," "),$phone);
		
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        
        $newsletter = $_POST['optinNews'];

        // Check that data was sent to the mailer.
        if ( empty($fname) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set a 400 (bad request) response code and exit.
            http_response_code(400);
            echo "Lo sentimos pero parece que hay un problema en el formulario enviado";
            exit;
        }

        // Set the recipient email address.
        $recipient = "CustomerService-Lexi@apextoolgroup.com, tequila.spears@clecotools.com";

        // Set the email subject.
        $subject = "New Cellcore Demo Request from $fname [Spanish]";

        // Build the email content.
        $email_content = "First Name: $fname\n";
        $email_content .= "Last Name: $lname\n";
        $email_content .= "Company Name: $cname\n";
        $email_content .= "Zip: $zip\n";
        $email_content .= "Phone: $phone\n";
        $email_content .= "Email: $email\n";
        $email_content .= "Opted In?: $newsletter\n";

        // Build the email headers.
        $email_headers = "From: $fname $lname <$email>";
        
        
        // Send the email.
        if (mail($recipient, $subject, $email_content, $email_headers)) {
            // Set a 200 (okay) response code.
            http_response_code(200);
            echo "Un representante de ventas se pondra en contacto con usted en breve.";
        } else {
            // Set a 500 (internal server error) response code.
            http_response_code(500);
            echo "Lo sentimos pero parece que hay un problema en el formulario enviado";
        }

    } else {
        // Not a POST request, set a 403 (forbidden) response code.
        http_response_code(403);
        echo "Lo sentimos pero parece que hay un problema en el formulario enviado";
    }

?>