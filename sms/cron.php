<?php
	require_once("../Connections/ma.php");
	require_once($serverroot."siti/sms_config.php");

@ini_set('user_agent', 'smscoin_generic_cron');
$response = file_get_contents(URL);
if ($response !== false) {
	if (preg_match('|(JSONResponse = \[.*?\])|is', $response, $feed) > 0) {
		$filename = dirname(__FILE__).'/lib/local.js';
		if (($hnd = @fopen($filename, 'w')) !== false) {
			if (@fwrite($hnd, $response) !== false) {
				die('Success, file updated @ '.date("r"));
			} else {
				die('File not writeable');
			}
			fclose($hnd);
		} else {
			die('Could not open file');
		}
	} else {
		die('Received file is not feed');
	}
} else {
	die('Unable to connect to remote server');
}
?>
