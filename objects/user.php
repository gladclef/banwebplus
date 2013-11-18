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
		$this->exists = $this->load_from_db($password, $crypt_password);
		if ($this->exists) {
				$this->set_accesses();
				$this->load_settings();
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

	public function update_settings($s_type, $a_settings) {
		global $maindb;
		global $settings_table;
		if ($this->check_is_guest())
				return 'error|settings can\'t be saved as a guest';

		$query_string = 'SELECT `id` FROM `[database]`.`[table]` WHERE '.array_to_where_clause($a_settings).' AND `user_id`=\'[user_id]\' AND `type`=\'[type]\'';
		$query_vars = array("database"=>$maindb, "table"=>$settings_table, "user_id"=>$this->id, "type"=>$s_type);
		$a_exists = db_query($query_string, $query_vars);
		if(count($a_exists) > 0)
				return "print success[*note*]Settings already saved";
		
		$a_current = db_query("SELECT * FROM `[database]`.`[table]` WHERE `user_id`='[user_id]' AND `type`='server'", $query_vars);
		create_row_if_not_existing($query_vars);
		$query_string = 'UPDATE `[database]`.`[table]` SET '.array_to_update_clause($a_settings).' WHERE `user_id`=\'[user_id]\' AND `type`=\'[type]\'';
		db_query($query_string, array_merge($a_settings, $query_vars));
		if (mysql_affected_rows() == 0) {
				return "print error[*note*]Failed to save settings";
		} else {
				$this->updateSpecialSettings($a_settings, $a_current[0]);
				return "print success[*note*]Settings saved successfully. Next time you log in these settings will take effect.";
		}
	}

	public function get_crypt_password() {
		global $maindb;
		global $userdb;
		if (!$this->exists)
				return '';
		$a_users = db_query("SELECT `pass` FROM `[maindb]`.`[userdb]` WHERE `username`='[username]'", array("maindb"=>$maindb, "userdb"=>$userdb, "username"=>$this->name));
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
		global $settings_table;
		
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
				if (!is_numeric($crn))
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
		$s_querystring = "SELECT * FROM `[database]`.`[tablename]` WHERE `year`='[year]' AND `semester`='[semester]' AND `timestamp`>'[timestamp]'";
		$a_query = db_query($s_querystring, $a_queryvars);
		if (is_array($a_query) && count($a_query) > 0)
				return -1;
		return $this->save_user_data($s_year, $s_semester, $s_tablename, $s_json_saveval, $s_timestamp);
	}

	private function save_user_data($s_year, $s_semester, $s_tablename, $s_json_saveval, $s_timestamp) {
		global $maindb;

		$a_queryvars = array("table"=>$s_tablename, "year"=>$s_year, "semester"=>$s_semester, "user_id"=>$this->get_id(), "database"=>$maindb);
		$s_querystring = "UPDATE `[database]`.`[table]` SET `json`='[json]',`time_submitted`='[timestamp]' WHERE `year`='[year]' AND `semester`='[semester]' AND `user_id`='[user_id]'";
		create_row_if_not_existing($a_queryvars);
		db_query($s_querystring, array_merge(array('json'=>$s_json_saveval, 'timestamp'=>$s_timestamp), $a_queryvars));
		return (mysql_affected_rows());
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
		global $userdb;
		$username = $this->name;

		if ($password !== NULL)
				$a_users = db_query("SELECT * FROM `[maindb]`.`[userdb]` WHERE `username`='[username]' AND `pass`=AES_ENCRYPT('[username]','[password]')", array("maindb"=>$maindb, "userdb"=>$userdb, "username"=>$username, "password"=>$password));
		else
				$a_users = db_query("SELECT * FROM `[maindb]`.`[userdb]` WHERE `username`='[username]' AND `pass`='[crypt_password]'", array("maindb"=>$maindb, "userdb"=>$userdb, "username"=>$username, "crypt_password"=>$crypt_password));
		if ($a_users === FALSE)
				return FALSE;
		if (count($a_users) == 0)
				return FALSE;
		$this->id = $a_users[0]['id'];
		$this->accesses_string = $a_users[0]['accesses'];
		$this->email = $a_users[0]['email'];
		return TRUE;
	}

	private function load_settings() {
		global $maindb;
		global $settings_table;
		$userid = $this->id;

		if ($this->name == "guest") {
				$this->a_server_settings = array('session_timeout'=>'10');
				return;
		}

		// load server settings
		$a_settings_vars = array("database"=>$maindb, "table"=>$settings_table, "user_id"=>$userid, "type"=>"server");
		$s_settings_string = "SELECT * FROM `[database]`.`[table]` WHERE `user_id`='[user_id]'";
		$a_settings = db_query($s_settings_string, $a_settings_vars);
		if (is_array($a_settings) && count($a_settings) > 0)
				$this->a_server_settings = $a_settings[0];
		// load other settings
	}
}

?>
