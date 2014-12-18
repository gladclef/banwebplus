<?php

class command {
	public $command;
	public $action;

	function __construct($s_command, $s_action) {
		$this->command = $s_command;
		$this->action = $s_action;
	}
}

?>