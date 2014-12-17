<?php

require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/../../resources/db_query.php");

class ProjectInstaller {
	/***************************************************************************
	 ********************* P U B L I C   F U N C T I O N S *********************
	 **************************************************************************/

	/**
	 * Checks that there aren't any arguments directed at the installer.
	 * If there aren't any or the arguments aren't necessary returns true.
	 * If there are arguments that cause a page to redirect returns false.
	 */
	public function check_arguments() {
		if (isset($_POST["installer"]) &&
			$_POST["installer"] === "do_install") {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Checks that a database is available to use.
	 * If not, draws the page to create a database and returns false.
	 */
	public function check_install_database() {
		if ($global_opened_db) { // defined in db_query.php
			return TRUE;
		}

		$o_project_installer->redirect();
		return FALSE;
	}

	/**
	 * Checks that the basic users have been created.
	 * If not, draws the page to create users and returns false.
	 */
	public function check_create_users() {
		
		$o_project_installer->redirect();
	}

	/***************************************************************************
	 ******************** P R I V A T E   F U N C T I O N S ********************
	 **************************************************************************/

	/**
	 * Draw the page to install the database.
	 */
	private function draw_install_database() {

	}
}
$o_project_installer = new ProjectInstaller();

?>