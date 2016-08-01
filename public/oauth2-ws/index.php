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
$client->setAccessType("offline"); // enable refresh token.
$client->setScopes(SCOPES);
$client->setRedirectUri("http://{$_SERVER['HTTP_HOST']}/oauth2-ws/oauth2callback.php");

session_start();

$accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
if ($accessToken) {
    $client->setAccessToken($accessToken);
    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->refreshToken($client->getRefreshToken());
        $_SESSION['access_token'] = $client->getAccessToken();
    }
    $sheetId = $_POST['sheetId'];
    $range = $_POST['range'];

    if ($sheetId && $range) {
      $service = new Google_Service_Sheets($client);
      $response = $service->spreadsheets_values->get($sheetId, $range);
      $values = $response->getValues();
      //echo json_encode($values);
    }
} else {
    // Create an anti-forgery state token.
    $state = sha1(openssl_random_pseudo_bytes(1024));
    $_SESSION['state'] = $state;
    $client->setState($state);
    // Redirect to the authorization request URI.
    $redirect_uri = $client->createAuthUrl();
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  </head>
  <body>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-default">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">Google OAuth2 Sample</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="/oauth2-ws/index.php">OAuth2 for Server-side Web App</a></li>
            <li><a href="/oauth2-s2s/index.php">OAuth2 for Service account</a></li>
            <li><a href="/sign-in/index.php">Google Sign-in</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container">
      <a href="/oauth2-ws/revoke.php">Revoke</a>
    </div>

    <div class="container">
      <h1>OAuth 2.0 for Web Server Applications</h1>
      <p>
        A sample that use "OAuth 2.0 for Web Server Applications". <br />
        You can access all Google API resources that you have permissions for. <br />
        See <a href="https://developers.google.com/identity/protocols/OAuth2WebServer">Using OAuth 2.0 for Web Server Applications</a> and
        <a href="https://developers.google.com/api-client-library/php/auth/web-app">Using OAuth 2.0 for Web Server Applications (PHP)</a>.
      </p>
    </div>

    <div class="container">
      <div class="alert alert-warning" role="alert">Set the Google Sheet ID that <strong>you</strong> can access.</div>
      <form class="form-inline" action="/oauth2-ws/index.php" method="post">
        <div class="form-group">
          <label for="sheetId">Sheet ID</label>
          <input type="text" class="form-control" name="sheetId" value="<?php echo $sheetId ?>" placeholder="Sheet ID">
        </div>
        <div class="form-group">
          <label for="range">Range</label>
          <input type="text" class="form-control" name="range" value="<?php echo $range ?>" placeholder="Range">
        </div>
        <input type="submit" class="btn btn-default" value="Get" />
      </form>

      <?php if ($values) { ?>
      <table class="table">
        <?php foreach($values as $row) { ?>
        <tr>
          <?php foreach($row as $value) { ?>
          <td>
            <?php echo $value; ?>
          </td>
          <?php } ?>
        </tr>
        <?php } ?>
      </table>
      <?php } ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
