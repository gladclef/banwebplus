typeSchedulerTab = function() {
	this.draw = function() {
		draw_schedule_tab();
	}
	
	this.addByCRN = function(jbutton) {
		var jdiv = get_parent_by_tag('div', jbutton);
		var crn = jdiv.find('input[name=crn]').val();
		var i_crn = parseInt(crn);
		var i_classes_added = 0;
		var jerrors = jdiv.find('[name=errors]');
		
		i_classes_added = o_courses.addUserClass(i_crn);
		if (i_classes_added == 0) {
			var user_classes = o_courses.getUserClasses();
			var b_class_selected = false;
			
			$.each(user_classes, function(k, v) {
				if (v == i_crn)
					b_class_selected = true;
			});
			
			if (b_class_selected)
				set_html_and_fade_in(jerrors, '', '<font style="color:black;">You already selected that class.</font>');
			else
				set_html_and_fade_in(jerrors, '', '<font style="color:red;">Could not find a matching course.</font>');
		} else {
			set_html_and_fade_in(jerrors, '', '<font style="color:gray;">Added course to your schedule.</font>');
			this.draw();
		}
	}
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

function draw_schedule_tab () {
	// remove the old table
	var jcurrent_cont = $("#schedule_tab_user_schedule");
	var jrecent_cont = $("#schedule_tab_user_recently_viewed_schedule");
	kill_children(jcurrent_cont);
	kill_children(jrecent_cont);
	// cet the crn index
	var i_crn_index = get_crn_index_from_headers(headers);
	if (i_crn_index < 0)
		return;
	// get the classes to add to the tables
	var all_classes = o_courses.getCurrentClasses();
	var user_classes = o_courses.getUserClasses();
	var recently_selected_classes = o_courses.getRecentlySelected();
	var current_classes = [];
	var recent_classes = [];
	for (var i = 0; i < all_classes.length; i++) {
		var a_class = all_classes[i];
		var i_crn = parseInt(a_class[i_crn_index]);
		if (jQuery.inArray(i_crn, user_classes) >= 0)
			current_classes.push(a_class);
		if (jQuery.inArray(i_crn, recently_selected_classes) >= 0)
			recent_classes.push(a_class);
	}
	// add the new tables
	draw_add_by_crn();
	jcurrent_cont.append(create_table(headers, current_classes, classes_table_classes, "delayed_schedule_click();add_remove_class"));
	jrecent_cont.append(create_table(headers, recent_classes, classes_table_classes, "delayed_schedule_click();add_remove_class"));
	set_selected_classes(jcurrent_cont);
	conflicting_object.draw_all_conflicts();
}

// it's delayed so that the javascript has time to add and remove classes
function delayed_schedule_click() {
	//setTimeout("click_tab_by_tabname('Schedule');",100);
}

o_schedule = new typeSchedulerTab();