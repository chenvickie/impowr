<?php
include_once '../config/auth.php';
include_once '../objects/users.php';

if ( ! $_POST ) {
    echo json_encode (
        array( "message" => "Invalid User Data", "success" => false )
    );
    return false;
}

$requiredColumns = [ "user_name" ];
$params          = [];
foreach ($requiredColumns as $column) {
    if ( ! isset ($_POST[$column]) ) {
        echo json_encode (
            array( "message" => "Invalid User Data", "success" => false )
        );
        return false;
    } else {
        $params[] = $_POST[$column];
    }
}

$users = new Users($db, $loginUser->info);

//For updating data
if ( ! $users->updateUser ($params) ) {
    echo json_encode (
        array( "message" => "Failed", "success" => false )
    );
    return;
}

$msg = "User is updatesd.";
echo json_encode (
    array( "message" => $msg, "success" => true )
);
?>