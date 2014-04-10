<?php

function getStartEndDays($s_sem, $year) {
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
	return array("start"=>$start_date, "end"=>$end_date);
}

function number_to_season($sem) {
	$sem = (int)$sem;
	if ($sem == 10) {
			return "Summer";
	} else if ($sem == 20) {
			return "Fall";
	} else {
			return "Spring";
	}
}

function school_to_real_year($year, $sem) {
	$sem = (int)$sem;
	$year = (int)$year;
	if ($sem == 30) {
			return $year;
	}
	return $year-1;
}

?>