<?php

require_once(dirname(__FILE__)."/pages/install/install.php");

global $o_project_installer;
if ($o_project_installer->check_installed()) {
	require_once(dirname(__FILE__)."/pages/login/index.php");
} else {
	require_once(dirname(__FILE__)."/resources/common_functions.php");
	header("Location: " . dirname(curPageURL()) . "/help.php");
}
?>