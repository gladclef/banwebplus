<?php

require_once(dirname(__FILE__)."/../resources/globals.php");

function init_users_tab() {
	return '<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Choose an User</div>
    <div id=\'user_list_content_container\'>&nbsp;</div>
</td></tr></table>
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Choose an Action</div>
    <table class=\'centered\'>
        <tr>
            <td id=\'user_action_selector_container\'>
                '.create_user_action_buttons().'
            </td>
        </tr>
        <tr>
            <td id=\'user_action_form_container\'>
            </td>
        </tr>
    </div>
</td></tr></table>';
}

function create_user_action_buttons() {
	global $global_user;

	$a_actions = array(
		array("name"=>"Create New", "access"=>"users.create", "onclick"=>''),
		array("name"=>"Modify Accesses", "access"=>"users.modify|accesses", "onclick"=>''),
		array("name"=>"Reset Password", "access"=>"users.modify.password", "onclick"=>''),
		array("name"=>"Delete", "access"=>"users.delete", "onclick"=>'')
	);

	$a_retval = array();
	foreach($a_actions as $a_action) {
			$a_accesses = explode('|', $a_action['access']);
			$b_has_access = TRUE;
			foreach($a_accesses as $s_access)
					if (!$global_user->has_access($s_access))
							$b_has_access = FALSE;
			if (!$b_has_access)
					continue;
			$a_retval[] = '<input type="button" value="'.$a_action['name'].'" onclick="'.$a_action['onclick'].'">';
	}

	return implode('
                ', $a_retval);
}

$tab_init_function = 'init_users_tab';

?>