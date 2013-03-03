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
		if ($this->check_is_guest())
				return 'error|settings can\'t be saved as a guest';
		
		$query_string = 'SELECT `id` FROM `[database]`.`[table]` WHERE '.array_to_where_clause($a_settings).' AND `user_id`=\'[user_id]\' AND `type`=\'[type]\'';
		$query_vars = array("database"=>$maindb, "table"=>"user_settings", "user_id"=>$this->id, "type"=>$s_type);
		$a_exists = db_query($query_string, $query_vars);
		if(count($a_exists) > 0)
				return "print success[*note*]Settings already saved";
		$query_string = 'UPDATE `[database]`.`[table]` SET '.array_to_where_clause($a_settings).' WHERE `user_id`=\'[user_id]\' AND `type`=\'[type]\'';
		db_query($query_string, $query_vars);
		if (mysql_affected_rows() == 0)
				return "print error[*note*]Failed to save settings";
		else
				return "print success[*note*]Settings saved successfully. Next time you log in these settings will take effect.";
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

	/*********************************************************************
	 *                   P R I V A T E   F U N C T I O N S               *
	 *********************************************************************/

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
		return TRUE;
	}

	private function load_settings() {
		global $maindb;
		global $settings_table;
		$userid = $this->id;
		
		// load server settings
		$a_settings_vars = array("database"=>$maindb, "table"=>$settings_table, "user_id"=>$userid, "type"=>"server");
		$s_settings_string = "SELECT * FROM `[database]`.`[table]` WHERE `user_id`='[user_id]'";
		$a_settings = db_query($s_settings_string, $a_settings_vars);
		$this->a_server_settings = $a_settings[0];
		// load other settings
	}
}

?>