<?php

require_once(dirname(__FILE__)."/../resources/globals.php");

class accesses {
	private $access_list = array();

	// should be passed a list of accesses
	// eg: array('accesses', 'users.create')
	public function __construct($a_accesses) {
		$a_all_accesses = $this->build_accesses();
		$this->access_list = $this->build_access_list($a_accesses, $a_all_accesses);
	}

	public function has_access($s_access) {
		return in_array($s_access, $this->access_list);
	}

	private function build_access_list($a_user_accesses, $a_source_accesses) {
		$a_retval = array();
		foreach($a_source_accesses as $s_source_access) {
				foreach($a_user_accesses as $s_user_access) {
						if ($s_user_access == '')
								continue;
						if (strpos($s_source_access, $s_user_access) === 0) {
								$a_retval[] = $s_source_access;
								break;
						}
				}
		}
		return $a_retval;
	}

	private function build_accesses() {
		global $maindb;
		
		$a_accesses = db_query("SELECT * FROM `[maindb]`.`accesses`",
							   array("maindb"=>$maindb));
		if ($a_accesses == FALSE || count($a_accesses) == 0)
				return array();
		
		// go through and build all access levels
		$a_dyn_prog_access = array(); // (each entry an "level_originalname"=>array(level, name, originalname))
		for ($i_level = 1; $i_level < 999; $i_level++) {
				$a_curr_level = $this->find_accesses_at_level($a_accesses, $i_level);
				$a_prev_level = $this->find_accesses_at_level($a_accesses, $i_level-1);
				if (count($a_curr_level) == 0)
						break;

				foreach($a_curr_level as $a_access) {
						$parent = $a_access['parent'];
						if($parent == '') {
								$name = $a_access['name'];
								$originalname = $name;
								$a_dyn_prog_access[$i_level.'_'.$originalname] = array('level'=>$i_level, 'name'=>$name, 'originalname'=>$originalname);
						} else {
								$originalname = $a_access['name'];
								$name = $a_dyn_prog_access[($i_level-1).'_'.$parent]['name'].'.'.$originalname;
								$a_dyn_prog_access[$i_level.'_'.$originalname] = array('level'=>$i_level, 'name'=>$name, 'originalname'=>$originalname);
						}
				}
		}

		// get the retval
		$a_retval = array();
		foreach($a_dyn_prog_access as $a_access) {
				$a_retval[] = $a_access['name'];
		}
		return $a_retval;
	}

	private function find_accesses_at_level($a_accesses, $i_level) {
		$a_retval = array();
		foreach($a_accesses as $a_access)
				if ((int)$a_access['level'] == $i_level)
						$a_retval[] = $a_access;
		return $a_retval;
	}
}

?>