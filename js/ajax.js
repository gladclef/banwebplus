var send_ajax_call_retval = "";
function send_ajax_call(php_file_name, posts, callback) {
	a_message = send_async_ajax_call(php_file_name, posts, false, callback);
	return a_message;
}

function send_async_ajax_call(php_file_name, posts, async, callback) {
	if (typeof(async) == 'undefined')
		async = true;
	if (typeof(callback) == 'undefined')
		callback = null
	var ajax_object = {
		url: php_file_name,
		async: async,
		cache: false,
		data: posts,
		type: "POST",
		timeout: 10000,
		success: function(data) {
			send_ajax_call_retval = data;
			if (callback !== null) {
				callback(data);
			}
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
			if (callback !== null) {
				callback(data);
			}
		}
	};
	
	$.ajax(ajax_object);
	return send_ajax_call_retval;
}

/**
 * Like send_ajax_call_from_form, but requests the logged in user's password to verify an admin action
 * @php_file_name string Typically /resources/ajax_calls_super.php
 * @form_id       string The id of the form to serialize (will insert the super_password field into the form)
 * @callback      string The function name to be called upon getting a return value from send_ajax_call_from_form, or null
 */
function send_ajax_call_from_form_super(php_file_name, form_id, callback) {

	// remove the old passbox
	while($("#passbox_super").length > 0)
		$("#passbox_super").remove();
	
	var left = $(window).width()/2-200/2;
	var top = $(window).height()/2-80/2;
	var passbox = "<div id='passbox_super' style='width:200px; height:100px; border:1px solid black; border-radius:5px; background-color:white; padding:10px; position:fixed; left:"+left+"px; top:"+top+"px; z-index:1;'>";
	passbox += "Enter your password to continue:<br />";
	passbox += "<input type='password' class='password'></input> <input type='button' value='Cancel' class='cancel' /><br />";
	passbox += "<label class='error'></label></div>";
	var jpassbox = $(passbox);
	var jpassword = jpassbox.find('input.password');
	var jcancel = jpassbox.find('input.cancel');
	var jerror = jpassbox.find('label.error');

	$($("div")[0]).append(jpassbox);
	jpassword.focus();
	jpassword.keydown(function(event) {
		if (event.which == 13) {
			if (jpassword.val() == '') {
				alert("Enter your password, first");
			} else {
				var jform = $("#"+form_id);
				jform.find("input[name=super_password]").remove();
				jform.append("<input type='hidden' name='super_password' value='"+jpassword.val()+"'></input>");
				var retval = send_ajax_call_from_form(php_file_name, form_id);
				$.each(retval, function(k,v) {
					if (v[0] == 'print success') {
						jerror.css({ color:'black' });
						set_html_and_fade_in(jerror, "", v[1]);
						setTimeout(function(){ alert(v[1]); jpassbox.remove() }, 300);
					} else if (v[0] == 'print failure') {
						jerror.css({ color:'red' });
						set_html_and_fade_in(jerror, "", v[1]);
					}
				});
				if (callback !== null)
					exec(callback+"("+retval+")");
			}
		}
	});
	jcancel.click(function() {
		jpassbox.remove();
	});
}

function send_ajax_call_from_form(php_file_name, form_id) {
	
	// get the form and its inputs
	var jform = $("#"+form_id);
	var inputs = get_values_in_form(jform);
	var jerrors_label = $(jform.find("label.errors"));
	
	// for each input, get the name and value of the input to be posted to the server
	var posts = {};
	var full_posts = [];
	for(var i = 0; i < inputs.length; i++) {
		var jinput = $(inputs[i]);
		var name = jinput.prop("name");
		var value = jinput.val();
		if (jinput.prop('type') == 'checkbox') {
			value = jinput[0].checked ? 1 : 0;
		}
		full_posts.push([name, value]);
		posts[name] = value;
	}

	// init the errors label and send the data
	set_html_and_fade_in(jerrors_label, "", "<span style='color:gray;'>Please wait...</span>");
	var commands_array = retval_to_commands(send_ajax_call(php_file_name, posts));
	set_html_and_fade_in(jerrors_label, "", "&nbsp;");
	
	// interpret commands send back
	interpret_common_ajax_commands(commands_array);
	for (var i = 0; i < commands_array.length; i++) {
		var command = commands_array[i][0];
		var note = commands_array[i][1];
		if (command == "print failure") {
			set_html_and_fade_in(jerrors_label, "", "<span style='color:red;'>"+note+"</span>");
		} else if (command == "print success") {
			set_html_and_fade_in(jerrors_label, "", "<span style='color:black;font-weight:normal;'>"+note+"</span>");
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
		} else if (command == "set value") {
			var parts = note;
			console.log('a');
			console.log(parts);
			$(parts.element_find_by).html(parts.html);
			console.log('b');
		} else if (command == "remove class") {
			var parts = note;
			$(parts.element_find_by).removeClass(parts['class']);
		} else if (command == "add class") {
			var parts = note;
			$(parts.element_find_by).addClass(parts['class']);
		}
	}
	
	return commands_array;
}

function ajax_logout() {
	var posts = {};
	posts["action"] = "logout";
	var retval = send_ajax_call("/pages/login/logout_ajax.php", posts);
	var commands_array = retval_to_commands(retval);
	interpret_common_ajax_commands(commands_array);
}

function retval_to_commands(retval) {
	var commands_list = JSON.parse(retval);
	var commands_array = [];
	for (var i = 0; i < commands_list.length; i++) {
		var command = commands_list[i];
		commands_array.push([command.command, command.action]);
	}
	return commands_array;
}

function interpret_common_ajax_commands(commands_array) {
	for (var i = 0; i < commands_array.length; i++) {
		var command = commands_array[i][0];
		var note = commands_array[i][1];
		if (command == "load page") {
			var page = note;
			var posts = [];
			if (note.indexOf('[*post*]') > 0) {
				var parts = note.split('[*post*]');
				page = parts[0];
				for (var i = 1; i < parts.length; i++) {
					var post = parts[i].split('[*value*]');
					posts.push(post);
				}
			}
			window.location = page;
			var form_string = '<form action="'+page+'" method="post">\n';
			$.each(posts, function(k, v) {
				form_string += '<input type="hidden" name="'+v[0]+'" value="'+v[1]+'" />\n';
			});
			form_string += '</form>';
			$(form_string).submit();
		} else if (command == "alert") {
			alert(note);
		} else if (command == "reload page") {
			location.reload(true);
		}
	}
}

function set_html_and_fade_in(jparent_object, parent_id, html) {
	setTimeout(function() {
		if (jparent_object === null)
			jparent_object = $("#"+parent_id);
		kill_children(jparent_object);
		jparent_object.html('');
		jparent_object.append(html);
		jparent_object.stop(true,true);
		jparent_object.children().css({opacity:0});
		jparent_object.children().animate({opacity:1},300);
	}, 10);
}