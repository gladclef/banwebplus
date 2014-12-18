<?php
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/../../objects/user.php");
require_once(dirname(__FILE__)."/../../objects/command.php");

my_session_start();
logout_session();

$s_username = get_post_var('username');
$s_password = get_post_var('password');
$o_user = new user($s_username, $s_password, '');

if ($o_user->exists_in_db()) {
		$global_user = $o_user;
		login_session($o_user);
		echo json_encode(array(
			new command("load page", "/pages/classes/main.php")));
} else {
		echo json_encode(array(
			new command("print failure", "Bad username or password"),
			new command("clear field", "password")));
}

?>