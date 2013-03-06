<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");

function draw_create_user_form() {
	$a_retval = array();
	$a_retval[] = '<form id="create_user_form">';
	$a_retval[] = '<input type="hidden" name="command" value="create_user">';
	
	$a_retval[] = '<div id="choose_username_form"><label>Choose a username:</label><br />';
	$a_retval[] = '<input type="textarea" size="30" name="username" placeholder="Eg: The Dude" onblur="send_ajax_call_from_form(\'ajax.php\',\'choose_username_form\');" /><br />';
	$a_retval[] = '<label class="errors">&nbsp;</label></div>';
	
	$a_retval[] = '<label>Choose a password:</label><br />';
	$a_retval[] = '<input type="password" name="password" size="30" /><br /><br />';
	$a_retval[] = '<label>Please enter an email (used for resetting your password):</label><br />';
	$a_retval[] = '<input type="textarea" name="email" size="30" placeholder="Eg: thedudeabides@anywhere.net" onkeypress="if (event.keyCode==13){ $(\'#create_submit\').click(); }" /><br />';
	$a_retval[] = '<label class="errors"></label><br />';
	$a_retval[] = '<input id="create_submit" type="button" onclick="send_ajax_call_from_form(\'ajax.php\',\'create_user_form\');" value="Submit" /><br />';
	$a_retval[] = '</form>';
	return implode("\n", $a_retval);
}

function draw_create_page() {
	$a_retval = array();
	$a_retval[] = draw_page_head('<div><a class="black_link" href="/">Back to login page</a></div>');
	$a_retval[] = '<script type="text/javascript">dont_check_session_expired = true;</script>';
	$a_retval[] = draw_create_user_form();
	$a_retval[] = draw_page_foot();
	return implode("\n", $a_retval);
}

if (isset($_POST['draw_create_user_page'])) {
		echo draw_create_page();
}

?>
