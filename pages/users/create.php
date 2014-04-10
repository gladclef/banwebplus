<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");

function draw_create_user_form() {
	ob_start();
	?>
	<form id="create_user_form">
		<input type="hidden" name="command" value="create_user">

		<div id="choose_username_form">
			<label>Choose a username:</label><br />
			<input type="textarea" size="30" name="username" placeholder="Eg: The Dude" onblur="send_ajax_call_from_form('ajax.php','choose_username_form');" /><br />
			<label class="errors">&nbsp;</label>
		</div>

		<label>Choose a password:</label><br />
		<input type="password" name="password" size="30" /><br /><br />
		<label>Please enter an email (used for resetting your password):</label><br />
		<input type="textarea" name="email" size="30" placeholder="Eg: thedudeabides@anywhere.net" onkeypress="if (event.keyCode==13){ $('#create_submit').click(); }" /><br />
		<label class="errors"></label><br />
		<input id="create_submit" type="button" onclick="send_ajax_call_from_form('ajax.php','create_user_form');" value="Submit" /><br />
	</form>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	return $s_page;
}

function draw_create_page() {
	$a_retval = array();
	$a_retval[] = draw_page_head('<div><a class="black_link" href="/">Back to login page</a></div>');
	$a_retval[] = '<script type="text/javascript">dont_check_session_expired = true;</script>';
	$a_retval[] = draw_create_user_form();
	$a_retval[] = draw_page_foot();
	return implode("\n", $a_retval);
}

echo manage_output(draw_create_page());

?>
