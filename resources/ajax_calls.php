<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/check_logged_in.php");
require_once(dirname(__FILE__)."/db_query.php");

$s_command = get_post_var("command");
$s_classes = get_post_var("classes");
$s_year = get_post_var("year");
$s_semester = get_post_var("semester");
$s_timestamp = get_post_var("timestamp");
$s_settings = get_post_var("settings");

function save_classes($s_classes, $s_year, $s_semester, $s_timestamp) {
	global $maindb;
	global $global_user;

	if ($global_user->check_is_guest())
			return 'failed|guest can\'t save classes';
	
	$a_queryvars = array("classes"=>$s_classes, "tablename"=>"semester_classes", "year"=>$s_year, "semester"=>$s_semester, "timestamp"=>$s_timestamp, "id"=>"", "maindb"=>$maindb, "user_id"=>$global_user->get_id());
	// check if the year/semester already exists
	$a_saved_query = db_query("SELECT `id` FROM `[maindb]`.`[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `user_id`='[user_id]'", $a_queryvars);
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
			$a_saved_query = db_query("SELECT `id` FROM `[maindb]`.`[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `time_submitted`>'[timestamp]' AND `user_id`='[user_id]'", $a_queryvars);
			if (count($a_saved_query) > 0)
					return "success|already saved later query";
	}
	// update/insert
	if ($b_exists) {
			db_query("UPDATE `[maindb]`.`[tablename]` SET `classes`='[classes]',`time_submitted`='[timestamp]' WHERE `id`='[id]'", $a_queryvars);
			if (mysql_affected_rows() > 0)
					return "success|updated classes";
			else
					return "failed|update failed";
	} else {
			db_query("INSERT INTO `[maindb]`.`[tablename]` (`classes`,`time_submitted`,`year`,`semester`,`user_id`) VALUES ('[classes]','[timestamp]','[year]','[semester]','[user_id]')", $a_queryvars);
			if (mysql_affected_rows() > 0)
					return "success|inserted classes";
			else
					return "failed|insert failed";
	}
}

function load_classes($s_year, $s_semester) {
	global $maindb;
	global $global_user;
	$user_id = $global_user->get_id();
	
	$a_queryvars = array("tablename"=>"semester_classes", "year"=>$s_year, "semester"=>$s_semester, "user_id"=>$user_id, "maindb"=>$maindb);
	$s_querystring = "SELECT `classes` FROM `[maindb]`.`[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `user_id`='[user_id]'";
	$a_classes = db_query($s_querystring, $a_queryvars);
	if ($a_classes == FALSE)
			return;
	return $a_classes[0]['classes'];
}

function load_user_classes($s_year, $s_semester) {
	global $global_user;
	
	//todo
}

function load_semester_classes($s_year, $s_semester) {
	//todo
}

function update_settings($setting_type, $a_postvars) {
	global $global_user;

	if ($global_user->check_is_guest())
			return 'failed|guest can\'t change settings';

	$a_settings = array();
	foreach($a_postvars as $k=>$v)
			if (strpos($k, 'setting_') === 0)
					$a_settings[substr($k,strlen('setting_'))] = $v;
	return $global_user->update_settings($setting_type, $a_settings);
}

if ($s_command == "save classes") {
		echo save_classes($s_classes, $s_year, $s_semester, $s_timestamp);
} else if ($s_command == "load classes") {
		echo load_classes($s_year, $s_semester);
} else if ($s_command == "update server settings") {
		echo update_settings("server", $_POST);
} else if ($s_command == "load semester classes") {
		echo load_semester_classes($s_year, $s_semester);
} else if ($s_command == "load user classes") {
		echo load_user_classes($s_year, $s_semester);
} else {
		echo "failed|bad command";
}
?>