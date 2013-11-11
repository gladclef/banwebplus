<?php

$a_required_getvars = array("username", "key");
for($i = 0; $i < count($a_required_getvars); $i++) {
		if (!isset($_GET[$a_required_getvars[$i]])) {
				echo "Missing required getvar ".$a_required_getvars[$i];
				return;
		}
}

ob_start();

require_once(dirname(__FILE__)."/../../resources/db_query.php");
require_once(dirname(__FILE__)."/icalendar_functions.php");

$o_icalendarFunctions = new icalendarFunctions($_GET['username'], $_GET['key']);
if (!$o_icalendarFunctions->exists()) {
		$s_calendar = "Invalid Credentials";
} else {
		$s_calendar = $o_icalendarFunctions->calendarToString();
}

$s_trash = ob_get_contents();
ob_end_clean();

echo $s_calendar;

?>