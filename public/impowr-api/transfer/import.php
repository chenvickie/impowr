<?php
include_once '../config/auth.php';
include_once '../objects/jobs.php';
include_once '../objects/impowrTransfer.php';

if ( ! $_POST || ! isset ($_POST['id']) || $_POST["id"] == "" ) {
    echo json_encode (
        array( "message" => "Invalid Job ID", "success" => false )
    );
    return;
}

$id       = $_POST['id'];
$errorMsg = "";

$jobs    = new Jobs($db, $loginUser->info);
$jobInfo = $jobs->getJobInfo ($id);

$it  = new ImpowrTransfer($db, $jobInfo, $_POST['userId'], $_POST);
$res = $it->tranfer ();

if ( $res == false || $it->errorMsg != '' ) {
    // set response code - 404 Not found
    http_response_code (404);

    echo json_encode (
        array( "message" => $errorMsg, "success" => false )
    );
} else {
    // set response code - 200 OK
    http_response_code (200);

    // show data in json format
    echo json_encode (
        array( "success" => true )
    );
}