<?php
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/../../objects/user.php");

my_session_start();
logout_session();

$s_command = get_post_var('command', 'login');
$s_username = get_post_var('username');
$s_password = get_post_var('password');
$o_user = new user($s_username, $s_password, '');

if ($s_command == 'login') {
		if ($o_user->exists_in_db()) {
				$global_user = $o_user;
				login_session($o_user);
				echo "load page[*note*]/pages/classes/main.php";
		} else {
				echo "print error[*note*]Bad username or password[*command*]clear field[*note*]password";
		}
} else if ($s_command == 'verify_password') {
		if ($o_user->exists_in_db()) {
				echo "success";
		} else {
				echo "failure";
		}
}

?>