<?php
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/login.php");
require_once(dirname(__FILE__)."/../../objects/command.php");

$s_command = get_post_var("command");

function check_session_expired() {
	my_session_start();
	if (get_session_expired())
		return json_encode(array(
			new command("alert", "Your session has expired. You are now being redirected to the login screen.
(change the time it takes to expire under settings)"),
			new command("load page", "/pages/login/index.php")));
	else
		return "[]";
}

if ($s_command == "check_session_expired") {
	echo check_session_expired();
} else {
	echo json_encode(array(
		new command("failure", "bad command")));
}

?>