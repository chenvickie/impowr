<?php
session_start ();

include_once '../utils/phpCAS/CAS.php';
include_once '../utils/curl.php';

define ('SESSION_EXPIRE', 1800);

/*** Log Settings ***/
$logFile = "../logs/auth-" . date ("Y-m-d") . ".log";
ini_set ("log_errors", 1);
ini_set ("error_log", $logFile);
/*** END Log Settings ***/

class Authentication
{
	private $auth_config;

	public function __construct ($config)
	{
		$this->auth_config = $config;
	}

	public function logout ()
	{
		if ( isset ($_SESSION) ) {
			try {
				session_destroy ();
				session_unset ();
			}
			catch ( e ) {
				// print_r(e)
			}
		}
		return '';
	}

	public function checkSession ($username, $token = "")
	{
		// check and refresh session
		if ( isset ($_SESSION["session_expire_time"]) ) {
			if ( $_SESSION["session_expire_time"] > time () ) {
				$_SESSION["session_expire_time"] = time () + SESSION_EXPIRE; # refresh session
				return $_SESSION;
			} else {
				$this->logout ();
				return $_SESSION;
			}
		} else {
			$_SESSION["username"]            = $username;
			$_SESSION["token"]               = $token;
			$_SESSION["session_expire_time"] = time () + SESSION_EXPIRE;
		}
		return $_SESSION;
	}

	// public function ldapLogin ($username, $password)
	// {
	// 	if ( ! isset ($username) || ! isset ($password) ) {
	// 		return '';
	// 		//exit();
	// 	}
	// 	$connect = ldap_connect ($this->auth_config['host']);

	// 	if ( ! $connect ) {
	// 		return "couldn't connect to LDAP {$this->auth_config['host']}<br>";
	// 		//exit;
	// 	}

	// 	ldap_set_option ($connect, LDAP_OPT_PROTOCOL_VERSION, $this->auth_config['version']);
	// 	ldap_set_option ($connect, LDAP_OPT_REFERRALS, $this->auth_config['referrals']);

	// 	foreach ($this->auth_config['bindSuffix'] as $suffix) {
	// 		try {
	// 			$bind = ldap_bind ($connect, $username . $suffix, $password);
	// 		}
	// 		catch ( Exception $e ) {
	// 			return $e;
	// 		}

	// 		if ( $bind ) {
	// 			$_SESSION["username"] = $username;
	// 			logs ("[DEBUG] session_start: " . $_SESSION["username"]);
	// 			return $this->checkSession ($username);
	// 		}
	// 	}
	// 	return '';
	// }

	function orcidCallback ($code)
	{
		// Orcid Configuration
		$clientId     = $this->auth_config["clientID"];
		$clientSecret = $this->auth_config["clientSecret"];
		$redirectUri  = $this->auth_config["redirectUri"];

		// Prepare the data for the POST request
		$postFields = [
			'client_id'     => $clientId,
			'client_secret' => $clientSecret,
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'redirect_uri'  => $redirectUri,
		];

		return callAPI ("POST", $this->auth_config["tokenEndpoint"], $postFields);
	}
}
?>