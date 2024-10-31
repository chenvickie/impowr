<?php
include_once '../config/auth.php';
include_once '../objects/users.php';

$users = new Users($db, $loginUser->info);

$searchKey   = $_POST && isset ($_POST['key']) ? $_POST['key'] : "";
$searchValue = $_POST && isset ($_POST['value']) ? $_POST['value'] : "";
$partial     = $_POST && isset ($_POST['partial']) ? $_POST['partial'] : "YES";
$offset      = $_POST && isset ($_POST['offset']) ? $_POST['offset'] : 0;
$limit       = $_POST && isset ($_POST['limit']) ? $_POST['limit'] : 100;
$sort        = $_POST && isset ($_POST['sort']) ? $_POST['sort'] : "user_name";
$dir         = $_POST && isset ($_POST['dir']) ? $_POST['dir'] : "asc";

// query users
$result = $users->read ($searchKey, $searchValue, $partial, $sort, $dir, $offset, $limit);

if ( is_array ($result["data"]) ) {

    // set response code - 200 OK
    http_response_code (200);

    // show data in json format
    echo json_encode (
        array( "data" => $result["data"], "total" => $result["total"], "success" => true )
    );

} else {

    // set response code - 404 Not found
    http_response_code (404);

    echo json_encode (
        array( "message" => "Opps, something wrong!", "success" => false )
    );

}