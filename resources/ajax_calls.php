<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/check_logged_in.php");
require_once(dirname(__FILE__)."/db_query.php");
require_once(dirname(__FILE__)."/../tabs/Feedback.php");
require_once(dirname(__FILE__)."/load_semester_classes_from_database.php");

// only functions within this class can be called by ajax
class ajax {
	
	// saves the user's class selection to the db
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

	// only lists semester that have classes in the database
	// @return: json_encoded array of classes {"yyyypp", "season year"}
	//     where "yyyy" is the school year, "pp" is one of {10,20,30}, and
	//     "season year" is the real season and real year
	function list_available_semesters() {
		
		// get the list of available semesters according to banweb
		require(dirname(__FILE__).'/../scraping/banweb_terms.php');
		
		// remove any semesters that don't have classes
		foreach($terms as $k=>$a_term) {
				$s_semester = substr($a_term[0], 4, 2);
				$s_year = substr($a_term[0], 0, 4);
				if (count_semester_classes_in_database($s_year, $s_semester) === 0) {
						unset($terms[$k]);
				}
		}
		
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
		return load_semester_classes_from_database($s_year, $s_semester);
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
	 * @$b_ignore_disabled boolean if true, checks the 'disabled' flag on the user's account
	 */
	function get_full_users_list($b_ignore_disabled = TRUE) {
		global $maindb;
		$b_ignore_disabled = (bool)get_post_var("ignore_disabled", $b_ignore_disabled);
		$s_disabled = ($b_ignore_disabled) ? "WHERE `disabled`='0'" : "";
		$a_query_results = db_query("SELECT `username`,`email`,`disabled` FROM `[maindb]`.`students` {$s_disabled}", array("maindb"=>$maindb));
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

	function edit_post() {
		$s_post_id = get_post_var("post_id");
		$s_new_query_string = get_post_var("post_text");
		$s_tablename = get_post_var("tablename");
		if ($s_tablename == "feedback") {
				global $o_feedback;
				return $o_feedback->handleEditPostAJAX($s_post_id, $s_new_query_string);
		} else if ($s_tablename == "buglog") {
				global $o_bugtracker;
				return $o_bugtracker->handleEditPostAJAX($s_post_id, $s_new_query_string);
		}
	}

	function create_post() {
		$s_tablename = get_post_var("tablename");
		$b_no_response = (get_post_var("noresponse") === "1") ? TRUE : FALSE;
		if ($s_tablename == "feedback") {
				global $o_feedback;
				$a_response = $o_feedback->handleCreatePostAJAX($b_no_response);
		} else if ($s_tablename == "buglog") {
				global $o_bugtracker;
				$a_response = $o_bugtracker->handleCreatePostAJAX($b_no_response);
		}
		return $a_response["response"];
	}

	function delete_post() {
		$s_post_id = get_post_var("post_id");
		$s_tablename = get_post_var("tablename");
		if ($s_tablename == "feedback") {
				global $o_feedback;
				return $o_feedback->handleDeletePostAJAX($s_post_id);
		} else if ($s_tablename == "buglog") {
				global $o_bugtracker;
				return $o_bugtracker->handleDeletePostAJAX($s_post_id);
		}
	}

	function respond_post() {
		$s_post_id = get_post_var("post_id");
		$s_tablename = get_post_var("tablename");
		if ($s_tablename == "feedback") {
				global $o_feedback;
				return $o_feedback->handleRespondPostAJAX($s_post_id);
		} else if ($s_tablename == "buglog") {
				global $o_bugtracker;
				return $o_bugtracker->handleRespondPostAJAX($s_post_id);
		}
	}

	function change_bug_owner() {
		$s_post_id = get_post_var("post_id");
		$s_tablename = get_post_var("tablename");
		$s_userid = get_post_var("userid");
		global $o_bugtracker;
		return $o_bugtracker->handleChangeBugOwnerAJAX($s_post_id, $s_userid);
	}

	function change_bug_status() {
		$s_post_id = get_post_var("post_id");
		$s_tablename = get_post_var("tablename");
		$s_status = get_post_var("status");
		global $o_bugtracker;
		return $o_bugtracker->handleChangeBugStatusAJAX($s_post_id, $s_status);
	}

	function change_password() {
		global $global_user;
		$s_username = get_post_var('username');
		$s_new_password = get_post_var('new_password');
		$success = $this->verify_password();
		if ($success == "success" && $s_username != "guest") {
				$success = ($global_user->update_password($s_new_password)) ? "success" : "failure";
				if ($success) {
						$global_user = new user($s_username, $s_new_password, "");
						login_session($global_user);
				}
		}
		return $success;
	}

	function verify_password() {
		$s_username = get_post_var('username');
		$s_password = get_post_var('password');
		$o_user = new user($s_username, $s_password, '');
		if ($o_user->exists_in_db()) {
				return "success";
		} else {
				return "failure";
		}
	}

	function disable_account() {
		global $global_user;
		$b_verified = $this->verify_password() == "success";
		$is_guest = $global_user->get_name() == "guest";
		if ($b_verified && !$is_guest && $global_user->disable_account()) {
				return "success";
		}
		return "failure";
	}

	function delete_account() {
		global $global_user;
		$b_verified = $this->verify_password() == "success";
		$is_guest = $global_user->get_name() == "guest";
		if ($b_verified && !$is_guest && $global_user->delete_account()) {
				return "success";
		}
		return "failure";
	}

	function add_custom_class() {
		global $global_user;
		$s_values = get_post_var("values");
		$sem = get_post_var("semester");
		$year = get_post_var("year");
		$a_values = json_decode($s_values);
		return save_custom_class_to_db($a_values, $global_user->get_id(), $sem, $year);
	}

	function edit_custom_course() {
		$sem = get_post_var("semester");
		$year = get_post_var("year");
		$crn = get_post_var("crn");
		$attribute = get_post_var("attribute");
		$value = get_post_var("value");
		return edit_custom_course($sem, $year, $crn, $attribute, $value);
	}

	function remove_custom_course_access() {
		$sem = get_post_var("semester");
		$year = get_post_var("year");
		$crn = get_post_var("crn");
		return remove_custom_course_access($sem, $year, $crn);
	}

	function share_custom_class() {
		$sem = get_post_var("semester");
		$year = get_post_var("year");
		$crn = get_post_var("crn");
		$w = (get_post_var("w", "") == "") ? "" : "w";
		$x = (get_post_var("x", "") == "") ? "" : "x";
		$username = get_post_var("username");
		return share_custom_class($sem, $year, $crn, "{$w}{$x}", $username);
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