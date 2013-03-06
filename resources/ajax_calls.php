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
				db_query("UPDATE `[maindb]`.`[tablename]` SET `json`='[classes]',`time_submitted`='[timestamp]' WHERE `id`='[id]'", $a_queryvars);
				if (mysql_affected_rows() > 0)
						return "success|updated classes";
				else
						return "failed|update failed";
		} else {
				db_query("INSERT INTO `[maindb]`.`[tablename]` (`json`,`time_submitted`,`year`,`semester`,`user_id`) VALUES ('[classes]','[timestamp]','[year]','[semester]','[user_id]')", $a_queryvars);
				if (mysql_affected_rows() > 0)
						return "success|inserted classes";
				else
						return "failed|insert failed";
		}
	}

	function load_classes($s_year, $s_semester) {
		global $maindb;
		global $global_user;
		$s_year = get_post_var('year', $s_year);
		$s_semester = get_post_var('semester', $s_semester);

		return json_encode($global_user->get_user_classes($s_year, $s_semester));
	}

	function list_available_semesters() {
		require(dirname(__FILE__).'/../scraping/banweb_terms.php');
		return json_encode($terms);
	}

	// returns array('user_classes'=>stuff, 'user_whitelist'=>stuff, 'user_blacklist'=>stuff) as JSON
	function load_user_classes() {
		$s_year = get_post_var('year');
		$s_semester = get_post_var('semester');
		global $global_user;

		$user_classes = $global_user->get_user_classes($s_year, $s_semester);
		$user_whitelist = $global_user->get_user_whitelist($s_year, $s_semester);
		$user_blacklist = $global_user->get_user_blacklist($s_year, $s_semester);
		if ($user_classes == '') $user_classes = array();
		if ($user_whitelist == '') $user_whitelist = array();
		if ($user_blacklist == '') $user_blacklist = array();
		$a_user_data = array('user_classes'=>$user_classes, 'user_whitelist'=>$user_whitelist, 'user_blacklist'=>$user_blacklist);
		return json_encode($a_user_data);
	}
	
	function save_user_data() {
		$s_year = get_post_var('year');
		$s_semester = get_post_var('semester');
		$s_json_saveval = get_post_var('json');
		$s_datatype = get_post_var('datatype');
		$s_timestamp = get_post_var('timestamp');
		$i_affected_rows = 0;
		global $global_user;
		
		if ($s_datatype == 'whitelist')
				$i_affected_rows = $global_user->save_user_whitelist($s_year, $s_semester, $s_json_saveval, $s_timestamp);
		else if ($s_datatype == 'blacklist')
				$i_affected_rows = $global_user->save_user_blacklist($s_year, $s_semester, $s_json_saveval, $s_timestamp);
		else
				return 'failure|bad datatype';
		
		if ($i_affected_rows > 0)
				return 'success|'.$i_affected_rows;
		else
				return 'failure|'.$i_affected_rows;
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
		$s_semester = semesterData::to_json();
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
} else {
		echo 'failed|no command';
}

?>