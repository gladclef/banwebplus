<?php
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/../../objects/user.php");
require_once(dirname(__FILE__)."/../../objects/command.php");

$action = get_post_var("action");
if ($action == "logout") {
	my_session_start();
	logout_session();
	echo json_encode(array(
		new command("load page", "/pages/login/index.php")));
} else {
	echo json_encode(array(
		new command("alert", "unknown parameters")));
}
?>