<?php
$maindb = "banwebplus";
$settings_table = "user_settings";

$global_user = NULL;
$global_opened_db = FALSE;
$session_started = FALSE;
$global_path_to_jquery = "/jquery/js/jquery-1.9.0.js";
$tab_init_function = NULL; // redefined with each tab file required

require_once(dirname(__FILE__)."/common_functions.php");
require_once(dirname(__FILE__)."/../objects/user.php");

?>