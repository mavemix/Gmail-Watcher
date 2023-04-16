<?php 

declare( strict_types = 1 );

namespace Andres\Telefonica;

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
     *  SCOPES: permissions
     *  ACCESS_TYPE: 
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
    public $token = [];

    /**
     * Save token to file & returns false on error
     * 
     * @return int|false
     */
    public function saveTokenFile() {
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

}