<?php
require_once(dirname(__FILE__)."/../resources/db_query.php");
require_once(dirname(__FILE__)."/../resources/globals.php");
require_once(dirname(__FILE__)."/accesses.php");

class user {
	private $name = '';
	private $id = 0;
	private $access = NULL;
	private $exists = FALSE;
	private $a_server_settings = array();
	private $accesses_string = '';
	private $a_classes = array();
	private $a_whitelists = array();
	private $a_blacklists = array();
	private $email = '';

	function __construct($username, $password, $crypt_password) {
		$this->name = $username;
		$a_user = $this->load_from_db($password, $crypt_password);
		$this->exists = ($a_user !== FALSE && $a_user !== NULL);
		if ($this->exists) {
				$this->set_accesses();
				$this->load_settings();
				self::global_user($a_user, $this);
		}
	}

	/*********************************************************************
	 *                     P U B L I C   F U N C T I O N S               *
	 *********************************************************************/

	public function exists_in_db() {
		return $this->exists;
	}
	public function get_name() {
		return $this->name;
	}
	public function get_id() {
		return $this->id;
	}
	public function get_email() {
		return $this->email;
	}
	public function check_is_guest() {
		return (strtolower($this->get_name()) == 'guest');
	}

	public function get_server_setting($setting_name) {
		$s_retval = '';
		if (isset($this->a_server_settings[$setting_name]))
				$s_retval = $this->a_server_settings[$setting_name];
		return $s_retval;
	}

	/**
	 * update the "user_settings" table with changes to $a_settings
	 * @$s_type The "type" column of the "user_settings" table, typically "server"
	 * @$a_settings The settings to be updated, as an array(columnName=>values, ...)
	 */
	public function update_settings($s_type, $a_settings) {
		global $maindb;
		global $mysqli;
		if ($this->check_is_guest())
			return json_encode(array(
				new command("error", "settings can\'t be saved as a guest")));

		$query_string = 'SELECT `id` FROM `[database]`.`user_settings` WHERE '.array_to_where_clause($a_settings).' AND `user_id`=\'[user_id]\' AND `type`=\'[type]\'';
		$query_vars = array("database"=>$maindb, "user_id"=>$this->id, "type"=>$s_type, "table"=>"user_settings");
		$a_exists = db_query($query_string, $query_vars);
		if(count($a_exists) > 0)
			return json_encode(array(
				new command("print success", "Settings already saved")));
		
		create_row_if_not_existing($query_vars);
		$a_current = db_query("SELECT * FROM `[database]`.`[table]` WHERE `user_id`='[user_id]' AND `type`='server'", $query_vars);
		$query_string = 'UPDATE `[database]`.`[table]` SET '.array_to_update_clause($a_settings).' WHERE `user_id`=\'[user_id]\' AND `type`=\'[type]\'';
		db_query($query_string, array_merge($a_settings, $query_vars));
		if ($mysqli->affected_rows == 0) {
			return json_encode(array(
				new command("print failure", "Failed to save settings")));
		} else {
				$this->updateSpecialSettings($a_settings, $a_current[0]);
			return json_encode(array(
				new command("print success", "Settings saved successfully. Next time you log in these settings will take effect.")));
		}
	}

	public function get_crypt_password() {
		global $maindb;
		if (!$this->exists)
				return '';
		$a_users = db_query("SELECT `pass` FROM `[maindb]`.`students` WHERE `username`='[username]'", array("maindb"=>$maindb, "username"=>$this->name));
		if ($a_users !== FALSE) {
				if (count($a_users) > 0) {
						$a_user = $a_users[0];
						return $a_user['pass'];
				}
		}
		return '';
	}

	public function has_access($s_access) {
		if ($s_access == '')
				return TRUE;
		return $this->access->has_access($s_access);
	}

	public function get_user_classes($s_year, $s_semester) {
		$s_semtext = $s_year.$s_semester;
		if (!isset($this->a_classes[$s_semtext]))
				$this->a_classes[$s_semtext] = $this->load_user_classes($s_year, $s_semester);
		return $this->a_classes[$s_semtext];
	}
	public function get_user_whitelist($s_year, $s_semester) {
		$s_semtext = $s_year.$s_semester;
		if (!isset($this->a_whitelists[$s_semtext]))
				$this->a_whitelists[$s_semtext] = $this->load_user_whitelist($s_year, $s_semester);
		return $this->a_whitelists[$s_semtext];
	}
	public function get_user_blacklist($s_year, $s_semester) {
		$s_semtext = $s_year.$s_semester;
		if (!isset($this->a_blacklists[$s_semtext]))
				$this->a_blacklists[$s_semtext] = $this->load_user_blacklist($s_year, $s_semester);
		return $this->a_blacklists[$s_semtext];
	}
	
	public function save_user_classes($s_year, $s_semester, $s_json_saveval, $s_timestamp) {
		return $this->save_time_dependent_user_data($s_year, $s_semester, 'semester_classes', $s_json_saveval, $s_timestamp);
	}
	public function save_user_whitelist($s_year, $s_semester, $s_json_saveval, $s_timestamp) {
		return $this->save_time_dependent_user_data($s_year, $s_semester, 'semester_whitelist', $s_json_saveval, $s_timestamp);
	}
	public function save_user_blacklist($s_year, $s_semester, $s_json_saveval, $s_timestamp) {
		return $this->save_time_dependent_user_data($s_year, $s_semester, 'semester_blacklist', $s_json_saveval, $s_timestamp);
	}
	
	public function update_password($s_password) {
		global $maindb;
		global $mysqli;
		if ($this->name == "guest") {
				return False;
		}
		$a_query = db_query("UPDATE `{$maindb}`.`students` SET `pass`=AES_ENCRYPT('[username]','[password]') WHERE `username`='[username]'", array("username"=>$this->name, "password"=>$s_password));
		if ($a_query !== FALSE && $mysqli->affected_rows > 0) {
				return TRUE;
		}
		return FALSE;
	}
	public function disable_account() {
		global $maindb;
		global $mysqli;
		$a_query = db_query("SELECT `disabled` FROM `[maindb]`.`students` WHERE `username`='[name]'",
			array("maindb"=>$maindb, "name"=>$this->name));
		if ($a_query === FALSE || count($a_query) == 0 || (int)$a_query[0]["disabled"] != 0) {
			return "Account already disabled.";
		}
		$a_query = db_query("UPDATE `[maindb]`.`students` SET `disabled`='1' WHERE `username`='[name]'",
			array("maindb"=>$maindb, "name"=>$this->name));
		if ($a_query !== FALSE && $mysqli->affected_rows > 0) {
			return "success";
		}
		return "Failed to update account.";
	}
	public function delete_account() {
		global $maindb;
		global $mysqli;
		$username = $this->name;
		$id = $this->get_id();
		$a_username = array("username"=>$username);
		$a_id = array("id"=>$id);
		$a_query = db_query("SELECT `id` FROM `[maindb]`.`students` WHERE `id`='[id]'", 
			array_merge(array("maindb"=>$maindb), $a_id));
		if ($a_query === FALSE || count($a_query) == 0) {
			return "Account already deleted/doesn't exist.";
		}
		db_query("DELETE FROM `{$maindb}`.`access_log` WHERE `username`='[username]'", $a_username);
		db_query("DELETE FROM `{$maindb}`.`generated_settings` WHERE `user_id`='[id]'", $a_id);
		db_query("DELETE FROM `{$maindb}`.`semester_blacklist` WHERE `user_id`='[id]'", $a_id);
		db_query("DELETE FROM `{$maindb}`.`semester_classes` WHERE `user_id`='[id]'", $a_id);
		db_query("DELETE FROM `{$maindb}`.`semester_whitelist` WHERE `user_id`='[id]'", $a_id);
		$query = db_query("DELETE FROM `{$maindb}`.`students` WHERE `id`='[id]'", $a_id);
		$affected_rows = $mysqli->affected_rows;
		db_query("DELETE FROM `{$maindb}`.`user_settings` WHERE `user_id`='[id]'", $a_id);
		if ($query !== FALSE && $affected_rows) {
				return "success";
		}
		return "Failed to update account.";
	}

	// get the ids of those users who can see this user's schedule
	// returns array[id1, id2, id2, ...]
	public function get_schedule_shared_users() {
		global $maindb;
		$a_queryvars = array("table"=>"user_settings", "database"=>$maindb, "user_id"=>$this->get_id());
		$s_querystring = "SELECT `share_schedule_with` FROM `[database]`.`[table]` WHERE `user_id`='[user_id]'";
		$a_rows = db_query($s_querystring, $a_queryvars);
		$a_retval = array();
		if (count($a_rows) > 0 ) {
				$ids = $a_rows[0]["share_schedule_with"];
				if ($ids !== null && $ids !== "NULL" && $ids !== "") {
						$a_retval = explode("||", trim($ids, "|"));
						for ($i = 0; $i < count($a_retval); $i++) {
								$a_retval[$i] = intval($a_retval[$i]);
						}
				}
		}
		return $a_retval;
	}

	// set the ids of those users who can see this user's schedule
	public function set_schedule_shared_users($a_user_ids) {
		$s_share_schedule_with = "|".implode("||", $a_user_ids)."|";
		if (count($a_user_ids) == 0) {
				$s_share_schedule_with = "";
		}
		$a_settings = array("share_schedule_with"=>$s_share_schedule_with);
		$this->update_settings("server", $a_settings);
	}

	/*********************************************************************
	 *                   P R I V A T E   F U N C T I O N S               *
	 *********************************************************************/

	/**
	 * Used to perform special operations when certain settings values are saved
	 * eg, creates a icalendar key if one doesn't already exist
	 * @$a_settings array The settings values to save.
	 * @$a_current  array The settings values before the save.
	 */
	private function updateSpecialSettings($a_settings, $a_current) {
		global $maindb;
		
		foreach($a_settings as $setting_name=>$setting_value) {
				
				// if the setting hasn't changed then don't do anything
				if ($a_current[$setting_name] == $setting_value)
						continue;
				
				if ($setting_name == 'enable_icalendar' && $a_current['enable_icalendar'] == '0') {
						create_row_if_not_existing(array('database'=>$maindb, 'table'=>'generated_settings', 'user_id'=>$this->get_id()));
						$a_generated_settings = db_query("SELECT `private_icalendar_key` FROM `[database]`.`generated_settings` WHERE `user_id`='[id]'", array('database'=>$maindb, 'id'=>$this->get_id()));
						if ($a_generated_settings[0]['private_icalendar_key'] == '') {
								$private_icalendar_key = md5($this->get_name().date("Y-m-d H:i:s")."this is a salt");
								db_query("UPDATE `[database]`.`generated_settings` SET `private_icalendar_key`='[private_icalendar_key]' WHERE `user_id`='[id]'", array('database'=>$maindb, 'id'=>$this->get_id(), 'private_icalendar_key'=>$private_icalendar_key));
						}
				}
		}
	}

	private function load_user_classes($s_year, $s_semester) {
		$a_user_data = $this->load_user_data($s_year, $s_semester, 'semester_classes');
		if (!is_array($a_user_data) || count($a_user_data) == 0)
				return array();
		foreach($a_user_data as $k=>$a_class) {
				$crn = $a_class->crn;
				if (strlen($crn) < 5)
						unset($a_user_data[$k]);
		}
		return $a_user_data;
	}
	private function load_user_whitelist($s_year, $s_semester) {
		return $this->load_user_data($s_year, $s_semester, 'semester_whitelist');
	}
	private function load_user_blacklist($s_year, $s_semester) {
		return $this->load_user_data($s_year, $s_semester, 'semester_blacklist');
	}

	private function load_user_data($s_year, $s_semester, $s_tablename) {
		global $maindb;

		$a_queryvars = array("tablename"=>$s_tablename, "year"=>$s_year, "semester"=>$s_semester, "user_id"=>$this->get_id(), "maindb"=>$maindb);
		$s_querystring = "SELECT `json` FROM `[maindb]`.`[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `user_id`='[user_id]'";
		$a_tableval = db_query($s_querystring, $a_queryvars);
		if ($a_tableval === FALSE || $a_tableval == '' || count($a_tableval) == 0)
				return '';
		$s_tableval = $a_tableval[0]['json'];
		$a_user_data = json_decode($s_tableval);

		if (!is_array($a_user_data) || count($a_user_data) == 0)
				return '';

		return $a_user_data;
	}

	private function save_time_dependent_user_data($s_year, $s_semester, $s_tablename, $s_json_saveval, $s_timestamp) {
		global $maindb;
		$a_queryvars = array('year'=>$s_year, 'semester'=>$s_semester, 'tablename'=>$s_tablename, 'database'=>$maindb, 'timestamp'=>$s_timestamp, 'json'=>$s_json_saveval);
		$s_querystring = "SELECT * FROM `[database]`.`[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `time_submitted`>'[timestamp]'";
		$a_query = db_query($s_querystring, $a_queryvars);
		if (is_array($a_query) && count($a_query) > 0)
				return -1;
		return $this->save_user_data($s_year, $s_semester, $s_tablename, $s_json_saveval, $s_timestamp);
	}

	private function save_user_data($s_year, $s_semester, $s_tablename, $s_json_saveval, $s_timestamp) {
		global $maindb;
		global $mysqli;

		$a_queryvars = array("table"=>$s_tablename, "year"=>$s_year, "semester"=>$s_semester, "user_id"=>$this->get_id(), "database"=>$maindb);
		$s_querystring = "UPDATE `[database]`.`[table]` SET `json`='[json]',`time_submitted`='[timestamp]' WHERE `year`='[year]' AND `semester`='[semester]' AND `user_id`='[user_id]'";
		create_row_if_not_existing($a_queryvars);
		db_query($s_querystring, array_merge(array('json'=>$s_json_saveval, 'timestamp'=>$s_timestamp), $a_queryvars));
		return ($mysqli->affected_rows);
	}

	private function set_accesses() {
		if ($this->exists === FALSE)
				return FALSE;
		$this->access = new accesses(
			explode('|',$this->accesses_string)
		);
	}

	private function load_from_db($password, $crypt_password) {
		global $maindb;
		$username = $this->name;

		if ($password !== NULL)
				$a_users = db_query("SELECT * FROM `[maindb]`.`students` WHERE `username`='[username]' AND `pass`=AES_ENCRYPT('[username]','[password]') AND `disabled`='0'", array("maindb"=>$maindb, "username"=>$username, "password"=>$password));
		else
				$a_users = db_query("SELECT * FROM `[maindb]`.`students` WHERE `username`='[username]' AND `pass`='[crypt_password]' AND `disabled`='0'", array("maindb"=>$maindb, "username"=>$username, "crypt_password"=>$crypt_password));
		if ($a_users === FALSE)
				return NULL;
		if (count($a_users) == 0)
				return NULL;
		$this->id = $a_users[0]['id'];
		$this->accesses_string = $a_users[0]['accesses'];
		$this->email = $a_users[0]['email'];
		return $a_users[0];
	}

	private function load_settings() {
		global $maindb;
		$userid = $this->id;

		$this->a_server_settings = array('session_timeout'=>'15');
		if ($this->name == "guest") {
			return;
		}

		// load server settings
		$a_settings_vars = array("database"=>$maindb, "user_id"=>$userid, "type"=>"server");
		$s_settings_string = "SELECT * FROM `[database]`.`user_settings` WHERE `user_id`='[user_id]'";
		$a_settings = db_query($s_settings_string, $a_settings_vars);
		if (is_array($a_settings) && count($a_settings) > 0)
				$this->a_server_settings = $a_settings[0];
		// load other settings
	}

	/*******************************************************
	 *           S T A T I C   F U N C T I O N S           *
	 ******************************************************/
	
	/**
	 * gets the id of a user by looking up the user by their username
	 * @returns the id, or -1 on failure
	 */
	public static function get_id_by_username($username) {
		global $maindb;
		$a_queryvars = array("database"=>$maindb, "table"=>"students", "username"=>$username, "disabled"=>"0");
		$s_querystring = "SELECT `id` FROM `[database]`.`[table]` WHERE `username`='[username]' AND `disabled`='[disabled]'";
		$a_rows = db_query($s_querystring, $a_queryvars);
		if (count($a_rows) == 0) {
				return -1;
		}
		$id = intval($a_rows[0]['id']);
		return $id;
	}

	/**
	 * retrieves a user by their id
	 * @$i_user_id         integer the id to search for
	 * @$b_ignore_disabled boolean if true, checks the 'disabled' flag on the user's account
	 * @return             object  either a user object or NULL
	 */
	public static function load_user_by_id($i_user_id, $b_ignore_disabled = TRUE) {

		global $maindb;
		
		// check if the user has already been loaded
		$o_user = self::global_user(NULL, NULL, "id", $i_user_id);
		if ($o_user !== NULL) {
				return $o_user;
		}
		
		// load the user
		$s_disabled = ($b_ignore_disabled) ? "AND `disabled`='0'" : "";
		$a_users = db_query("SELECT `username`,`pass` FROM `{$maindb}`.`students` WHERE `id`='[id]' {$s_disabled}", array("id"=>$i_user_id));
		if (is_array($a_users) && count($a_users) > 0) {
				$o_user = new user($a_users[0]['username'], NULL, $a_users[0]['pass']);
				// note: creating a new user registers the user with global_user
		}
		return $o_user;
	}

	/**
	 * Stores, or loads, a user
	 * (think of this function as a basic user manager)
	 * @$a_user   array        The database representation of a user, used to store users
	 * @$s_key    string       The key to search for a user by
	 * @$wt_value weakly typed The value to match to the key
	 * @return    object       Either the user object, or NULL if it can't be found
	 */
	private static function global_user($a_user, $o_user, $s_key = "", $wt_value = NULL) {
		
		static $a_users;
		if (!isset($a_users)) {
				$a_users = array();
		}
		$o_user_retval = NULL;

		// determine if a user is being stored or loaded
		if ($a_user === NULL || $o_user === NULL) {
				
				// return the already loaded user
				for($i = 0; $i < count($a_users); $i++) {
						if ($a_users[$i][0][$s_key] == $wt_value) {
								$o_user_retval = $a_users[$i][1];
								break;
						}
				}
		} else {
				
				// store the user
				$a_users[] = array($a_user, $o_user);
				$o_user_retval = $o_user;
		}

		return $o_user_retval;
	}
}

?>
