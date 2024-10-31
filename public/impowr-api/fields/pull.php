<?php
include_once '../config/auth.php';

// include impowr lib
include '../objects/impowr.php';

// include database and object files
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
$jobInfo = $jobs->getJobInfo ($id);

// connect to Redcap on source site 
$library = new IMPOWRLibrary($jobInfo, $isProd);

// initialize object
$job = new Job($db, $id, $loginUser->info);

// get import forms with import only = true
$forms = $job->getRequiredForms ();

// get all dictionary from the required forms and update dictionary control table
$dicData = $library->getDictionary ($forms);

// get all fields from the dictionary
$fields = array_column ($dicData, 'field_name');

// update fields control data on the db
$result = $job->updateImportFields ($fields);

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