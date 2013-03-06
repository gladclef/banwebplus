<?php

require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/../../resources/globals.php');
require_once(dirname(__FILE__).'/user_funcs.php');

class create_user_ajax {
	public static function username_status($s_username) {
		global $maindb;
		if (strlen($s_username) == 0)
				return 'blank';
		$a_usernames = db_query("SELECT `id` FROM `[maindb]`.`students` WHERE `username`='[username]'",
								array('maindb'=>$maindb, 'username'=>$s_username));
		if (count($a_usernames) > 0)
				return 'taken';
		else
				return 'available';
	}
	
	public static function check_username() {
		$s_username = get_post_var('username');
		$s_username_status = create_user_ajax::username_status($s_username);
		switch ($s_username_status) {
		case 'blank':
				return 'print error[*note*]The username is blank';
		case 'taken':
				return 'print error[*note*]That username is already taken.';
		case 'available':
				return 'print success[*note*]That username is available.';
		}
	}
	
	public static function create_user() {
		$s_username = trim(get_post_var('username'));
		$s_password = trim(get_post_var('password'));
		$s_email = trim(get_post_var('email'));
		
		if (strlen($s_username) == 0)
				return 'print error[*note*]The username is blank.';
		if (strlen($s_password) == 0)
				return 'print error[*note*]The password is blank.';
		if (strlen($s_email) == 0)
				return 'print error[*note*]The email is blank.';
		
		if (!user_funcs::create_user($s_username, $s_password, $s_email))
				return 'print error[*note*]Error creating user';
		
		mail($s_email, 'banwebplus account', 'You just created an account on banwebplus.com with the username "'.$s_username.'."
Log in to your new account from www.banwebplus.com.

If you ever forget your password you can reset it from the main page by clicking on the "forgot password" link (once I have it functioning).', 'From: noreply@banwebplus.com');
		return 'print success[*note*]Success! You can now use the username '.$s_username.' to log in from the main page!';
	}
}

if (isset($_POST['draw_create_user_page']))
		echo "load page[*note*]/pages/users/create.php[*post*]draw_create_user_page[*value*]1";
else if (isset($_POST['draw_forgot_password_page']))
		echo "load page[*note*]/pages/users/forgot_password.php[*post*]draw_forgot_password_page[*value*]1";
else if (isset($_POST['username']) && !isset($_POST['command']))
		$_POST['command'] = 'check_username';
if (isset($_POST['command'])) {
		$o_ajax = new create_user_ajax();
		$s_command = $_POST['command'];
		if (method_exists($o_ajax, $s_command)) {
				echo create_user_ajax::$s_command();
		} else {
				echo 'bad command';
		}
}

?>