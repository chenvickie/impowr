<?php
include_once '../config/auth.php';
include_once '../objects/fields.php';

if ( ! $_POST ) {
    echo json_encode (
        array( "message" => "Invalid Field Data", "success" => false )
    );
    return false;
}

$requiredColumns = [ "show_blank", "date_activated", "id" ];
$params          = [];
foreach ($requiredColumns as $column) {
    if ( ! isset ($_POST[$column]) ) {
        echo json_encode (
            array( "message" => "Invalid Field Data", "success" => false )
        );
        return false;
    } else {
        if ( $column == "show_blank" ) {
            $params[] = $_POST[$column] == "true" ? 1 : 0;
        } elseif ( $column == "date_activated" ) {
            $params[] = explode (" ", $_POST[$column])[0];
        } else {
            $params[] = $_POST[$column];
        }
    }
}

// initialize object
$field = new FieldControls($db, $loginUser->info);

//For updating data
if ( ! $field->updateFieldControl ($params) ) {
    echo json_encode (
        array( "message" => "Failed", "success" => false )
    );
    return;
}

$msg = "Field is updated.";
echo json_encode (
    array( "message" => $msg, "success" => true )
);
?>