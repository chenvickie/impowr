<?php
include_once '../config/auth.php';
include_once '../objects/jobs.php';

// initialize object
$jobs = new Jobs($db, $loginUser->info);

$searchKey   = $_POST && isset ($_POST['key']) ? $_POST['key'] : "";
$searchValue = $_POST && isset ($_POST['value']) ? $_POST['value'] : "";
$partial     = $_POST && isset ($_POST['partial']) ? $_POST['partial'] : "YES";
$offset      = $_POST && isset ($_POST['offset']) ? $_POST['offset'] : 0;
$limit       = $_POST && isset ($_POST['limit']) ? $_POST['limit'] : 100;
$sort        = $_POST && isset ($_POST['sort']) ? $_POST['sort'] : "id";
$dir         = $_POST && isset ($_POST['dir']) ? $_POST['dir'] : "asc";

// query jobs
$result = $jobs->read ($searchKey, $searchValue, $partial, $sort, $dir, $offset, $limit);

if ( is_array ($result["data"]) ) {

    // set response code - 200 OK
    http_response_code (200);

    $add = $loginUser->info["super_admin"] == "YES";
    if ( ! $add ) {
        // allow to pull fields if the login user is admin
        foreach ($loginUser->info["team_jobs"] as $tj) {
            if ( $tj["is_admin"] == 1 ) {
                $add = true;
            }
        }
    }

    // show data in json format
    echo json_encode (
        array( "data" => $result["data"], "total" => $result["total"], "add" => $add, "success" => true )
    );

} else {

    // set response code - 404 Not found
    http_response_code (404);

    echo json_encode (
        array( "message" => "Opps, something wrong!", "success" => false )
    );

}