<?php
require_once(dirname(__FILE__)."/resources/globals.php");
require_once(dirname(__FILE__)."/resources/common_functions.php");
my_session_start();
require_once(dirname(__FILE__)."/resources/check_logged_in.php");
require_once(dirname(__FILE__)."/tabs/tabs_functions.php");

function draw_logout_bar() {
	global $global_user;
	$s_retval = array();
	$s_retval[] = "<table class='logout_bar'><tr><td>";
	$s_retval[] = "Logged in: <font class='logout_label username_label'>".$global_user->get_name()."</font>";
	$s_retval[] = '<font class="logout_button" onmouseover="$(this).addClass(\'mouse_hover\');" onmouseout="$(this).removeClass(\'mouse_hover\');">Logout</font>';
	$s_retval[] = "</td></tr></table>";
	return implode("\n", $s_retval);
}

if ($global_user) {
		if ($global_user->exists_in_db()) {
				echo draw_page_head();
				echo '<script src="/js/table_functions.js"></script>';
				echo '<script src="/js/jslists201330.js"></script>';
				echo '<script src="/js/use_course_list.js"></script>';
				echo '<script src="/js/common_functions.js"></script>';
				echo '<script src="/js/tab_functions.js"></script>';
				echo '<script src="/js/schedule.js"></script>';
				echo '<link href="/css/auto_table.css" rel="stylesheet" type="text/css">';
				echo '<link href="/css/tabs.css" rel="stylesheet" type="text/css">';
				echo draw_logout_bar();
				echo "<br /><br /><dev id='content'>";
				echo draw_tabs();
				echo "</dev>";
				echo draw_page_foot();
		}
} else {
		logout_session();
}
?>