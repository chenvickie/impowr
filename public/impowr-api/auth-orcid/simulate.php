<?php

//auth
include_once '../config/auth.php';

if ( $loginUser->info != null ) {
    $msg = isset ($_POST["simulate"]) ? "Start Simulate!" : "Stop Simulate!";
    echo json_encode (
        array( "message" => $msg, "expire_on" => null, "success" => true )
    );
} else {
    echo json_encode (
        array( "message" => "Failed. User doesnt exist!", "expire_on" => null, "success" => false )
    );
}
?>