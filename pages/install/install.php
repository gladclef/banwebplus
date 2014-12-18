<?php

require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/../../resources/db_query.php");

class ProjectInstaller {
	/***************************************************************************
	 ********************* P U B L I C   F U N C T I O N S *********************
	 **************************************************************************/

	/**
	 * Checks that a database is available to use.
	 * If not, return false.
	 */
	public function check_install_database() {
		global $global_opened_db;

		if ($global_opened_db) { // defined in db_query.php
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Checks that all necessary *.ini files are present and formatted correctly.
	 * Returns TRUE if formatted correctly, or FALSE if not.
	 */
	public function check_ini_files() {
		global $global_loaded_server_settings;
		global $global_opened_db;

		$a_server = parse_ini_file(dirname(__FILE__)."/../../resources/server_config.ini");
		$a_mysql = parse_ini_file(dirname(__FILE__)."/../../resources/mysql_config.ini");
		if ($a_server === FALSE ||
			$a_mysql === FALSE) {
			return FALSE;
		}
		if (!$global_loaded_server_settings ||
			!$global_opened_db) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Checks that the basic users have been created.
	 * If not, return false.
	 */
	public function check_create_users() {
		global $maindb;
		
		// check if users already exist
		$a_users_count = db_query("SELECT COUNT(`id`) AS 'count' FROM `[maindb]`.`students`",
			array("maindb"=>$maindb));
		$i_users_count = intval($a_users_count[0]["count"]);
		if ($i_users_count > 0) {
			return TRUE;
		}
		return FALSE;
	}

	public function check_installed() {
		return ($this->check_install_database() &&
			$this->check_ini_files() &&
			$this->check_create_users());
	}
}

global $o_project_installer;
$o_project_installer = new ProjectInstaller();

?>