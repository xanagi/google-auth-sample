<?php
require __DIR__ . '/../../vendor/autoload.php';

define('CLIENT_SECRET_PATH', __DIR__ . '/../../client_secret.json');
define('SCOPES', implode(' ', [
    Google_Service_Sheets::SPREADSHEETS_READONLY
]));

$client = new Google_Client();
$client->setAuthConfigFile(CLIENT_SECRET_PATH);
$client->setAccessType("offline");
$client->setScopes(SCOPES);
$client->setRedirectUri("http://{$_SERVER['HTTP_HOST']}/oauth2-ws/oauth2callback.php");

session_start();

$client->revokeToken();
unset($_SESSION['access_token']);

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2-ws/';
header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
