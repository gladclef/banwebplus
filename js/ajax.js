var send_ajax_call_retval = "";
function send_ajax_call(php_file_name, posts) {
	a_message = send_async_ajax_call(php_file_name, posts, false);
	return a_message;
}

function send_async_ajax_call(php_file_name, posts, async) {
	$.ajax({
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
			alert("Error sending request: ("+xhr.status+") "+thrownError);
			send_ajax_call_retval = "error";
		}
	});
	return send_ajax_call_retval;
}

function send_ajax_call_from_form(php_file_name, form_id) {
	var inputs = $("#"+form_id).children("input");
	
	var posts = {};
	var full_posts = [];
	for(var i = 0; i < inputs.length; i++) {
		var name = $(inputs[i]).prop("name");
		var value = $(inputs[i]).val();
		full_posts.push([name, value]);
		posts[name] = value;
	}

	jerrors_label = $($("#"+form_id).children("label.errors"));
	set_html_and_fade_in(jerrors_label, "", "<font style='color:gray;'>Please wait...</font>");
	var commands_array = retval_to_commands(send_ajax_call(php_file_name, posts));
	interpret_common_ajax_commands(commands_array);
	for (var i = 0; i < commands_array.length; i++) {
		var command = commands_array[i][0];
		var note = commands_array[i][1];
		if (command == "print error") {
			set_html_and_fade_in(jerrors_label, "", "<font style='color:red;'>"+note+"</font>");
		} else if (command == "load page with post") {
			var posts_string = "";
			for (var i = 0; i < full_posts.length; i++)
				posts_string += '<input type="hidden" name="'+full_posts[i][0]+'" value="'+full_posts[i][1]+'" />';
			var id_string = get_unique_id();
			var create_string = '<form method="POST" action="'+note+'" id="'+id_string+'">'+posts_string+'</form>';
			$(create_string).appendTo("body");
			$("#"+id_string).submit();
		} else if (command == "clear field") {
			$("#"+form_id).children("input[name="+note+"]").val("");
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
	var a_childen = jparent_object.children();
	for (var i = 0; i < a_childen.length; i++)
		$(a_childen[i]).remove();
	jparent_object.append(html);
	jparent_object.stop(true,true);
	jparent_object.children().css({opacity:0});
	jparent_object.children().animate({opacity:1},300);
}