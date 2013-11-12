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
<div id=\'schedule_tab_user_schedule\' class=\'centered\'>&nbsp;</div><br />
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Recently Selected</div>
</td></tr></table>
<div id=\'schedule_tab_user_recently_viewed_schedule\' class=\'centered\'>&nbsp;</div><br />
'.schedule_icalendar_tostring().'
<input type=\'button\' style=\'display:none;\' name=\'onselect\' onclick=\'draw_schedule_tab();\' />';
}

function schedule_icalendar_tostring() {
	global $global_user;
	
	if ($global_user->get_server_setting('enable_icalendar') != '1')
			return '';
	$s_web_link = icalendarFunctions::calendarLinkToString("web");
	$s_view_link = icalendarFunctions::calendarLinkToString("view");
	$s_download_link = icalendarFunctions::calendarLinkToString("download");
	
	return '    <table class=\'table_title\'><tr><td>
<div class=\'centered\'>Link to icalendar</div>
</td></tr></table>
<div class=\'centered\'>Instructions: <a href=\'http://nmt.edu/~bbean/banweb/icalendar/about_icalendar.html\' target=\'_blank\'>About icalendar</a></div>
<div class=\'centered\'>'."<a href='$s_web_link'>For Use in Applications</a> <a href='$s_view_link'>Raw Format</a> <a href='$s_download_link'>Download icalendar</a>".'</div>
<br />';
}

$tab_init_function = 'init_schedule';

?>