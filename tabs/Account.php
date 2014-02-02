<?php

function init_account() {
	$section_head = "<div class='account_management_section'>
        <div class='title'>__TITLE__</div>
        <div class='body'>__BODY__</div>";
	$section_footer = "</div>";
	$section_break = "{$section_footer}
    {$section_head}";
	
	return "<table class='table_title'><tr><td>
    <div class='centered'>Account Management</div>
</td></tr></table>
<div class='centered' style='text-align:left;'>
    ".str_replace("__TITLE__", "Change Password", $section_head)."
    ".str_replace("__TITLE__", "Change Username", $section_break)."
    ".str_replace("__TITLE__", "Disable Account", $section_break)."
    {$section_footer}
</div>
";
}

$tab_init_function = 'init_account';

?>