<?php
session_start ();

//auth
include_once '../objects/authentication.php';
include_once '../objects/AuthenticationException.php';

//config
include '../config/config.php';
include '../utils/utils.php';

// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';

// Check for the authorization code in the query parameters
if ( ! isset ($_GET['code']) ) {
    echo json_encode (
        array( "message" => 'No authorization code found in the URL.', "success" => false )
    );
    exit ();
}

// instantiate database and product object
$database = new Database();
$db       = $database->getConnection ($connectionOptions);

// initialize object
$auth = new Authentication($orcidConfig);

$authorizationCode = $_GET['code'];

$data = $auth->orcidCallback ($authorizationCode);

// Check for the access token in the response
if ( isset ($data['access_token']) ) {

    // initialize object
    $user = new User($db);

    // check with impowr user permission by orcid
    $user->read ($data["orcid"]);

    if ( $user->info != null ) {
        $res         = $auth->checkSession ($data["orcid"], $data["access_token"]);
        $queryParams = [
            'access_token' => $data['access_token'],
            'orcid'        => $data['orcid'],
            'name'         => $data['name'],
            'expire_on'    => isset ($res['session_expire_time']) ? $res['session_expire_time'] : "",
            "is_admin"     => $user->info["super_admin"] == "YES" ? true : falsetes
        ];

        $user->updateLastLogin ();
        $queryString = http_build_query ($queryParams);
        $redirectUrl = $orcidConfig['callBackUrl'] . '?' . $queryString;

        header ('Location: ' . $redirectUrl);
    } else {
        $error = "Orcid ID: " . $data["orcid"] . " does not exist in Impowr system!";
        echo json_encode (
            array( "message" => $error, "success" => false )
        );
    }
} else {
    // Handle errors in the response
    $error = 'Failed to get access token: ' . (isset ($data['error_description']) ? $data['error_description'] : 'Unknown error');
    echo json_encode (
        array( "message" => $error, "success" => false )
    );
}
?>