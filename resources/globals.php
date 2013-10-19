<?php
$maindb = "banwebplus";
$userdb = "students";
$settings_table = "user_settings";
$on_bens_computer = 'main';
if ($_SERVER['SERVER_ADDR'] == '192.168.116.128') {
		$maindb = "banweb_test_main";
		$on_bens_computer = 'ben_laptop';
} else if ($_SERVER['DOCUMENT_ROOT'] == '/new_banweb') {
	$on_bens_computer = 'ben_worktop';
}

$global_user = NULL;
$global_opened_db = FALSE;
$session_started = FALSE;
$global_path_to_jquery = "/jquery/js/jquery-1.9.0.js";
if ($on_bens_computer == 'ben_worktop')
	$global_path_to_jquery = "/jquery-ui-1.10.0.custom/js/jquery-1.9.0.js";
$tab_init_function = NULL; // redefined with each tab file required

date_default_timezone_set('America/Denver');

require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/../objects/user.php");

?>