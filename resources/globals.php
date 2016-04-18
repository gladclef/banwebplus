<?php

require_once(dirname(__FILE__)."/debug.php");
define_global_vars();
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/../objects/user.php");

function define_global_vars() {
	global $maindb;
	global $global_user;
	global $global_opened_db;
	global $session_started;
	global $global_path_to_jquery;
	global $tab_init_function;
	global $global_loaded_server_settings;
	global $mysqli;
	global $fqdn;

	$maindb = "";
	$global_path_to_jquery = "";
	$global_user = NULL;
	$global_opened_db = FALSE;
	$session_started = FALSE;
	$tab_init_function = NULL; // redefined with each tab file required
	$global_loaded_server_settings = FALSE;
	$mysqli = NULL;

	$a_configs = array();
	$filename = dirname(__FILE__) . "/server_config.ini";
	if (file_exists($filename)) {
		$a_configs = parse_ini_file($filename);
	} else {
		print_debug_as_html_paragraph("Could not find ${filename}");
	}

	if ($a_configs === FALSE) {
		return;
	}

	if (isset($a_configs["maindb"])) {
		$maindb = $a_configs["maindb"];
	} else {
		print_debug_as_html_paragraph("maindb is not set in server_config.ini");
	}
	if (isset($a_configs["global_path_to_jquery"])) {
		$global_path_to_jquery = $a_configs["global_path_to_jquery"];
	} else {
		print_debug_as_html_paragraph("global_path_to_jquery is not set in server_config.ini");
	}
	if (isset($a_configs["timezone"])) {
		date_default_timezone_set($a_configs["timezone"]);
	} else {
		print_debug_as_html_paragraph("timezone is not set in server_config.ini");
	}
	if (isset($a_configs["fqdn"])) {
		$fqdn = $a_configs["fqdn"];
	} else {
		print_debug_as_html_paragraph("fqdn is not set in server_config.ini");
	}

	if (isset($a_configs["maindb"]) &&
		isset($a_configs["global_path_to_jquery"]) &&
		isset($a_configs["timezone"])) {
		$global_loaded_server_settings = TRUE;
	} else {
		print_debug_as_html_paragraph("server_config.ini is not configured properly");
	}
}

?>