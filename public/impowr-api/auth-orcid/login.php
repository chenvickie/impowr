<?php

include_once '../config/config.php';

// make sure these values are set in your configuration
$clientId    = $orcidConfig['clientID'];
$redirectUri = $orcidConfig['redirectUri'];
$authUrl     = $orcidConfig['authUrl'];

$authorizationUrl = $authUrl . "/authorize?client_id=" . $clientId . "&response_type=code&scope=/authenticate&redirect_uri=" . $redirectUri;

// Redirect to the authorization URL
header ("Location: $authorizationUrl");
exit ();
?>