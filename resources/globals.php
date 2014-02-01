<?php

define_global_vars();
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/../objects/user.php");

function define_global_vars() {
	global $maindb;
	global $on_bens_computer;
	global $global_user;
	global $global_opened_db;
	global $session_started;
	global $global_path_to_jquery;
	global $tab_init_function;
	global $db_is_already_connected;

	$a_configs = parse_ini_file(dirname(__FILE__)."/server_config.ini");

	$maindb = $a_configs["maindb"];
	$on_bens_computer = $a_configs["on_bens_computer"];

	if ($db_is_already_connected !== TRUE)
			$db_is_already_connected = FALSE;
	$global_user = NULL;
	$global_opened_db = FALSE;
	$session_started = FALSE;
	$global_path_to_jquery = $a_configs["global_path_to_jquery"];
	$tab_init_function = NULL; // redefined with each tab file required

	date_default_timezone_set($a_configs["timezone"]);
}

?>