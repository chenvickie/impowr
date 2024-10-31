<?php
include_once '../config/auth.php';
include_once '../objects/forms.php';

if ( ! $_POST ) {
    echo json_encode (
        array( "message" => "Invalid Form Data", "success" => false )
    );
    return false;
}

$requiredColumns = [ "import_need", "date_activated", "id" ];
$params          = [];
foreach ($requiredColumns as $column) {
    if ( ! isset ($_POST[$column]) ) {
        echo json_encode (
            array( "message" => "Invalid Form Data", "success" => false )
        );
        return false;
    } else {
        if ( $column == "import_need" ) {
            $params[] = $_POST[$column] == "true" ? 1 : 0;
        } elseif ( $column == "date_activated" ) {
            $params[] = explode (" ", $_POST[$column])[0];
        } else {
            $params[] = $_POST[$column];
        }
    }
}

// initialize object
$form = new FormControls($db, $loginUser->info);

//For updating data
if ( ! $form->updateFormControl ($params) ) {
    echo json_encode (
        array( "message" => "Failed", "success" => false )
    );
    return;
}

$msg = "Form is updatesd.";
echo json_encode (
    array( "message" => $msg, "success" => true )
);
?>