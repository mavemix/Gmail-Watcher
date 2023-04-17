<?php 

declare( strict_types = 1 );

namespace mavmix\gmailwatcher;
use \Google\Client;

/**
 * This class manage the auth process of Google with this App, obtaining a auth token & renew it if is neccessary.
 * In CONFIG constant are the parameters to configure this access.
 */
class GmailAuth 
{
    /**
     * Config parameters to construct the client to access services:
     *  CREDENTIALS_FILE: Google secret file obtained from credentials making
     *  REDIRECT_URI: On auth responses, this uri will be called by google with auth code obtained by GET 
     *  SCOPES: permissions (see Google)
     *  ACCESS_TYPE: 'online' / 'offline' Indicates if application can refresh access tokens when the user is not present at the browser
     *  TOKEN_FILE: where auth token will be saved & recovered in local storage
     */
    const CONFIG = [
        'CREDENTIALS_FILE' => '../credentials.json',
        'REDIRECT_URI' => 'http://localhost:8000/index.php',
        'SCOPES' => 'https://www.googleapis.com/auth/gmail.readonly',
        'ACCESS_TYPE' => 'offline',
        'TOKEN_FILE' => '../token.json'
    ];

    /**
     * The access token
     */
    private $token = [];

    /**
     * The client object
     */
    public $client;

    /**
     * Constructs the client based on config parameters
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig($this::CONFIG['CREDENTIALS_FILE']);
        $this->client->setRedirectUri($this::CONFIG['REDIRECT_URI']);
        $this->client->setScopes($this::CONFIG['SCOPES']);
        $this->client->setAccessType($this::CONFIG['ACCESS_TYPE']);
        if($this->getToken()) {
            $this->client->setAccessToken($this->token);
        }
    }

    /**
     * Get the token array with the auth code assigned by Google
     * 
     * @param string $code
     * @return int|false
     */
    public function getAuthToken(string $code) {
        return $this->saveToken($this->client->fetchAccessTokenWithAuthCode($code));
    }

    /**
     * This login returns false on success login, or an auth url from Google
     * 
     * @return false|string
     */
    public function needLogin() {
        if (empty($this->token) || !isset($this->token['access_token']) || !$this->setRenewedToken()) {
            return $this->client->createAuthUrl();    
        } 
        return false;
    }

    /**
     * Assigns a renewed token to client property
     * 
     * @return int|false
     */
    private function setRenewedToken() {

        $this->client->setAccessToken($this->token);

        if (!$this->client->isAccessTokenExpired()) {
            return 1;
        } 
        if( ($newToken = $this->client->getRefreshToken()) == null ) {
            return false;
        }
        
        return $this->saveToken($this->client->fetchAccessTokenWithRefreshToken($newToken));
    }



    /**
     * Save token to $token property & token file. Returns false on error
     * 
     * @param array $token 
     * @return int|false
     */
    private function saveToken(array $token) {
        if(isset($token['error'])) {
            return false;
        }
        $this->token = $token;
        // Cannot encode token
        if(($encodedToken = json_encode($this->token)) == false) {
            return false;
        }
        // No json file
        if(!file_exists($this::CONFIG['TOKEN_FILE'])) {
            return file_put_contents($this::CONFIG['TOKEN_FILE'], $encodedToken);
        } 
        // No update needed
        if($encodedToken == file_get_contents($this::CONFIG['TOKEN_FILE'])) {
            return true;
        }
        // Update token
        return file_put_contents($this::CONFIG['TOKEN_FILE'], $encodedToken);
    }

    /**
     * Obtains the token from file. Returns false if an error occurrs
     */
    private function getToken() {
        if(!file_exists($this::CONFIG['TOKEN_FILE']) || !is_readable($this::CONFIG['TOKEN_FILE'])) {
            return false;
        }
        $content = json_decode(file_get_contents($this::CONFIG['TOKEN_FILE']),true);

        if(!$content || empty($content)) {
            return false;
        }
        $this->token = $content;
        return true;
    }

    public function logout() {
        $this->saveToken([]);
    }

}