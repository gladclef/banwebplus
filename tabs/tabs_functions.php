<?php
require_once(dirname(__FILE__)."/../resources/globals.php");
require_once(dirname(__FILE__)."/../resources/common_functions.php");
require_once(dirname(__FILE__)."/../resources/db_query.php");

function draw_tabs() {
	global $maindb;
	global $global_user;
	$a_retval = array();

	$a_tabs = db_query('SELECT * FROM `[maindb]`.`tabs` WHERE `_deleted`=\'0\' ORDER BY `order` ASC', array("maindb"=>$maindb));
	$a_tabs_with_access = array();
	foreach($a_tabs as $a_tab) {
			if($global_user->has_access($a_tab['accesses']))
					$a_tabs_with_access[] = $a_tab;
	}
	$a_retval[] = '<table><tr><td></td><td id="tabs_container" class="centered" style="width:300px;"><table width="100%"><tr>';
	foreach($a_tabs_with_access as $a_tab) {
			$s_tab_name = $a_tab['name'];
			$s_tab_printed_name = $a_tab['printed_name'];
			$s_tab_id = 'tab_id_'.$s_tab_name;
			$s_display = ((int)$a_tab["draw_tab"] == 1) ? "" : "display:none;";
			$a_retval[] = '<td class="tab '.$s_tab_name.'" id="'.$s_tab_id.'" onclick="draw_tab(\''.$s_tab_name.'\')" onmouseover="$(\'#'.$s_tab_id.'\').addClass(\'mouse_hover\');" onmouseout="$(\'#'.$s_tab_id.'\').removeClass(\'mouse_hover\');" style="'.$s_display.'"><input type="hidden" name="tab_non_printed_name" value="'.$s_tab_name.'"></input>'.$s_tab_printed_name.'</td>';
			$a_retval = array_merge($a_retval, draw_tab_include_files($s_tab_name));
	}
	$a_retval[] = '</tr></table></td><td></td></tr>';
	$a_retval[] = '<tr><td colspan="3"><div class="tab_contents centered">';
	$a_retval[] = '<div class="spacer_for_div_contents">&nbsp;</div>';
	foreach($a_tabs_with_access as $a_tab) {
			$s_tab_name = $a_tab['name'];
			$a_retval[] = "\t".'<div class="tab_contents_div centered" id="'.$s_tab_name.'">'.str_replace("\n", "\n\t\t", load_tab_contents($s_tab_name))."\n\t".'</div>';
	}
	$a_retval[] = '</div></td></tr></table>';
	
	return implode("\n", $a_retval);
}

function draw_tab_include_files($s_tab_name) {
	$s_drawval = array();
	$s_lower_name = strToLower($s_tab_name);
	if (file_exists(dirname(__FILE__).'/../js/'.$s_lower_name.'.js'))
			$s_drawval[] = '<script src="/js/'.$s_lower_name.'.js"></script>';
	if (file_exists(dirname(__FILE__).'/../css/'.$s_lower_name.'.css'))
			$s_drawval[] = '<link href="/css/'.$s_lower_name.'.css" rel="stylesheet" type="text/css">';
	return $s_drawval;
}

function load_tab_contents($s_tab_name) {
	global $tab_init_function;

	require_once(dirname(__FILE__)."/".$s_tab_name.".php");
	return $tab_init_function();
}

?>