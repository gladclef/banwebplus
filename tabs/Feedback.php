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
		$this->setAccesses("development.createbugs", "development.deletebugs");
		$this->setRange(0, 10);
	}
	
	/**
	 * Draws the forum objects
	 * NOTE: skeleton function, should be overwritten by the implementation
	 * @$i_datetime_start integer the starting place of the posts to draw
	 * @$i_num_posts      integer the number of posts to draw
	 * @return            string  the html output of the recent forum posts
	 */
	public function drawRecentForumPosts($i_datetime_start = NULL, $i_num_posts = NULL) {

		// initialize some values
		$this->setRange($i_datetime_start, $i_num_posts);
		$s_postname_plural_lc = $this->a_postnames["plural"];
		$s_postname_plural = ucfirst($s_postname_plural_lc);
		$s_retval = "";
		
		// get the header string
		$s_header = "";
		$s_header .= "
<table class='table_title'><tr><td>
    <div class='centered'>Recent {$s_postname_plural}</div>
</td></tr></table>";

		// get the create string
		if ($this->user->has_access($this->s_createaccess)) {
				$s_header .= "
<div class='centered'>
    <form id='create_post_form_{$this->forum_instance}'>
        <input type='hidden' name='command' value='create_post'></input>
        <input type='hidden' name='tablename' value='{$this->s_tablename}'></input>
        <input type='hidden' name='noresponse' value='1'></input>
        <input type='button' onclick='o_forum.create_post({$this->forum_instance});' value='Create New'></input>
    </form>
</div>";
		}
		$s_retval .= $s_header;

		// check for any forum posts
		$a_forum_posts = self::loadRecentPosts();
		if (count($a_forum_posts) == 0) {
				$s_retval .= "
<div class='centered'>
    No recent {$s_postname_plural_lc}
</div>";
				return $s_retval;
		}
		
		// display forum posts
		$s_retval .= "
<div class='centered' style='text-align:left;'>";
		foreach($a_forum_posts as $id=>$a_post) {
				$s_retval .= $this->drawPost($id, $a_post, 0);
		}
		$s_retval .= "
</div>";
		return $s_retval;

	}

	private function drawPost($id, $a_post, $i_post_depth) {
		
		// init some values
		$s_retval = "";
		$s_edit_query = "";
		$s_response_query = "";
		$s_delete_query = "";
		$s_querier_name = $a_post['querier_name'];
		$s_username = $this->user->get_name();
		$s_stylename = $this->a_postnames["stylename"];

		// get the query string
		$s_query = "<span id='post_{$id}_{$this->forum_instance}'>".str_replace(array("\n","\r","\r\n"), "<br />", $a_post['query'])."</span>";

		// get the collapsable wrapper
		$s_wrapper_style = (count($a_post["responses"]) > 0) ? "cursor:pointer;' onclick='o_forum.collapse_wrapper(this);'" : "";
		$s_wrapper_collapsed = (count($a_post["responses"]) > 0 && $i_post_depth == 0) ? "collapsed" : "";
		$s_wrapper_mid_display = ($s_wrapper_collapsed == "") ? "inline-block;" : "none";
		$s_wrapper = "<div style='width:100%; margin:0; padding:0; border:none; display:inline-block; {$s_wrapper_style}' class='forum_wrapper_query {$s_wrapper_collapsed}'>";
		$s_wrapper_mid = "</div><div style='margin:0; padding:0; border:none; display:{$s_wrapper_mid_display};' class='forum_wrapper_rest'>";
		$s_wrapper_end = "</div>";
		
		// get the edit string
		if ($s_username == $s_querier_name) {
				$s_edit_query = " <input id='post_{$id}_edit_button_{$this->forum_instance}' type='button' onclick='o_forum.edit_query(this,{$id},{$this->forum_instance},\"{$this->s_tablename}\");' value='Edit'></input>";
		}

		// get the response string
		if ($this->user->has_access($this->s_createaccess)) {
				$s_respond_query = "
    <form id='respond_post_{$id}_form_{$this->forum_instance}' style='display:inline-block; margin:0;'>
        <input type='button' onclick='o_forum.create_response({$id},{$this->forum_instance});' value='Respond'></input>
        <input type='hidden' name='command' value='respond_post'></input>
        <input type='hidden' name='tablename' value='{$this->s_tablename}'></input>
        <input type='hidden' name='post_id' value='{$id}'></input>
    </form>";
		}
		
		// get the delete string
		if ($this->user->has_access($this->s_deleteaccess) || $s_querier_name == $s_username) {
				$s_delete_query = "
    <form id='delete_post_{$id}_form_{$this->forum_instance}' style='display:inline-block; margin:0;'>
        <input type='button' onclick='o_forum.delete_post({$id},{$this->forum_instance});' value='Delete'></input>
        <input type='hidden' name='command' value='delete_post'></input>
        <input type='hidden' name='tablename' value='{$this->s_tablename}'></input>
        <input type='hidden' name='post_id' value='{$id}'></input>
    </form>";
		}

		// get the responses strings
		$s_responses = "";
		if (count($a_post['responses']) > 0) {
				foreach($a_post['responses'] as $response_id=>$a_response) {
						$s_responses .= $this->drawPost($response_id, $a_response, $i_post_depth+1);
				}
		}
		
		// create an output
		$i_min_post_depth = min($i_post_depth, 4);
		$s_time_color = ($i_post_depth < 2) ? "gray" : "lightgray";
		$s_timedisplay = "<span style='color:{$s_time_color}'>Submitted ".date("F j, Y", strtotime($a_post['datetime']))." at ".date("g:ia", strtotime($a_post['datetime']))."</span>";
		$s_retval .= "
    <div class='{$s_stylename} depth_{$i_min_post_depth} {$s_wrapper_collapsed}'>
        {$s_wrapper}<span style='font-weight:bold'>{$s_querier_name}</span>: {$s_query}{$s_wrapper_mid}<br />{$s_edit_query}{$s_respond_query}{$s_delete_query}<br />{$s_timedisplay}<br />{$s_responses}{$s_wrapper_end}
    </div>";
		return $s_retval;
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