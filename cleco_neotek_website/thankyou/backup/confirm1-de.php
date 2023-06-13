<?php
    $api = null;

    function getAuth()
    {
        $clientid     = 'ub6jlRerq0';
        $clientsecret = '9ugS21kyuV24PTmpkqS0Vmb2GxUVZkV9';

        $token_url = 'https://rest.cleverreach.com/oauth/token.php';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $token_url);
        curl_setopt($curl, CURLOPT_USERPWD, $clientid . ':' . $clientsecret);
        curl_setopt($curl, CURLOPT_POSTFIELDS, ['grant_type' => 'client_credentials']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result);
    }

    function setup()
    {
        global $api;
        
        require __DIR__ . '/rest_client.php';
        $api = new \CR\tools\rest('https://rest.cleverreach.com/v3');
        $api->setAuthMode('bearer', getAuth()->access_token);
    }
    
    if($_SERVER['REQUEST_METHOD']=="POST"){

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

        if ( !empty($_POST) ) {
            //// SEND FORM SUBMISSION TO CLEVERREACH \\\\
            setup();

            $listId = '230932';
            $form_id = '8372-138131';

            $user = array(
                'email'             => $email,
                'registered'        => time(),
                'activated'         => 0,
                'source'            => 'Neotek German Demo',
                'global_attributes' => array(
                    "last_name" => $lname,
                    "opt_in" => $newsletter,
                    "first_name" => $fname,
                    "company" => $cname,
                    "phone" => $phone,
                    "postal" => $zip
                )
            );
            if( $success = $api->post("/groups.json/{$listId}/receivers", $user) ) {
                $api->post("/forms/{$form_id}/send/activate", array(
                    "email"   => $user["email"],
                    "doidata" => array(
                        "user_ip"    => $_SERVER["REMOTE_ADDR"],
                        "referer"    => $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"],
                        "user_agent" => $_SERVER["HTTP_USER_AGENT"]
                    )
                ));
            }
        }   

        //// SEND FORM SUBMISSION TO CLECO VIA EMAIL \\\\

        // Check that data was sent to the mailer.
        if ( empty($fname) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set a 400 (bad request) response code and exit.
            http_response_code(400);
            echo "Es tut uns leid, aber die von Ihnen übersandten Angaben scheinen fehlerhaft zu sein.";
            exit;
        }

        // Set the recipient email address.
        $recipient = "CustomerService-Lexi@apextoolgroup.com,Christina.schreiter@apextoolgroup.com,Ramona.Voglgsang@apextoolgroup.com,siegmar.schreyer@clecotools.com,jrhodes@webbmason.com,rbrocious@webbmason.com";
        
        // Set the email subject.
        $subject = "New Neotek Demo Request from $fname [German]";

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
            echo "Ein Verkaufsmitarbeiter wird sich in Kürze mit Ihnen in Verbindung setzen.";
        } else {
            // Set a 500 (internal server error) response code.
            http_response_code(500);
            echo "Es tut uns leid, aber die von Ihnen übersandten Angaben scheinen fehlerhaft zu sein.";
        }

    }else {
        // Not a POST request, set a 403 (forbidden) response code.
        http_response_code(403);
        echo "Es tut uns leid, aber die von Ihnen übersandten Angaben scheinen fehlerhaft zu sein.";
    }
?>