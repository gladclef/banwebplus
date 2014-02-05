typeForum = function() {
	this.old_post_values = [];
	
	this.edit_query = function(edit_button, i_post_id, i_forum_id, forum_tablename) {
		
		// get some values
		var jedit_button = $(edit_button);
		var jspan = $("#post_"+i_post_id+"_"+i_forum_id);
		var width = Math.max(parseInt(jspan.width()), 200);
		var height = parseInt(jspan.height()) + 40;
		var text = jspan[0].innerHTML;

		if (!this.old_post_values[i_post_id]) {
			this.old_post_values[i_post_id] = text;
		}

		while(text.match("<br />") && text.match("<br />").length > 0) { text = text.replace("<br />", "\n\r"); }
		while(text.match("<br>") && text.match("<br>").length > 0) { text = text.replace("<br>", "\n"); }
		
		// create the form
		var jform = $("<form id='post_"+i_post_id+"_form_"+i_forum_id+"' onkeypress='cancel_enter_keypress(event);'>\n    <input type='hidden' name='post_id' value='"+i_post_id+"'></input><input type='hidden' name='tablename' value='"+forum_tablename+"'></input><input type='hidden' name='command' value='edit_post'></input>\n    <textarea name='post_text' style='width:"+width+"px; height:"+height+"px; box-sizing:border-box;'></textarea>\n    <br />\n    <input type='button' value='Submit' onclick='o_forum.submit_edit_query("+i_post_id+","+i_forum_id+");'></input>\n    <input type='button' value='Cancel' onclick='o_forum.cancel_edit_query("+i_post_id+","+i_forum_id+");'></input>\n</form>");
		jform.find("[name=post_text]").text(text);
		jform.insertBefore(jedit_button);
		jspan.hide();
		jedit_button.hide();
	};

	this.submit_edit_query = function(i_post_id, i_forum_id) {
		var jspan = $("#post_"+i_post_id+"_"+i_forum_id);
		var jedit_button = $("#post_"+i_post_id+"_edit_button_"+i_forum_id);
		var jform = $("#post_"+i_post_id+"_form_"+i_forum_id);
		var text = jform.find("[name=post_text]")[0].value;

		while(text.match("\n\r") && text.match("\n\r").length > 0) { text = text.replace("\n\r", "<br />"); }
		while(text.match("\r\n") && text.match("\r\n").length > 0) { text = text.replace("\r\n", "<br />"); }
		while(text.match("\n") && text.match("\n").length > 0) { text = text.replace("\n", "<br />"); }
		while(text.match("\r") && text.match("\r").length > 0) { text = text.replace("\r", "<br />"); }

		jedit_button.show();
		jspan.show();
		jspan.html(text);
		jform.hide();

		var commands = send_ajax_call_from_form("/resources/ajax_calls.php", jform.prop("id"));
		if (commands.length > 1 && commands[1][0] == "reset old values") {
			jspan.html(this.old_post_values[i_post_id]);
		} else {
			this.old_post_values[i_post_id] = text;
		}
		jform.remove();
	};

	this.cancel_edit_query = function(i_post_id, i_forum_id) {
		var jspan = $("#post_"+i_post_id+"_"+i_forum_id);
		var jedit_button = $("#post_"+i_post_id+"_edit_button_"+i_forum_id);
		var jform = $("#post_"+i_post_id+"_form_"+i_forum_id);

		jform.remove();
		jedit_button.show();
		jspan.show();
	};

	this.create_post = function(i_forum_id) {
		send_ajax_call_from_form("/resources/ajax_calls.php", "create_post_form_"+i_forum_id);
	};

	this.delete_post = function(i_post_id, i_forum_id) {
		var jspan = $("#post_"+i_post_id+"_"+i_forum_id);

		// check if the user really wants to perform this action
		if (!confirm("Are you sure you want to delete this post?\n\nQ: "+jspan.text())) {
			return;
		}

		// the user really wants to delete the post
		send_ajax_call_from_form("/resources/ajax_calls.php", "delete_post_"+i_post_id+"_form_"+i_forum_id);
	};

	this.create_response = function(i_post_id, i_forum_id) {
		send_ajax_call_from_form("/resources/ajax_calls.php", "respond_post_"+i_post_id+"_form_"+i_forum_id);
	}

	this.collapse_wrapper = function(element) {
		var jstart = $(element);
		var jend = jstart.siblings(".forum_wrapper_rest");
		var jparent = jstart.parent();
		
		if (jstart.hasClass("collapsed")) {
			jend.stop(true,true);
			jend.show(200);
			jend.css("display", "inline-block");
			jstart.removeClass("collapsed");
			jparent.removeClass("collapsed");
		} else {
			jend.stop(true,true);
			jend.hide(200);
			jstart.addClass("collapsed");
			jparent.addClass("collapsed");
		}
	}
};

o_forum = new typeForum();