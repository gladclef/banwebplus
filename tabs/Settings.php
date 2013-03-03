<?php

// $wt_default_value can either be a string (for things like textboxes)
// or an array (for dropdowns, checkboxes, and radiobuttons)
// $s_name_prefix can be blank "" or something similar to setting_
function draw_input($s_type, $s_name_prefix, $s_name, $wt_default_value) {
	if ($s_type == 'textbox') {
			return '<input type=\'textbox\' size=\'20\' name=\''.$s_name_prefix.$s_name.'\' value=\''.$wt_default_value.'\' />';
	}
}

$i_formid_index = 0;
function create_save_preferences_table($table_rows) {
	global $i_formid_index;
	$s_formid = 'form_id'.$i_formid_index;
	$s_retval = '<table class=\'centered\' style=\'text-align:center;\'><tr><td><form id=\''.$s_formid.'\'><label class=\'errors\' />&nbsp</label><table class=\'settings\' cellspacing=\'0\' cellpadding=\'0\'>';
	foreach($table_rows as $a_table_row) {
			$s_title = $a_table_row['title'];
			$s_type = $a_table_row['type'];
			$s_name = $a_table_row['name'];
			$s_default_value = (isset($a_table_row['default'])) ? $a_table_row['default'] : '';
			$s_query = draw_input($s_type, "setting_", $s_name, $s_default_value);
			$s_retval .= '<tr><td class=\'settings query\'>'.$s_title.'</td><td class=\'settings response\'>'.$s_query.'</td></tr>';
	}
	$s_retval .= '<tr><td class=\'settings query\'></td><td class=\'settings response\'>
<input type=\'hidden\' name=\'command\' value=\'update server settings\' />
<input type=\'button\' onclick=\'send_ajax_call_from_form("/resources/ajax_calls.php", "'.$s_formid.'");\' value=\'Save\' /></td></tr>';
	$s_retval .= '</table></form></td></tr></table>';
	return $s_retval;
}

function settings_get_user_settings() {
	global $global_user;
	$a_defaults = array("session_timeout"=>"10[*replace*]0");
	
	$a_retval = array();
	foreach($a_defaults as $k=>$v) {
			$s_value = $global_user->get_server_setting($k);
			if (strpos($v, "[*replace*]") !== FALSE) {
					$a_replace_parts = explode("[*replace*]", $v);
					$s_newval = $a_replace_parts[0];
					$s_replace = $a_replace_parts[1];
					if ($s_replace == $s_value)
							$s_value = $s_newval;
					else if ($s_value == '')
							$s_value = $s_newval;
			} else {
					if ($s_value == '') $s_value = $v;
			}
			$a_retval[$k] = $s_value;
	}
	
	return $a_retval;
}

function init_settings_tab() {
	$a_settings = settings_get_user_settings();

	$server_options = array();
	$server_options[] = array("title"=>"Minutes before timing out (-1 for never, 0 for default)", "type"=>"textbox", "name"=>"session_timeout", "default"=>$a_settings['session_timeout']);
	$server_table = create_save_preferences_table($server_options);
	return '<link href="/tabs/Settings.css" rel="stylesheet" type="text/css">
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Server Configuration</div>
    '.$server_table.'
</td></tr></table>';
}

$tab_init_function = 'init_settings_tab';

?>