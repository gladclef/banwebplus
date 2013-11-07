<?php

global $o_access_object;
$o_access_object = new AccessObject();

class AccessObject {

	/**
	 * Checks if the user has access to reset the password or if they've tried too many times in the last 15 minutes.
	 * Creates a new row if one doesn't exist for the last 15 minutes.
	 * @param  string  $s_username The username of the password to reset, or "".
	 * @return integer             The number of seconds until the next password reset attempt can be made, or 0.
	 */
	public function check_reset_access($s_username) {

		// get some initial values
		global $maindb;
		$i_now = strtotime("now");
		$a_row = $this->get_access_log($s_username);

		// increase the number of access attempts
		db_query("UPDATE `[maindb]`.`access_log` SET `num_reset_attempts`='[num_reset_attempts]' WHERE `id`='[id]'", array('maindb'=>$maindb, 'num_reset_attempts'=>((int)$a_row['num_reset_attempts']+1), 'id'=>$a_row['id']));

		// check the number of recent reset attempts
		if ((int)$a_row['num_reset_attempts'] >= 5) {
				$i_initial_time = strtotime($a_row['initial_access']);
				$i_next_time = strtotime("+15 minutes", $i_initial_time);
				$i_secs_left = $i_next_time - $i_now;
				return $i_secs_left;
		}

		return 0;
	}

	/**
	 * Find the last access log for the user from within the last 15 minutes,
	 * or create it if it doesn't exist.
	 * @param  string $s_username The username of the log to find/create.
	 * @return array              The row from the database.
	 */
	public function get_access_log($s_username) {

		// get some initial values
		global $maindb;
		$i_now = strtotime("now");
		$s_now = date("Y-m-d H:i:s");
		$i_start_time = strtotime("-15 minutes");
		$s_start_time = date("Y-m-d H:i:s", $i_start_time);
		$s_ip_address = $_SERVER['REMOTE_ADDR'];

		// try and find a previous row from the last 15 minutes
		$a_rows = db_query("SELECT * FROM `[maindb]`.`access_log` WHERE `username`='[username]' AND `initial_access` > '[start_time]'", array('maindb'=>$maindb, 'username'=>$s_username, 'start_time'=>$s_start_time));
		if (count($a_rows) == 0)
				db_query("INSERT INTO `[maindb]`.`access_log` (`username`,`ip_address`,`initial_access`,`num_successes`,`num_failures`,`num_reset_attempts`) VALUES ('[username]','[ip_address]','[initial_access]','[num_successes]','[num_failures]','[num_reset_attempts]')", array('maindb'=>$maindb, 'username'=>$s_username,'ip_address'=>$s_ip_address,'initial_access'=>$s_now,'num_successes'=>0,'num_failures'=>0,'num_reset_attempts'=>0));
		$a_rows = db_query("SELECT * FROM `[maindb]`.`access_log` WHERE `username`='[username]' AND `initial_access` > '[start_time]'", array('maindb'=>$maindb, 'username'=>$s_username, 'start_time'=>$s_start_time));

		return $a_rows[0];
	}

	/**
	 * Generate and return a key to verifiy the reset attempt.
	 * @param  string  $s_username The username of the password to be reset.
	 * @param  boolean $b_generate If TRUE generates the key as of now, if FALSE simply returns the key
	 * @return string              The key, not guaranteed to be unique.
	 */
	public function get_reset_key($s_username, $b_generate) {

		global $maindb;

		// check that the log exists
		$a_logs = db_query("SELECT * FROM `[maindb]`.`access_log` WHERE `username`='[username]' ORDER BY `initial_access` DESC LIMIT 1", array('maindb'=>$maindb, 'username'=>$s_username));
		if (count($a_logs) == 0) {
				$this->check_reset_access($s_username);
				$a_logs = db_query("SELECT * FROM `[maindb]`.`access_log` WHERE `username`='[username]' ORDER BY `initial_access` DESC LIMIT 1", array('maindb'=>$maindb, 'username'=>$s_username));
		}
		$a_log = $a_logs[0];

		// generate a key
		$s_key = $a_log['reset_key'];
		if ($b_generate) {
				$s_key = md5("hello world".$s_username.date("Y-m-d H:i:s"));
				db_query("UPDATE `[main_db]`.`access_log` SET `reset_key`='[reset_key]' WHERE `id`='[id]'", array('id'=>$a_log['id'], 'main_db'=>$maindb, 'reset_key'=>$s_key));
		}

		return $s_key;
	}

	/**
	 * Generate and return the time until the reset link expires.
	 * @param  string  $s_username The username of the password to be reset.
	 * @param  boolean $b_generate If TRUE generates the time as of now, if FALSE simply returns the time
	 * @return integer             The time at which the password reset link expires.
	 */
	public function get_reset_expiration($s_username, $b_generate) {

		global $maindb;

		// check that the log exists
		$a_logs = db_query("SELECT * FROM `[maindb]`.`access_log` WHERE `username`='[username]' ORDER BY `initial_access` DESC LIMIT 1", array('maindb'=>$maindb, 'username'=>$s_username));
		if (count($a_logs) == 0) {
				$this->check_reset_access($s_username);
				$a_logs = db_query("SELECT * FROM `[maindb]`.`access_log` WHERE `username`='[username]' ORDER BY `initial_access` DESC LIMIT 1", array('maindb'=>$maindb, 'username'=>$s_username));
		}
		$a_log = $a_logs[0];

		// generate a time
		$i_expiration = strtotime($a_log['reset_expiration']);
		if ($b_generate) {
				$i_expiration = strtotime("+15 minutes");
				$s_reset_expiration = date("Y-m-d H:i:s", $i_expiration);
				db_query("UPDATE `[main_db]`.`access_log` SET `reset_expiration`='[reset_expiration]' WHERE `id`='[id]'", array('id'=>$a_log['id'], 'main_db'=>$maindb, 'reset_expiration'=>$s_reset_expiration));
		}

		return $i_expiration;
	}
}

?>