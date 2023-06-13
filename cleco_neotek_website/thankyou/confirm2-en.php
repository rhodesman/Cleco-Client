<?php
//if(isset($_POST['email'])) {
if($_SERVER['REQUEST_METHOD']=="POST"){
    // EDIT THE 2 LINES BELOW AS REQUIRED
    $email_to = "CustomerService-Lexi@apextoolgroup.com, tequila.spears@clecotools.com";
    $email_subject = "Email Sign Up - Neotek (English)";
    
    if ( !empty($_POST) ) {
        $codedb_user = "neotekwm_demo";
        $password = "aVF0jHcP26dA";
        $database = "neotekwm_demodb";
        $server = "localhost";
        $link = mysqli_connect($server, $codedb_user, $password, $database);
    }

    function died($error) {
        // your error code can go here
        echo "We are very sorry, but there were error(s) found with the form you submitted. ";
        echo "These errors appear below.<br /><br />";
        echo $error."<br /><br />";
        echo "Please go back and fix these errors.<br /><br />";
        die();
    }
 
 
    // validation expected data exists
    if(!isset($_POST['Email'])) {
        died('We are sorry, but there appears to be a problem with the form you submitted.');       
    }
 
    $demoEmail = mysqli_real_escape_string($link, $_POST['Email']);
    $now = new DateTime();
    $submitted_on = $now->format('Y-m-d H:i:s');

    $sql = "INSERT INTO formSubmit (FName,LName,Company,ZipCode,PhoneNum,Email,submitted_on,NewsLetter) VALUES ('', '', '', '', '', '$demoEmail', '$submitted_on', 'YES')";
    mysqli_query($link, $sql);

    $email_from = $_POST['Email']; // required
 
    $error_message = "";
    $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
 


  if(!preg_match($email_exp,$email_from)) {
    $error_message .= 'The Email Address you entered does not appear to be valid.<br />';
  }

  if(strlen($error_message) > 0) {
    died($error_message);
  }
 
    $email_message = "Form details below.\n\n";
 
     
    function clean_string($string) {
      $bad = array("content-type","bcc:","to:","cc:","href");
      return str_replace($bad,"",$string);
    }
 
    $email_message .= "Email: ".clean_string($email_from)."\n";
 
// create email headers
$headers = 'From: '.$email_from."\r\n".
'Reply-To: '.$email_from."\r\n" .
'X-Mailer: PHP/' . phpversion();
@mail($email_to, $email_subject, $email_message, $headers);  
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

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
                            <a class="nav-link" href="/index.html#overview">
                                <span>Overview</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.html#productView">
                                <span>Product View</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.html#productDetails">
                                <span>Product Details</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.html#specifications">
                                <span>Specifications</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index.html#contact">
                                <span>Contact</span>
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
                <h2>Thank you for signing up.</h2>
                <p>You will receive a confirmation by e-mail. Only by clicking the link given there, your newsletter
                    subscription
                    is activated.</p>
                <p><a href="/index.html" class="btn btn-orange">Return to Home</a></p>
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
                        <h6>Sites</h6>
                        <a target="_blank" href="http://www.apex-tools.com/">Visit APEX Tools</a><br />
                        <a target="_blank" href="https://www.weller-tools.com/index.html">Visit Weller Tools</a>
                    </div>
                    <div class="col-md-2">
                        <h6>Resources</h6>
                        <a target="_blank" href="http://www.clecotools.com/downloads/service-literature">Downloads</a><br />
                        <a target="_blank" href="http://webmail.apextoolgroup.com/">Customer Login</a><br />
                        <a target="_blank" href="http://distributors.apexpowertools.com/">Distributor Login</a><br />
                        <a target="_blank" href="https://www.atgcustomerlink.com/">Employee Login</a>
                    </div>
                    <div class="col-md-2">
                        <h6>Contact Us</h6>
                        800.845.5629<br />
                        <a target="_blank" href="http://www.clecotools.com/support/contact">Send Message</a><br />
                        <a target="_blank" href="http://www.clecotools.com/where-to-buy/request-quote">Request Quote</a><br />
                        <a target="_blank" href="http://www.clecotools.com/privacy-policy">Privacy Policy</a>
                    </div>
                    <div class="col-md-3 socials">
                        <p>
                            <a target="_blank" href="https://www.facebook.com/ClecoPowerTools/" target="_blank"><i
                                    class="fab fa-facebook-f"></i></a>
                            <a target="_blank" href="https://www.youtube.com/channel/UCZ7OgImHsG0Cf9d69FSmtoA" target="_blank"><i
                                    class="fab fa-youtube"></i></a>
                            <a target="_blank" href="https://www.linkedin.com/company/apex-tool-group-llc/" target="_blank"><i
                                    class="fab fa-linkedin-in"></i></a>
                        </p>
                        <p>©
                            <script type="text/javascript">document.write(new Date().getFullYear());</script><br />All
                            rights reserved</p>
                    </div>
                </div>
            </div>
        </footer><!-- /footer -->
    </main>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    

</body>
</html>
<?php
 
}
?>