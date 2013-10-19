<?php

require_once(dirname(__FILE__).'/../../resources/db_query.php');
require_once(dirname(__FILE__).'/../../resources/globals.php');

class user_funcs {
	public static function create_user($s_username, $s_password, $s_email, $a_other = NULL) {
		global $maindb;
		$a_other = ($a_other == NULL) ? array() : $a_other;
		$s_access = (isset($a_other['access'])) ? $a_other['access'] : 'feedback';
		
		// check that the data is good
		if ($s_username == '' || $s_password == '' || $s_email == '')
				return FALSE;
		if (strpos($s_email, '@') === FALSE || strpos($s_email, '.') === FALSE || strpos($s_email, '|') !== FALSE || strpos($s_email, '<') !== FALSE || strpos($s_email, '>') !== FALSE)
				return false;
		$a_users = db_query("SELECT `id` FROM `[maindb]`.`students` WHERE `username`='[username]'",
							array('maindb'=>$maindb, 'username'=>$s_username));
		if (count($a_users) > 0)
				return FALSE;
		
		// create the user
		db_query("INSERT INTO `[maindb]`.`students` (`username`,`pass`,`email`,`accesses`) VALUES ('[username]',AES_ENCRYPT('[username]','[password]'),'[email]','[accesses]')",
				 array('maindb'=>$maindb, 'username'=>$s_username, 'password'=>$s_password, 'email'=>$s_email, 'accesses'=>$s_access));
		if (mysql_affected_rows() > 0)
				return TRUE;
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
}

?>