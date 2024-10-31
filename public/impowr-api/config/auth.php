<?php
session_start ();
set_time_limit (300);

header ('Access-Control-Allow-Origin: *');
header ('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header ('Access-Control-Allow-Headers: Authorization, Content-Type, Simulated');

include_once 'config.php';
include_once '../utils/utils.php';
include_once '../config/database.php';
include_once '../objects/user.php';

function getAuthorizationHeader () : string|null
{
  if ( isset ($_SERVER['HTTP_AUTHORIZATION']) ) {
    return trim ($_SERVER['HTTP_AUTHORIZATION']);
  } elseif ( function_exists ('apache_request_headers') ) {
    $headers = apache_request_headers ();
    if ( isset ($headers['Authorization']) ) {
      return trim ($headers['Authorization']);
    }
  }
  return null;
}

function getSimulatedID () : string|null
{
  if ( function_exists ('apache_request_headers') ) {
    $headers = apache_request_headers ();
    if ( isset ($headers['Simulated']) && $headers["Simulated"] != "" ) {
      return trim ($headers['Simulated']);
    }
  }
  return null;
}


$authHeader = getAuthorizationHeader ();
$isAuth     = false;
$reason     = "";

// instantiate database and login user
$database  = new Database();
$db        = $database->getConnection ($connectionOptions);
$loginUser = new User($db);

if ( $authHeader ) {
  // Assuming the authorization header is in the form "Basic base64encodedstring"
  if ( strpos ($authHeader, 'Basic ') === 0 ) {
    $encodedCredentials = substr ($authHeader, 6); // Remove "Basic " prefix
    $decodedString      = base64_decode ($encodedCredentials);

    // Split the credentials
    list( $username, $token ) = explode (':', string: $decodedString);

    if ( $_SERVER['PHP_AUTH_USER'] == $username && $_SERVER['PHP_AUTH_PW'] == $token ) {
      $loginUser->read ($username);
      if ( $loginUser->info != null ) {
        $isAuth = true;

        // check if the login user is a super admin, if so, allow simulate if any
        if ( $loginUser->info['super_admin'] == "YES" ) {
          $simulatedUser = getSimulatedID ();
          if ( $simulatedUser != null ) {
            $loginUser = new User($db);
            $loginUser->read ($simulatedUser);
          }
        }
      }

    } else {
      $reason = "Auth Does Not Matched!";
    }
  } else {
    $reason = "Invalid Token Format";
  }
} else {
  $reason = "Invalid Request Header";
}

if ( ! $isAuth ) {
  print_r ("Invalid credentials:" . $reason . "<br>");
  exit ();
}


?>