<?php

require_once(dirname(__FILE__)."/../resources/globals.php");
require_once(dirname(__FILE__)."/../resources/db_query.php");

/**
 * Gets the next term to load from the php files
 * @return: a term array, or NULL if there are no more terms
 */
function getNextTerm() {
	static $terms;
	if (!$terms) {
			require_once(dirname(__FILE__)."/banweb_terms.php");
			if (!$terms) {
					$terms = array();
			}
	}
	if (count($terms) > 0) {
			$retval = $terms[0];
			$newterms = array();
			for ($i = 1; $i < count($terms); $i++) {
					$newterms[] = $terms[$i];
			}
			$terms = $newterms;
	} else {
			$retval = NULL;
	}
	return $retval;
}

/**
 * Loads the term from the sem_[term].php file
 * @$term: should be in the form array("201430", "Spring 2015")
 * @return: array("name"=>string, "term"=>string, "year"=>int, "semester"=>string, "subjects"=>array, "classes"=>array)
 *          or NULL on failure
 */
function loadTerm($term) {
	$filename = dirname(__FILE__)."/sem_".$term[0].".php";
	if (!file_exists($filename)) {
			return NULL;
	}
	require_once($filename);
	$a_semester = explode(" ",$semesterData["name"]);
	$semesterData["term"] = $term[0];
	$semesterData["year"] = (int)$a_semester[1];
	$semesterData["semester"] = strtolower(substr($a_semester[0], 0, 3));
	if ($semesterData["semester"] != "spr") {
			$semesterData["year"] = $semesterData["year"]-1;
			if ($semesterData["semester"] == "sum") {
					$semesterData["name"] = "Summer ".$semesterData["year"];
			} else {
					$semesterData["name"] = "Fall ".$semesterData["year"];
			}
	}
	return $semesterData;
}

function saveData($s_semester, $s_year, $a_data_to_save, $a_keys, $s_primary_key, $s_table, $exclude_comparison_columns = NULL, $a_searchby = NULL) {
	
	global $maindb;

	// compiles the keys
	$s_keylist = "`".implode("`,`",$a_keys)."`";
	$a_exclude_comparison_columns = array();
	if ($exclude_comparison_columns !== NULL && count($exclude_comparison_columns) > 0) {
			foreach($exclude_comparison_columns as $k=>$v) {
					$a_exclude_comparison_columns[$v] = 0;
			}
	}
	
	// load existing data from the database
	// loads them each as an "primary_key"=>array("key"=>value, ...)
	$a_searchby = ($a_searchby === NULL) ? array() : $a_searchby;
	$a_searchby = array_merge(array("semester"=>$s_semester, "year"=>$s_year), $a_searchby);
	if ($s_table == "classes") {
			$a_searchby = array_merge(array("user_ids_with_access"=>""), $a_searchby);
	}
	$s_where_clause = array_to_where_clause($a_searchby);
	$db_data_loaded = db_query("SELECT {$s_keylist} FROM `{$maindb}`.`{$s_table}` WHERE {$s_where_clause} ORDER BY `{$s_primary_key}`", $a_searchby);
	$s_where_clause = ($s_where_clause == "") ? "" : "AND {$s_where_clause}";
	$db_data = array();
	foreach($db_data_loaded as $db_row) {
			$db_data[$db_row[$s_primary_key]] = $db_row;
	}
	
	// determine what data has not already been saved,
	// and which should be removed
	$data_to_add = array();
	$data_to_remove = array();
	$data_to_change = array();
	foreach ($a_data_to_save as $k=>$a_row) {
			$primary_value = $a_row[$s_primary_key];
			
			// decided if it should be changed or inserted
			$row_exists = FALSE;
			if (isset($db_data[$primary_value])) {
					$row_exists = TRUE;
					
					// build the comparison for updating
					if (count($a_exclude_comparison_columns) == 0) {
							$s_db_row = implode(",", $db_data[$primary_value]);
							$s_tosave_row = implode(",", $a_row);
					} else {
							$a_row1 = array_diff_key($db_data[$primary_value], $a_exclude_comparison_columns);
							$a_row2 = array_diff_key($a_row, $a_exclude_comparison_columns);
							$s_db_row = implode(",", $a_row1);
							$s_tosave_row = implode(",", $a_row2);
					}
					
					// compare for updates
					if ($s_db_row != $s_tosave_row) {
							$data_to_change[$primary_value] = $a_row;
					}
			}
			if (!$row_exists) {
					
					// should be added
					$data_to_add[$primary_value] = $a_row;
			} else {
					unset($db_data[$primary_value]);
			}
			unset($a_data_to_save[$primary_value]);
	}
	foreach($db_data as $primary_value=>$a_db_row) {
			
			// delete everything else
			$data_to_remove[$primary_value] = $primary_value;
			unset($db_data[$primary_value]);
	}

	echo "update: ".count($data_to_change)."\ndelete: ".count($data_to_remove)."\ninsert: ".count($data_to_add)."\n";
	
	// change, then remove, then add
	foreach($data_to_change as $a_row) {
			$s_update_clause = array_to_update_clause($a_row);
			$success = db_query("UPDATE `{$maindb}`.`{$s_table}` SET {$s_update_clause} WHERE `{$s_primary_key}`='[$s_primary_key]' {$s_where_clause}", array_merge($a_searchby, $a_row));
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
	foreach($data_to_remove as $primary_value) {
			$success = db_query("DELETE FROM `{$maindb}`.`{$s_table}` WHERE `{$s_primary_key}`='[{$s_primary_key}]' {$s_where_clause}", array_merge($a_searchby, array("{$s_primary_key}"=>$primary_value)));
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
	foreach($data_to_add as $a_row) {
			$a_row = array_merge($a_row, array("year"=>$s_year, "semester"=>$s_semester));
			$s_insert_clause = array_to_insert_clause($a_row);
			$success = db_query("INSERT INTO `{$maindb}`.`{$s_table}` {$s_insert_clause}", $a_row);
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
}

function fillClass(&$a_class, $a_keys) {
	foreach($a_keys as $k=>$v) {
			if (!isset($a_class[$k])) {
					$a_class[$k] = $v;
			}
	}
}

/**
 * Saves the subjects of the semester into `subjects`
 * @$term: the semester data, including "semester", "year", and "subjects"
 */
function saveSubjects($term) {
	
	// get the semester data
	$semester = $term["semester"];
	$year = $term["year"];

	// build the array to save
	$data_to_save = array();
	foreach ($term["subjects"] as $abbr=>$title) {
			$data_to_save[$abbr] = array("abbr"=>$abbr, "title"=>$title);
	}
	$fields = array("abbr","title");

	return saveData($semester, $year, $data_to_save, $fields, "abbr", "subjects");
}

/**
 * Saves the classes to the mysql database
 * @$term: the semester data, including "semester", "year", and "classes"
 */
function saveClasses($term) {
	
	// get some common data
	$semester = $term["semester"];
	$year = $term["year"];
	$s_sem = strtolower(substr($semester,0,3));
	if ($s_sem == "spr") {
			$start_date = "{$year}-01-01 00:00:00";
			$end_date = "{$year}-05-31 23:59:59";
	} else if ($s_sem == "sum") {
			$start_date = "{$year}-06-01 00:00:00";
			$end_date = "{$year}-07-31 23:59:59";
	} else {
			$start_date = "{$year}-08-01 00:00:00";
			$end_date = "{$year}-12-31 23:59:59";
	}
	$modtime = date("Y-m-d H:i:s");

	// build the class data
	$last_class_crn = "";
	$classes_to_save = array();
	$subclasses_to_save = array();
	foreach($term["classes"] as $a_class) {
			fillClass($a_class, array("CRN"=>0, "subject"=>"", "Course"=>"", "*Campus"=>"", "Days"=>"", "Time"=>"", "Location"=>"", "Hrs"=>0, "Title"=>"", "Instructor"=>"", "Seats"=>0, "Limit"=>0, "Enroll"=>0));
			$a_days = str_split(str_replace(" ", "", $a_class["Days"]));
			$a_days_times_locations = array();
			foreach($a_days as $s_day) {
					$a_days_times_locations[] = array($s_day, $a_class["Time"], $a_class["Location"]);
			}
			$days_times_locations = json_encode($a_days_times_locations);
			if ((int)$a_class["CRN"] == 0) {
					$parent_class = (int)$last_class_crn;
					$subclass_index++;
					$is_subclass = TRUE;
			} else {
					$last_class_crn = $a_class["CRN"];
					$parent_class = 0;
					$subclass_index = 0;
					$is_subclass = FALSE;
			}
			$subclass_id = (int)"{$parent_class}{$subclass_index}";
			$a_class_to_save = array("crn"=>$a_class["CRN"],
									 "year"=>$year, "semester"=>$s_sem,
									 "subject"=>$a_class["subject"],
									 "course"=>$a_class["Course"],
									 "campus"=>$a_class["*Campus"],
									 "days"=>$a_class["Days"],
									 "days_times_locations"=>$days_times_locations,
									 "start_date"=>$start_date,
									 "end_date"=>$end_date,
									 "time"=>$a_class["Time"],
									 "location"=>$a_class["Location"],
									 "hours"=>$a_class["Hrs"],
									 "title"=>$a_class["Title"],
									 "instructor"=>$a_class["Instructor"],
									 "seats"=>$a_class["Seats"],
									 "limit"=>$a_class["Limit"],
									 "enroll"=>$a_class["Enroll"],
									 "parent_class"=>$parent_class,
									 "subclass_identifier"=>$subclass_id,
									 "last_mod_time"=>$modtime);
			if ($is_subclass) {
					$subclasses_to_save[] = $a_class_to_save;
			} else {
					$classes_to_save[] = $a_class_to_save;
			}
	}

	// build the array of fields to pass through
	$fields = array();
	foreach($classes_to_save[0] as $k=>$v) {
			$fields[] = $k;
	}
	
	saveData($semester, $year, $subclasses_to_save, $fields, "subclass_identifier", "classes", array("last_mod_time"), array("crn"=>"0"));
	return saveData($semester, $year, $classes_to_save, $fields, "crn", "classes", array("last_mod_time"), array("parent_class"=>"0"));
}

if (!open_db()) {
		echo "failed to connect to database, aborting";
		return FALSE;
}

$term = loadTerm(getNextTerm());
while ($term !== NULL) {
		echo "===============================================================================\n";
		echo "===============================================================================\n";
		echo "...".$term["name"]."\n";
		echo "...subjects\n";
		saveSubjects($term);
		echo "...classes\n";
		saveClasses($term);
		$term = loadTerm(getNextTerm());
}

?>