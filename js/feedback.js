typeFeedback = function() {
	this.edit_query = function(edit_button, i_feedback_id) {
		
		// get some values
		var jedit_button = $(edit_button);
		var jspan = $("#feedback_"+i_feedback_id);
		var width = parseInt(jspan.width());
		var height = parseInt(jspan.height()) + 40;
		var text = jspan.text();
		
		// create the form
		var jform = $("<form id='feedback_"+i_feedback_id+"_form'>\n    <input type='hidden' name='feedback_id' value='"+i_feedback_id+"'></input>\n    <input type='textbox' name='feedback_text' style='width:"+width+"px; height:"+height+"px; box-sizing:border-box;'></input>\n    <br />\n    <input type='button' value='Submit' onclick='o_feedback.submit_edit_query("+i_feedback_id+");'></input>\n    <input type='button' value='Cancel' onclick='o_feedback.cancel_edit_query("+i_feedback_id+");'></input>\n</form>");
		jform.find("input[name=feedback_text]").val(text);
		jform.insertBefore(jedit_button);
		jspan.hide();
		jedit_button.hide();
	};

	this.submit_edit_query = function(i_feedback_id) {
		var jspan = $("#feedback_"+i_feedback_id);
		var jedit_button = $("#feedback_"+i_feedback_id+"_edit_button");
		var jform = $("#feedback_"+i_feedback_id+"_form");

		jedit_button.show();
		jspan.show();
		jspan.text(jform.find("input[name=feedback_text]").val());
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
};

o_feedback = new typeFeedback();