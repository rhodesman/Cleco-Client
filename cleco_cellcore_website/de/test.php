<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    $api = null;

    function getAuth()
    {
        //$settings     = Drupal::config('atg_cleverreach.settings');
        $clientid     = 'ub6jlRerq0';
        $clientsecret = '9ugS21kyuV24PTmpkqS0Vmb2GxUVZkV9';

        // The official CleverReach URL, no need to change this.
        $token_url = 'https://rest.cleverreach.com/oauth/token.php';

        // This must be the same as the previous redirect uri
        //$fields['grant_type'] = 'client_credentials';

        // We use curl to make the request
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
        //if (!isset($this->api)) {
            // @todo figure out autoload. Class is never found.
            require __DIR__ . '/rest_client.php';

            //$settings = Drupal::config('atg_cleverreach.settings');

            //$this->
            $api = new \CR\tools\rest('https://rest.cleverreach.com/v3');

            //$this->
            //print_r(getAuth());
            //exit;
            $api->setAuthMode('bearer', getAuth()->access_token);
        //}
    }

    setup();

    $listId = '230930';
    //$email  = $form_state->getValue('email');
    $email = 'rbrocious@webbmason.com';

    $user = [
        'email'             => $email,
        'registered'        => time(),
        'activated'         => time(),
        'source'            => 'CELLCORE Website',
        'global_attributes' => array(
            "FIRST_NAME" => "James",
            "LAST_NAME" => "Rhode"
            )
    ];

    try {
        $response = $api->post("/groups/{$listId}/receivers", $user);
        print_r($response);
        exit;
    }catch (\Exception $e) {
        print_r($e->getMessage());
    }

    try {
        $response = $api->post("/groups/{$listId}/receivers", $user);
        //$this->log('Generated Lead: ' . $email, 'notice');
    } catch (\Exception $e) {
       //$this->log(print_r(Json::decode($e), true), 'error');
        print_r($e->getMessage());
       // $this->log(print_r(Json::decode($e->getMessage()), true), 'error');
    }
    


?>