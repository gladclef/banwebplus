<?php

require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/../../resources/globals.php');
require_once(dirname(__FILE__)."/../login/access_object.php");

class user_funcs {
	public static function create_user($s_username, $s_password, $s_email, $a_other = NULL, &$s_feedback = "") {
		global $maindb;
		global $mysqli;
		$a_other = ($a_other == NULL) ? array() : $a_other;
		$s_access = (isset($a_other['access'])) ? $a_other['access'] : 'feedback';
		
		// check that the data is good
		if ($s_username == '' || $s_password == '' || $s_email == '') {
			$s_feedback = "Empty username, password, or email address.";
			return FALSE;
		}
		if (strpos($s_email, '@') === FALSE || strpos($s_email, '.') === FALSE || strpos($s_email, '|') !== FALSE || strpos($s_email, '<') !== FALSE || strpos($s_email, '>') !== FALSE) {
			$s_feedback = "Invalid email address.";
			return false;
		}
		$a_users = db_query("SELECT `id` FROM `[maindb]`.`students` WHERE `username`='[username]'",
							array('maindb'=>$maindb, 'username'=>$s_username));
		if (count($a_users) > 0) {
			$s_feedback = "User with name \"{$s_username}\" already exists in database.";
			return FALSE;
		}
		
		// create the user
		db_query("INSERT INTO `[maindb]`.`students` (`username`,`pass`,`email`,`accesses`) VALUES ('[username]',AES_ENCRYPT('[username]','[password]'),'[email]','[accesses]')",
				 array('maindb'=>$maindb, 'username'=>$s_username, 'password'=>$s_password, 'email'=>$s_email, 'accesses'=>$s_access));
		if ($mysqli->affected_rows > 0) {
			return TRUE;
		}
		$s_feedback = "Failed to add user to database.";
		return FALSE;
	}
	
	/**
	 * Emails a user from "noreply@banwebplus.com"
	 * @param  string $s_username The username to email.
	 * @param  string $s_header   The header of the email.
	 * @param  string $s_body     The body of the email.
	 * @return boolean            TRUE on success, FALSE on failure
	 */
	public static function email_notification($s_username, $s_header, $s_body) {
		$a_user = db_query("SELECT `email` FROM `students` WHERE `username`='[username]'", array('username'=>$s_username));
		if (count($a_user) == 0)
				return FALSE;
		$s_email_address = $a_user[0]['email'];
		if ($s_email_address == '')
				return FALSE;
		mail($s_email_address, $s_header, $s_body, "From: noreply@banwebplus.com");
		return TRUE;
	}

	/**
	 * Resets the password for the user, if they applied for the forgot password script and have the correct key.
	 * The key is verified against the key in the `access_log`.`reset_key`.
	 * @param  string $s_username The username of the user to reset the password for.
	 * @param  string $s_key      The key to verify against `access_log`.`reset_key`.
	 * @param  string $s_password The password to use for the user.
	 * @param  boolean $b_force   If TRUE, does not check the key or time.
	 * @return string             An array with either TRUE/FALSE, and one of 'Your password has been set. You can now login with the username [login].', 'The username [username] can't be found.', 'Invalid credentials', 'The reset has timed out. Please resubmit the request to reset your password.'
	 */
	public static function reset_password($s_username, $s_key, $s_password, $b_force = FALSE) {
	
		global $maindb;
		global $o_access_object;
	
		// check that the user exists
		$s_username_exists = self::username_status($s_username);
		if ($s_username_exists != "taken")
				return array(FALSE, "The username {$s_username} can't be found.");
	
		// get some variables
		$i_now = time();
		$i_reset_expiration = $o_access_object->get_reset_expiration($s_username, FALSE);
		$s_reset_key = $o_access_object->get_reset_key($s_username, FALSE);
	
		// check the key and time
		if ($s_reset_key != $s_key && !$b_force)
				return array(FALSE, "Invalid credentials");
		if ($i_reset_expiration < $i_now && !$b_force)
				return array(FALSE, "The reset has timed out. Please resubmit the request to reset your password.");
	
		// reset the password
		db_query("UPDATE `[maindb]`.`students` SET `pass`=AES_ENCRYPT('[username]','[password]') WHERE `username`='[username]'", array("username"=>$s_username, "password"=>$s_password, "maindb"=>$maindb));
		return array(TRUE, "Your password has been set. You can now login with the username {$s_username}.");
	}

	/**
	 * Checks that a username doesn't exist, yet
	 * @$s_username string The username to be checking for
	 * @return      string One of "blank", "taken", or "available"
	 */
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
}

?>