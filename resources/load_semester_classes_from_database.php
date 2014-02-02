<?php

require_once(dirname(__FILE__)."/globals.php");
require_once(dirname(__FILE__)."/db_query.php");

function load_semester_classes_from_database($s_year, $s_semester, $s_output_type = "json") {
	
	// get some common variables
	global $maindb;
	$a_subjects = array();
	$a_classes = array();
	$s_name = "";

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
	$a_subjects_db = db_query("SELECT `abbr`,`title` FROM `{$maindb}`.`subjects` WHERE `semester`='[semester]' AND `year`='[year]' ORDER BY `abbr`", array("semester"=>$s_load_semester, "year"=>$s_load_year));
	if ($a_subjects_db === FALSE || count($a_subjects_db) == 0) {
			return "Failed to load the subjects for the semester, given semester ({$s_year}, {$s_semester}) possibly out of range.";
	}
	for($i = 0; $i < count($a_subjects_db); $i++) {
			$a_subjects[$a_subjects_db[$i]["abbr"]] = $a_subjects_db[$i]["title"];
	}
	
	// load the classes
	$a_classes_db = db_query("SELECT `subject`,`enroll` AS `Enroll`,`title` AS `Title`,`days` AS `Days`,`hours` AS `Hrs`,`limit` AS `Limit`,`location` AS `Location`,`time` AS `Time`,`parent_class`,`crn` AS `CRN`,`course` AS `Course`,`campus` AS `*Campus`,`seats` AS `Seats`,`instructor` AS `Instructor` FROM `{$maindb}`.`classes` WHERE `semester`='[semester]' AND `year`='[year]' ORDER BY `subject`,`course`", array("semester"=>$s_load_semester, "year"=>$s_load_year));
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

?>