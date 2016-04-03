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
	 * Checks the server_config.ini file's validity, verifying that the global_path_to_jquery is
	 * correct (aka that the file at that location exists).
	 */
	public function check_jquery_path_validity() {
		global $global_path_to_jquery;

		return file_exists(dirname(__FILE__)."/../../$global_path_to_jquery");
	}

	/**
	 * Checks for the existance of tables (and table columns) in the database and
	 * creates or adds them if not existing.
	 * <p>
	 * Does not update the columns if they already exist.
	 */
	public function init_database()
	{
		global $maindb;
		global $mysqli;

		require_once(DIRNAME(__FILE__)."/../../resources/database_structure.php");

		// get the existing tables
		$a_tables = getTableNames();

		// save each table
		foreach ($a_basic_tables_structure as $s_table_name => $a_table_structure)
		{
			// get the information necessary to create the table, or just the row, as necessary
			$a_column_create_statements = array();
			$a_indexed_columns = array();
			$s_primary_key_column = "";
			foreach ($a_table_structure as $s_column_name => $a_column_structure)
			{
				if ($a_column_structure["isPrimaryKey"] === TRUE)
					$s_primary_key_column = $s_column_name;
				if ($a_column_structure["indexed"])
					$a_indexed_columns[] = $s_column_name;
				$s_create_statement = sprintf("%s NOT NULL %s",
					$a_column_structure["type"], $a_column_structure["special"]);
				$a_column_create_statements[$s_column_name] = $s_create_statement;
			}

			// does the table exist?
			if (!in_array($s_table_name, $a_tables))
			{
				// create the table
				$a_vars = array("maindb" => $maindb, "table" => $s_table_name);
				$s_id_column = "(`id` INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))";
				$b_create_var = db_query("CREATE TABLE IF NOT EXISTS `[maindb]`.`[table]` $s_id_column", $a_vars);
				if ($b_create_var === FALSE)
				{
					error_log("error creating mysql table $s_table_name");
					continue;
				}
			}

			// get the existing column names
			$a_column_names = getColumnNames($s_table_name);

			// save each column
			foreach ($a_column_create_statements as $s_column_name => $s_column_create_statement)
			{
				// does the column exist?
				if (in_array($s_column_name, $a_column_names))
				{
					continue;
				}

				// add the column!
				$a_vars = array("maindb" => $maindb, "table" => $s_table_name,
					            "column_create" => $s_column_create_statement,
					            "column_name" => $s_column_name);
				db_query("ALTER TABLE `[maindb]`.`[table]` ADD COLUMN `[column_name]` [column_create]", $a_vars);

				// set the index or primary key
				if (in_array($s_column_name, $a_indexed_columns))
				{
					db_query("ALTER TABLE `[maindb]`.`[table]` ADD INDEX `[column_name]` (`[column_name]`)", $a_vars);
				}
				if ($s_primary_key_column === $s_column_name)
				{
					db_query("ALTER TABLE `[maindb]`.`[table]` ADD PRIMARY KEY `[column_name]` (`[column_name]`)", $a_vars);
				}
			} // save each column
		} // save each table

		// insert predefined values
		foreach ($a_database_insert_values as $s_table_name => $a_rows)
		{
			// determine if there are enough missing rows to constitute inserting the predefined rows
			$a_vars = array("database" => $maindb, "table" => $s_table_name);
			$i_existing_row_count = db_query("SELECT COUNT(`id`) AS count FROM `[database]`.`[table]`", $a_vars);
			$i_existing_row_count = $i_existing_row_count[0]["count"];
			$i_new_row_count = count($a_rows);
			if ($i_new_row_count > $i_existing_row_count / 2)
			{

				// enough rows were missing, insert the defined rows!
				foreach ($a_rows as $i => $a_row)
				{
					$a_row_and_db = array_merge($a_row, $a_vars);
					create_row_if_not_existing($a_row_and_db);
				}
			}
		}
	} // check init database

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

		// check if users count > 0
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
		if ($this->check_install_database())
			$this->init_database();
		return ($this->check_install_database() &&
			$this->check_ini_files() &&
			$this->check_create_users() &&
			$this->check_classes_availability() &&
			$this->check_jquery_path_validity());
	}
}

global $o_project_installer;
$o_project_installer = new ProjectInstaller();

?>