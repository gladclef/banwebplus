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
		
		// bind the buttons
		var kill = function() {
			$("#authenticateUser").remove();
		};
		jcancel.click(kill);
		jsubmit.click(function() {
			draw_error(jform, "Contacting server...", null);
			send_ajax_call("/pages/login/login_ajax.php", {username:get_username(), password:jpass.val(), command:"verify_password"}, function(success){
				if (success == "success") {
					draw_error(jform, "Success", true);
				} else {
					draw_error(jform, "Failure", true);
				}
			});
		});
	};

	this.changePassword = function(form_element) {
		
		var form = get_parent_by_tag("form", $(form_element));

		// verify the passwords
		if (!this.verifyPasswords(form_element)) {
			draw_error(form, "Error: invalid password", false);
			return false;
		}
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
		draw_error(form, "Contacting server...", null);
	};

	this.disableAccount = function(form_element) {
		
		var form = get_parent_by_tag("form", $(form_element));
		var checkbox = form.find(".user_verification");
		
		// check that the checkbox is checked
		if (!checkbox[0].checked) {
			draw_error(form, "Please confirm", false);
			return false;
		}
		draw_error(form, "Contacting server...", null);
	};
};

o_account_manager = new typeAccountManager();