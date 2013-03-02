<?php

function init_classes_tab() {
	return '<select id=\'subject_selector\' onchange=\'draw_course_table();\'></select>
<input id="add_subject_button" type="button" onclick="add_extra_subject(this);" value="Add Subject" />
<input id="add_subject_all_button" type="button" onclick="add_extra_subject_all();" value="All" /><br />
<div id=\'classes_content\'>&nbsp;</div>';
}

$tab_init_function = 'init_classes_tab';

?>