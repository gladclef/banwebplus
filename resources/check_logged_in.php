<?php
require_once(dirname(__FILE__)."/../pages/login/login.php");

if (!check_logged_in()) {
		logout_session();
		header('Location: /pages/login/index.php');	
}
?>