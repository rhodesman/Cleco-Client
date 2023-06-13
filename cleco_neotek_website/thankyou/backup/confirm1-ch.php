<?php
//if(isset($_POST['email'])) {
if($_SERVER['REQUEST_METHOD']=="POST"){
    // EDIT THE 2 LINES BELOW AS REQUIRED
    $email_to = "Customerservice-lexi@apextoolgroup.com";
    $email_subject = "Schedule Demo Submission - Neotek (Chinese)";

    if ( !empty($_POST) ) {
        $codedb_user = "neotekwm_demo";
        $password = "aVF0jHcP26dA";
        $database = "neotekwm_demodb";
        $server = "localhost";
        $link = mysqli_connect($server, $codedb_user, $password, $database);
    }

    function died($error) {
        // your error code can go here
        echo "很抱歉，您提交的表格有错误";
        echo "错误如下所示 <br /><br />";
        echo $error."<br /><br />";
        echo "请您返回修改错误 <br /><br />";
        die();
    }
    // validation expected data exists
    if(!isset($_POST['First_Name']) ||
        !isset($_POST['Last_Name']) ||
        !isset($_POST['Company_Name']) ||
        !isset($_POST['Zip']) ||
        !isset($_POST['Phone']) ||
        !isset($_POST['Email'])) {
        died('很抱歉，您提交的表格有错误.');       
    }
    // Add Submission to DB \\
    $firstName = mysqli_real_escape_string($link, $_POST['First_Name']);
    $lastName = mysqli_real_escape_string($link, $_POST['Last_Name']);
    $democompany = mysqli_real_escape_string($link, $_POST['Company_Name']);
    $demoZip = mysqli_real_escape_string($link, $_POST['Zip']);
    $demoPhone = mysqli_real_escape_string($link, $_POST['Phone']);
    $demoEmail = mysqli_real_escape_string($link, $_POST['Email']);
    $newsLetter = mysqli_real_escape_string($link, $_POST['optinNews']);
    $now = new DateTime();
    $submitted_on = $now->format('Y-m-d H:i:s');

    $sql = "INSERT INTO formSubmit (FName,LName,Company,ZipCode,PhoneNum,Email,submitted_on,NewsLetter) VALUES ('$firstName', '$lastName', '$democompany', '$demoZip', '$demoPhone', '$demoEmail', '$submitted_on', '$newsLetter')";
    mysqli_query($link, $sql);
 
    $first_name = $_POST['First_Name']; // required
    $last_name = $_POST['Last_Name']; // required
    $company = $_POST['Company_Name']; // required
    $zipcode = $_POST['Zip']; // not required
    $phone = $_POST['Phone']; // required
    $email_from = $_POST['Email']; // required
    $news_letter = $_POST['optinNews'];
 
    $error_message = "";
    $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
 
  if(!preg_match($email_exp,$email_from)) {
    $error_message .= '您输入的邮箱地址无效.<br />';
  }
 
    $string_exp = "/^[A-Za-z .'-]+$/";
 
  if(!preg_match($string_exp,$first_name)) {
    $error_message .= '您输入的名字无效.<br />';
  }
 
  if(!preg_match($string_exp,$last_name)) {
    $error_message .= '您输入的姓氏无效.<br />';
  }
 
  if(strlen($error_message) > 0) {
    died($error_message);
  }
 
    $email_message = "Form details below.\n\n";
 
     
    function clean_string($string) {
      $bad = array("content-type","bcc:","to:","cc:","href");
      return str_replace($bad,"",$string);
    }
 
     
 
    $email_message .= "First Name: ".clean_string($first_name)."\n";
    $email_message .= "Last Name: ".clean_string($last_name)."\n";
    $email_message .= "Company: ".clean_string($company)."\n";
    $email_message .= "ZipCode: ".clean_string($zipcode)."\n";
    $email_message .= "Email: ".clean_string($email_from)."\n";
    $email_message .= "Telephone: ".clean_string($phone)."\n";
    $email_message .= "OptIn to Newsletter: ".clean_string($news_letter)."\n";
 
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
    <link rel="icon" href="/favicon.ico">

    <title>Cleco - Neotek</title>

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ"
        crossorigin="anonymous">
    <link href="/assets/css/main.css" rel="stylesheet">

</head>

<body id="chinese">

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
                            <a class="nav-link" href="/index-Chinese.html#overview">
                                <span>概述</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Chinese.html#productView">
                                <span>产品视图</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Chinese.html#productDetails">
                                <span>产品详情</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Chinese.html#specifications">
                                <span>规格</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/index-Chinese.html#contact">
                                <span>联系方式</span>
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
                <h2>谢谢您的关注.</h2>
                <p>我们的销售代表会尽快与您联系.</p>
                <p><a href="/index-Chinese.html" class="btn btn-orange">回到家</a></p>
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
                        <h6>网站</h6>
                        <a href="http://www.apex-tools.com/">跳转至APEX工具
                        </a>
                        <br />
                        <a href="https://www.weller-tools.com/index.html">跳转至Weller工具</a>
                    </div>
                    <div class="col-md-2">
                        <h6>资源</h6>
                        <a href="http://www.clecotools.com/downloads/service-literature">下载</a>
                        <br />
                        <a href="http://webmail.apextoolgroup.com/">客户登录</a>
                        <br />
                        <a href="http://distributors.apexpowertools.com/">经销商登录</a>
                        <br />
                        <a href="https://www.atgcustomerlink.com/">员工登陆</a>
                    </div>
                    <div class="col-md-2">
                        <h6>联系我们</h6>
                        800.845.5629
                        <br />
                        <a href="http://www.clecotools.com/support/contact">发送信息</a>
                        <br />
                        <a href="http://www.clecotools.com/where-to-buy/request-quote">咨询报价</a>
                        <br />
                        <a href="http://www.clecotools.com/privacy-policy">隐私政策</a>
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
                            <script type="text/javascript">document.write(new Date().getFullYear());</script><br />保留所有权利</p>
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