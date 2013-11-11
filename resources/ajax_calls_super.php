<?php
require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/check_logged_in.php");
require_once(dirname(__FILE__)."/db_query.php");
require_once(dirname(__FILE__)."/../pages/users/user_funcs.php");

// used to do administrative ajax requests
// only functions within this class can be called by ajax
class ajax_super {
	function reset_password() {
		$username = get_post_var("username");
		$password = get_post_var("password");
		$a_retval = user_funcs::reset_password($username, "", $password, TRUE);
		if ($a_retval[0])
				return "print success[*note*]".$a_retval[1];
		if ($a_retval[0])
				return "print failure[*note*]".$a_retval[1];
	}
}

$s_command = get_post_var("command");
$s_super_password = get_post_var("super_password");

if ($s_command != '' && $s_super_password != '') {
		$o_user = new user($global_user->get_name(), $s_super_password, "");
		if (!$o_user->exists_in_db()) {
				echo "print error[*note*]Invalid credentials";
				return;
		}
		
		$o_ajax_super = new ajax_super();
		if (method_exists($o_ajax_super, $s_command)) {
				echo $o_ajax_super->$s_command('','','','');
		} else {
				echo 'failed|bad command';
		}
} else {
		echo 'failed|no command';
}

?>