<?php

require_once(dirname(__FILE__)."/user.php");

class forum_object_type {
	
	function __construct($s_tablename) {
		global $global_user;
		static $i_forum_instance;
		$this->s_tablename = $s_tablename;
		$this->a_range = array("datetime_start"=>0, "num_feeds"=>5);
		$this->user = $global_user;
		$this->forum_instance = (int)$i_forum_instance;
		$this->a_postnames = array("singular"=>"post", "plural"=>"posts", "stylename"=>"forum_posts");
		$this->s_createaccess = "development.createposts";
		$this->s_deleteaccess = "development.deleteposts";
		$i_forum_instance++;
	}

	/**
	 * Sets the accesses necessary to create or delete posts
	 **/
	public function setAccesses($s_createaccess, $s_deleteaccess) {
		$this->s_createaccess = $s_createaccess;
		$this->s_deleteaccess = $s_deleteaccess;
	}

	/**
	 * Sets the range of recent forum feeds to draw
	 * @$i_datetime_start integer the starting place of the feeds to draw
	 * @$i_num_feeds      integer the number of feeds to draw
	 */
	public function setRange($i_datetime_start, $i_num_feeds) {
		if ($i_datetime_start !== NULL) {
				$this->a_range["datetime_start"] = (int)$i_datetime_start;
		}
		if ($i_num_feeds !== NULL) {
				$this->a_range["num_feeds"] = (int)$i_num_feeds;
		}
	}

	/**
	 * Sets the user
	 * Because ability to edit, add new, and delete feeds is based on user accesses
	 * @$o_user object user type object
	 */
	public function setUser($o_user) {
		if (is_a($o_user, "user")) {
				$this->user = $o_user;
		}
	}
	
	/**
	 * Sets the post name variables
	 * Only useful when using the built-in draw function
	 * @$s_singular  string the name of a singular post, as seen by the user (eg "post")
	 * @$s_plural    string the name of many posts, as seen by the user (eg "posts")
	 * @$s_stylename string the name to use for the css styleing
	 */
	public function setPostNames($s_singular, $s_plural, $s_stylename) {
		$this->a_postnames["singular"] = $s_singular;
		$this->a_postnames["plural"] = $s_plural;
		$this->a_postnames["stylename"] = $s_stylename;
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
		$username = $this->user->get_name();
		$this->setRange($i_datetime_start, $i_num_posts);
		$s_postname_plural_lc = $this->a_postnames["plural"];
		$s_postname_plural = ucfirst($s_postname_plural_lc);
		$s_stylename = $this->a_postnames["stylename"];
		
		$s_header = "";
		$s_header .= "
<table class='table_title'><tr><td>
    <div class='centered'>Recent {$s_postname_plural}</div>
</td></tr></table>";
		if ($this->user->has_access($this->s_createaccess)) {
				$s_header .= "
<div class='centered'>
    <form id='create_post_form_{$this->forum_instance}'>
        <input type='hidden' name='command' value='create_post'>
        <input type='hidden' name='tablename' value='{$this->s_tablename}'>
        <input type='button' onclick='o_forum.create_post({$this->forum_instance});' value='Create New'>
    </form>
</div>";
		}
		$s_retval = "";
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
<div class='centered'>";
		foreach($a_forum_posts as $id=>$a_post) {
				$s_query = "<span id='post_{$id}_{$this->forum_instance}'>".str_replace(array("\n","\r","\r\n"), "<br />", $a_post['query'])."</span>";
				$s_edit_query = "";
				if ($username == $a_post['querier_name']) {
						$s_edit_query = " <input id='post_{$id}_edit_button_{$this->forum_instance}' type='button' onclick='o_forum.edit_query(this,{$id},{$this->forum_instance},\"{$this->s_tablename}\");' value='Edit'></input>";
				}
				$s_response = "";
				$s_edit_response = "";
				if (count($a_post['responses']) > 0) {
						foreach($a_post["responses"] as $a_response) {
								$response_string = $a_response["query"];
								$response_id = $a_response["id"];
								$s_response = "<span id='post_{$response_id}_{$this->forum_instance}'>".str_replace(array("\n","\r","\r\n"), "<br />", $response_string)."</span>";
								if ($username == $a_response['querier_name']) {
										$s_edit_response = " <input id='post_{$response_id}_edit_button_{$this->forum_instance}' type='button' onclick='o_forum.edit_query(this,{$response_id},{$this->forum_instance},\"{$this->s_tablename}\");' value='Edit'></input>";
								}
						}
				}
				$s_timedisplay = "<span style='color:gray'>Submitted ".date("F j, Y", strtotime($a_post['datetime']))." at ".date("g:ia", strtotime($a_post['datetime']))."</span>";
				$s_retval .= "
    <div class='{$s_stylename}'>
        <span style='font-weight:bold'>Q</span>: {$s_query}{$s_edit_query}<br /><br /><span style='font-weight:bold;'>A</span>: {$s_response}{$s_edit_response}<br />{$s_timedisplay}
    </div>";
				if ($this->user->has_access($this->s_deleteaccess) || $a_post["querier_name"] == $this->user->get_name()) {
						$s_retval .= "
    <div class='centered'>
        <form id='delete_post_{$id}_form_{$this->forum_instance}'>
            <input type='button' onclick='o_forum.delete_post({$id},{$this->forum_instance});' value='Delete'></input>
            <input type='hidden' name='command' value='delete_post'></input>
            <input type='hidden' name='tablename' value='{$this->s_tablename}'></input>
            <input type='hidden' name='post_id' value='{$id}'></input>
        </form>
    </div>";
				}
		}
		$s_retval .= "
</div>";
		return $s_retval;
	}

	/**
	 * Loads forum posts from the database
	 * @$original_id integer The id of the original post (indicates that this should be looking for responses)
	 * @return       array   An array of posts, in the form array(post id=>array("query"=>string, "id"=>post id, "responses"=>array(array of posts), "datetime"=>integer, "querier_name"=>username), ...)
	 */
	public function loadRecentPosts($original_id = -1) {
		
		global $maindb;

		// default the starttime
		$t_since = date("Y-m-d H:i:s", $this->a_range["datetime_start"]);
		$i_limit = $this->a_range["num_feeds"];
		
		// load posts from the database
		$s_response_check = ($original_id === -1) ? "`is_response`='0'" : "`is_response`='1' AND `original_post_id`='".((int)$original_id)."'";
		$a_forum_posts = db_query("SELECT * FROM `{$maindb}`.`[table]` WHERE `datetime`>'[starttime]' AND {$s_response_check} AND `deleted`='0' ORDER BY `datetime` DESC LIMIT [limit]", array("table"=>$this->s_tablename, "starttime"=>$t_since, "limit"=>$i_limit));
		if (!is_array($a_forum_posts) || count($a_forum_posts) == 0) {
				return array();
		}
		
		// index by id and add the username/responses fields
		$a_forum_posts_new = array();
		for($i = 0; $i < count($a_forum_posts); $i++) {
				$s_username = self::getUsernameForId($a_forum_posts[$i]['userid']);
				$a_forum_posts_new[$a_forum_posts[$i]['id']] = array_merge($a_forum_posts[$i], array("responses"=>array(), "querier_name"=>$s_username));
		}
		$a_forum_posts = $a_forum_posts_new;
		unset($a_forum_posts_new);

		// load responses from the database
		$a_feed_ids = array();
		foreach($a_forum_posts as $k=>$a_feed) {
				$a_forum_posts[$k]["responses"] = $this->loadRecentPosts($a_feed['id']);
		}

		return $a_forum_posts;
	}

	/**
	 * updates post entries if the user has the proper access
	 * @$s_post_id          string the string representation of the post id
	 * @$s_new_query_string string the new query string to insert into the database
	 * @return              string one of "alert[*note*]message[*command*]reset old value[*note*]" on error or "" on success
	 */
	public function handelEditPostAJAX($s_post_id, $s_new_query_string) {
		global $maindb;

		// try and find the note
		$id = (int)$s_post_id;
		$a_forum_posts = db_query("SELECT * FROM `{$maindb}`.`[table]` WHERE `id`='{$id}'", array("table"=>$this->s_tablename));
		if (!is_array($a_forum_posts) || count($a_forum_posts) == 0) {
				return "alert[*note*]Post {$id} not found. Value not saved.[*command*]reset old values[*note*]";
		}
		if ($a_forum_posts[0]["userid"] != $this->user->get_id()) {
				return "alert[*note*]Incorrect permissions. Value not saved.[*command*]reset old values[*note*]";
		}
		
		// try and update the note
		$query = db_query("UPDATE `{$maindb}`.`[table]` SET `query`='[query]' WHERE `id`='[id]'", array("table"=>$this->s_tablename, "id"=>$id, "query"=>$s_new_query_string));
		if ($query === FALSE) {
				return "alert[*note*]Failed to update database. Value not saved.[*command*]reset old values[*note*]";
		}
		return "";
	}

	/**
	 * creates a new post and response
	 * @$b_no_response boolean if TRUE, don't automatically generate a response to the post
	 * @return         string  one of "alert[*note*]message" on error or "reload page[*note*]" on success
	 */
	public function handelCreatePostAJAX($b_no_response = FALSE) {
		global $maindb;
		
		// check if the user has permission
		if (!$this->user->has_access($this->s_createaccess)) {
				return "alert[*note*]Incorrect permissions";
		}

		// create the new post
		$a_insert_post = array("userid"=>$this->user->get_id(), "owner_userid"=>$this->user->get_id(), "datetime"=>date("Y-m-d H:i:s"));
		$s_insert_post = array_to_insert_clause($a_insert_post);
		$query = db_query("INSERT INTO `{$maindb}`.`[table]` {$s_insert_post}", array_merge($a_insert_post,array("table"=>$this->s_tablename)));
		if ($query === FALSE) {
				return "alert[*note*]Failed to insert into database";
		}

		// create the response
		if (!$b_no_response) {
				$a_insert_response = array("userid"=>$this->user->get_id(), "owner_userid"=>$this->user->get_id(), "datetime"=>date("Y-m-d H:i:s"), "is_response"=>1, "original_post_id"=>mysql_insert_id());
				$s_insert_response = array_to_insert_clause($a_insert_response);
				$query = db_query("INSERT INTO `{$maindb}`.`[table]` {$s_insert_response}", array_merge($a_insert_response,array("table"=>$this->s_tablename)));
		}

		return "reload page[*note*]";
	}

	public function handelRespondPostAJAX($post_id) {
		global $maindb;
		
		// check if the user has permission
		if (!$this->user->has_access($this->s_createaccess)) {
				return "alert[*note*]Incorrect permissions";
		}

		// check that the post exists that we're trying to create a response to
		$a_posts = db_query("SELECT `id` FROM `{$maindb}`.`[table]` WHERE `id`='[id]' LIMIT 1", array("table"=>$this->s_tablename, "id"=>$post_id));
		if (!is_array($a_posts) || count($a_posts) == 0) {
				return "alert[*note*]Original post not found, possible error in database";
		}

		// create the response
		$a_insert_response = array("userid"=>$this->user->get_id(), "owner_userid"=>$this->user->get_id(), "datetime"=>date("Y-m-d H:i:s"), "is_response"=>1, "original_post_id"=>$post_id);
		$s_insert_response = array_to_insert_clause($a_insert_response);
		$query = db_query("INSERT INTO `{$maindb}`.`[table]` {$s_insert_response}", array_merge($a_insert_response,array("table"=>$this->s_tablename)));
		if ($query === FALSE) {
				return "alert[*note*]Failed to insert into database";
		}

		return "reload page[*note*]";
	}

	/**
	 * Used to delete a post via ajax
	 * Marks the post as "deleted=1"
	 * @$post_id integer the id of the post
	 */
	public function handelDeletePostAJAX($post_id) {
		global $maindb;
		
		// check that the user has permission
		if (!$this->user->has_access($this->s_deleteaccess)) {
				return "alert[*note*]Incorrect permission";
		}
		
		// try and delete the post
		$query = db_query("UPDATE `{$maindb}`.`[table]` SET `deleted`='1' WHERE `id`='[id]'", array("table"=>$this->s_tablename, "id"=>$post_id));
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
	public function getUsernameForId($i_user_id) {

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

?>