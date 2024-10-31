<?php
include_once '../config/auth.php';
include_once '../objects/teams.php';

if ( ! $_POST ) {
    echo json_encode (
        array( "message" => "Invalid Team Data", "success" => false )
    );
    return false;
}

$requiredColumns = [ "team_name", "description" ];
$params          = [];
foreach ($requiredColumns as $column) {
    if ( ! isset ($_POST[$column]) ) {
        echo json_encode (
            array( "message" => "Invalid Team Data", "success" => false )
        );
        return false;
    } else {
        $params[] = $_POST[$column];
    }
}

// initialize object
$id     = isset ($_POST['id']) ? $_POST['id'] : null;
$teams  = new Teams($db, $loginUser->info, $id);
$action = isset ($_POST['update']) ? "update" : "new";

//For updating data
if ( isset ($_POST['update']) && $id !== null ) {
    $params[] = $id;

    if ( ! $teams->updateTeam ($params) ) {
        echo json_encode (
            array( "message" => "Failed", "success" => false )
        );
        return;
    }

    if ( $_POST["team_users"] && count ($_POST["team_users"]) > 0 ) {
        if ( ! $teams->updateTeamUsers ($id, $_POST["team_users"]) ) {
            echo json_encode (
                array( "message" => "Failed to update team users", "success" => false )
            );
            return;
        }
    }
} //END For updating data 

//For new  data
else {
    $id = $teams->addTeam ($params);
    if ( ! $id || $id < 0 ) {
        echo json_encode (
            array( "message" => "Failed", "success" => false )
        );
        return;
    }

    if ( $_POST["team_users"] && count ($_POST["team_users"]) > 0 ) {
        if ( ! $teams->addTeamUsers ($id, $_POST["team_users"]) ) {
            echo json_encode (
                array( "message" => "Failed to add team users", "success" => false )
            );
            return;
        }
    }
} //END For new data 

$msg = ucfirst ($action) . " completed!";
echo json_encode (
    array( "message" => $msg, "success" => true, "data" => $teams->getTeamInfo ($id) )
);
?>