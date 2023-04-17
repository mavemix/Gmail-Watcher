<?php 

namespace Andres\Telefonica;

require_once '../vendor/autoload.php';

$auth = new GmailAuth();
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

// On Logout, destroy session token
if (isset($_GET['logout']) && $_GET['logout']) {
    $auth->logout();
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    return;
}

// Is there a response?
if (!empty($_GET['code'])) {
  if(!$auth->getAuthToken($_GET['code'])) {
    $errorGettingAuthCode = true;
  }
  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  return;
}

if( ($authUrl = $auth->needLogin()) === false) {
  echo "<a href='".$redirect_uri."?logout=true'>Salir</a><br><br>";

  $service = new \Google\Service\Gmail($auth->client);
  $allMessages = [];

  try{

    // Get the especific messages
    $user = 'me';
    
    // First page of results
    $results = $service->users_messages->listUsersMessages($user,['q' => 'from:no-reply@accounts.google.com']);
    $allMessages = $results->getMessages();

    // Nexts pages & save
    while($results->nextPageToken) {
      $results = $service->users_messages->listUsersMessages($user,['q' => 'from:no-reply@accounts.google.com', 'pageToken' => $results->nextPageToken]);
      $allMessages = array_merge($allMessages, $results->getMessages());
    }

    foreach($allMessages as $message) {
      echo "ID: ".$message->id."<br>Thread: ".$message->threadId."<hr>";
    }
    
  }
  catch(\Exception $e) {
      // TODO(developer) - handle error appropriately
      echo 'Message: ' .$e->getMessage();
  }
  
} else {
  echo "
  <div class='request'>
    <a class='login' href='".$authUrl."'>Conectar cuenta de Google</a>
  </div>";
}

?>


