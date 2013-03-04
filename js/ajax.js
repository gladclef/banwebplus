var send_ajax_call_retval = "";
function send_ajax_call(php_file_name, posts) {
	a_message = send_async_ajax_call(php_file_name, posts, false);
	return a_message;
}

function send_async_ajax_call(php_file_name, posts, async) {
	if (typeof(async) == 'undefined')
		async = true;
	var ajax_object = {
		url: php_file_name,
		async: async,
		cache: false,
		data: posts,
		type: "POST",
		timeout: 10000,
		success: function(data) {
			send_ajax_call_retval = data;
		},
		error: function(xhr, ajaxOptions, thrownError) {
			if (parseInt(xhr.status) == 0 && thrownError) {
				if ((thrownError+"").indexOf("NETWORK_ERR") > -1) {
					send_ajax_call_retval = "network error encountered";
					return;
				}
			}
			//alert("Error sending request: ("+xhr.status+") "+thrownError);
			send_ajax_call_retval = "error";
		}
	};
	
	$.ajax(ajax_object);
	return send_ajax_call_retval;
}

function send_ajax_call_from_form(php_file_name, form_id) {
	var jform = $("#"+form_id);
	var a_inputs = jform.find("input");
	var a_selects = jform.find("select");
	var a_textareas = jform.find("textarea");
	var inputs = $.merge($.merge(a_inputs, a_selects), a_textareas);
	
	var posts = {};
	var full_posts = [];
	for(var i = 0; i < inputs.length; i++) {
		var name = $(inputs[i]).prop("name");
		var value = $(inputs[i]).val();
		full_posts.push([name, value]);
		posts[name] = value;
	}

	jerrors_label = $(jform.find("label.errors"));
	set_html_and_fade_in(jerrors_label, "", "<font style='color:gray;'>Please wait...</font>");
	var commands_array = retval_to_commands(send_ajax_call(php_file_name, posts));
	set_html_and_fade_in(jerrors_label, "", "&nbsp;");
	interpret_common_ajax_commands(commands_array);
	for (var i = 0; i < commands_array.length; i++) {
		var command = commands_array[i][0];
		var note = commands_array[i][1];
		if (command == "print error") {
			set_html_and_fade_in(jerrors_label, "", "<font style='color:red;'>"+note+"</font>");
		} else if (command == "print success") {
			set_html_and_fade_in(jerrors_label, "", "<font style='color:black;font-weight:normal;'>"+note+"</font>");
		} else if (command == "load page with post") {
			var posts_string = "";
			for (var i = 0; i < full_posts.length; i++)
				posts_string += '<input type="hidden" name="'+full_posts[i][0]+'" value="'+full_posts[i][1]+'" />';
			var id_string = get_unique_id();
			var create_string = '<form method="POST" action="'+note+'" id="'+id_string+'">'+posts_string+'</form>';
			$(create_string).appendTo("body");
			$("#"+id_string).submit();
		} else if (command == "clear field") {
			jform.find("input[name="+note+"]").val("");
		}
	}
}

function ajax_logout() {
	var posts = {};
	posts["action"] = "logout";
	var retval = send_ajax_call("/pages/login/logout_ajax.php", posts);
	var commands_array = retval_to_commands(retval);
	interpret_common_ajax_commands(commands_array);
}

function retval_to_commands(retval) {
	var commands_list = retval.split("[*command*]");
	var commands_array = [];
	for (var i = 0; i < commands_list.length; i++) {
		var command = commands_list[i].split("[*note*]");
		commands_array.push(command);
	}
	return commands_array;
}

function interpret_common_ajax_commands(commands_array) {
	for (var i = 0; i < commands_array.length; i++) {
		var command = commands_array[i][0];
		var note = commands_array[i][1];
		if (command == "load page") {
			window.location = note;
		} else if (command == "alert") {
			alert(note);
		}
	}
}

function set_html_and_fade_in(jparent_object, parent_id, html) {
	if (jparent_object === null)
		jparent_object = $("#"+parent_id);
	kill_children(jparent_object);
	jparent_object.html('');
	jparent_object.append(html);
	jparent_object.stop(true,true);
	jparent_object.children().css({opacity:0});
	jparent_object.children().animate({opacity:1},300);
}