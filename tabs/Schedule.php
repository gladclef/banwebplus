<?php

require_once(dirname(__FILE__)."/../pages/icalendar/icalendar_functions.php");

function init_schedule() {
	return schedule_icalendar_tostring().'
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
<div id=\'schedule_tab_user_recently_viewed_schedule\' class=\'centered\'>&nbsp;</div>
<input type=\'button\' style=\'display:none;\' name=\'onselect\' onclick=\'draw_schedule_tab();\' />';
}

function schedule_icalendar_tostring() {
	global $global_user;
	
	if ($global_user->get_server_setting('enable_icalendar') != '1')
			return '';
	$s_link = icalendarFunctions::calendarLinkToString();
	
	return '    <table class=\'table_title\'><tr><td>
<div class=\'centered\'>Link to icalendar</div>
</td></tr></table>
<div class=\'centered\'><a href=\''.$s_link.'\' target=\'_blank\'>'.$s_link.'</a></div><br />';
}

$tab_init_function = 'init_schedule';

?>