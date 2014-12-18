typeAccountManager = function() {
	/**
	 * Verifies that the password is valid and the password fields match
	 * @form_element: an element within the form, used to find the form and password_verification label
	 * @return: true if the passwords match, false if they don't
	 */
	this.verifyPasswords = function(form_element) {

		var form = get_parent_by_tag("form", $(form_element));
		var jp1 = form.find(".p1");
		var jp2 = form.find(".p2");
		var jlabel = form.find(".password_verification");

		// check if the password fields are blank
		if (jp1.val() == '') {
			jlabel.stop(true,true);
			jlabel.css({ color:'red', opacity:0 });
			jlabel.animate({ opacity:1 }, 300);
			jlabel.text('It is advised that your password not be blank.');
			return true;
		}

		// check if they passwords match
		if (jp1.val() != jp2.val()) {
			jlabel.stop(true,true);
			jlabel.css({ color:'red', opacity:0 });
			jlabel.animate({ opacity:1 }, 300);
			jlabel.text('Passwords do not match.');
			return false;
		}

		// all good to go!
		jlabel.stop(true,true);
		jlabel.css({ color:'green', opacity:0 });
		jlabel.animate({ opacity:1 }, 300);
		jlabel.text('OK!');
		return true;
	};

	/**
	 * Used to authenticate the user before making any drastic changes to the account
	 * If the user can't be authenticated, a alert will pop up
	 * @callback: the function to call once the user has been authenticated
	 * @s_action: a description to the user, in present verbal form, of what action is being requested
	 */
	this.authenticateUser = function(callback, s_action) {
		
		var jaccount = $("#Account");
		var jbody = $("body");
		
		// build the password confirmation/authentication box
		var top = parse_int($(window).scrollTop())+50;
		var jconfirm = $("<div id='authenticateUser' style='position:absolute; top:"+top+"px; left:0; right:0; margin:0 auto; width:300px; height:150px; border:2px solid black; border-radius:15px; background-color:white; padding:15px;'>Please authenticate with your password to continue "+s_action+":<br /><form style='display:inline-block; margin:0; padding:0;'>Password: <input class='password' type='password' size='20'></input><br /><input class='submit' type='button' value='Submit'></input><input class='cancel' type='button' value='Cancel'></input><br /><label class='errors'></label></form></div>");
		jaccount.append(jconfirm);
		var jform = jconfirm.find("form");
		var jpass = jform.find(".password");
		var jsubmit = jform.find(".submit");
		var jcancel = jform.find(".cancel");
		jpass.focus();

		// bind the password field
		jpass.keydown(function(e) {
			if (e.which == 13) {
				jsubmit.click();
				e.preventDefault();
				return false;
			}
		});
		
		// bind the buttons
		var kill = function() {
			$("#authenticateUser").remove();
		};
		jcancel.click(kill);
		jsubmit.click(function() {
			draw_error(jform, "Contacting server...", null);
			vars = {username:get_username(), password:jpass.val(), command:"verify_password"};
			send_ajax_call("/resources/ajax_calls.php", vars, function(message){
				var command = JSON.parse(message)[0];
				var success = command.command;
				if (success == "success") {
					draw_error(jform, "Success", true);
					setTimeout(kill, 200);
					callback(vars, true);
				} else {
					var fail_msg = (command.action != "") ? command.action : "failure";
					draw_error(jform, fail_msg, false);
					callback(vars, false);
				}
			});
		});
	};

	this.keyPress = function(e, form_element) {
		
		if (e.which == 13) {
			var jform = get_parent_by_tag("form", $(form_element));
			var jsubmit = jform.find(".submit");
			jsubmit.click();
		}
	};

	this.changePassword = function(form_element) {
		
		var jform = get_parent_by_tag("form", $(form_element));
		var jpass = jform.find(".p2");

		// verify the passwords
		if (!this.verifyPasswords(form_element)) {
			draw_error(jform, "Error: invalid password", false);
			return false;
		}

		// try to update the password
		this.authenticateUser(function(vars, success) {
			if (success) {
				vars = {username:vars.username, password:vars.password, new_password:jpass.val(), command:"change_password"};
				send_ajax_call("/resources/ajax_calls.php", vars, function(message) {
					var success = JSON.parse(message)[0].command;
					if (success == "success") {
						draw_error(jform, "Success: your password has been changed", true);
					} else {
						draw_error(jform, "Error: failed to update your password", false);
					}
				});
			}
		}, "changing your password");
	};

	this.changeUsername = function(form_element) {
		
		var form = get_parent_by_tag("form", $(form_element));
		var username = form.find(".username").val();
		
		// check that the username is valid
		username = username.trim();
		if (username == "") {
			draw_error(form, "Username can't be blank", false);
			return false;
		}
		draw_error(form, "Coming soon...", null);
	};

	this.drawDelete = function(form_element) {

		var jform = get_parent_by_tag("form", $(form_element));
		var jdelete = jform.find(".user_verification_delete");
		var jdiv = get_parent_by_tag("div", jdelete);
		
		jdiv.show();
	}

	this.disableAccount = function(form_element) {
		
		var jform = get_parent_by_tag("form", $(form_element));
		var jdisable = jform.find(".user_verification_disable");
		var jdelete = jform.find(".user_verification_delete");
		var b_disable = jdisable[0].checked;
		var b_delete = jdelete[0].checked;
		
		// check that the checkbox is checked
		if (!b_disable && !b_delete) {
			draw_error(jform, "Please confirm", false);
			return false;
		}

		// try to disable/delete the account
		s_action = (b_disable) ? "disabling your account" : "deleting your account";
		s_command = (b_disable) ? "disable_account" : "delete_account";
		s_success = (b_disable) ? "your account has be disabled" : "your account has been deleted";
		s_failure = (b_disable) ? "failed to disable your account" : "failed to delete your account";
		this.authenticateUser(function(vars, success) {
			if (success) {
				vars = {username:vars.username, password:vars.password, command:s_command};
				send_ajax_call("/resources/ajax_calls.php", vars, function(message) {
					var command = JSON.parse(message)[0];
					var success = command.command;
					if (success == "success") {
						draw_error(jform, "Success: "+s_success, true);
					} else {
						s_failure += (command.action != "") ? (" ("+command.action+")") : "";
						draw_error(jform, "Error: "+s_failure, false);
					}
				});
			}
		}, s_action);
	};
};

o_account_manager = new typeAccountManager();