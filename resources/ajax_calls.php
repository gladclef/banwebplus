<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/check_logged_in.php");
require_once(dirname(__FILE__)."/db_query.php");
require_once(dirname(__FILE__)."/../tabs/Feedback.php");

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

		if ($global_user->check_is_guest())
				return 'failed|guest can\'t save classes';

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
				return 'file "'.$s_fullname.'" doesn\'t exists';
	
		require($s_fullname);
		if (class_exists("semesterData"))
				$s_semester = semesterData::to_json();
		else
				$s_semester = $s_classes_json;
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
	
	/**
	 * Gets or sets the default semester, which is loaded first every time the user logs in.
	 * If the first part of the semester doesn't match the latest semester, return the latest semester.
	 * @default_semester string  The semester to set as the default or NULL
	 * @b_load           boolean If TRUE returns the saved value, FALSE set the value
	 * @return           string  The default semester to load
	 */
	function default_semester($default_semester = NULL, $b_load = FALSE) {
		
		global $global_user;

		// get some values
		$a_semester_list = json_decode($this->list_available_semesters());
		$s_latest_semester = $a_semester_list[count($a_semester_list)-1][0];
		
		// check that a value was passed
		$s_semester = get_post_var('default_semester', $default_semester);
		if ($b_load) {
			$s_setting = $global_user->get_server_setting('default_semester');
			$a_setting = explode("|", $s_setting);
			if ((string)$a_setting[0] != $s_latest_semester)
				return $s_latest_semester;
			return $a_setting[1];
		}
		if ($default_semester === NULL)
			$default_semester = "{$s_latest_semester}|{$s_latest_semester}";
		
		// set the default semester setting
		$global_user->update_settings("server", array('default_semester'=>"{$s_latest_semester}|{$s_semester}"));
		
		return "set";
	}
	function get_default_semester() {
		return $this->default_semester(NULL, TRUE);
	}

	/**
	 * Get all of the users in the `students` table as a json string
	 */
	function get_full_users_list() {
		global $maindb;
		$a_query_results = db_query("SELECT `username`,`email` FROM `[maindb]`.`students`", array("maindb"=>$maindb));
		if (count($a_query_results) == 0 || $a_query_results === FALSE)
				return json_encode((object)array('success'=>FALSE, 'details'=>'MySQL query failed'));
		return json_encode((object)array('success'=>TRUE, 'details'=>$a_query_results));
	}

	function email_developer_bugs() {
		global $global_user;
		$s_subject = get_post_var("email_subject");
		$s_body = get_post_var("email_body");
		if ($s_subject == "")
				return "print error[*note*]Please include a subject in your email.<br />";
		if ($s_body == "")
				return "print error[*note*]Please include a body in your email.<br />";
		mail("bbean@cs.nmt.edu", "Banwebplus Feedback: {$s_subject}", $s_body, "From: ".$global_user->get_email());
		return "print success[*note*]Thank you for your feedback!<br />";
	}

	function edit_feedback() {
		$s_feedback_id = get_post_var("feedback_id");
		$s_new_query_string = get_post_var("feedback_text");
		return feedbackTab::handelEditFeedbackAJAX($s_feedback_id, $s_new_query_string);
	}

	function create_feedback() {
		return feedbackTab::handelCreateFeedbackAJAX();
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