<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/check_logged_in.php");
require_once(dirname(__FILE__)."/db_query.php");
require_once(dirname(__FILE__)."/../pages/users/user_funcs.php");
require_once(dirname(__FILE__)."/../objects/command.php");

// used to do administrative ajax requests
// only functions within this class can be called by ajax
class ajax_super {
	function reset_password() {
		$username = get_post_var("username");
		$password = get_post_var("password");
		$a_retval = user_funcs::reset_password($username, "", $password, TRUE);
		if ($a_retval[0])
			return json_encode(array(
				new command("print success", $a_retval[1])));
		return json_encode(array(
			new command("print failure", $a_retval[1])));
	}

	function enable_account() {
		global $maindb;
		global $mysqli;
		$username = get_post_var("username");
		$query = db_query("UPDATE `{$maindb}`.`students` SET `disabled`='0' WHERE `username`='[username]'", array("username"=>$username));
		if ($query !== FALSE && $mysqli->affected_rows > 0) {
			return json_encode(array(
				new command("print success", "The account has been enabled. Reload the page to see the affects of the changes.")));
		}
		return json_encode(array(
			new command("print failure", "Failed to enable account \"{$username}\".")));
	}
}

$s_command = get_post_var("command");
$s_super_password = get_post_var("super_password");

if ($s_command != '' && $s_super_password != '') {
	if ($global_user->check_is_guest()) {
		echo json_encode(array(
			new command("failed", "Guest can't use super calls")));
		return;
	}

	$o_user = new user($global_user->get_name(), $s_super_password, "");
	if (!$o_user->exists_in_db()) {
		echo json_encode(array(
			new command("print error", "Invalid credentials")));
		return;
	}
	
	$o_ajax_super = new ajax_super();
	if (method_exists($o_ajax_super, $s_command)) {
		echo $o_ajax_super->$s_command('','','','');
	} else {
		echo json_encode(array(
			new command("failed", "bad command")));
	}
} else {
	echo json_encode(array(
		new command("failed", "no command")));
}

?>