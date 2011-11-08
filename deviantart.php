<?php
//Relies on the oAuth2 library by Pierrick Charron: https://github.com/adoy/PHP-OAuth2/
require('./lib/oauth2.php');

const CLIENT_ID = '0'; // OAuth 2.0 client_id
const CLIENT_SECRET = '0123456789abcdefghigklmnopqrstuv'; // OAuth 2.0 client_secret

const REDIRECT_URI = 'http://path.to/this/file';
const AUTHORIZATION_ENDPOINT = 'https://www.deviantart.com/oauth2/draft15/authorize';
const TOKEN_ENDPOINT = 'https://www.deviantart.com/oauth2/draft15/token';
const SUBMIT_API = "http://www.deviantart.com/api/draft15/submit";
const APPNAME = 'App.Name';

try {
    $client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET, OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
    if (!isset($_REQUEST['code'])) {
        $params = array('redirect_uri' => REDIRECT_URI);
        $auth_url = $client->getAuthenticationUrl(AUTHORIZATION_ENDPOINT, REDIRECT_URI);
        header('Location: ' . $auth_url);
        die('Redirecting ...');
    } else {
        $params = array('code' => $_REQUEST['code'], 'redirect_uri' => REDIRECT_URI);
        $response = $client->getAccessToken(TOKEN_ENDPOINT, OAuth2\Client::GRANT_TYPE_AUTH_CODE, $params);
        $val = json_decode($response['result']);

        if (!$val) {
            throw new Exception('No valid JSON response returned');
        }

        if (!$val->access_token) {
            throw new Exception("No access token returned: ".$val->human_error);
        }

        $client->setAccessToken($val->access_token);

        $client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_OAUTH);
        
        $response = $client->fetch(
            SUBMIT_API,
            array(
                'title' => 'Fella Sample Image',
                'artist_comments' => 'Fella Sample Image',
                'keywords' => 'fella sample image',
                'folder' => APPNAME,
                'file' => "@fella.png"
            ),
            OAuth2\Client::HTTP_METHOD_POST
        );

        $result = json_decode($response['result']);

        if (!$result) {
            throw new Exception('No valid JSON response returned');
        }
        
        if ($result->status == 'success') {
            print "Great Success! <a href=\"http://sta.sh/1{$result->stashid}\" target=\"_blank\">Stash ID {$result->stashid}</a>";
        } else {
            throw new Exception($result->human_error);
        }
    }
} catch (Exception $e) {
    print "Fatal Error: ".$e->getMessage();
}
?>
