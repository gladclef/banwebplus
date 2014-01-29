typeFeedback = function() {
	this.old_feedback_values = [];
	
	this.edit_query = function(edit_button, i_feedback_id) {
		
		// get some values
		var jedit_button = $(edit_button);
		var jspan = $("#feedback_"+i_feedback_id);
		var width = Math.max(parseInt(jspan.width()), 200);
		var height = parseInt(jspan.height()) + 40;
		var text = jspan[0].innerHTML;

		if (!this.old_feedback_values[i_feedback_id]) {
			this.old_feedback_values[i_feedback_id] = text;
		}

		while(text.match("<br />") && text.match("<br />").length > 0) { text = text.replace("<br />", "\n\r"); }
		while(text.match("<br>") && text.match("<br>").length > 0) { text = text.replace("<br>", "\n"); }
		
		// create the form
		var jform = $("<form id='feedback_"+i_feedback_id+"_form' onkeypress='cancel_enter_keypress(event);'>\n    <input type='hidden' name='feedback_id' value='"+i_feedback_id+"'></input><input type='hidden' name='command' value='edit_feedback'></input>\n    <textarea name='feedback_text' style='width:"+width+"px; height:"+height+"px; box-sizing:border-box;'></textarea>\n    <br />\n    <input type='button' value='Submit' onclick='o_feedback.submit_edit_query("+i_feedback_id+");'></input>\n    <input type='button' value='Cancel' onclick='o_feedback.cancel_edit_query("+i_feedback_id+");'></input>\n</form>");
		jform.find("[name=feedback_text]").text(text);
		jform.insertBefore(jedit_button);
		jspan.hide();
		jedit_button.hide();
	};

	this.submit_edit_query = function(i_feedback_id) {
		var jspan = $("#feedback_"+i_feedback_id);
		var jedit_button = $("#feedback_"+i_feedback_id+"_edit_button");
		var jform = $("#feedback_"+i_feedback_id+"_form");
		var text = jform.find("[name=feedback_text]")[0].value;

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
			jspan.html(this.old_feedback_values[i_feedback_id]);
		} else {
			this.old_feedback_values[i_feedback_id] = text;
		}
		jform.remove();
	};

	this.cancel_edit_query = function(i_feedback_id) {
		var jspan = $("#feedback_"+i_feedback_id);
		var jedit_button = $("#feedback_"+i_feedback_id+"_edit_button");
		var jform = $("#feedback_"+i_feedback_id+"_form");

		jform.remove();
		jedit_button.show();
		jspan.show();
	};

	this.create_feedback = function() {
		send_ajax_call_from_form("/resources/ajax_calls.php", "create_feedback_form");
	};

	this.delete_feedback = function(i_feedback_id) {
		var jspan = $("#feedback_"+i_feedback_id);

		// check if the user really wants to perform this action
		if (!confirm("Are you sure you want to delete this feedback?\n\nQ: "+jspan.text())) {
			return;
		}

		// the user really wants to delete the feedback
		send_ajax_call_from_form("/resources/ajax_calls.php", "delete_feedback_"+i_feedback_id+"_form");
	};
};

o_feedback = new typeFeedback();