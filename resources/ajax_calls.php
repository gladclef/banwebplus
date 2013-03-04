<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/check_logged_in.php");
require_once(dirname(__FILE__)."/db_query.php");

// only functions within this class can be called by ajax
class ajax {
	function save_classes($s_classes, $s_year, $s_semester, $s_timestamp) {
		global $maindb;
		global $global_user;
		$s_classes = get_post_var('classes', $s_classes);
		$s_year = get_post_var('year', $s_year);
		$s_semester = get_post_var('semester', $s_semester);
		$s_timestamp = get_post_var('timestamp', $s_timestamp);
		
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
		$s_year = get_post_var('year', $s_year);
		$s_semester = get_post_var('semester', $s_semester);
	
		$a_queryvars = array("tablename"=>"semester_classes", "year"=>$s_year, "semester"=>$s_semester, "user_id"=>$user_id, "maindb"=>$maindb);
		$s_querystring = "SELECT `classes` FROM `[maindb]`.`[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `user_id`='[user_id]'";
		$a_classes = db_query($s_querystring, $a_queryvars);
		if ($a_classes == FALSE)
				return;
		return $a_classes[0]['classes'];
	}

	function load_user_classes($s_year, $s_semester) {
		global $global_user;
		$s_year = get_post_var('year', $s_year);
		$s_semester = get_post_var('semester', $s_semester);
	
		//todo
	}

	function load_semester_classes($s_year, $s_semester) {
		$s_year = get_post_var('year', $s_year);
		$s_semester = get_post_var('semester', $s_semester);

		$s_filename = "sem_".$s_year.$s_semester.".php";
		$s_dirname = dirname(__FILE__).'/../scraping/';
		$s_fullname = $s_dirname.$s_filename;
		if (!file_exists($s_fullname))
				return 'file doesn\'t exists';
	
		require($s_fullname);
		$o_semesterData = new semesterData();
		$s_semester = json_encode($o_semesterData);
		return $s_semester;
	}

	function update_settings($setting_type) {
		global $global_user;
		$setting_type = get_post_var('s_setting_type', $setting_type);
		$a_postvars = $_POST;

		if (!in_array($setting_type, array('server')))
				return 'failed|invalid setting type';
		if ($global_user->check_is_guest())
				return 'failed|guest can\'t change settings';

		$a_settings = array();
		foreach($a_postvars as $k=>$v)
				if (strpos($k, 'setting_') === 0)
						$a_settings[substr($k,strlen('setting_'))] = $v;
		return $global_user->update_settings($setting_type, $a_settings);
	}
}

$s_command = get_post_var("command");

if ($s_command != '') {
		$o_ajax = new ajax();
		if (method_exists($o_ajax, $s_command)) {
				echo $o_ajax->$s_command('','','','');
		} else {
				echo 'failed|bad command';
		}
}

?>