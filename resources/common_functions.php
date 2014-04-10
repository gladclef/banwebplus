<?php
require_once(dirname(__FILE__)."/globals.php");

function get_post_var($postname, $s_default = '') {
	return isset($_POST[$postname]) ? $_POST[$postname] : $s_default;
}

function my_session_start() {
	global $session_started;
	if ($session_started === FALSE) {
			$session_started = TRUE;
			session_start();
	}
}

function login_session($o_user) {
	my_session_start();
	$_SESSION['username'] = $o_user->get_name();
	$_SESSION['last_activity'] = time();
	$_SESSION['crypt_password'] = urlencode($o_user->get_crypt_password());
	$_SESSION['loggedin'] = 1;
	$_SESSION['time_before_page_expires'] = (int)$o_user->get_server_setting('session_timeout');
	remove_timestamp_on_saves();
}

// removes the timestamp on all semesters so that new
// incoming data can be written (see resources/ajax_calls.php)
function remove_timestamp_on_saves() {
	global $maindb;
	global $global_user;
	$user_id = $global_user->get_id();
	
	$a_semester_classes = db_query("SELECT `id` FROM `[maindb]`.`[table]` WHERE `user_id`='[user_id]'", array("maindb"=>$maindb, "table"=>"semester_classes", "user_id"=>$user_id));
	if ($a_semester_classes === FALSE)
			return;
	foreach($a_semester_classes as $a_semester_class)
			db_query("UPDATE `[maindb]`.`[table]` SET `time_submitted`='0000-00-00 00:00:00' WHERE `id`='[id]'", array("maindb"=>$maindb, "table"=>"semester_classes", "id"=>$a_semester_class['id']));
}

function logout_session() {
	my_session_start();
	if (isset($_SESSION)) {
			foreach($_SESSION as $k=>$v) {
					$_SESSION[$k] = NULL;
					unset($_SESSION[$k]);
			}
	}
}

function dont_check_session_expired() {
	global $global_user;
	if (!is_object($global_user)) {
			return "";
	}
	if (!method_exists($global_user, "get_server_setting")) {
			return "";
	}
	if ($global_user->get_server_setting("session_timeout") == "-1") {
			return "<script type='text/javascript'>dont_check_session_expired = true;</script>";
	}
	return "";
}

function draw_page_head($outside_content = '') {
	global $global_path_to_jquery;
	$a_page = array();
	$a_page[] = "<html>";
	$a_page[] = "<head>";
	$a_page[] = "<link href='/css/main.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/login_logout.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/popup_notifications.css' rel='stylesheet' type='text/css'>";
	$a_page[] = "<link href='/css/calendar.css' rel='stylesheet' type='text/css'>";
	$a_page[] = '<script src="'.$global_path_to_jquery.'"></script>';
	$a_page[] = '<script src="/js/common_functions.js"></script>';
	$a_page[] = '<script src="/js/ajax.js"></script>';
	$a_page[] = '<script src="/js/login_logout.js"></script>';
	$a_page[] = '<script src="/js/tab_custom.js"></script>';
	$a_page[] = '<script src="/js/main.js"></script>';
	$a_page[] = '<script src="/js/storage.js"></script>';
	$a_page[] = '<script src="/js/popup_notifications.js"></script>';
	$a_page[] = '<script src="/js/calendar_preview.js"></script>';
	$a_page[] = '<script src="/js/feedback.js"></script>';
	$a_page[] = dont_check_session_expired();
	$a_page[] = "</head>";
	$a_page[] = "<body>";
	$a_page[] = "<table class='main_page_container'><tr><td class='centered'>";
	$a_page[] = "<table class='main_page_content'><tr><td>";
	$a_page[] = $outside_content."</td></tr><tr><td>";
	$a_page[] = "<table style='border:2px solid black;border-radius:5px;padding:15px 30px;margin:0 auto;background-color:#fff;'><tr><td>";
	return implode("\n", $a_page);
}

function draw_page_foot() {
	$a_page = array();
	$a_page[] = "</td></tr></table>";
	$a_page[] = "</td></tr></table>";
	$a_page[] = "</td></tr></table>";
	$a_page[] = "</body>";
	$a_page[] = "<script type='text/javascript'>set_body_min_height();</script>";
	$a_page[] = "</html>";
	return implode("\n", $a_page);
}

function manage_output($s_output) {
	
	// insert the latest datetime stamp into each javascript link
	$parts_explode = "<script";
	$a_parts = explode($parts_explode, $s_output);
	for ($i = 0; $i < count($a_parts); $i++) {
			$mid_explode = "</script";
			$a_mid = explode($mid_explode, $a_parts[$i]);
			$mid_index = 0;
			$s_mid = $a_mid[$mid_index];
			$js_pos = stripos($s_mid, ".js");
			$moddatetime = "";
			if ($js_pos !== FALSE) {
					$js_string = substr($s_mid, 0, $js_pos+3);
					$js_rest = substr($s_mid, $js_pos+3);
					$single_pos = (int)strrpos($js_string, "'");
					$double_pos = (int)strrpos($js_string, '"');
					$js_substr = substr($js_string, max($single_pos, $double_pos)+1);
					$modtime = filemtime(dirname(__FILE__)."/../{$js_substr}");
					$moddatetime = urlencode(date("Y-m-d H:i:s"));
					$a_mid[$mid_index] = "{$js_string}?{$moddatetime}{$js_rest}";
			}
			$a_parts[$i] = implode($mid_explode, $a_mid);
	}
	$s_output = implode($parts_explode, $a_parts);

	return $s_output;
}

?>