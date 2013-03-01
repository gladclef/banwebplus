<?php
require_once(dirname(__FILE__)."/../pages/login/login.php");

if (!check_logged_in()) {
		header('Location: /index.php');	
}
?>