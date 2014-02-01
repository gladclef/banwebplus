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
	return $semesterData;
}

function saveData($s_semester, $s_year, $a_data_to_save, $a_keys, $s_primary_key, $s_table) {
	
	global $maindb;

	// compiles the keys
	$s_keylist = "`".implode("`,`",$a_keys)."`";
	
	// load existing data from the database
	// loads them each as an "primary_key"=>array("key"=>value, ...)
	$db_data_loaded = db_query("SELECT {$s_keylist} FROM `{$maindb}`.`{$s_table}` WHERE `year`='[year]' AND `semester`='[semester]' ORDER BY `{$s_primary_key}`", array("semester"=>$semester, "year"=>$year));
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
			$row_exists = FALSE;
			if (isset($db_data[$k])) {
					$s_db_row = implode("", $db_data[$k]);
					$s_tosave_row = implode("", $a_row);
					if ($s_db_row == $s_tosave_row) {
							$row_exists = TRUE;
							unset($db_data[$k]);
					} else {
							$row_exists = TRUE;
							$data_to_change[$k] = $a_row;
					}
			}
			if (!$row_exists) {
					$data_to_add[$k] = $a_row;
			}
			unset($a_data_to_save[$k]);
	}
	foreach($db_data as $primary_value=>$a_db_row) {
			$data_to_remove[$primary_value] = $primary_value;
			unset($db_data[$primary_value]);
	}

	echo "update: ".count($data_to_change)."\ndelete: ".count($data_to_remove)."\ninsert: ".count($data_to_add)."\n";
	return;
	
	// change, then remove, then add
	foreach($data_to_change as $a_row) {
			$s_update_clause = array_to_update_clause($a_row);
			$success = db_query("UPDATE `{$maindb}`.`{$s_table}` SET {$s_update_clause} WHERE `{$s_primary_key}`='[$s_primary_key]'", $a_row);
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
	foreach($data_to_remove as $primary_value) {
			$success = db_query("DELETE FROM `{$maindb}`.`{$s_table}` WHERE `{$s_primary_key}`='[{$s_primary_key}]'", array("{$s_primary_key}"=>$primary_value));
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
	foreach($data_to_add as $a_row) {
			$a_row = array_merge($a_row, array("year"=>$year, "semester"=>$semester));
			$s_insert_clause = array_to_insert_clause($a_row);
			$success = db_query("INSERT INTO `{$maindb}`.`{$s_table}` {$s_insert_clause}", $a_row);
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
}

/**
 * Saves the subjects of the semester into `subjects`
 * @$term: the semester data, including "semester", "year", and "subjects"
 */
function saveSubjects($term) {
	
	global $maindb;
	
	// get the semester data
	$semester = $term["semester"];
	$year = $term["year"];

	return saveData($semester, $year, $term["subjects"], array("abbr","title"), "abbr", "subjects");
	
	// load existing subjects from the database
	// loads them each as an "abbr"=>array("abbr"=>string, "id"=>int, "title"=>string)
	$db_subjs_loaded = db_query("SELECT `id`,`abbr`,`title` FROM `{$maindb}`.`subjects` WHERE `year`='[year]' AND `semester`='[semester]' ORDER BY `abbr`", array("semester"=>$semester, "year"=>$year));
	$db_subjs = array();
	foreach($db_subjs_loaded as $db_subj) {
			$db_subjs[$db_subj["abbr"]] = $db_subj;
	}
	
	// determine which subject have not already been saved,
	// and which should be removed
	$subjects_to_add = array();
	$subjects_to_remove = array();
	$subjects_to_change = array();
	foreach ($term["subjects"] as $abbreviation=>$title) {
			$row_exists = FALSE;
			if (isset($db_subjs[$abbreviation])) {
					if ($db_subjs[$abbreviation]["title"] == $title) {
							$row_exists = TRUE;
							unset($db_subjs[$abbreviation]);
					} else {
							$subjects_to_change[$abbreviation] = array("id"=>$db_subjs[$abbreviation]["id"], "title"=>$title);
					}
			}
			if (!$row_exists) {
					$subjects_to_add[$abbreviation] = $title;
			}
			unset($term["subjects"][$abbreviation]);
	}
	foreach($db_subjs as $k=>$db_subj) {
			$subjects_to_remove[$db_subj["abbr"]] = $db_subj["id"];
			unset($db_subjs[$k]);
	}

	echo "update: ".count($subjects_to_change)."\ndelete: ".count($subjects_to_remove)."\ninsert: ".count($subjects_to_add)."\n";
	
	// change, then remove, then add
	foreach($subjects_to_change as $v) {
			$success = db_query("UPDATE `{$maindb}`.`subjects` SET `title`='[title]' WHERE `id`='[id]'", array("id"=>$v['id'], "title"=>$v["title"]));
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
	foreach($subjects_to_remove as $id) {
			$success = db_query("DELETE FROM `{$maindb}`.`subjects` WHERE `id`='[id]'", array("id"=>$id));
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
	foreach($subjects_to_add as $abbr=>$title) {
			$success = db_query("INSERT INTO `{$maindb}`.`subjects` (`year`,`semester`,`abbr`,`title`) VALUES ('[year]','[semester]','[abbr]','[title]')", array("year"=>$year, "semester"=>$semester, "abbr"=>$abbr, "title"=>$title));
			if ($success === FALSE)
					echo mysql_error()."\n";
	}
}

/**
 * Saves the classes to the mysql database
 * @$term: the semester data, including "semester", "year", and "classes"
 */
function saveClasses($term) {
}

if (!open_db()) {
		echo "failed to connect to database, aborting";
		return FALSE;
}
$term = loadTerm(getNextTerm());
saveSubjects($term);

?>