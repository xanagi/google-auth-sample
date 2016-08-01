<?php
require __DIR__ . '/../../vendor/autoload.php';

define('CLIENT_SECRET_PATH', __DIR__ . '/../../client_secret.json');
define('SCOPES', implode(' ', [
    'email',
    'openid',
    'profile',
    Google_Service_Sheets::SPREADSHEETS_READONLY
]));

$client = new Google_Client();
$client->setAuthConfigFile(CLIENT_SECRET_PATH);
$client->setAccessType("offline");
$client->setScopes(SCOPES);
$client->setRedirectUri("http://{$_SERVER['HTTP_HOST']}/oauth2-ws/oauth2callback.php");

session_start();

if (! isset($_GET['code'])) {
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2-ws/';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
} else {
    // Confirm anti-forgery state token
    if ($_GET['state'] != $_SESSION['state']) {
        http_response_code(401);
        echo 'Invalid state parameter';
        return;
    }
    // Exchange code for access token and ID token
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    // Obtain user information from the ID token
    $_SESSION['user'] = $client->verifyIdToken()->getAttributes();

    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2-ws/';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
