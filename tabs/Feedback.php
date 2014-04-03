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
		global $maindb;
		$s_retval = "";
		$s_edit_query = "";
		$s_response_query = "";
		$s_delete_query = "";
		$s_querier_name = $a_post['querier_name'];
		$s_username = $this->user->get_name();
		$s_stylename = $this->a_postnames["stylename"];
		$s_container_id = "bug_container_{$id}";

		// get the status class
		$s_status_class = "status_".strtolower(str_replace(" ", "_", $a_post["status"]));
		if ($i_post_depth > 0) {
				$s_status_class = "";
		}
		
		// get the collapsable wrapper
		$s_wrapper_style = "cursor:pointer;' onclick='o_forum.collapse_wrapper(this);'";
		$s_wrapper_collapsed = ($i_post_depth == 0) ? "collapsed" : "";
		$s_wrapper_noresponses = (count($a_post["responses"]) == 0) ? "noresponses" : "";
		$s_wrapper_mid_display = ($s_wrapper_collapsed == "") ? "inline-block;" : "none";
		$s_wrapper = "<div style='width:100%; margin:0; padding:0; border:none; display:inline-block; {$s_wrapper_style}' class='forum_wrapper_query {$s_wrapper_collapsed}'>";
		$s_wrapper_mid = "</div><div style='margin:0; padding:0; border:none; display:{$s_wrapper_mid_display};' class='forum_wrapper_rest'>";
		$s_wrapper_end = "</div>";
		
		// get the owner string
		$s_owner = $this->getUsernameForId($a_post["owner_userid"]);
		$s_owner = "Owner: <span style='font-weight:bold;' id='bug_owner_{$id}'>{$s_owner}</span>";
		if ($this->user->has_access($this->s_createaccess)) {
				$s_owner .= "
    <input type='button' value='Change' onclick='o_bugtracker.showChange(this, event, \"Owner\");'></input>
    <form class='changeOwner' id='post_change_owner_{$id}_{$this->forum_instance}' style='display:none; margin:0;'>
        <input type='hidden' name='tablename' value='{$this->s_tablename}'></input>
        <input type='hidden' name='post_id' value='{$id}'></input>
        <input type='hidden' name='command' value='change_bug_owner'></input>
        <select name='userid'>";
				foreach(db_query("SELECT `id`,`username` FROM `{$maindb}`.`students` WHERE `deleted`='0'") as $a_student) {
						$s_owner .= "
            <option value='".$a_student["id"]."'>".$a_student["username"]."</option>";
				}
				$s_owner .= "
        </select>
        <input type='button' value='Apply' onclick='o_bugtracker.change(this, \"Owner\");'></input>
    </form>";
		}

		// get the status string
		$s_status = $a_post["status"];
		$s_status_string = "";
		if ($i_post_depth == 0) {
				$s_status_string = "Status: <span style='font-weight:bold;' id='bug_status_{$id}'>{$s_status}</span>";
				if ($this->user->has_access($this->s_createaccess)) {
						$s_status_string .= "
    <input type='button' value='Change' onclick='o_bugtracker.showChange(this, event, \"Status\");'></input>
    <form class='changeStatus' id='post_change_status_{$id}_{$this->forum_instance}' style='display:none; margin:0;'>
        <input type='hidden' name='tablename' value='{$this->s_tablename}'></input>
        <input type='hidden' name='post_id' value='{$id}'></input>
        <input type='hidden' name='command' value='change_bug_status'></input>
        <select name='status'>
            <option>New</option><option>Needs Confirmation</option><option>In Progress</option><option>Wont Fix</option><option>Fixed</option>
        </select>
        <input type='button' value='Apply' onclick='o_bugtracker.change(this, \"Status\");'></input>
    </form>";
				}
		}

		// get the query string
		$s_query = "<span id='post_{$id}_{$this->forum_instance}'>".str_replace(array("\n","\r","\r\n"), "<br />", $a_post['query'])."</span> <span style='opacity:0.5;'>~ {$s_querier_name}</span>";

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
		$i_min_post_depth = min($i_post_depth, 5);
		$s_time_color = ($i_post_depth < 3) ? "gray" : "lightgray";
		$s_timedisplay = "<span style='color:{$s_time_color}'>Submitted ".date("F j, Y", strtotime($a_post['datetime']))." at ".date("g:ia", strtotime($a_post['datetime']))."</span>";
		$s_retval .= "
    <div id='{$s_container_id}' class='{$s_stylename} depth_{$i_min_post_depth} {$s_wrapper_collapsed} {$s_wrapper_noresponses} {$s_status_class}'>
        {$s_wrapper}{$s_query}{$s_wrapper_mid}<br />{$s_edit_query}{$s_respond_query}{$s_delete_query}<br />{$s_owner} {$s_status_string}<br />{$s_timedisplay}<br />{$s_responses}{$s_wrapper_end}
    </div>";
		return $s_retval;
	}

	/**
	 * Changes the owner of the bug
	 * @$s_post_id string id of the post
	 * @$s_userid  string id of the user that should own the bug
	 * @return     string should be "alert[*note*]message" on failure or "set value[*note*]{'element_find_by':string,'html':string}" on success
	 */
	public function handleChangeBugOwnerAJAX($s_post_id, $s_userid) {
		global $maindb;
		
		// check that the user has permission
		if (!$this->user->has_access($this->s_createaccess)) {
				return "alert[*note*]Incorrect permission";
		}
		
		// check that the post and owner exist
		$a_posts = db_query("SELECT `id` FROM `{$maindb}`.`buglog` WHERE `id`='[id]'", array("id"=>$s_post_id));
		$a_users = db_query("SELECT `username` FROM `{$maindb}`.`students` WHERE `id`='[id]'", array("id"=>$s_userid));
		if (!is_array($a_posts) || !is_array($a_users) || count($a_posts) == 0 || count($a_users) == 0) {
				return "alert[*note*]Error: either the user can't be found or the bug can't be found in the database";
		}

		// change the owner and return
		db_query("UPDATE `{$maindb}`.`buglog` SET `owner_userid`='[userid]' WHERE `id`='[id]'", array("id"=>$s_post_id, "userid"=>$s_userid));
		$s_json = json_encode(array("element_find_by"=>"#bug_owner_{$s_post_id}", "html"=>$a_users[0]["username"]));
		return "set value[*note*]{$s_json}";
	}

	/**
	 * Changes the status of the bug
	 * @$s_post_id string id of the post
	 * @$s_status  string the status to be changed to
	 * @return     string should be "alert[*note*]message" on failure or "set value[*note*]{'element_find_by':string,'html':string}" on success
	 */
	public function handleChangeBugStatusAJAX($s_post_id, $s_status) {
		global $maindb;
		
		// check that the user has permission
		if (!$this->user->has_access($this->s_createaccess)) {
				return "alert[*note*]Incorrect permission";
		}
		
		// check that the post exists
		$a_posts = db_query("SELECT `status` FROM `{$maindb}`.`buglog` WHERE `id`='[id]'", array("id"=>$s_post_id));
		if (!is_array($a_posts) || count($a_posts) == 0) {
				return "alert[*note*]Error: the bug can't be found in the database";
		}

		// change the status
		db_query("UPDATE `{$maindb}`.`buglog` SET `status`='[status]' WHERE `id`='[id]'", array("id"=>$s_post_id, "status"=>$s_status));

		// init return values
		$s_old_status_string = strtolower(str_replace(" ","_",$a_posts[0]["status"]));
		$s_new_status_string = strtolower(str_replace(" ","_",$s_status));
		$a_retval = array();
		
		// return
		$s_json = json_encode(array("element_find_by"=>"#bug_status_{$s_post_id}", "html"=>$s_status));
		$a_retval[] = "set value[*note*]{$s_json}";
		$s_json = json_encode(array("element_find_by"=>"#bug_container_{$s_post_id}", "class"=>"status_{$s_old_status_string}"));
		$a_retval[] = "remove class[*note*]{$s_json}";
		$s_json = json_encode(array("element_find_by"=>"#bug_container_{$s_post_id}", "class"=>"status_{$s_new_status_string}"));
		$a_retval[] = "add class[*note*]{$s_json}";
		return implode("[*command*]", $a_retval);
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