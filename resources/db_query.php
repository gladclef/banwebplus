<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/globals.php");

if ($global_opened_db === FALSE) {
		if (open_db())
						  $global_opened_db = TRUE;
}

function user_query($s_query, $a_values) {
	global $global_user;

	$username = $global_user->get_name();
	$s_query = str_ireplace("from ", "FROM `student_$username_db`.", $s_query);
	return db_query($s_query, $a_values);
}

function replace_values_in_db_query_string($s_query, $a_values) {
	foreach($a_values as $k=>$v) {
			$s_query = str_replace("[$k]", "[--$k--]", $s_query);
	}
	foreach($a_values as $k=>$v) {
			$s_query = str_replace("[--$k--]", $v, $s_query);
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
?>