<?php

date_default_timezone_set ('America/New_York');

//API Token
$token = "yourtoken";

/*** debugging ***/
$isProd = false; //TODO: might need to have have ssl cert set up for production site in order to call APIs in CURLOPT_SSL_VERIFYPEER
$debug  = false;
/*** END debugging ***/

/*** Log Settings ***/
$today   = date ('Y-m-d');
$logFile = "logs/impowr-" . $today . ".log";
ini_set ("log_errors", 1);
ini_set ("error_log", $logFile);
/*** END Log Settings ***/

//MSSQL (make sure you have ssl driver installed and set up for your php)
$dbServer = "yourdb";
$dbPort   = 1433;
$dbName   = "yourdbname";
$dbUser   = "yourdbuser";
$dbPw     = "yourdbpw";

//database connection options
$connectionOptions = array(
    "Database" => $dbName,
    "UID"      => $dbUser,
    "PWD"      => $dbPw,
);

$headerOpts = array(
    'Content-Type: application/json',
    'Accept: application/json',
);

//assign return values;
$res = [];

$res['dbServer']          = $connectionOptions["dbServer"];
$res['connectionOptions'] = $connectionOptions;

// ORCID is a site for researchers to sign up via their email address, and they offer an OAuth2 login process. 
$orcidConfig = [
    'authUrl'       => 'https://orcid.org/oauth',
    'authEndpoint'  => 'https://orcid.org/oauth/authorize',
    'authScope'     => '/authenticate',
    'responseType'  => 'code',
    'clientID'      => 'yourClientID',
    'clientSecret'  => 'yourClientSecret',
    'tokenEndpoint' => 'https://orcid.org/oauth/token',
    'redirectUri'   => 'yourServer/impowr-api/auth-orcid/callback.php',
    'callBackUrl'   => 'yourServer/callback'
];

$res['orcidConfig'] = $orcidConfig;

return $res;