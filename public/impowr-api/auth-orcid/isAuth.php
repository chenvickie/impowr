<?php

//auth
include_once '../config/auth.php';
include_once '../objects/authentication.php';
include_once '../objects/AuthenticationException.php';

// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';

// initialize object
$auth = new Authentication($orcidConfig);

if ( ! $_POST['username'] ) {
    echo json_encode (
        array( "message" => "Invalid Credenticals!", "success" => false )
    );
    return;
}

// check if session still alive
$res = $auth->checkSession ($_POST['username'], "");

if ( (isset ($res['username']) && $res['username'] == $_POST['username']) || isset ($res["session_expire_time"]) ) {
    echo json_encode (
        array( "message" => "Activate Session", "expire_on" => $res["session_expire_time"], "success" => true )
    );
} else {
    $msg = "Session Expired, Please login!";
    echo json_encode (
        array( "message" => $msg, "expire_on" => null, "success" => false )
    );
}
?>