<?php

require_once(dirname(__FILE__)."/../objects/forum.php");
require_once(dirname(__FILE__)."/../objects/user.php");

function init_feedback_tab() {
	global $o_feedback;
	global $o_bugtracker;
	$s_feedback = $o_feedback->drawRecentForumPosts();
	$s_bugtracker = (isset($o_bugtracker)) ? $o_bugtracker->drawRecentForumPosts() : "";
	$s_retval = "
<table class='table_title'><tr><td>
    <div class='centered'>Email Bugs/Enhancement Ideas To Developer</div>
    <div id='email_developer_container'>
        <table style='font-size:normal;font-weight:normal;text-align:center;' class='centered'><tr><td>
            <form id='email_developer' style='text-align:left;' action='#scroll_to_element'>
                <label class='errors'></label>
                Subject<br />
                <input type='text' size='50' name='email_subject' /><br />
                Body<br />
                <textarea rows='5' cols='50' name='email_body'></textarea><br />
                <input type='hidden' name='command' value='email_developer_bugs'></input>
                <input type='button' onclick='send_ajax_call_from_form(\"/resources/ajax_calls.php\", \"email_developer\");' value='Send' /><br />
            </form>
        </td></tr></table>
    </div>
</td></tr></table>".$s_feedback.$s_bugtracker;
	return $s_retval;
}

class feedback_object_type extends forum_object_type {
	function __construct() {
		parent::__construct("feedback");
		$this->setPostNames("feedback", "feedback", "recent_feedback");
	}
}

class bugtracker_object_type extends forum_object_type {
	function __construct() {
		parent::__construct("buglog");
		$this->setPostNames("bug", "bugs", "recent_bugs");
		$this->setRange(0, 10);
	}
}

global $o_feedback;
$o_feedback = new feedback_object_type();

global $global_user;
if ($global_user->has_access("development.bugtracker")) {
		global $o_bugtracker;
		$o_bugtracker = new bugtracker_object_type();
}

$tab_init_function = 'init_feedback_tab';

?>