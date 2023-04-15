<?php 

namespace Andres\Telefonica;

require_once '../vendor/autoload.php';

session_start();

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$oauth_credentials = '../credentials.json';

$client = new \Google\Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->setScopes('https://www.googleapis.com/auth/gmail.readonly');
$client->setAccessType('offline');


// On Logout, destroy session token
if (isset($_GET['logout'])) {
    unset($_SESSION['id_token_token']);
}

// Is there a response?
if (!empty($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  // store in the session also
  $_SESSION['id_token_token'] = $token;
  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  return;
}

  /************************************************
   If we have an access token, we can make
   requests, else we generate an authentication URL.
   ************************************************/
if (!empty($_SESSION['id_token_token']) && isset($_SESSION['id_token_token']['access_token'])) {
    $client->setAccessToken($_SESSION['id_token_token']);
} else {
  $authUrl = $client->createAuthUrl();
}

/************************************************
 If we're signed in we can go ahead and retrieve
 the ID token, which is part of the bundle of
 data that is exchange in the authenticate step
 - we only need to do a network call if we have
 to retrieve the Google certificate to verify it,
 and that can be cached.
 ************************************************/
if ($client->isAccessTokenExpired()) {
  if(!$client->getRefreshToken()) {
    $authUrl = $client->createAuthUrl();
  } else {
    $_SESSION['id_token_token'] = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
  }
}

if(isset($authUrl)) {
  echo "
  <div class='request'>
    <a class='login' href='".$authUrl."'>Conectar cuenta de Google</a>
  </div>";

} else {
  echo "<a href='".$redirect_uri."?logout=true'>Salir</a><br><br>";

  $service = new \Google\Service\Gmail($client);

  try{

    // Get the especific messages
    $user = 'me';
    $results = $service->users_messages->listUsersMessages($user,[]);

    foreach($results->messages as $message) {
      echo "ID: ".$message->id."<br>Thread: ".$message->threadId."<hr>";
    }
    
  }
  catch(\Exception $e) {
      // TODO(developer) - handle error appropriately
      echo 'Message: ' .$e->getMessage();
  }
}

?>


