<!DOCTYPE html>
<?php
require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/install.php");

if ($o_project_installer->check_arguments() &&
	$o_project_installer->check_install_database() &&
	$o_project_installer->check_create_users()) {
	if (check_logged_in()) {
		header('Location: /pages/classes/main.php');
	} else {
		echo manage_output(draw_login_page(get_post_var('session_expired')));
	}
}

?>