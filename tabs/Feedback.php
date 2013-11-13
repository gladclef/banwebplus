<?php

function init_feedback_tab() {
	return '<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Email Bugs/Enhancement Ideas To Developer</div>
    <div id=\'email_developer_container\'><table style=\'font-size:normal;font-weight:normal;text-align:center;\' class=\'centered\'><tr><td>
        <form id=\'email_developer\' style=\'text-align:left;\'>
        <label class=\'errors\'></label>
        Subject<br />
        <input type=\'text\' size=\'50\' name=\'email_subject\' /><br />
        Body<br />
        <textarea rows=\'5\' cols=\'50\' name=\'email_body\'></textarea><br />
        <input type=\'hidden\' name=\'command\' value=\'email_developer_bugs\'></input>
        <input type=\'button\' onclick=\'send_ajax_call_from_form("/resources/ajax_calls.php", "email_developer");\' value=\'Send\' /><br />
        </form>
    </td></tr></table></div>
</td></tr></table>'.feedbackTab::recentFeedback();
}

class feedbackTab {
	public static function recentFeedback() {
		$s_header = '<table class=\'table_title\'><tr><td>
<div class=\'centered\'>Recent Feedback</div>
</td></tr></table>';
		$a_feedbacks = array(
			array(
				'query'=>"Is there a feature for adding recitation times? Like the recitations for physics or chemistry?",
				'response'=>"Thanks for the feedback!

There does not currently exist a way to add in recitation times but there are plans to include one of the following (or both) features:
* Better scraping of NMT's banweb site to account for recitation classes
* A way for users to create \"custom\" classes with their own times, descriptions, and places, viewable only by the user that created the class

Hopefully one of these features will be coming soon! Cheers!",
				'datetime'=>"2013-12-01 23:08:00"
			)
		);
		
		$s_retval = "";
		$s_retval .= $s_header;
		$s_retval .= "<div class='centered'>";
		foreach($a_feedbacks as $a_feedback) {
				$s_query = str_replace(array("\n","\r","\r\n"), "<br />", $a_feedback['query']);
				$s_response = str_replace(array("\n","\r","\r\n"), "<br />", $a_feedback['response']);
				$s_timedisplay = "<span style='color:gray'>Submitted ".date("F j, Y", strtotime($a_feedback['datetime']))." at ".date("g:ia", strtotime($a_feedback['datetime']))."</span>";
				$s_retval .= "<div class='recent_feedback'><span style='font-weight:bold'>Q</span>: {$s_query}<br /><br /><span style='font-weight:bold;'>A</span>: {$s_response}<br />{$s_timedisplay}</div>";
		}
		return $s_retval;
	}
}

$tab_init_function = 'init_feedback_tab';

?>