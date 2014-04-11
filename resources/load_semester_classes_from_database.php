<?php

require_once(dirname(__FILE__)."/globals.php");
require_once(dirname(__FILE__)."/db_query.php");
require_once(dirname(__FILE__)."/conversions.php");

function count_semester_classes_in_database($s_year, $s_semester) {

	// test load
	return load_semester_classes_from_database($s_year, $s_semester, "json", TRUE);
}

function load_semester_classes_from_database($s_year, $s_semester, $s_output_type = "json", $b_just_count = FALSE) {
	
	// get some common variables
	global $maindb;
	global $global_user;
	$a_subjects = array();
	$a_classes = array();
	$s_name = "";
	$id = $global_user->get_id();

	// translate school time to real time
	$s_semester = (string)$s_semester;
	$a_semester = school_time_to_real_time($s_semester, $s_year);
	$s_load_year = $a_semester["year"];
	$s_load_semester = $a_semester["semester"];
	$s_name = $a_semester["name"];

	// load the subjects
	if (!$b_just_count) {
			$a_subjects_db = db_query("SELECT `abbr`,`title` FROM `{$maindb}`.`subjects` WHERE `semester`='[semester]' AND `year`='[year]' ORDER BY `title`", array("semester"=>$s_load_semester, "year"=>$s_load_year));
			if ($a_subjects_db === FALSE || count($a_subjects_db) == 0) {
					return "Failed to load the subjects for the semester, given semester ({$s_year}, {$s_semester}) possibly out of range.";
			}
			for($i = 0; $i < count($a_subjects_db); $i++) {
					$a_subjects[$a_subjects_db[$i]["abbr"]] = $a_subjects_db[$i]["title"];
			}
	}
	
	// build the query to load the classes
	$access_to_custom_class = "`subject`!='CUSTOM' OR `user_ids_with_access` LIKE '%|{$id},%'";
	$s_select_clause = "`subject`,`enroll` AS `Enroll`,`title` AS `Title`,`days` AS `Days`,`hours` AS `Hrs`,`limit` AS `Limit`,`location` AS `Location`,`time` AS `Time`,`parent_class`,`crn` AS `CRN`,`course` AS `Course`,`campus` AS `*Campus`,`seats` AS `Seats`,`instructor` AS `Instructor`,`user_ids_with_access` AS `accesses`";
	if ($b_just_count) {
			$s_select_clause = "COUNT(*) AS `count`";
	}

	// load the classes
	$a_classes_db = db_query("SELECT {$s_select_clause} FROM `{$maindb}`.`classes` WHERE `semester`='[semester]' AND `year`='[year]' AND ({$access_to_custom_class}) ORDER BY `subject`,`course`", array("semester"=>$s_load_semester, "year"=>$s_load_year), 2);
	if ($a_classes_db === FALSE || count($a_classes_db) == 0) {
			return "Failed to load the classes for the semester, given semester ({$s_year}, {$s_semester}) possibly out of range.";
	}

	// is this just a test count?
	if ($b_just_count) {
			return (int)$a_classes_db[0]["count"];
	}
	
	$a_subclasses = array();
	foreach($a_classes_db as $k=>$a_class) {
			if ($a_class["CRN"] == 0) {
					$a_classes_db[$k]["CRN"] = ((string)$a_class["parent_class"])."A";
					$a_subclasses[] = $a_classes_db[$k];
					unset($a_classes_db[$k]);
			} else {
					$a_classes_db[$k]["CRN"] = (string)$a_class["CRN"];
			}
			if ($a_class["accesses"] != "") {
					$s_access = $a_class["accesses"];
					$s_access = substr($s_access, 0, strpos($s_access, "|{$id},"));
					$s_access = substr($s_access, max(0, (int)strrpos($s_access, ",")));
					$a_classes_db[$k]["accesses"] = ltrim($s_access, ",");
			}
	}
	foreach($a_classes_db as $a_class) {
			$a_classes[] = $a_class;
			$crn = $a_class["CRN"];
			$subcrn = $crn+"A";
			foreach($a_subclasses as $k=>$a_subclass) {
					if ($a_subclass["CRN"] == $subcrn) {
							$a_classes[] = $a_subclass;
							unset($a_subclass[$k]);
					}
			}
	}
	
	$a_retval = array('name'=>$s_name, 'subjects'=>$a_subjects, 'classes'=>$a_classes);
	if ($s_output_type == "json") {
			$s_semester_data = json_encode($a_retval);
			return $s_semester_data;
	} else if ($s_output_type == "array") {
			return $a_retval;
	}
}

function save_custom_class_to_db($a_values, $i_user_id, $sem, $year) {
	
	global $maindb;
	
	// index the array
	foreach($a_values as $k=>$v) {
			unset($a_values[$k]);
			$a_values[$v->name] = $v->value;
	}

	// standardize the inputs
	$semester_string = number_to_season($sem);
	$semester_string = strtolower(substr($semester_string,0,3));
	$realyear = school_to_real_year($year, $sem);
	$a_matches = array();
	$a_values["Days"] = strtoupper($a_values["Days"]);
	$a_values["Days"] = preg_replace("/[^UMTWRFS]+/", "", $a_values["Days"]);
	preg_match('/U?M?T?W?R?F?S?/', $a_values["Days"], $a_matches);
	$a_values["Days"] = (count($a_matches) > 0) ? $a_matches[0] : "";
	$a_values["Time"] = preg_replace("/[^\d-]+/", "", $a_values["Time"]);
	preg_match('/\d\d\d\d-\d\d\d\d/', $a_values["Time"], $a_matches);
	$a_values["Time"] = (count($a_matches) > 0) ? $a_matches[0] : "";
	foreach(array("Hrs", "Limit") as $cat) {
			$a_values[$cat] = preg_replace("/[^\d]+/", "", $a_values[$cat]);
	}
	
	// check that none of the fields are blank
	foreach($a_values as $k=>$v) {
			if ($v == "") {
					return "Failure: bad value for {$k}";
			}
	}

	// get the next crn for custom classes
	$i_crn = 1;
	$a_custom_classes = db_query("SELECT `crn` FROM `{$maindb}`.`classes` WHERE `semester`='[sem]' AND `year`='[year]' AND `subject`='CUSTOM' ORDER BY `crn` DESC LIMIT 1", array("sem"=>$semester_string, "year"=>$realyear));
	if (count($a_custom_classes) > 0) {
			$i_crn = (int)$a_custom_classes[0]["crn"];
			$i_crn++;
			
			// check that it doesn't conflict with other types of classes
			$query_string = "SELECT `crn` FROM `{$maindb}`.`classes WHERE `semester`='[sem]' AND `year`='[year]' AND `crn`='[crn]'";
			$query_vars = array("sem"=>$semester_string, "year"=>$realyear, "crn"=>$i_crn);
			$a_class = db_query($query_string, $query_vars);
			while ($a_class !== FALSE && count($a_class) > 0) {
					$i_crn++;
					$query_vars["crn"] = $i_crn;
					$a_class = db_query($query_string, $query_vars);
			}
	}

	// find some specific information
	$a_days = str_split($a_values["Days"]);
	$a_days_times_locations = array();
	foreach($a_days as $s_day) {
			$a_days_times_locations[] = array($s_day, $a_values["Time"], $a_values["Location"]);
	}
	$s_days_times_locations = json_encode($a_days_times_locations);
	$a_dates = getStartEndDays($semester_string, $realyear);
	
	// build the class
	$a_class = array(
		"crn"=>$i_crn,
		"year"=>$realyear,
		"semester"=>$semester_string,
		"subject"=>"CUSTOM",
		"course"=>"CUSTOM {$i_crn}",
		"campus"=>$a_values["*Campus"],
		"days"=>$a_values["Days"],
		"days_times_locations"=>$s_days_times_locations,
		"start_date"=>$a_dates["start"],
		"end_date"=>$a_dates["end"],
		"time"=>$a_values["Time"],
		"location"=>$a_values["Location"],
		"hours"=>$a_values["Hrs"],
		"title"=>$a_values["Title"],
		"instructor"=>$a_values["Instructor"],
		"seats"=>0,
		"limit"=>$a_values["Limit"],
		"enroll"=>0,
		"parent_class"=>"",
		"subclass_identifier"=>"",
		"user_ids_with_access"=>"rwx|{$i_user_id},",
		"last_mod_time"=>date("Y-m-d H:i:s"),
	);
	
	// insert into the database
	$s_insert_clause = array_to_insert_clause($a_class);
	$query = db_query("INSERT INTO `{$maindb}`.`classes` {$s_insert_clause}", $a_class);
	if ($query !== FALSE) {
			return "success";
	}
	return "failure";
}

/**
 * checks if the user has a specific kind of access to the given course
 * @$o_user: a user object
 * @$s_access: any combination of "w", "r", and "x" ("x" is for sharing)
 * @return: TRUE or FALSE
 */
function user_has_custom_access($o_user, $s_access, $crn, $year, $semester) {
	global $maindb;
	$id = $o_user->get_id();

	// get the row
	$a_query_vars = array("year"=>$year, "crn"=>$crn, "semester"=>$semester, "subject"=>"CUSTOM");
	$s_where_clause = array_to_where_clause($a_query_vars);
	$a_query = db_query("SELECT `user_ids_with_access` AS `accesses` FROM `{$maindb}`.`classes` WHERE {$s_where_clause} LIMIT 1", $a_query_vars);
	if ($a_query === FALSE || count($a_query) == 0) {
			return FALSE;
	}
	$caccess = $a_query[0]["accesses"];
	if (strpos($caccess, "|{$id},") === FALSE) {
			return FALSE;
	}
	$caccess = substr($caccess, 0, max(0,(int)strpos($caccess,"|{$id},")));
	$caccess = substr($caccess, (int)strrpos($caccess, ","));

	// check the accesses
	$a_accesses = str_split($s_access);
	for($i = 0; $i < count($a_accesses); $i++) {
			if (strpos($caccess, $a_accesses[$i]) === FALSE) {
					return FALSE;
			}
	}
	return TRUE;
}

function edit_custom_course($sem, $year, $crn, $attribute, $value) {
	
	global $global_user;
	global $maindb;

	// get the real semester/year
	$semester = get_real_semester($sem, $year);
	$year = get_real_year($sem, $year);
	$crn = (int)$crn;
	
	// check that the user has the proper accesses
	if (!user_has_custom_access($global_user, "w", $crn, $year, $semester)) {
			return "Can't update: you don't have permission to update this custom class.";
	}

	// normalize the attribute
	$attribute = strtolower($attribute);
	switch($attribute) {
	case "*campus":
			$attribute = "campus";
			break;
	}

	// build the query
	$a_where_vars = array("semester"=>$semester, "year"=>$year, "crn"=>$crn);
	$s_where_clause = array_to_where_clause($a_where_vars);
	
	// check that the attribute is valid
	$a_query = db_query("SELECT `[attr]` FROM `{$maindb}`.`classes` WHERE {$s_where_clause}", array_merge(array("attr"=>$attribute), $a_where_vars));
	if ($a_query === FALSE || count($a_query) == 0) {
			return "Can't update: bad attribute name \"{$attribute}.\"";
	}
	// and get a safe name for the attribute
	$a_attr = $a_query[0];
	foreach($a_attr as $k=>$v) {
			$s_attr = $k;
	}

	// update the class
	$a_update_vars = array($s_attr=>$value);
	$s_update_clause = array_to_update_clause($a_update_vars);
	$a_query = db_query("UPDATE `{$maindb}`.`classes` SET {$s_update_clause} WHERE {$s_where_clause}", array_merge($a_update_vars, $a_where_vars));
	if ($a_query === FALSE) {
			return "Failed to update database.";
	}
	if (mysql_affected_rows() == 0) {
			return "success";
	}
	return "success";
}

function remove_custom_course_access($sem, $year, $crn) {
	
	global $global_user;
	global $maindb;

	// get the real semester/year
	$id = $global_user->get_id();
	$semester = get_real_semester($sem, $year);
	$year = get_real_year($sem, $year);
	$crn = (int)$crn;

	// get the accesses of the course
	$a_where_vars = array("subject"=>"CUSTOM", "semester"=>$semester, "year"=>$year, "crn"=>$crn);
	$s_where_clause = array_to_where_clause($a_where_vars);
	$a_query = db_query("SELECT `user_ids_with_access` AS `accesses` FROM `{$maindb}`.`classes` WHERE {$s_where_clause}", $a_where_vars);
	if ($a_query === FALSE || count($a_query) == 0) {
			return "Error: can't find that class.";
	}
	$s_accesses = $a_query[0]["accesses"];
	$i_pos = strpos($s_accesses, "|{$id},");
	if ($i_pos === FALSE) {
			return "success";
	}

	// parse out this user
	$s_user = substr($s_accesses, 0, $i_pos+strlen("|{$id}"));
	$s_user = substr($s_user, (int)strrpos($s_user, ","));
	$s_user = ltrim($s_user,",").",";
	$s_accesses = str_replace($s_user, "", $s_accesses);
	
	// update the accesses
	$a_update_vars = array("user_ids_with_access"=>$s_accesses);
	$s_update_clause = array_to_update_clause($a_update_vars);
	$a_query = db_query("UPDATE `{$maindb}`.`classes` SET {$s_update_clause} WHERE {$s_where_clause}", array_merge($a_update_vars, $a_where_vars));
	if ($a_query === FALSE) {
			return "Failed to update database.";
	}
	return "success";
}

function get_user_accesses($crn, $semester, $year) {
	global $maindb;

	$crn = (int)$crn;
	$a_where_vars = array("subject"=>"CUSTOM", "crn"=>$crn, "semester"=>$semester, "year"=>$year);
	$s_where_clause = array_to_where_clause($a_where_vars);
	$a_query = db_query("SELECT `user_ids_with_access` AS `accesses` FROM `{$maindb}`.`classes` WHERE {$s_where_clause}", $a_where_vars);
	if ($a_query === FALSE || count($a_query) == 0) {
			return NULL;
	}

	$a_user_accesses = explode(",", rtrim($a_query[0]["accesses"], ","));
	$a_new_user_accesses = array();
	for($i = 0; $i < count($a_user_accesses); $i++) {
			$s_access = $a_user_accesses[$i];
			$s_id = substr($s_access, strrpos($s_access, "|")+1);
			$a_new_user_accesses[(int)$s_id] = $s_access;
	}
	unset($a_user_accesses);
	return $a_new_user_accesses;
}

function share_custom_class($sem, $year, $crn, $accesses, $username) {
	
	// get some common values
	global $global_user;
	global $maindb;
	$semester = get_real_semester($sem, $year);
	$year = get_real_year($sem, $year);
	$accesses = "r{$accesses}";
	
	// check for permissions
	if (!user_has_custom_access($global_user, $accesses, $crn, $year, $semester)) {
			return "Error: you don't have permission to share this class like that.";
	}
	
	// check that the class and user exist
	$a_query = db_query("SELECT `id` FROM `{$maindb}`.`students` WHERE `username`='[username]' AND `disabled`='0'", array("username"=>$username));
	if ($a_query === FALSE || count($a_query) == 0) {
			return "Error: can't find that banwebplus username to share with.";
	}
	$i_user_id = (int)$a_query[0]['id'];
	$a_user_accesses = get_user_accesses($crn, $semester, $year);
	if ($a_user_accesses == NULL) {
			return "Error: can't find that class to share.";
	}

	// compute the new user accesses
	$s_access_to_assign = $accesses;
	// if the assignee already has access and the access is being modified
	if (isset($a_user_accesses[$i_user_id])) {
			// the current user doesn't have write access
			if (strpos($a_user_accesses[(int)$global_user->get_id()], "w") === FALSE) {
					// the assignee does have write access
					if (strpos($a_user_accesses[$i_user_id],"w") !== FALSE) {
							// trying to grant share access
							if (strpos($accesses, "x") !== FALSE) {
									$s_access_to_assign = "rwx";
							} else {
									$s_access_to_assign = "rw";
							}
					}
			}
	}
	$s_access_to_assign = "{$s_access_to_assign}|{$i_user_id}";
	$a_user_accesses[$i_user_id] = $s_access_to_assign;
	$s_all_accesses = implode(",", $a_user_accesses);
	$s_all_accesses .= ",";

	// share the class
	$a_where_vars = array("subject"=>"CUSTOM", "crn"=>$crn, "semester"=>$semester, "year"=>$year);
	$s_where_clause = array_to_where_clause($a_where_vars);
	$a_update_vars = array("user_ids_with_access"=>$s_all_accesses);
	$s_update_clause = array_to_update_clause($a_update_vars);
	$a_query = db_query("UPDATE `{$maindb}`.`classes` SET {$s_update_clause} WHERE {$s_where_clause}", array_merge($a_update_vars, $a_where_vars));
	if ($a_query == FALSE) {
			return "Failed to update database.";
	}
	return "success";
}

?>