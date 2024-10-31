<?php
include_once '../config/auth.php';

// include impowr lib
include '../objects/impowr.php';

// include object files
include_once '../objects/job.php';
include_once '../objects/jobs.php';

if ( ! $_POST || ! isset ($_POST['id']) || $_POST["id"] == "" ) {
    echo json_encode (
        array( "message" => "Invalid Job ID", "success" => false )
    );
    return;
}

$id = $_POST['id'];

// instantiate database connection and get site information
$jobs    = new Jobs($db, $loginUser->info);
$jobInfo = $jobs->getActivatedJobInfo ($id);

if ( ! $jobInfo ) {
    echo json_encode (
        array( "message" => "The Job " . $id . " is not activated", "success" => false )
    );
    return;
}

// connect to Redcap on source site 
$library = new IMPOWRLibrary($jobInfo, $isProd);

// get all available forms from the source site
$surveyForms = $library->getForms ();//$library->getDestForms ();

if ( $surveyForms == null ) {
    echo json_encode (
        array( "message" => "Could not retrive form information", "success" => false )
    );
} else {
    // initialize object
    $job = new Job($db, $id, $loginUser->info);

    // update import forms
    $result = $job->updateImportForms ($surveyForms);

    if ( $result && $result["success"] ) {

        // set response code - 200 OK
        http_response_code (200);

        // show data in json format
        echo json_encode (
            array( "success" => true, "message" => $result["message"] )
        );

    } else {

        // set response code - 404 Not found
        http_response_code (404);
        echo json_encode (
            array( "message" => "Opps, something wrong!", "success" => false )
        );

    }
}
