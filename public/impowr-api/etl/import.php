<?php

include '../config/config.php';
include '../utils/utils.php';

// include database and object files
include_once '../config/database.php';
include_once '../objects/jobs.php';
include_once '../objects/impowrTransfer.php';

// instantiate database and product object
$database = new Database();
$db       = $database->getConnection ($connectionOptions);

/**
 * override default config values if given by command line arguments $argv
 * args options:
 *      env: string, either dev or prod
 *      site: string, key of the site from the endpoint - required
 *      forms: string, a string sperator with comma to indicate what forms for import. Default to empty string, so we can get the list of forms from form control table
 *      fields: string, a string sperator with comma to indicate what fields of the forms to import. Default to empty string, so we can get the list of fields from field control table
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

// instantiate database connection and get job information
$jobsClass = new Jobs($db);

$jobs = [];
if ( $args['site'] == "all" ) {
    $jobs = $jobsClass->getScheduledJobs ();
} else {
    $jobs = $jobsClass->getActivatedJobInfoByJobName ($args['site']);
}

if ( count ($jobs) == 0 ) {
    logs ("Invalid job Info or No scheduled jobs avaiable for transfer, process terminated!", true, true);
    return false;
}

foreach ($jobs as $jobInfo) {
    $it = new ImpowrTransfer($db, $jobInfo, 'system', $args);
    $it->tranfer ();
}
