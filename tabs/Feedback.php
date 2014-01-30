<?php

require_once(dirname(__FILE__)."/../objects/forum.php");
require_once(dirname(__FILE__)."/../objects/user.php");

function init_feedback_tab() {
	global $o_feedback;
	return '
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Email Bugs/Enhancement Ideas To Developer</div>
    <div id=\'email_developer_container\'>
        <table style=\'font-size:normal;font-weight:normal;text-align:center;\' class=\'centered\'><tr><td>
            <form id=\'email_developer\' style=\'text-align:left;\' action=\'#scroll_to_element\'>
                <label class=\'errors\'></label>
                Subject<br />
                <input type=\'text\' size=\'50\' name=\'email_subject\' /><br />
                Body<br />
                <textarea rows=\'5\' cols=\'50\' name=\'email_body\'></textarea><br />
                <input type=\'hidden\' name=\'command\' value=\'email_developer_bugs\'></input>
                <input type=\'button\' onclick=\'send_ajax_call_from_form("/resources/ajax_calls.php", "email_developer");\' value=\'Send\' /><br />
            </form>
        </td></tr></table>
    </div>
</td></tr></table>'.$o_feedback->drawRecentForumPosts();
}

class feedback_object_type extends forum_object_type {
	function __construct($s_tablename) {
		parent::__construct($s_tablename);
	}
}

global $o_feedback;
$o_feedback = new feedback_object_type("feedback");
$o_feedback->setPostNames("feedback", "feedback", "recent_feedback");

$tab_init_function = 'init_feedback_tab';

?>