<?php

session_start();

//Redirects to GitHub authentication page, restricting scope to read-only user data.
function goToAuthURL() {
    $client_id= "02786875d196f38bfdf1";
    $redirect_uri= "https://rachel.sems-tech.com/callback.php";
        
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $url = 'https://github.com/login/oauth/authorize?client_id='. $client_id. '&redirect_uri='. $redirect_uri.'&scope=read:user';
        header("location: $url");
    }
}

//Uses code returned from GitHub to get access token and fetch user data.
function fetchData() {

    $client_id= "02786875d196f38bfdf1";
    $redirect_uri= "https://rachel.sems-tech.com/callback.php";
    $client_secret = "31da8f9408febd03d766f5672c91623d6736a2cc";
    
    //If authorization code was returned, extract authorization code.
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
                             
            //Generate URL-encoded querystring from all variables.
            $post = http_build_query(array(
                        'client_id' => $client_id,
                        'redirect_uri' => $redirect_uri,
                        'client_secret' => $client_secret,
                        'code' => $code,
                    ));

            //Get and isolate access token returned from GitHub.
            $access_data = file_get_contents("https://github.com/login/oauth/access_token?". $post);
            $exploded1 = explode("access_token=", $access_data);
            $exploded2 = explode('&scope=read:user', $exploded1[1]);
            $access_token = $exploded2[0];
       
            $opts = [ 'http' => [
                             'method' => 'GET',
                             'header' => [ 'User-Agent: PHP']
                             ]
                    ];
               
            //Using access token, fetch user data.
            $url = "https://api.github.com/user?access_token=". $access_token;
            $context = stream_context_create($opts);
            $data = file_get_contents($url, false, $context);
              
            //Decode and convert JSON string to PHP variable, and extract pieces of data.
            $user_data = json_decode($data, true);
            $username = $user_data['login'];
            $email = $user_data['email'];
                     
               //Load user data into session variables.
            $_SESSION['user'] = $username;
            $_SESSION['email'] = $email;               
        }
    }
    else {            
       die('Error.');  
    }
}
