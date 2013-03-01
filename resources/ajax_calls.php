<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/check_logged_in.php");
require_once(dirname(__FILE__)."/db_query.php");

$s_command = get_post_var("command");
$s_classes = get_post_var("classes");
$s_year = get_post_var("year");
$s_semester = get_post_var("semester");
$s_timestamp = get_post_var("timestamp");

function save_classes($s_classes, $s_year, $s_semester, $s_timestamp) {
	$a_queryvars = array("classes"=>$s_classes, "tablename"=>"semester_classes", "year"=>$s_year, "semester"=>$s_semester, "timestamp"=>$s_timestamp, "id"=>"");
	// check if the year/semester already exists
	$a_saved_query = user_query("SELECT `id` FROM `[tablename]` WHERE `year`='[year]' AND `semester`='[semester]'", $a_queryvars);
	$b_exists = TRUE;
	if ($a_saved_query === FALSE) 
			$b_exists = FALSE;
	if (count($a_saved_query) == 0)
			$b_exists = FALSE;
	if ($b_exists)
			$a_queryvars['id'] = $a_saved_query[0]['id'];
	// check if the date is greater than the current date
	// don't save if it is
	if ($b_exists) {
			$a_saved_query = user_query("SELECT `id` FROM `[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `time_submitted`>'[timestamp]'", $a_queryvars);
			if (count($a_saved_query) > 0)
					return "success|already saved later query";
	}
	// update/insert
	if ($b_exists) {
			user_query("UPDATE `[tablename]` SET `classes`='[classes]',`time_submitted`='[timestamp]' WHERE `id`='[id]'", $a_queryvars);
			if (mysql_affected_rows() > 0)
					return "success|updated classes";
			else
					return "failed|update failed";
	} else {
			user_query("INSERT INTO `[tablename]` (`classes`,`time_submitted`,`year`,`semester`) VALUES ('[classes]','[timestamp]','[year]','[semester]')", $a_queryvars);
			if (mysql_affected_rows() > 0)
					return "success|inserted classes";
			else
					return "failed|insert failed";
	}
}

function load_classes($s_year, $s_semester) {
	$a_queryvars = array("tablename"=>"semester_classes", "year"=>$s_year, "semester"=>$s_semester);
	$a_classes = user_query("SELECT `classes` FROM `[tablename]` WHERE `year`='[year]' AND `semester`='[semester]'", $a_queryvars);
	if ($a_classes == FALSE)
			return;
	return $a_classes[0]['classes'];
}

if ($s_command == "save classes") {
		echo save_classes($s_classes, $s_year, $s_semester, $s_timestamp);
} else if ($s_command == "load classes") {
		echo load_classes($s_year, $s_semester);
} else {
		echo "failed|bad command";
}
?>