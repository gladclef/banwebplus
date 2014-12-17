typeSchedulerTab = function() {
	
	// a list of all users this user's schedule is shared with,
	// as a map of username->boolean
	this.sharedUsers = {};
	// a list of all users that have shared their schedules with this user,
	// including their schedule, as an array of objects:
	// [{username:string, schedule:[crn1, crn2, ...]}, ...]
	this.otherUserSchedules = {};
	
	this.draw = function() {
		
		// remove the old table
		var jcurrent_cont = $("#schedule_tab_user_schedule");
		var jrecent_cont = $("#schedule_tab_user_recently_viewed_schedule");
		kill_children(jcurrent_cont);
		kill_children(jrecent_cont);
		
		// get the crn index
		var s_crn_index = get_crn_index_from_headers(headers);
		if (s_crn_index < 0)
			return;
		
		// get the classes to add to the tables
		var all_classes = o_courses.getCurrentClasses();
		var user_classes = o_courses.getUserClasses();
		var recently_selected_classes = o_courses.getRecentlySelected();
		var current_classes = [];
		var recent_classes = [];
		for (var i = 0; i < all_classes.length; i++) {
			var a_class = all_classes[i];
			var s_crn = a_class[s_crn_index].trim();
			if (jQuery.inArray(s_crn, user_classes) >= 0)
				current_classes.push(a_class);
			if (jQuery.inArray(s_crn, recently_selected_classes) >= 0)
				recent_classes.push(a_class);
		}
		
		// add the new tables
		draw_add_by_crn();
		jcurrent_cont.append(create_table(headers, current_classes, classes_table_classes, "add_remove_class"));
		jrecent_cont.append(create_table(headers, recent_classes, classes_table_classes, "add_remove_class"));
		set_selected_classes(jcurrent_cont);
		conflicting_object.draw_all_conflicts();

		// draw the share schedule option
		this.drawShareSchedule();
	}

	this.drawShareSchedule = function() {
		
		// find the container
		var o_this = this;
		var jshareContainer = $("#schedule_tab_share_schedule");
		var junshareContainer = $("#schedule_tab_unshare_schedule");
		
		// build the contents for sharing
		var shareHtml = '';
		shareHtml += 'Share your calender with another banwebplus user!<br /> Just enter their username here:<br />';
		shareHtml += '<input type="hidden" name="command" value="share_user_schedule" />';
		shareHtml += '<input type="textbox" placeholder="username" name="username" onkeypress="if (event.keyCode == 13) { $(this).parent().find(\'input[type=button]\').click(); }" />';
		shareHtml += '<input type="button" value="Share" onclick="o_schedule.shareScheduleWithUser();" /><br />';
		shareHtml += '<label class="errors">&nbsp;</label>';

		// build the contents for unsharing
		var unshareHtml = '';
		unshareHtml += 'Remove users from sharing your calendar:<br />';
		var columns = ['Username', 'Remove'];
		var rows = [];
		var numSharedUsers = 0;
		$.each(o_this.sharedUsers, function(username, shared) {
			if (shared) {
				rows.push([username, "<img src='/images/trash.png' style='height:16px; width:16px; padding:0; cursor:pointer; border:none;' onclick='o_schedule.unshareScheduleWithUser(\""+username+"\");'>"]);
				numSharedUsers++;
			}
		});
		unshareHtml += create_table(columns, rows, null, null);
		if (numSharedUsers == 0) {
			unshareHtml = '';
		}
		
		// insert the contents
		if (jshareContainer.length > 0) {
			jshareContainer.html('');
			jshareContainer.append(shareHtml);
		}
		if (junshareContainer.length > 0) {
			junshareContainer.html('');
			junshareContainer.append(unshareHtml);
		}
	};

	this.loadSharedUsers = function() {
		var o_this = this;
		var year = o_courses.getCurrentYear().school_year;
		var semester = o_courses.getCurrentSemester().value;
		var successFunc = function(data) {
			data = JSON.parse(data);
			o_this.sharedUsers = data.sharedUsers;
			o_this.otherUserSchedules = data.otherUserSchedules;
		};
		$.ajax({
			async: true,
			cache: false,
			url: "/resources/ajax_calls.php",
			data: {command:'load_shared_user_schedules', year:year, semester:semester},
			type: "POST",
			success: successFunc
		});
	};
	
	this.addByCRN = function(jbutton) {
		var jdiv = get_parent_by_tag('div', jbutton);
		var crn = jdiv.find('input[name=crn]').val();
		var s_crn = crn.trim();
		var i_classes_added = 0;
		var jerrors = jdiv.find('[name=errors]');
		
		i_classes_added = o_courses.addUserClass(s_crn);
		if (i_classes_added == 0) {
			var user_classes = o_courses.getUserClasses();
			var b_class_selected = false;
			
			$.each(user_classes, function(k, v) {
				if (v == s_crn)
					b_class_selected = true;
			});
			
			if (b_class_selected)
				set_html_and_fade_in(jerrors, '', '<span style="color:black;">You already selected that class.</span>');
			else
				set_html_and_fade_in(jerrors, '', '<span style="color:red;">Could not find a matching course.</span>');
		} else {
			set_html_and_fade_in(jerrors, '', '<span style="color:gray;">Added course to your schedule.</span>');
			this.draw();
		}
	}
	
	this.drawicalendarLink = function() {
		var jcal = $("#icalendar_reveal_link");

		if (jcal.hasClass("visible"))
			return;
		jcal.stop(true, true);
		jcal.show();
		jcal.children().css({ width:"0px", opacity:0 });
		jcal.children().animate({ width:"400px", opacity:1 }, 300, "linear");
		jcal.addClass("visible");
	}
	
	this.drawGuestCalendarLink = function(classList) {
		if (getUsername() !== "guest")
			return false;
		
		var jschedule = $("#Schedule");
		var jlink = jschedule.find(".icalendarGuestDownloadLink");

		var classes = "";
		for (var i = 0; i < classList.length; i++) {
			classes += classList[i];
			if (i < classList.length-1)
				classes += ",";
		}
		var sem = o_courses.getCurrentSemester();
		var href = "/pages/icalendar/calendars/guest/"+sem.year.school_year+sem.value+"/"+classes+"/"+sem.name+"_"+sem.year.year+".ics";
		jlink.attr("href", href);

		return true;
	}

	this.shareScheduleWithUser = function() {
		
		// send the ajax call
		var a_commands = send_ajax_call_from_form("/resources/ajax_calls.php", "schedule_tab_share_schedule");

		// go through each command, looking for ones specific to this case
		for (var i = 0; i < a_commands.length; i++) {
			var command = a_commands[i][0];
			var note = a_commands[i][1];
			if (command == "share with user") {
				username = note;
				this.sharedUsers[username] = true;
				this.drawShareSchedule();
				var jform = $("#schedule_tab_share_schedule");
				var jerrors_label = $(jform.find("label.errors"));
				set_html_and_fade_in(jerrors_label, '', '<span style="color:gray;font-weight:normal;">Shared schedule with '+username+'</span>');
			}
		}
	};

	this.unshareScheduleWithUser = function(username) {
		
		// go through each command, looking for ones specific to this case
		var o_this = this;
		var successFunc = function(data) {
			if (data === "success") {
				o_this.sharedUsers[username] = false;
				o_this.drawShareSchedule();
				var jform = $("#schedule_tab_unshare_schedule");
				var jerrors_label = $(jform.find("label.errors"));
				set_html_and_fade_in(jerrors_label, '', '<span style="color:gray;font-weight:normal;">removed user '+username+'</span>');
			}
		}

		// send the ajax call
		$.ajax({
			cache: false,
			async: true,
			url: "/resources/ajax_calls.php",
			type: "POST",
			data: {command:"unshare_user_schedule", username:username},
			success: successFunc
		});
	};
}

function draw_add_by_crn() {
	
	var jcontainer = $("#schedule_tab_add_by_crn");
	kill_children(jcontainer);
	jcontainer.html('');
	var html = 'Enter a course refference number: ';
	html += '<input type="textarea" size="8" placeholder="123456" name="crn" onkeypress="if (event.keyCode == 13) { $(this).parent().find(\'input[type=button]\').click(); }" /> ';
	html += '<input type="button" onclick="o_schedule.addByCRN($(this));" value="Add" /><br />';
	html += '<label name="errors">&nbsp;</label>';
	jcontainer.append(html);
}

o_schedule = new typeSchedulerTab();
window.o_schedule = o_schedule;