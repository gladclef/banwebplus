<?php

require_once(dirname(__FILE__)."/globals.php");
require_once(dirname(__FILE__)."/db_query.php");
require_once(dirname(__FILE__)."/conversions.php");

function load_semester_classes_from_database($s_year, $s_semester, $s_output_type = "json") {
	
	// get some common variables
	global $maindb;
	global $global_user;
	$a_subjects = array();
	$a_classes = array();
	$s_name = "";
	$id = $global_user->get_id();

	// translate school time to real time
	$s_semester = (string)$s_semester;
	if ($s_semester == "30") {
			$s_load_year = (int)$s_year;
			$s_load_semester = "spr";
			$s_name = "Spring {$s_year}";
	} else if ($s_semester == "10") {
			$s_load_year = ((int)$s_year) - 1;
			$s_load_semester = "sum";
			$s_name = "Summer {$s_year}";
	} else if ($s_semester == "20") {
			$s_load_year = ((int)$s_year) - 1;
			$s_load_semester = "fal";
			$s_name = "Fall {$s_year}";
	}

	// load the subjects
	$a_subjects_db = db_query("SELECT `abbr`,`title` FROM `{$maindb}`.`subjects` WHERE `semester`='[semester]' AND `year`='[year]' ORDER BY `title`", array("semester"=>$s_load_semester, "year"=>$s_load_year));
	if ($a_subjects_db === FALSE || count($a_subjects_db) == 0) {
			return "Failed to load the subjects for the semester, given semester ({$s_year}, {$s_semester}) possibly out of range.";
	}
	for($i = 0; $i < count($a_subjects_db); $i++) {
			$a_subjects[$a_subjects_db[$i]["abbr"]] = $a_subjects_db[$i]["title"];
	}
	
	// load the classes
	$user_id_selector = "`user_ids_with_access`='' OR `user_ids_with_access` LIKE '%{$id}|'";
	$a_classes_db = db_query("SELECT `subject`,`enroll` AS `Enroll`,`title` AS `Title`,`days` AS `Days`,`hours` AS `Hrs`,`limit` AS `Limit`,`location` AS `Location`,`time` AS `Time`,`parent_class`,`crn` AS `CRN`,`course` AS `Course`,`campus` AS `*Campus`,`seats` AS `Seats`,`instructor` AS `Instructor` FROM `{$maindb}`.`classes` WHERE `semester`='[semester]' AND `year`='[year]' AND ($user_id_selector) ORDER BY `subject`,`course`", array("semester"=>$s_load_semester, "year"=>$s_load_year));
	if ($a_classes_db === FALSE || count($a_classes_db) == 0) {
			return "Failed to load the classes for the semester, given semester ({$s_year}, {$s_semester}) possibly out of range.";
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
		"user_ids_with_access"=>"{$i_user_id}|",
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

?>