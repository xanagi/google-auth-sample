<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../..');
$dotenv->load();

define('CLIENT_EMAIL', getenv('SERVICE_ACCOUNT_CLIENT_EMAIL'));
define('PRIVATE_KEY_PATH', __DIR__ . '/../../service_account.p12');
define('SCOPES', implode(' ', [
    Google_Service_Sheets::SPREADSHEETS_READONLY
]));

$credentials = new Google_Auth_AssertionCredentials(
    CLIENT_EMAIL,
    SCOPES,
    file_get_contents(PRIVATE_KEY_PATH)
);

$client = new Google_Client();
$client->setAssertionCredentials($credentials);
if ($client->getAuth()->isAccessTokenExpired()) {
    $client->getAuth()->refreshTokenWithAssertion();
}

$sheetId = $_POST['sheetId'];
$range = $_POST['range'];

if ($sheetId && $range) {
  $service = new Google_Service_Sheets($client);
  $response = $service->spreadsheets_values->get($sheetId, $range);
  $values = $response->getValues();
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
            <li><a href="/oauth2-ws/index.php">OAuth2 for Server-side Web App</a></li>
            <li class="active"><a href="/oauth2-s2s/index.php">OAuth2 for Service account</a></li>
            <li><a href="/sign-in/index.php">Google Sign-in</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container">
      <h1>OAuth 2.0 for Server to Server Applications</h1>
      <p>
        A sample that use "OAuth 2.0 for Server to Server  Applications". <br />
        You can access resources that a service account has their permissions. <br />
        See <a href="https://developers.google.com/identity/protocols/OAuth2ServiceAccount">Using OAuth 2.0 for Server to Server Applications</a> and
        <a href="https://developers.google.com/api-client-library/php/auth/service-accounts">Using OAuth 2.0 for Server to Server Applications (PHP)</a>.
      </p>
    </div>

    <div class="container">
      <div class="alert alert-warning" role="alert">Set the Google Sheet ID that is shared with <strong>the service account</strong>.</div>
      <form class="form-inline" action="/oauth2-s2s/index.php" method="post">
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
