<?php

require_once(dirname(__FILE__)."/../pages/icalendar/icalendar_functions.php");

function init_calendar() {
	return '
'.schedule_icalendar_tostring().'
'.schedule_calendar_preview().'
<input type=\'button\' style=\'display:none;\' name=\'onselect\' />';
}

function schedule_calendar_preview() {
	$s_header = '    <table class=\'table_title\'><tr><td>
<div class=\'centered\'>Calendar Preview</div>
</td></tr></table>';
	return $s_header."
<div id='calendar_preview' class='centered'>&nbsp;</div>";
}

function schedule_icalendar_tostring() {
	global $global_user;
	
	$s_header = '';
	if ($global_user->get_name() == "guest") {
			return '    <table class=\'table_title\'><tr><td>
<div class=\'centered\'>Download Semester Calendar</div>
</td></tr></table>
<div class=\'centered\'><a class=\'icalendarGuestDownloadLink\' href=\'\' target=\'_blank\'>Download</a></div>
<br />';
	} else {
			$s_header = '    <table class=\'table_title\'><tr><td>
<div class=\'centered\'>Export Full Calendar</div>
</td></tr></table>';
	}

	if ($global_user->get_server_setting('enable_icalendar') != '1') {
			return $s_header."
<div class='centered'>You don't have the calendar exports enabled.<br />
Go to the <a href='#scroll_to_element' onclick='draw_tab(\"settings\");'>Settings Tab</a>, check \"Enable icalendar,\" and click \"Save\" to enable.</div><br />";
	}
	
	$s_web_link = icalendarFunctions::calendarLinkToString("web");
	$s_view_link = icalendarFunctions::calendarLinkToString("view");
	$s_download_link = icalendarFunctions::calendarLinkToString("download");
	
	return $s_header.'
<div class=\'centered\'>'.": <a href='#scroll_to_element' onclick='scrollWindowCurrent(); o_schedule.drawicalendarLink();'>Link To Calendar</a> : <a href='$s_download_link' target='_blank'>Download Calendar</a> : <a href='http://nmt.edu/~bbean/banweb/icalendar/exporting.html' target=\'_blank\'>Help</a> :".'</div>
<div class=\'centered\' id=\'icalendar_reveal_link\' style=\'display:none;\'><input type=\'textarea\' value=\''.$s_web_link.'\'></input></div>
<br />';
}

$tab_init_function = 'init_calendar';

?>