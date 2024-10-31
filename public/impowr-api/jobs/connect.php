<?php
include_once '../config/auth.php';

// include impowr lib
include '../objects/impowr.php';

// include object file
//include_once '../objects/job.php';

if ( ! $_POST ) {
    echo json_encode (
        array( "message" => "Invalid Job Data", "success" => false )
    );
    return false;
}

$queryArrs      = [];
$doTransaction  = true;
$observationIds = [];

$requiredColumns = [ "project_url", "project_token", "source_project_url", "source_project_token" ];
foreach ($requiredColumns as $column) {
    if ( ! isset ($_POST[$column]) ) {
        echo json_encode (
            array( "message" => "Invalid Job Data", "success" => false )
        );
        return false;
    }
}

$jobInfo = array(
    "project_url"          => $_POST["project_url"],
    "project_token"        => $_POST["project_token"],
    "source_project_url"   => $_POST["source_project_url"],
    "source_project_token" => $_POST["source_project_token"]
);

$library    = new IMPOWRLibrary($jobInfo, $isProd);
$sourceInfo = $library->exportProjectInfo ();
$destInfo   = $library->exportDestProjectInfo ();

if ( $sourceInfo == false ) {
    echo json_encode (
        array( "message" => "Source site connection Failed", "success" => false )
    );
} else if ( $sourceInfo == false ) {
    echo json_encode (
        array( "message" => "Destination site connection Failed", "success" => false )
    );
} else {
    echo json_encode (
        array( "message" => "connected", "success" => true, "sourceInfo" => $sourceInfo, "destInfo" => $destInfo )
    );
}
?>