<?php

require_once(dirname(__FILE__)."/../objects/user.php");

function init_feedback_tab() {
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
</td></tr></table>'.feedbackTab::recentFeedback();
}

class feedbackTab {
	public static function recentFeedback() {
		
		global $global_user;
		$username = $global_user->get_name();
		
		$s_header = "";
		$s_header .= '
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Recent Feedback</div>
</td></tr></table>';
		if ($global_user->has_access("createfeedback")) {
				$s_header .= "
<div class='centered'>
    <form id='create_feedback_form'>
        <input type='hidden' name='command' value='create_feedback'>
        <input type='button' onclick='o_feedback.create_feedback();' value='Create New'>
    </form>
</div>";
		}
		$s_retval = "";
		$s_retval .= $s_header;

		// check for any feedbacks
		$a_feedbacks = self::loadRecentFeedbacks();
		if (count($a_feedbacks) == 0) {
				$s_retval .= "
<div class='centered'>
    No recent feedback
</div>";
				return $s_retval;
		}
		
		// display feedbacks
		$s_retval .= "
<div class='centered'>";
		foreach($a_feedbacks as $id=>$a_feedback) {
				$s_query = "<span id='feedback_{$id}'>".str_replace(array("\n","\r","\r\n"), "<br />", $a_feedback['query'])."</span>";
				$s_edit_query = "";
				if ($username == $a_feedback['querier_name']) {
						$s_edit_query = " <input id='feedback_{$id}_edit_button' type='button' onclick='o_feedback.edit_query(this,{$id});' value='Edit'></input>";
				}
				$s_response = "";
				$s_edit_response = "";
				if (count($a_feedback['responses']) > 0) {
						$response_string = $a_feedback['responses'][0][1];
						$response_id = $a_feedback['responses'][0][2];
						$s_response = "<span id='feedback_{$response_id}'>".str_replace(array("\n","\r","\r\n"), "<br />", $response_string)."</span>";
						if ($username == $a_feedback['querier_name']) {
								$s_edit_response = " <input id='feedback_{$response_id}_edit_button' type='button' onclick='o_feedback.edit_query(this,{$response_id});' value='Edit'></input>";
						}
				}
				$s_timedisplay = "<span style='color:gray'>Submitted ".date("F j, Y", strtotime($a_feedback['datetime']))." at ".date("g:ia", strtotime($a_feedback['datetime']))."</span>";
				$s_retval .= "
    <div class='recent_feedback'>
        <span style='font-weight:bold'>Q</span>: {$s_query}{$s_edit_query}<br /><br /><span style='font-weight:bold;'>A</span>: {$s_response}{$s_edit_response}<br />{$s_timedisplay}
    </div>";
				if ($global_user->has_access("deletefeedback") || $a_feedback["querier_name"] == $global_user->get_name()) {
						$s_retval .= "
    <div class='centered'>
        <form id='delete_feedback_{$id}_form'>
            <input type='button' onclick='o_feedback.delete_feedback({$id});' value='Delete'></input>
            <input type='hidden' name='command' value='delete_feedback'></input>
            <input type='hidden' name='feedback_id' value='{$id}'></input>
        </form>
    </div>";
				}
		}
		$s_retval .= "
</div>";
		return $s_retval;
	}

	/**
	 * Loads feedback from the database to display on the feedback tab.
	 * @$t_since timestamp the earliest date to start loading feedback from, defaults to "0000-00-00 00:00:00"
	 * @$i_start integer   the first feedback to load (the beggining of the range)
	 * @$i_end   integer   the last feedback to load (the end of the range)
	 * @return   array     An array of feedbacks, in the form array(feedback id=>array("query"=>string, "responses"=>array(array(username,response,id),...), "datetime"=>integer, "querier_name"=>username), ...)
	 */
	public static function loadRecentFeedbacks($t_since = NULL, $i_start = 0, $i_end = 5) {
		
		global $maindb;

		// default the starttime
		if ($t_since === NULL) {
				$t_since = "0000-00-00 00:00:00";
		}
		
		// load feedbacks from the database
		$a_feedbacks = db_query("SELECT * FROM `{$maindb}`.`feedback` WHERE `datetime`>'[starttime]' AND `is_response`='0' AND `deleted`='0' ORDER BY `datetime` DESC LIMIT [start],[end]", array("starttime"=>$t_since, "start"=>$i_start, "end"=>$i_end));
		if (!is_array($a_feedbacks) || count($a_feedbacks) == 0) {
				return array();
		}
		
		// index by id and add the username/responses fields
		$a_feedbacks_new = array();
		for($i = 0; $i < count($a_feedbacks); $i++) {
				$s_username = self::getUsernameForId($a_feedbacks[$i]['userid']);
				$a_feedbacks_new[$a_feedbacks[$i]['id']] = array_merge($a_feedbacks[$i], array("responses"=>array(), "querier_name"=>$s_username));
		}
		$a_feedbacks = $a_feedbacks_new;
		unset($a_feedbacks_new);

		// load responses from the database
		$a_feedback_ids = array();
		foreach($a_feedbacks as $a_feedback) {
				$a_feedback_ids[] = mysql_real_escape_string($a_feedback['id']);
		}
		$s_feedback_ids = "('".implode("','", $a_feedback_ids)."')";
		$a_responses = db_query("SELECT * FROM `{$maindb}`.`feedback` WHERE `is_response`='1' AND `original_feedback_id` IN {$s_feedback_ids}");
		for($j = 0; $j < count($a_responses); $j++) {
				if (!isset($a_feedbacks[$a_responses[$j]['original_feedback_id']])) {
						continue;
				}
				$s_username = self::getUsernameForId($a_responses[$j]['userid']);
				$s_query = $a_responses[$j]['query'];
				$i_id = $a_responses[$j]['id'];
				$a_feedbacks[$a_responses[$j]['original_feedback_id']]['responses'][] = array($s_username, $s_query, $i_id);
		}
		
		return $a_feedbacks;
	}

	/**
	 * updates feedback entries if the user has the proper access
	 * @$s_feedback_id      string the string representation of the feedback id
	 * @$s_new_query_string string the new query string to insert into the database
	 * @return              string one of "alert[*note*]message[*command*]reset old value[*note*]" on error or "" on success
	 */
	public static function handelEditFeedbackAJAX($s_feedback_id, $s_new_query_string) {
		global $global_user;
		global $maindb;

		// try and find the note
		$id = (int)$s_feedback_id;
		$a_feedbacks = db_query("SELECT * FROM `{$maindb}`.`feedback` WHERE `id`='{$id}'");
		if (!is_array($a_feedbacks) || count($a_feedbacks) == 0) {
				return "alert[*note*]Feedback {$id} not found. Value not saved.[*command*]reset old values[*note*]";
		}
		if ($a_feedbacks[0]["userid"] != $global_user->get_id()) {
				return "alert[*note*]Incorrect permissions. Value not saved.[*command*]reset old values[*note*]";
		}
		
		// try and update the note
		$query = db_query("UPDATE `{$maindb}`.`feedback` SET `query`='[query]' WHERE `id`='[id]'", array("id"=>$id, "query"=>$s_new_query_string));
		if ($query === FALSE) {
				return "alert[*note*]Failed to update database. Value not saved.[*command*]reset old values[*note*]";
		}
		return "";
	}

	/**
	 * creates a new feedback and response
	 * @return strong one of "alert[*note*]message" on error or "reload page[*note*]" on success
	 */
	public static function handelCreateFeedbackAJAX() {
		global $maindb;
		global $global_user;
		
		// check if the user has permission
		if (!$global_user->has_access("createfeedback")) {
				return "alert[*note*]Incorrect permissions";
		}

		// create the new feedback
		$a_insert_feedback = array("userid"=>$global_user->get_id(), "datetime"=>date("Y-m-d H:i:s"));
		$s_insert_feedback = array_to_insert_clause($a_insert_feedback);
		$query = db_query("INSERT INTO `{$maindb}`.`feedback` {$s_insert_feedback}", $a_insert_feedback);
		if ($query === FALSE) {
				return "alert[*note*]Failed to insert into database";
		}
		$a_insert_response = array("userid"=>$global_user->get_id(), "datetime"=>date("Y-m-d H:i:s"), "is_response"=>1, "original_feedback_id"=>mysql_insert_id());
		$s_insert_response = array_to_insert_clause($a_insert_response);
		$query = db_query("INSERT INTO `{$maindb}`.`feedback` {$s_insert_response}", $a_insert_response);
		return "reload page[*note*]";
	}

	/**
	 * Used to delete a feedback via ajax
	 * Marks the feedback as "deleted=1"
	 * @$feedback_id integer the id of the feedback
	 */
	public static function handelDeleteFeedbackAJAX($feedback_id) {
		global $maindb;
		global $global_user;
		
		// check that the user has permission
		if (!$global_user->has_access("createfeedback.deletefeedback")) {
				return "alert[*note*]Incorrect permission";
		}
		
		// try and delete the feedback
		$query = db_query("UPDATE `{$maindb}`.`feedback` SET `deleted`='1' WHERE `id`='[id]'", array("id"=>$feedback_id));
		if ($query === FALSE || mysql_affected_rows() == 0) {
				return "alert[*note*]Failed to update database";
		}
		return "reload page[*note*]";
	}

	/**
	 * loads a user and returns their username
	 * @$i_user_id integer the user id to search for
	 * @return     string  the user's username
	 */
	public static function getUsernameForId($i_user_id) {

		// check if this user is one of the null users
		static $a_null_users;
		if (!isset($a_null_users)) {
				$a_null_users = array();
		}
		if (isset($a_null_users[$i_user_id])) {
				return "unknown user";
		}
		
		// load the user and get the username
		$o_user = user::load_user_by_id($i_user_id);
		if ($o_user === NULL) {
				$a_null_users[$i_user_id] = TRUE;
				return "unknown user";
		}
		return $o_user->get_name();
	}
}

$tab_init_function = 'init_feedback_tab';

?>