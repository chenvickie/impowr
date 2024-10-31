<?php
include_once '../config/auth.php';
include_once '../objects/job.php';

if ( ! $_POST || ! isset ($_POST['id']) || $_POST["id"] == "" ) {
    echo json_encode (
        array( "message" => "Invalid Job ID", "success" => false )
    );
    return;
}

$id = $_POST['id'];

// initialize object
$job = new Job($db, $id, $loginUser->info);

// delete job
$result = $job->deleteJob ($id);

if ( $result ) {

    // set response code - 200 OK
    http_response_code (200);

    // show data in json format
    echo json_encode (
        array( "success" => true )
    );

} else {

    // set response code - 404 Not found
    http_response_code (404);

    echo json_encode (
        array( "message" => "Opps, something wrong!", "success" => false )
    );

}