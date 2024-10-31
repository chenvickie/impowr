<?php

include '../config/config.php';
include '../utils/utils.php';

// include database and object files
include_once '../config/database.php';
include_once '../objects/job.php';
include_once '../objects/jobs.php';

// include impowr lib
include '../objects/impowr.php';

// instantiate database and product object
$database = new Database();
$db       = $database->getConnection ($connectionOptions);

/**
 * override default config values if given by command line arguments $argv
 * args options:
 *      env: string, either dev or prod
 *      site: string, key of the site from the endpoint - required
 *      skipForms: string, a string sperator with comma to indicate what forms to skip. Default to empty string, so we can get the list of forms from form control table
 *      blankFields: string, a string sperator with comma to indicate what fields should be blank. Default to empty string, so we can get the list of fields from field control table
 */
$args = array();
if ( count ($argv) > 1 ) {
    for ( $a = 1; $a < count ($argv); $a++ ) {
        list( $key, $val ) = explode ("=", $argv[$a]);
        $args[$key]        = $val;
    }
}

if ( ! $args['site'] || $args['site'] == "" ) {
    logs ("Invalid Site " . $args['site'] . ", job terminated!\n", true);
    return false;
}

$blankFields = $args['blankFields'] && $args['blankFields'] != "" ? explode (",", $args['blankFields']) : [];
$skipForms   = $args['skipForms'] && $args['skipForms'] != "" ? explode (",", $args['skipForms']) : [];

// instantiate database connection and get site information
$jobsClass = new Jobs($db);

$jobs = [];
if ( $args['site'] == "all" ) {
    $jobs = $jobsClass->getAllActivatedJobs ();
} else {
    $jobs = $jobsClass->getActivatedJobInfoByJobName ($args['site']);
}

if ( count ($jobs) == 0 ) {
    logs ("Invalid site Info, process terminated!", true);
    return false;
}

foreach ($jobs as $jobInfo) {
    // create a new redcap impowr library based on the site name
    logs ("*************************************************************", true);
    logs ("********* BEGIN IMPOWR Fields update " . date ('Y-m-d H:i:s') . " *********", true);
    logs ("*************************************************************\n", true);

    logs ("[PROJECT] : " . $jobInfo["project_name"] . "\n", true);
    $job = new Job($db, $jobInfo['id']);

    // connect to Redcap on source site 
    logs ("Connect to " . $jobInfo['source_project_url'], true);
    $library = new IMPOWRLibrary($jobInfo, $isProd);

    // get all available forms from the source site
    $forms = $library->getForms ();

    // update the form control data on the table based on skip forms passed from args
    $res          = $job->updateImportForms ($forms, $skipForms);
    $updatedForms = $res["forms"];
    logs ("Get required forms from impowr_form_controls table: " . json_encode ($updatedForms) . "\n", true);

    // if count of required forms if greater than 0, then continue the process
    if ( count ($updatedForms) > 0 ) {
        // get all dictionary from the required forms and update dictionary control table
        $dicData           = $library->getDestDictionary ($updatedForms);
        $updatedDictionary = $job->updateDictionary ($dicData);
        logs ("Add fields on impowr_dictionary_controls table: " . json_encode ($dicData) . "\n", true);

        // get all fields from the dictionary
        $fields = array_column ($dicData, 'field_name');
        logs ("Update fields on impowr_field_controls table: " . json_encode ($fields) . "\n", true);

        // update fields control data on the db based on the blank field
        $updatedFields = $job->updateImportFields ($fields, $blankFields);
        logs ("[INFO] insert audit logs in impowr_field_controls table:: " . json_encode ($updatedFields) . "\n", true);
    }

    logs ("**************************************************************", true);
    logs ("********** END IMPOWR Fields update " . date ('Y-m-d H:i:s') . " ***********", true);
    logs ("**************************************************************\n", true);
}