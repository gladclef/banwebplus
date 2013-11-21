<?php

require_once(dirname(__FILE__)."/../pages/icalendar/icalendar_functions.php");

function init_schedule() {
	return '
    <table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Add By CRN</div>
</td></tr></table>
<div id=\'schedule_tab_add_by_crn\' class=\'centered\'>&nbsp;</div><br />
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Selected Classes</div>
</td></tr></table>
<div id=\'schedule_tab_user_schedule\' class=\'centered\'>&nbsp;</div><br /><br />
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Recently Selected</div>
</td></tr></table>
<div id=\'schedule_tab_user_recently_viewed_schedule\' class=\'centered\'>&nbsp;</div><br /><br />
'.schedule_icalendar_tostring().'
<input type=\'button\' style=\'display:none;\' name=\'onselect\' />';
}

function schedule_icalendar_tostring() {
	global $global_user;
	
	if ($global_user->get_server_setting('enable_icalendar') != '1')
			return '';
	$s_web_link = icalendarFunctions::calendarLinkToString("web");
	$s_view_link = icalendarFunctions::calendarLinkToString("view");
	$s_download_link = icalendarFunctions::calendarLinkToString("download");
	
	return '    <table class=\'table_title\'><tr><td>
<div class=\'centered\'>Export Full Calendar</div>
</td></tr></table>
<div class=\'centered\'>'.": <a href='#' onclick='o_schedule.drawicalendarLink();'>Link To Calendar</a> : <a href='$s_download_link' target='_blank'>Download Calendar</a> : <a href='http://nmt.edu/~bbean/banweb/icalendar/exporting.html' target=\'_blank\'>Help</a> :".'</div>
<div class=\'centered\' id=\'icalendar_reveal_link\' style=\'display:none;\'><input type=\'textarea\' value=\''.$s_web_link.'\'></input></div>
<br />';
}

$tab_init_function = 'init_schedule';

?>
