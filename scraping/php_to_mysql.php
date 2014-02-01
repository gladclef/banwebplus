<?php

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
	$semesterData["semester"] = substr($a_semester[0], 3);
	return $semesterData;
}

/**
 * Saves the subjects of the semester into `subjects`
 */
function saveSubjects($semesterData) {
}

$term = loadTerm(getNextTerm());
echo $term["semester"]."\n";

?>