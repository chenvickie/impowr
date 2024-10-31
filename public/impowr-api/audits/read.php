<?php
include_once '../config/auth.php';
include_once '../objects/audits.php';

// initialize object
$audits = new Audits($db, $loginUser->info);

$searchKey   = $_POST && isset ($_POST['key']) ? $_POST['key'] : "";
$searchValue = $_POST && isset ($_POST['value']) ? $_POST['value'] : "";
$partial     = $_POST && isset ($_POST['partial']) ? $_POST['partial'] : "YES";
$offset      = $_POST && isset ($_POST['offset']) ? $_POST['offset'] : 0;
$limit       = $_POST && isset ($_POST['limit']) ? $_POST['limit'] : 100;
$sort        = $_POST && isset ($_POST['sort']) ? $_POST['sort'] : "process_start";
$dir         = $_POST && isset ($_POST['dir']) ? $_POST['dir'] : "desc";
$jobId       = $_POST && isset ($_POST['jobId']) ? $_POST['jobId'] : "all";

// query study
$result = $audits->read ($searchKey, $searchValue, $partial, $sort, $dir, $offset, $limit, $jobId);

if ( is_array ($result["data"]) ) {

    // set response code - 200 OK
    http_response_code (200);

    $pull = $loginUser->info["super_admin"] == "YES";
    if ( ! $pull ) {
        // allow to pull forms if the login user is admin or editable for now
        foreach ($loginUser->info["team_jobs"] as $tj) {
            if ( $tj["is_admin"] == 1 || $tj["is_editable"] ) {
                $pull = true;
            }
        }
    }

    // show data in json format
    echo json_encode (
        array( "data" => $result["data"], "total" => $result["total"], "pull" => $pull, "success" => true )
    );

} else {

    // set response code - 404 Not found
    http_response_code (404);

    echo json_encode (
        array( "message" => "Opps, something wrong!", "success" => false )
    );

}