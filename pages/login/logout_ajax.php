<?php
require_once(dirname(__FILE__)."/../../resources/common_functions.php");
require_once(dirname(__FILE__)."/../../objects/user.php");

$action = get_post_var("action");
if ($action == "logout") {
		my_session_start();
		logout_session();
		echo "load page[*note*]/pages/login/index.php";
} else {
		echo "alert[*note*]unknown parameters";
}
?>