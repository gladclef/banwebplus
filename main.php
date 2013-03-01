<?php
require_once(dirname(__FILE__)."/resources/check_logged_in.php");
require_once(dirname(__FILE__)."/resources/globals.php");
require_once(dirname(__FILE__)."/resources/common_functions.php");

function draw_logout_bar() {
	global $global_user;
	$s_retval = array();
	$s_retval[] = "<table class='logout_bar'><tr><td>";
	$s_retval[] = "Logged in: <font class='logout_label username_label'>".$global_user->get_name()."</font>";
	$s_retval[] = '<font class="logout_button" onmouseover="$(this).addClass(\'mouse_hover\');" onmouseout="$(this).removeClass(\'mouse_hover\');">Logout</font>';
	$s_retval[] = "</td></tr></table>";
	return implode("\n", $s_retval);
}

function draw_tabs() {
	$s_schedule_contents = '&nbsp;';
	$s_classes_contents = '<select id=\'subject_selector\' onchange=\'draw_course_table();\'></select><input id="add_subject_button" type="button" onclick="add_extra_subject(this);" value="Add Subject" /><input id="add_subject_all_button" type="button" onclick="add_extra_subject_all();" value="All" /><br /><div id=\'classes_content\'>&nbsp;</div>';
	$s_lists_contents = '&nbsp;';
	$s_settings_contents = '&nbsp;';
	
	$a_tabs = array("Schedule"=>$s_schedule_contents, "Classes"=>$s_classes_contents, "Lists"=>$s_lists_contents, "Settings"=>$s_settings_contents);

	$a_retval = array();
	$a_retval[] = '<table><tr><td></td><td id="tabs_container" class="centered" style="width:300px;"><table width="100%"><tr>';
	foreach($a_tabs as $s_tab_name=>$s_tab_contents)
			$a_retval[] = '<td class="tab" onclick="draw_tab(\''.$s_tab_name.'\')">'.$s_tab_name.'</td>';
	$a_retval[] = '</tr></table></td><td></td></tr>';
	$a_retval[] = '<tr><td colspan="3"><div class="tab_contents centered">';
	$a_retval[] = '<div class="spacer_for_div_contents">&nbsp;</div>';
	foreach($a_tabs as $s_tab_name=>$s_tab_contents)
			$a_retval[] = '<div class="tab_contents_div centered" id="'.$s_tab_name.'">'.$s_tab_contents.'</div>';
	$a_retval[] = '</div></td></tr></table>';
	return implode("\n", $a_retval);
}

if ($global_user->exists_in_db()) {
		echo draw_page_head();
		echo '<script src="/js/table_functions.js"></script>';
		echo '<script src="/js/jslists201330.js"></script>';
		echo '<script src="/js/use_course_list.js"></script>';
		echo '<script src="/js/common_functions.js"></script>';
		echo '<script src="/js/tab_functions.js"></script>';
		echo '<link href="/css/auto_table.css" rel="stylesheet" type="text/css">';
		echo '<link href="/css/tabs.css" rel="stylesheet" type="text/css">';
		echo draw_logout_bar();
		echo "<br /><br /><dev id='content'>";
		echo draw_tabs();
		echo "</dev>";
		echo draw_page_foot();
}
?>