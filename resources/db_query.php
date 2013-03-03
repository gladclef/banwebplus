<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/globals.php");

if ($global_opened_db === FALSE) {
		if (open_db())
						  $global_opened_db = TRUE;
}

function replace_values_in_db_query_string($s_query, $a_values) {
	foreach($a_values as $k=>$v) {
			$s_query = str_replace("[$k]", "[--$k--]", $s_query);
	}
	foreach($a_values as $k=>$v) {
			$s_query = str_replace("[--$k--]", mysql_real_escape_string($v), $s_query);
	}
	return $s_query;
}

function db_query($s_query, $a_values=NULL) {
	if ($a_values !== NULL && gettype($a_values) == 'array')
			$s_query_string = replace_values_in_db_query_string($s_query, $a_values);
	else
			$s_query_string = $s_query;
	$wt_retval = mysql_query($s_query_string);
	if ($wt_retval === TRUE || $wt_retval === FALSE)
			return $wt_retval;
	$a_retval = array();
	while ($row = mysql_fetch_assoc($wt_retval))
			$a_retval[] = $row;
	return $a_retval;
}

function open_db() {
	$link = mysql_connect('localhost', 'root', 'password');
	if ($link)
			return TRUE;
	else
			return FALSE;
}

// returns "(`key1`,`key2`,...) VALUES ('value1','value2',...)"
function array_to_where_clause($a_vars) {
	$a_where = array();
	foreach($a_vars as $k=>$v) {
			$k = mysql_real_escape_string($k);
			$v = mysql_real_escape_string($v);
			$a_where[] = "`$k`='$v'";
	}
	$s_where = implode(' AND ', $a_where);
	return $s_where;
}

// returns "`key1`='value1' AND `key2`='value2' AND ..."
function array_to_set_clause($a_vars) {
	$a_set = array();
	$a_values = array();
	foreach($a_vars as $k=>$v) {
			$k = mysql_real_escape_string($k);
			$v = mysql_real_escape_string($v);
			$a_set[] = $k;
			$a_values[] = $v;
	}
	$s_set = "(`".implode("`,`", $a_set)."`) VALUES ('".implode("','",$a_values)."')";
	return $s_set;
}

function create_row_if_not_existing($a_vars) {
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
	$a_result = db_query($s_query_string, $a_query_vars);
	if ($a_result !== NULL) {
			if (count($a_result) == 0) {
					$s_query_string = "INSERT INTO `[database]`.`[table]` $s_set";
					$a_query_vars = array("database"=>$database, "table"=>$table);
					$a_result = db_query($s_query_string, $a_query_vars);
					return TRUE;
			}
	}
	return FALSE;
}

?>