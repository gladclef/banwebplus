<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/globals.php");

if ($global_opened_db === FALSE) {
	if (open_db()) {
		$global_opened_db = TRUE;
	}
}

function replace_values_in_db_query_string($s_query, $a_values) {
	global $mysqli;

	foreach($a_values as $k=>$v) {
			$s_query = str_replace("[$k]", "[--$k--]", $s_query);
	}
	foreach($a_values as $k=>$v) {
			$s_query = str_replace("[--$k--]", $mysqli->real_escape_string($v), $s_query);
	}
	return $s_query;
}

function db_query($s_query, $a_values=NULL, $b_print_query = FALSE) {
	global $mysqli;

	if ($a_values !== NULL && gettype($a_values) == 'array')
			$s_query_string = replace_values_in_db_query_string($s_query, $a_values);
	else
			$s_query_string = $s_query;
	if ($b_print_query === TRUE || $b_print_query === 2)
			error_log($s_query_string);
	else if ($b_print_query === 1)
			echo $s_query_string;
	$wt_retval = $mysqli->query($s_query_string);

	// check for booleans
	if ($wt_retval === TRUE || $wt_retval === FALSE) {
			if ($wt_retval === FALSE && $mysqli->errno != 0) {
				error_log($s_query_string);
				error_log("Last MySQL call failed: " . $mysqli->error);
			}
			return $wt_retval;
	}

	// return array of selected values
	$a_retval = array();
	while ($row = $wt_retval->fetch_assoc())
			$a_retval[] = $row;

	$wt_retval->free_result();
	return $a_retval;
}

function open_db() {
	global $global_opened_db;
	global $mysqli;

	if ($global_opened_db === TRUE) {
			return TRUE;
	}

	$a_configs = [];
	$filename = dirname(__FILE__)."/mysql_config.ini";
	if (file_exists($filename)) {
		$a_configs = parse_ini_file($filename);
	} else {
		print_debug_as_html_paragraph("Could not find file ${filename}");
		return FALSE;
	}
	if (!isset($a_configs["host"]) ||
		!isset($a_configs["user"]) ||
		!isset($a_configs["password"])) {
		print_debug_as_html_paragraph("Undefined host, user, and password in ${filename}");
		return FALSE;
	}

	# try and open the database
	if (!function_exists("mysqli_connect")) {
		print_debug_as_html_paragraph("Must install php5-mysql to interface to MySQL database. Then add extension=php_mysqli.so to your php.ini configuration file and restart the Apache server.");
		//return FALSE;
	}
	try {
		$mysqli = mysqli_connect($a_configs["host"], $a_configs["user"], $a_configs["password"]);
	} catch (Exception $e) {
		print_debug_as_html_paragraph("Unable to connect to MySQL. ${e}");
		return FALSE;
	}
	if ($mysqli->connect_errno) {
		return FALSE;
	}
	$global_opened_db = TRUE;
	return TRUE;
}

// returns "`key1`='value1' AND `key2`='value2' AND ..."
function array_to_where_clause($a_vars) {
	global $mysqli;

	$a_where = array();
	foreach($a_vars as $k=>$v) {
			$k = $mysqli->real_escape_string($k);
			$v = $mysqli->real_escape_string($v);
			$a_where[] = "`$k`='$v'";
	}
	$s_where = implode(' AND ', $a_where);
	return $s_where;
}

// returns "(`key1`,`key2`,...) VALUES ('value1','value2',...)"
function array_to_set_clause($a_vars) {
	global $mysqli;

	$a_set = array();
	$a_values = array();
	foreach($a_vars as $k=>$v) {
			$k = $mysqli->real_escape_string($k);
			$v = $mysqli->real_escape_string($v);
			$a_set[] = $k;
			$a_values[] = $v;
	}
	$s_set = "(`".implode("`,`", $a_set)."`) VALUES ('".implode("','",$a_values)."')";
	return $s_set;
}

// returns "`key1`='[key1]', `key2`='[key2]'"
function array_to_update_clause($a_vars) {
	$a_retval = array();
	foreach($a_vars as $k=>$v)
			$a_retval[] = "`{$k}`='[{$k}]'";
	return implode(",", $a_retval);
}

// returns "(`key1`, `key2`, ...) VALUES ('[key1]', '[key2]', ...)"
function array_to_insert_clause($a_vars) {
	if (count($a_vars) == 0)
			return "";
	$a_keys = array();
	foreach($a_vars as $k=>$v)
			$a_keys[] = $k;
	return "(`".implode("`,`",$a_keys)."`) VALUES ('[".implode("]','[",$a_keys)."]')";
}

function create_row_if_not_existing($a_vars, $b_print_queries = FALSE) {
	// get the database, table, and properties
	$database = $a_vars['database'];
	$table = $a_vars['table'];
	$a_properties = $a_vars;
	foreach($a_properties as $k=>$v)
			if (in_array($k, array('database','table')))
					unset($a_properties[$k]);
	if (count($a_properties) == 0)
			return FALSE;
	// get the where and set strings
	$s_where = array_to_where_clause($a_properties);
	$s_set = array_to_set_clause($a_properties);
	// check if it exists
	$s_query_string = "SELECT `id` FROM `[database]`.`[table]` WHERE $s_where";
	$a_query_vars = array("database"=>$database, "table"=>$table);
	$a_result = db_query($s_query_string, $a_query_vars, $b_print_queries);
	if ($a_result !== NULL) {
			if (count($a_result) == 0) {
					$s_query_string = "INSERT INTO `[database]`.`[table]` $s_set";
					$a_query_vars = array_merge($a_properties, array("database"=>$database, "table"=>$table));
					$a_result = db_query($s_query_string, $a_query_vars, $b_print_queries);
					return TRUE;
			}
	}
	return FALSE;
}

function getTableNames() {
	global $maindb;
	$a_tables = db_query("SHOW TABLES IN `[maindb]`", array("maindb"=>$maindb));
	$a_retval = array();
	for($i = 0; $i < count($a_tables); $i++) {
			$s_tablename = $a_tables[$i]["Tables_in_{$maindb}"];
			$a_retval[] = $s_tablename;
	}
	return $a_retval;
}

function getColumnNames($s_tablename)
{
	global $maindb;
	global $mysqli;
	$a_retval = array();

	// get the description
	$a_vars = array("maindb"=>$maindb, "table"=>$s_tablename);
	$a_description = db_query("DESCRIBE `[maindb]`.`[table]`", $a_vars);

	// parse the description for column names
	$a_column_names = array();
	foreach ($a_description as $index => $a_column_description)
	{
		$a_column_names[] = $a_column_description["Field"];
	}

	return $a_column_names;
}

?>
