<?php
//auth
include_once '../config/auth.php';
include_once '../objects/authentication.php';
include_once '../objects/AuthenticationException.php';

// initialize object
$auth = new Authentication($orcidConfig);

// logout session 
$res = $auth->logout ();

if ( $res == '' ) {
	echo json_encode (
		array( "message" => "Logout Session", "success" => true )
	);
} else {
	echo json_encode (
		array( "message" => $res, "success" => false )
	);
}
?>