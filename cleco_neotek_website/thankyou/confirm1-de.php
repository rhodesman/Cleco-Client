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
        $title = strip_tags(trim($_POST["title"]));
    		$title = str_replace(array("\r","\n"),array(" "," "),$title);
        $fname = strip_tags(trim($_POST["first_name"]));
    		$fname = str_replace(array("\r","\n"),array(" "," "),$fname);
    		$lname = strip_tags(trim($_POST["last_name"]));
    		$lname = str_replace(array("\r","\n"),array(" "," "),$lname);
    		$cname = strip_tags(trim($_POST["company_name"]));
    		$cname = str_replace(array("\r","\n"),array(" "," "),$cname);
        $street = strip_tags(trim($_POST["address"]));
    		$street = str_replace(array("\r","\n"),array(" "," "),$street);
        $zip = strip_tags(trim($_POST["zip"]));
    		$zip = str_replace(array("\r","\n"),array(" "," "),$zip);
        $city = strip_tags(trim($_POST["city"]));
    		$city = str_replace(array("\r","\n"),array(" "," "),$city);
        $country = strip_tags(trim($_POST["Country"]));
    		$country = str_replace(array("\r","\n"),array(" "," "),$country);
    		$phone = strip_tags(trim($_POST["phone"]));
    		$phone = str_replace(array("\r","\n"),array(" "," "),$phone);
    		$email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $product = $_POST['product'];
        $newsletter = $_POST['optinNews'];

        if ( !empty($_POST) ) {
            //// SEND FORM SUBMISSION TO CLEVERREACH \\\\
            setup();


            //$listId = '230932';
            //$form_id = '8372-138131';
            // https://seu.cleverreach.com/f/8372-138494/
            $listId = '231246';
            $form_id = '8372-138494';

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
                    "postal" => $zip,
                    "ANREDE" => $title,
                    "PLZ" =>  $zip,
                    "LAND" => $country,
                    "ORT" => $city,
                    "PRODUKTKATEGORIE" => $product,
                    "ADRESSE" => $street,
                    "TELEFON" => $phone,
                    "VORNAME" => $fname,
                    "NACHNAME" => $lname
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


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/favicon.ico">

    <title>Cleco - Neotek</title>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ"
        crossorigin="anonymous">
    <link href="/assets/css/main.css" rel="stylesheet">

</head>

<body>

    <div id="overview"></div>

    <nav id="homeNav" class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="http://www.clecotools.com">
                <img src="/images/logo-cleco.png" height="60" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="nav navbar-nav ml-auto" id="nav-grinder">
                    <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Deutsch.html#overview">
                                <span>Übersicht</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Deutsch.html#productView">
                                <span>Produkt Ansicht</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Deutsch.html#productDetails">
                                <span>Produkt Details</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Deutsch.html#specifications">
                                <span>Spezifikationen</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Deutsch.html#contact">
                                <span>Kontakt</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <main role="main" class="container-fluid thank-you" data-spy="scroll" data-target="#homeNav" data-offset="90">
        <section id="thankyou">
            <div class="container">
                <h2>Vielen Dank für Ihr Interesse.</h2>
                <p>Ein Verkaufsmitarbeiter wird sich in Kürze mit Ihnen in Verbindung setzen.</p>
                <p><a href="/index-Deutsch.html" class="btn btn-orange">Zurück</a></p>
            </div>
        </section>
        <!-- Footer -->
        <footer id="footer" class="footer-resources">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <a target="_blank" href="http://www.clecotools.com"><img class="logo" src="/images/logo-cleco.png"
                                alt="logo"></a>
                    </div>
                    <div class="col-md-2">
                        <h6>STANDORTE</h6>
                        <a href="http://www.apex-tools.com/">Besuchen Sie Apex Tools</a>
                        <br />
                        <a href="https://www.weller-tools.com/index.html">Besuchen Sie Weller Tools</a>
                    </div>
                    <div class="col-md-2">
                        <h6>RESSOURCEN</h6>
                        <a href="http://www.clecotools.com/downloads/service-literature">Downloads</a>
                        <br />
                        <a href="http://webmail.apextoolgroup.com/">Kunden Login</a>
                        <br />
                        <a href="http://distributors.apexpowertools.com/">Händler Login</a>
                        <br />
                        <a href="https://www.atgcustomerlink.com/">Mitarbeiter Login</a>
                    </div>
                    <div class="col-md-2">
                        <h6>KONTAKTIEREN SIE UNS</h6>
                        +49 7363-810
                        <br />
                        <a href="http://www.clecotools.com/support/contact">Absenden</a>
                        <br />
                        <a href="http://www.clecotools.com/where-to-buy/request-quote">Anfrageformular</a>
                        <br />
                        <a href="http://www.clecotools.com/privacy-policy">Datenschutzinformationen</a>
                    </div>
                    <div class="col-md-3 socials">
                        <p>
                            <a href="https://www.facebook.com/ClecoPowerTools/" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.youtube.com/channel/UCZ7OgImHsG0Cf9d69FSmtoA" target="_blank">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="https://www.linkedin.com/company/apex-tool-group-llc/" target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </p>
                        <p>©
                        <script type="text/javascript">document.write(new Date().getFullYear());</script><br />Alle Rechte vorbehalten</p>
                    </div>
                </div>
            </div>
        </footer><!-- /footer -->
    </main>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>

</body>
</html>
<?php }
?>
