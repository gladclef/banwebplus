<?php
require_once(dirname(__FILE__)."/login.php");

if (check_logged_in())
		header('Location: /pages/classes/main.php');
else
		echo draw_login_page(get_post_var('session_expired'));
?>