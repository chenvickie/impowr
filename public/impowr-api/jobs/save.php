<?php
include_once '../config/auth.php';
include_once '../objects/job.php';

$success = true;
$msg     = "";

if ( ! $_POST ) {
    echo json_encode (
        array( "message" => "Invalid Job Data", "success" => false )
    );
    return false;
}

$queryArrs      = [];
$doTransaction  = true;
$observationIds = [];

$requiredColumns = [ "job_name", "project_name", "project_id", "project_url", "project_token", "project_contact_name", "project_contact_email", "source_institution", "source_project_name", "source_project_id", "source_project_url", "source_project_token", "source_contact_name", "source_contact_email", "transfer_frequency", "date_activated" ];
$optionalColums  = [ "date_deactivated", "scheduled_on", "note" ];
$allColumns      = array_merge ($requiredColumns, $optionalColums);

$params   = [];
$params[] = $today;
$params[] = $_POST['userId'];

foreach ($allColumns as $column) {
    if ( ! isset ($_POST[$column]) && in_array ($column, $requiredColumns) ) {
        echo json_encode (
            array( "message" => "Invalid Site Data", "success" => false )
        );
        return false;
    } else {
        if ( $column == "date_activated" || $column == "date_deactivated" ) {
            if ( isset ($_POST[$column]) && $_POST[$column] != "" ) {
                $params[] = explode (" ", $_POST[$column])[0];
            } else {
                $params[] = "";
            }
        } else {
            $params[] = $_POST[$column];
        }
    }
}

// initialize object
$id     = isset ($_POST['id']) ? $_POST['id'] : null;
$job    = new Job($db, $id, $loginUser->info);
$action = isset ($_POST['update']) ? "update" : "new";

//For updating data
if ( isset ($_POST['update']) && $id !== null ) {
    $params[] = $id;

    if ( ! $job->updateJob ($params, $id) ) {
        echo json_encode (
            array( "message" => "Failed", "success" => false )
        );
        return;
    }

    // update job teams
    if ( isset ($_POST["job_teams"]) && count ($_POST["job_teams"]) > 0 ) {
        if ( ! $job->updateJobTeams ($id, $_POST["job_teams"]) ) {
            $msg .= "Failed to update job teams<br>";
            $success = false;
        }
    }

    // deactivate forms and fields if the job deactivated is not null and the date is before today
    if ( isset ($_POST["date_deactivated"]) && $_POST["date_deactivated"] != "" && isPastToday ($_POST["date_deactivated"]) ) {
        $res = $job->deactivatedForms ($_POST["date_deactivated"]);
        $msg .= $res["message"] . "<br>";
        if ( $res["success"] == false ) {
            $success = false;
        }

        $res1 = $job->deactivatedFields ($_POST["date_deactivated"]);
        $msg .= $res1["message"] . "<br>";
        if ( $res1["success"] == false ) {
            $success = false;
        }
    }

} //END For updating data 

//For new  data
else {
    $insertedID = $job->addJob ($params);
    if ( ! $insertedID || $insertedID < 0 ) {
        echo json_encode (
            array( "message" => "Failed", "success" => false )
        );
        return;
    }

    // add job teams
    if ( isset ($_POST["job_teams"]) && count ($_POST["job_teams"]) > 0 ) {
        if ( ! $job->addJobTeams ($insertedID, $_POST["job_teams"]) ) {
            echo json_encode (
                array( "message" => "Failed to add job teams", "success" => false )
            );
            return;
        }
    }

} //END For new data 

$msg .= ucfirst ($action) . " completed!";
echo json_encode (
    array( "message" => $msg, "success" => $success, "data" => $job->jobInfo )
);
?>