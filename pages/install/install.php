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
	 * @return TRUE if database is available, FALSE otherwise
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
	 * @return TRUE if formatted correctly, or FALSE if not.
	 */
	public function check_ini_files() {
		global $global_loaded_server_settings;
		global $global_opened_db;

		$server_file = dirname(__FILE__)."/../../resources/server_config.ini";
		$mysql_file = dirname(__FILE__)."/../../resources/mysql_config.ini";

		if (!file_exists($server_file) ||
			!file_exists($mysql_file)) {
			return FALSE;
		}

		$a_server = parse_ini_file($server_file);
		$a_mysql = parse_ini_file($mysql_file);
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
	 * @return TRUE if users have been created, FALSE otherwise.
	 */
	public function check_create_users() {
		global $maindb;
		global $global_opened_db;

		if (!$global_opened_db) {
			return FALSE;
		}
		
		// check if users already exist
		$a_users_count = db_query("SELECT COUNT(`id`) AS 'count' FROM `[maindb]`.`students`",
			array("maindb"=>$maindb));
		$i_users_count = intval($a_users_count[0]["count"]);
		if ($i_users_count > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Check that class and semester data is available.
	 * @return TRUE if data is available, FALSE otherwise.
	 */
	public function check_classes_availability() {
		$filename = dirname(__FILE__) . "/../../scraping/banweb_terms.php";

		if (!file_exists($filename)) {
			return FALSE;
		}
		return TRUE;
	}

	public function check_installed() {
		return ($this->check_install_database() &&
			$this->check_ini_files() &&
			$this->check_create_users() &&
			$this->check_classes_availability());
	}
}

global $o_project_installer;
$o_project_installer = new ProjectInstaller();

?>