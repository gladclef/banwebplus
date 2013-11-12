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
		if (isset($_GET['download'])) {
				header('Content-Type: application/octet-stream');
				header("Content-Transfer-Encoding: Binary"); 
				header("Content-disposition: attachment; filename=\"".basename($_SERVER['REQUEST_URI'])."\"");
		}
		$s_calendar = $o_icalendarFunctions->calendarToString();
}

$s_trash = ob_get_contents();
ob_end_clean();

if (isset($_GET['pretty'])) {
		$s_calendar = str_replace("BEGIN:VEVENT", "\r\nBEGIN:VEVENT", $s_calendar);
		echo "<pre>".$s_calendar."</pre>";
} else {
		echo $s_calendar;
}

?>