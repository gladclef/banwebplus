o_userManager = {
	users: null,
	selectedUsername: null,
	disabledUsers: null,
	
	/**
	 * Load the list of users from the server
	 * If already loaded force a load again with "force_load"
	 * @return array [success:true, users:An array of users] if the users are loaded, or [success:false, details:string] if there was an error
	 */
	getUsers: function(force_load) {
		if (typeof(force_load) == 'undefined')
			force_load = false;
		if (this.users === null || force_load) {
			var loadVal = $.parseJSON(send_ajax_call('/resources/ajax_calls.php', { command:'get_full_users_list' }));
			if (loadVal.success)
				this.users = {success:true, users:loadVal.details};
			else
				return {success:false, details:loadVal.details};
		}
		return this.users;
	},
	
	/**
	 * Selects a user and prepares the action buttons for the user management area
	 * @user_row dom element The user row that was clicked on
	 */
	selectUser: function(user_row) {
		var get_username_index = function(jheader_row) {
			var i_username_index = -1;
			for (var i = 0; i < jheader_row.children().length; i++)
				if ($(jheader_row.children()[i]).text().toLowerCase() == "username") {
					i_username_index = i;
					break;
				}
			return i_username_index;
		}
		
		// get the index of "username" in the table header
		var juser_row = $(user_row);
		var jheader_row = $(juser_row.siblings()[0]);
		var i_username_index = get_username_index(jheader_row);
		if (i_username_index == -1)
			return;
		
		// get the username
		var s_username = $(juser_row.find("td")[i_username_index]).text();
		this.selectedUsername = s_username;
		
		// set the username as active
		$("#userManagementChooseUser").hide();
		$("#userManagementApplyAction").find("span.username").text(s_username);
		$("#userManagementApplyAction").show();

		// check for disabled/non-disabled account
		var jdisabled = $("#userManagementApplyAction").find(".disabled_actions");
		var jnormal = $("#userManagementApplyAction").find(".normal_actions");
		if (this.disabledUsers.indexOf(s_username) >= 0) {
			jdisabled.show();
			jnormal.hide();
		} else {
			jnormal.hide();
			jdisabled.show();
		}
	},
	
	/**
	 * returns the username of the selected user or "" if no username has been selected
	 */
	getSelectedUser: function() {
		if (this.selectedUsername === null)
			return ""
		return this.selectedUsername;
	},

	/** 
	 * populates the user management tool area with the selected tool
	 * @which string One of "resetPassword", "createNew", "modifyAccess", and "delete"
	 */
	populateUserManagement: function(which) {
		
		// get/set some values
		var jcontainer = $("#user_action_form_container");
		
		// get the content to insert
		var s_setval = "";
		switch(which) {
		case 'resetPassword':
			s_setval += "<form id='user_action_form_container_form'>Enter the new password: ";
			s_setval += "<input type='password' name='password'></input>";
			s_setval += "<input type='button' value='Submit' onclick='send_ajax_call_from_form_super(\"/resources/ajax_calls_super.php\", \"user_action_form_container_form\", null);'></input>";
			s_setval += "<input type='hidden' name='username' value='"+this.selectedUsername+"' ></input>";
			s_setval += "<input type='hidden' name='command' value='reset_password'></input>";
			s_setval += "<label class='error'></label><br />";
			s_setval += "</form>";
			break;
		case 'createNew':
			break;
		case 'modifyAccess':
			break;
		case 'delete':
			break;
		case 'enableAccount':
			s_setval += "<form id='user_action_form_container_form'>";
			s_setval += "<input type='hidden' name='username' value='"+this.selectedUsername+"' ></input>";
			s_setval += "<input type='hidden' name='command' value='enable_account'></input>";
			s_setval += "<label class='error'></label>";
			s_setval += "</form>";
			s_setval += "<script type='text/javascript'>send_ajax_call_from_form_super(\"/resources/ajax_calls_super.php\", \"user_action_form_container_form\", null);</script>";
			break;
		}
		
		// insert the content
		jcontainer.html("");
		jcontainer.append(s_setval);
	},

	/**
	 * Sets the password for the given user
	 */
	resetPassword: function() {
	},

	/**
	 * Populates the users in the users management area
	 */
	populateUsers: function() {
		var users_array = this.getUsers(true);
		if (!users_array.success) {
			$("#user_list_content_container").html("");
			$("#user_list_content_container").append("error: "+users_array.details);
			return;
		}
		var users = users_array.users;

		var a_col_names = [];
		var username_index = 0;
		var i = 0;
		$.each(users[0], function(k,v) {
			if (k == "username") {
				username_index = i;
			}
			if (k != "disabled") {
				a_col_names.push(k);
			}
			i++;
		});
		
		// get the rows to add to the table
		// get the names of the disabled students
		var a_rows = [];
		var disabled = [];
		$.each(users, function(key,user) {
			var a_row = [];
			$.each(user, function(k,v) {
				if (k != "disabled") {
					a_row.push(v);
				} else if (parse_int(v) == 1) {
					disabled.push(user.username);
				}
			});
			a_rows.push(a_row);
		});
		this.disabledUsers = disabled;
		
		var table = create_table(a_col_names, a_rows, null, "o_userManager.selectUser");
		$("#user_list_content_container").html("");
		$("#user_list_content_container").append(table);

		// mark disabled rows
		var jtable = $("#user_list_content_container").find("table");
		var rows = jtable.find("tr");
		$.each(rows, function(k,v) {
			var jtr = $(v);
			var cols = jtr.find("td");
			if (cols.length == 0) {
				return;
			}
			var username = $(cols[username_index]).text();
			if (disabled.indexOf(username) > -1) {
				jtr.addClass("disabled");
			}
		});
	},
};