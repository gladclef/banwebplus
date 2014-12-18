<?php

require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/load_semester_classes_from_database.php");
require_once(dirname(__FILE__)."/../objects/user.php");

class sharing {
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

	function share_user_schedule() {
		global $global_user;
		$username = get_post_var("username");
		if ($username === $global_user->get_name()) {
			return json_encode(array(
				new command("print failure", "Why are you trying to share a schedule with yourself?")));
		}
		$id = user::get_id_by_username($username);
		if ($id == -1) {
			return json_encode(array(
				new command("print failure", "The user \"{$username}\" can't be found.")));
		}
		$a_ids = $global_user->get_schedule_shared_users();
		if (!in_array($id, $a_ids)) {
				$a_ids[] = $id;
				$global_user->set_schedule_shared_users($a_ids);
			return json_encode(array(
				new command("share with user", "{$username}")));
		}
		return json_encode(array(
			new command("print failure", "Already shared schedule with \"{$username}\".")));
	}

	function unshare_user_schedule() {
		global $global_user;
		$username = get_post_var("username");
		$id = user::get_id_by_username($username);
		if ($id == -1) {
			return json_encode(array(
				new command("success", "")));
		}
		$a_ids = $global_user->get_schedule_shared_users();
		$index = array_search($id, $a_ids);
		if ($index !== FALSE) {
				unset($a_ids[$index]);
				$global_user->set_schedule_shared_users($a_ids);
		}
		return json_encode(array(
			new command("success", "")));
	}

	function load_shared_user_schedules() {
		global $global_user;
		global $maindb;

		// get some values
		$s_year = get_post_var("year");
		$s_semester = get_post_var("semester");

		// get the set of usernames and ids
		$a_queryvars = array("database"=>$maindb, "table"=>"students", "disabled"=>"0");
		$s_querystring = "SELECT `username`,`id` FROM `[database]`.`[table]` WHERE `disabled`='[disabled]'";
		$user_data = db_query($s_querystring, $a_queryvars);

		// get the users that shared their schedules with this user
		$a_queryvars = array("database"=>$maindb, "table"=>"user_settings", "id"=>$global_user->get_id());
		$s_querystring = "SELECT GROUP_CONCAT(`user_id`,',') AS `ids` FROM `[database]`.`[table]` WHERE `share_schedule_with` LIKE '%|[id]|%'";
		$can_view_db = db_query($s_querystring, $a_queryvars);
		$can_view_db = explode(",", $can_view_db[0]["ids"]);
		$can_view = array();
		for($i = 0; $i < count($can_view_db); $i++) {
				$can_view_db[$i] = intval($can_view_db[$i]);
		}

		// get the users that can view this user's schedule
		$shared_with_db = $global_user->get_schedule_shared_users();
		$shared_with = array();

		// translate user ids to usernames
		for($i = 0; $i < count($user_data); $i++) {
				$id = intval($user_data[$i]["id"]);
				$username = $user_data[$i]["username"];
				if (in_array($id, $can_view_db)) {
						$user = user::load_user_by_id($id, FALSE);
						if ($user !== null) {
								$can_view[] = $user;
						}
				}
				if (in_array($id, $shared_with_db)) {
						$shared_with[] = $username;
				}
		}
		
		// build the return value
		$retval = new stdClass();
		$retval->sharedUsers = new stdClass();
		$retval->otherUserSchedules = array();
		for($i = 0; $i < count($shared_with); $i++) {
				$retval->sharedUsers->$shared_with[$i] = TRUE;
		}
		for($i = 0; $i < count($can_view); $i++) {
				$retval->otherUserSchedules[$i] = new stdClass();
				$retval->otherUserSchedules[$i]->username = $can_view[$i]->get_name();
				$a_classes = $can_view[$i]->get_user_classes($s_year, $s_semester);
				$a_crns = array();
				for($j = 0; $j < count($a_classes); $j++) {
						$a_crns[] = $a_classes[$j]->crn;
				}
				$retval->otherUserSchedules[$i]->schedule = $a_crns;
		}
		
		// return the return value
		return json_encode(array(
			new command("success", $retval)));
	}
}