<?php

require_once(dirname(__FILE__)."/globals.php");
require_once(dirname(__FILE__)."/db_query.php");

foreach($argv as $arg){
	$e = explode("=", $arg);
	if (count($e) == 2) {
		$_GET[$e[0]] = $e[1];
	}
}

if (isset($_GET["action"])) {
		executeAction($_GET["action"]);
} else {
		drawOptions();
}

function executeAction($s_action) {
	if ($s_action == "save") {
			saveTables();
	} else if ($s_action == "load") {
			loadTables();
	} else if ($s_action == "save_common_data") {
			saveCommon_Data();
	} else if ($s_action == "load_common_data") {
			loadCommon_Data();
	}
}

function drawOptions() {
	echo "<form action='' method='GET'><input type='hidden' name='action' value='save'></input><input type='submit' value='Save Tables'></input> saves the current table configuration to a file (db->file)</form>
<form action='' method='GET'><input type='hidden' name='action' value='load'></input><input type='submit' value='Update Tables'></input> takes the table configuration in a file and applies it to the database (file->db)</form>
<form action='' method='GET'><input type='hidden' name='action' value='save_common_data'></input><input type='submit' value='Save Common_Data'></input> saves the accesses, buglog, and feedback to a file (db->file)</form>
<form action='' method='GET'><input type='hidden' name='action' value='load_common_data'></input><input type='submit' value='Update Common_Data'></input> takes the accesses, buglog, and feedback from a file and applies it to the database (file->db)</form>";
}

function saveTables() {
	$filename = dirname(__FILE__)."/../database_desc.txt";
	$a_tables = unserialize(file_get_contents($filename));
	$a_new_tables = array("Tables"=>getTables());
	$a_tables = array_merge($a_tables, $a_new_tables);
	$s_tables = serialize($a_tables);
	$success = file_put_contents($filename, $s_tables);
	if ($success === FALSE) {
			echo "<span style='color:red;'>ERROR:</span> failed to save file";
	} else {
			echo "<pre>saved to file ".realpath($filename).":\n\nmodtime:\n".date("Y-m-d H:i:s",filemtime($filename))." (current time ".date("Y-m-d H:i:s").")</pre>";
	}
	echo "<pre>\n\ncontents:\n".file_get_contents($filename)."</pre>";
}

function loadTables() {
	$filename = dirname(__FILE__)."/../database_desc.txt";
	$a_file_tables = unserialize(file_get_contents($filename));
	$a_file_tables = $a_file_tables["Tables"];
	$a_tables = getTables();
	updateTables($a_tables, $a_file_tables);
}

function saveCommon_Data() {
	global $maindb;
	$a_common_data = getCommon_date();
	$filename = dirname(__FILE__)."/../database_desc.txt";
	$a_tables = unserialize(file_get_contents($filename));
	$a_tables = array_merge($a_tables, array("Common_Data"=>$a_common_data));
	$success = file_put_contents($filename, serialize($a_tables));
	if ($success === FALSE) {
			echo "<span style='color:red;'>ERROR:</span> failed to save file";
	} else {
			echo "<pre>saved to file ".realpath($filename).":\n\nmodtime:\n".date("Y-m-d H:i:s",filemtime($filename))." (current time ".date("Y-m-d H:i:s").")</pre>";
	}
	echo "<pre>\n\ncontents:\n".file_get_contents($filename)."</pre>";	
}

function loadCommon_Data() {
	global $maindb;
	$filename = dirname(__FILE__)."/../database_desc.txt";
	$a_tables = unserialize(file_get_contents($filename));
	$a_common_data = $a_tables["Common_Data"];
	$a_curr_common_data = getCommon_date();
	updateCommon_Data($a_curr_common_data, $a_common_data);
}

function getCommon_date() {
	global $maindb;
	return array(
		array("name"=>"accesses", "index"=>"name", "rows"=>db_query("SELECT * FROM `{$maindb}`.`accesses`")),
		array("name"=>"buglog", "index"=>"id", "rows"=>db_query("SELECT * FROM `{$maindb}`.`buglog`")),
		array("name"=>"feedback", "index"=>"id", "rows"=>db_query("SELECT * FROM `{$maindb}`.`feedback`"))
	);
}

function updateCommon_Data($a_curr_common_data, $a_common_data) {
	global $maindb;
	echo "<pre>";
	foreach($a_common_data as $a_table) {
			$s_tablename = mysql_real_escape_string($a_table["name"]);
			$s_index = mysql_real_escape_string($a_table["index"]);
			foreach($a_table["rows"] as $a_row) {
					$b_found = FALSE;
					foreach($a_curr_common_data as $a_curr_table) {
							if ($a_curr_table["name"] != $s_tablename) {
									continue;
							}
							foreach($a_curr_table["rows"] as $a_curr_acc) {
									if ($a_curr_acc[$s_index] == $a_row[$s_index]) {
											if (print_r($a_row,TRUE) != print_r($a_curr_acc,TRUE)) {
													db_query("UPDATE `{$maindb}`.`{$s_tablename}` SET ".array_to_update_clause($a_row)." WHERE `{$s_index}`='[{$s_index}]'", $a_row, 1);
													echo "\n";
											}
											$b_found = TRUE;
											break;
									}
							}
							break;
					}
					if (!$b_found) {
							db_query("INSERT INTO `{$maindb}`.`{$s_tablename}` ".array_to_insert_clause($a_row), $a_row, 1);
							echo "\n";
					}
			}
	}
	echo "</pre>";
}

function updateTables($a_old_tables, $a_new_tables) {

	global $maindb;
	echo "<pre>";
	
	// index current tables by name,
	// and their columns by name,
	// and add a "visited" marker so as to know if the column needs to be deleted
	$a_tables = array();
	foreach($a_old_tables as $a_table) {
			foreach($a_table["columns"] as $k=>$a_column) {
					unset($a_table["columns"][$k]);
					$a_table["columns"][$a_column["name"]] = array_merge($a_column, array("visited"=>0));
			}
			$a_tables[$a_table["Table"]] = $a_table;
	}
	
	// check for non-existant tables
	foreach($a_new_tables as $k=>$a_table) {
			if (!isset($a_tables[$a_table["Table"]])) {
					db_query(str_replace("CREATE TABLE ", "CREATE TABLE `{$maindb}`.", $a_table["Create Table"]), 1);
					echo "\n";
					unset($a_new_tables[$k]);
			}
	}

	// all other tables are either the same or need to be updated
	// check for tables that need to be updated
	foreach($a_new_tables as $a_table) {
			$s_tablename = $a_table["Table"];
			$a_curr_table = $a_tables[$s_tablename];
			$a_curr_cols = $a_curr_table["columns"];
			
			// check for columns that need to be updated
			// or columns that don't need to be updated
			$s_prev_colname = "";
			foreach($a_table["columns"] as $col_key=>$a_column) {
					$s_colname = $a_column["name"];
					if ($s_prev_colname != "") {
						$a_table["columns"][$col_key]["after_clause"] = "AFTER ".mysql_real_escape_string($s_prev_colname);
					}
					$s_prev_colname = $s_colname;
					if (isset($a_curr_cols[$s_colname])) {
							if ($a_curr_cols[$s_colname]["desc"] != $a_column["desc"]) {
									db_query("ALTER TABLE `{$maindb}`.`[table]` MODIFY COLUMN `[colname]` [desc]", array("table"=>$s_tablename, "colname"=>$s_colname, "desc"=>$a_column["desc"]), 1);
									echo "\n";
							}
							unset($a_table["columns"][$col_key]);
							unset($a_curr_cols[$s_colname]);
					}
			}

			// check for columns that need to be deleted
			foreach($a_curr_cols as $col_key=>$a_curr_column) {
					$b_found = FALSE;
					$s_colname = $a_curr_column["name"];
					if ($s_colname == "") {
						unset($a_curr_cols[$col_key]);
						continue;
					}
					foreach($a_table["columns"] as $col_key=>$a_column) {
							if ($s_colname == $a_column["name"]) {
									$b_found = TRUE;
									break;
							}
					}
					if (!$b_found) {
							db_query("ALTER TABLE `{$maindb}`.`[table]` DROP COLUMN [colname]", array("table"=>$s_tablename, "colname"=>$s_colname), 1);
							echo "\n";
					}
			}
			
			// check for columns that need to be created
			foreach($a_table["columns"] as $col_key=>$a_column) {
					$s_colname = $a_column["name"];
					$s_after = $a_column["after_clause"];
					db_query("ALTER TABLE `{$maindb}`.`[table]` ADD COLUMN [colname] [desc] {$s_after}", array("table"=>$s_tablename, "colname"=>$s_colname, "desc"=>$a_column["desc"]), 1);
					echo "\n";
			}

			// check for keys to modify
			foreach($a_table["keys"] as $k=>$s_key) {
					$b_found = FALSE;
					
					// does the key already exist?
					foreach($a_curr_table["keys"] as $s_curr_key) {
							if ($s_curr_key == $s_key) {
									$b_found = TRUE;
									break;
							}
					}
					
					// it doesn't! Create it!
					if (!$b_found) {
							$s_keytype = (strpos($s_key, "PRIMARY") === 0) ? "PRIMARY KEY" : "KEY";
							$a_keyparts = explode("`", $s_key);
							$s_keyname = $a_keyparts[1];
							db_query("ALTER TABLE `{$maindb}`.`[table]` ADD {$s_keytype} '[keyname]'", array("table"=>$s_tablename, "keyname"=>$s_keyname), 1);
							echo "\n";
					}
			}
	}

	echo "</pre>";
}

function getTables() {
	global $maindb;
	$a_tables = db_query("SHOW TABLES IN `[maindb]`", array("maindb"=>$maindb));
	$a_retval = array();
	for($i = 0; $i < count($a_tables); $i++) {
			$s_tablename = $a_tables[$i]["Tables_in_{$maindb}"];
			$a_retval[] = getTableDescription($s_tablename);
	}
	return $a_retval;
}

function getTableDescription($s_tablename) {
	global $maindb;
	$a_create = db_query("SHOW CREATE TABLE `[maindb]`.`[table]`", array("maindb"=>$maindb, "table"=>$s_tablename));
	$a_create = $a_create[0];
	$a_desc = explode("\n", $a_create["Create Table"]);
	$a_vals = array("columns"=>array(), "keys"=>array());
	foreach($a_desc as $k=>$s_desc) {
			$s_line = trim($s_desc);
			if (strpos($s_line, "CREATE TABLE ") === 0 || strpos($s_line, ") ENGINE=MyISAM ") === 0) {
					unset($a_desc[$k]);
					continue;
			}
			if (strpos($s_line, "KEY ") !== FALSE) {
					$a_vals["keys"][] = trim(str_replace(",", "", $s_line));
			} else {
					$a_column = explode("`", $s_line);
					$s_colname = trim($a_column[1]);
					$s_coldesc = trim(str_replace(",", "",$a_column[2]));
					$a_vals["columns"][] = array("name"=>$s_colname, "desc"=>$s_coldesc);
			}
	}
	$a_create = array_merge($a_create, $a_vals);
	return $a_create;
}

?>
