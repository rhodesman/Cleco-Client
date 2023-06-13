<?php

require 'rest_client.php';
//$rest = new CR\tools\rest("https://rest.cleverreach.com/v2");
$rest = new CR\tools\rest("https://rest.cleverreach.com/v3");
$rest->throwExceptions = true;	//default
echo "<pre>";

echo "### Login - will retrieve Token ###\n";
try {
	/*
	try to login and receive token!
	on error script execution will be cancled
	token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImtpZCI6IjIwMTYifQ.eyJpc3MiOiJyZXN0LmNsZXZlcnJlYWNoLmNvbSIsImlhdCI6MTU1MTcyNTk2NiwiZXhwIjoxNTUxNzI2NTY2LCJjbGllbnRfaWQiOjgzNzIsInNoYXJkIjoic2hhcmQ0Iiwiem9uZSI6MSwidXNlcl9pZCI6MCwibG9naW4iOiJIRTY4UnJ0ZjFzIiwicm9sZSI6InVzZXIiLCJzY29wZXMiOiJiYXNpYyIsImluZGVudGlmaWVyIjoic3lzdGVtIiwiY2FsbGVyIjoxfQ.3P2sJ_H4XjbgoTbvT__F34TZ2sSO2IxSjbggElsucAs
	*/
	// Values from your OAuth app. You create ONE for a page/plugin/service.
	$clientid     = "HE68Rrtf1s";
	$clientsecret = "qeqjsqEnd7wzcTVpK1sD69hdh8FKoGcO";

	// The official CleverReach URL, no need to change this.
	$token_url = "https://rest.cleverreach.com/oauth/token.php";

	// We use curl to make the request
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL, $token_url);
	curl_setopt($curl,CURLOPT_USERPWD, $clientid . ":" . $clientsecret);
	curl_setopt($curl,CURLOPT_POSTFIELDS, array("grant_type" => "client_credentials"));
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($curl);
	curl_close ($curl);

	// The final $result contains the access_token and some other information besides.
	// For you to see it, we dump it out here.
	var_dump($result);
	$token = $result;

	//$rest->setAuthMode("jwt", $token);
	//var_dump($token);
	
	
/*** 	
	$token = $rest->post('/login', 
		array(
			"client_id"=>'8372',
			"login"=>'Webbmason',
			"password"=>'Cleco'
		)
	);
	//no error, lets use the key
	$rest->setAuthMode("jwt", $token);
	var_dump($token);
****/	
} catch (\Exception $e){
	var_dump( (string) $e );
	var_dump($rest->error);
	exit;
}


echo "### Return basic client information ###\n";
var_dump( 
	$rest->get("/clients")
);


echo "### Return all available groups ###\n";
var_dump( 
	$rest->get("/groups")
);


echo "### Create a new group ###\n";
$gotham_group = false;
try {
	$gotham_group = $rest->post("/groups", array("name"=>"Gotham Newsletter (REST)"));
	var_dump($gotham_group);
} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### Add group attributes to Gotham Newsletter ###\n";
try {
	//attributes bound to group
	$rest->post("/groups/{$gotham_group->id}/attributes", array("name"=>"firstname", "type"=>"text"));
	$rest->post("/groups/{$gotham_group->id}/attributes", array("name"=>"lastname", "type"=>"text"));
	$rest->post("/groups/{$gotham_group->id}/attributes", array("name"=>"gender", "type"=>"gender"));
} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### Add global attributes to Gotham Newsletter ###\n";
try {
	//global attribute
	$rest->post("/attributes", array("name"=>"is_vilain", "type"=>"number"));
	$rest->post("/attributes", array("name"=>"is_batman", "type"=>"number"));

} catch (\Exception $e){
	// echo "!!!! Batcomputer Error: {$rest->error} !!!!\n";
	echo "Field probably allready exists!\n";
}


echo "### Adding single receiver to Gotham Newsletter ###\n";
$batman = false;
try {
	$receiver = array(
		"email"				=> "bruce@wayne.com",
		"registered"		=> time(),	//current date
		"activated"			=> time(),
		"source"			=> "Batcave Computer",
		"attributes"		=> array(
								"firstname" => "Bruce",
								"lastname" => "Wayne",
								"gender" => "male"
							),
		"global_attributes"	=> array(
								"is_batman" => 1
							),
		"orders"			=> array(
									array(
										"order_id" => "xyz12345",	//required
										"product_id" => "SN12345678",	//optional
										"product" => "Batman - The Movie (DVD)", //required
										"price" => 9.99, //optional
										"currency" => "EUR", //optional
 										"amount" => 1, //optional
										"mailing_id" => "8765432", //optional
										"source" => "Batshop", //optional
									),
									array(
										"order_id" => "xyz12345",	//required
										"product" => "Batman - The Musical (CD)", //required
									)

								)


	);
	$batman = $rest->post("/groups/{$gotham_group->id}/receivers", $receiver);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}


echo "### Adding Multiple receivers to Gotham Newsletter ###\n";
try {
	$receivers = array();

	$receivers[] = array(
		"email"				=> "joker@gotham.com",
		"attributes"		=> array(
								"firstname" => "unknown",
								"lastname" => "unknown",
								"gender" => "male"
							),
	);

	$receivers[] = array(
		"email"				=> "twoface@gotham.com",
		"attributes"		=> array(
								"firstname" => "harvey",
								"lastname" => "dent",
								"gender" => "male"
							),
	);

	$receivers[] = array(
		"email"				=> "poson-ivy@gotham.com",
		"attributes"		=> array(
								"firstname" => "Pamela Lillian ",
								"lastname" => "Isley",
								"gender" => "female"
							),
	);

	$rest->post("/groups/{$gotham_group->id}/receivers", $receivers);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### updating single receiver ###\n";
try {
	$receiver = array(
		"email" => "bruce@wayne.com",
		"global_attributes"	=> array("is_batman" => "2")
	);

	$rest->put("/groups/{$gotham_group->id}/receivers/bruce@wayne.com", $receiver);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### update Multiple receivers in Gotham Newsletter ###\n";
try {
	$receivers = array();

	$receivers[] = array(
		"email"				=> "joker@gotham.com",
		"global_attributes"	=> array("is_vilain" => "1"),
		"orders"			=> array(
									array(
										"order_id" => "xyz345345",	//required
										"product_id" => "CDX35434534",	//optional
										"product" => "Inhumans - The Movie (DVD)", //required
										"price" => 9.99, //optional
										"currency" => "EUR", //optional
 										"amount" => 1, //optional
										"mailing_id" => "87654321", //optional
										"source" => "Inhumans Shop", //optional

									)
								)
	);

	$receivers[] = array(
		"email"				=> "twoface@gotham.com",
		"global_attributes"	=> array("is_vilain" => "1")
	);

	$receivers[] = array(
		"email"				=> "poson-ivy@gotham.com",
		"global_attributes"	=> array("is_vilain" => "1")
	);

	$rest->post("/groups/{$gotham_group->id}/receivers/upsert", $receivers);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### deactivate receiver ###\n";
try {
	$rest->put("/groups/{$gotham_group->id}/receivers/poson-ivy@gotham.com/setinactive", $receiver);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}
