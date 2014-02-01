typeConflictingCourses = function(o_courses) {
	var current_conflicting_classes = [];

	this.getConflictingClasses = function() {
		return current_conflicting_classes;
	}
	
	this.getConflictingSubjects = function() {
		var a_conflicts_all = this.getConflictingClasses();
		var a_conflicts = [];
		var a_user_classes = o_courses.getUserClasses();
		var a_subjects = o_courses.getAvailableSubjects();
		var crn_index = get_crn_index_from_headers(headers);
		var a_retval = [];
		var a_user_classes = o_courses.getUserClasses();
		
		$.each(a_conflicts_all, function(k, v) {
			if (v.length > 0)
				a_conflicts.push(k);
		});
		
		$.each(a_subjects, function(k, a_subject) {
			var s_subject = a_subject[0];
			var a_classes = o_courses.getCurrentClasses(s_subject);
			var b_all_conflict = true;
			if (a_classes.length == 0)
				b_all_conflict = false;
			for (var i = 0; i < a_classes.length; i++) {
				if ($.inArray(a_classes[i][crn_index]+'', a_conflicts) == -1 && $.inArray(a_classes[i][crn_index].trim(), a_user_classes) == -1) {
					b_all_conflict = false;
					break;
				}
			}
			if (b_all_conflict)
				a_retval.push(k);
		});
		return a_retval;
	}

	// takes the conflicts for the given crn
	// and draws them on that class' row
	var update_class_show_conflictions_trs = null; // needed for efficiency
	var update_class_show_conflictions_lastloaded = 0; // needed for efficiency
	this.update_class_show_conflictions = function(s_class_crn) {
		var started = (new Date()).getMilliseconds();
		var s_crn_index = get_crn_index_from_headers(headers);
		var a_trs = null;
		var a_jrow = [];
		var b_found = false;

		if ((new Date()).getTime()/1000-update_class_show_conflictions_lastloaded > 1 ||
			update_class_show_conflictions_trs == null) {
			update_class_show_conflictions_trs = $("tr."+classes_table_classes[2]);
			update_class_show_conflictions_lastloaded = (new Date()).getTime()/1000;
		}
		a_trs = update_class_show_conflictions_trs;

		for (var i = 0; i < a_trs.length; i++) {
			var html_tr = document.getElementById(a_trs[i].id);
			if (html_tr == null)
				continue;
			var a_tds = html_tr.childNodes;
			if (typeof(a_tds[s_crn_index]) == 'undefined')
				continue;
			if (a_tds[s_crn_index].innerHTML.trim() == s_class_crn) {
				b_found = true;
				a_jrow.push($("#"+a_trs[i].id));
				//break;
			}
		}
		if (!b_found)
			return;

		var normal_image = '/images/red_sphere.png';
		var highlighted = '/images/red_sphere.png';//'/images/red_sphere_highlighted.png';
		var i_conflicts_index = get_index_of_header("conflicts", headers);
		for (var i = 0; i < a_jrow.length; i++) {
			var jrow = a_jrow[i];
			if (current_conflicting_classes[s_class_crn].length > 0) {
				edit_class_row_property(jrow, i_conflicts_index, '<div class="centered"><img src="'+normal_image+'" style="width:21px;height:21px" onmouseover="conflicting_object.show_conflicting_classes(this,\''+jrow.prop('id')+'\');$(this).attr(\'src\',\''+highlighted+'\');" onmouseout="conflicting_object.hide_conflicting_classes_popup();$(this).attr(\'src\',\''+normal_image+'\');"><input type="hidden" name="conflicting_classes" value="'+current_conflicting_classes[s_class_crn].join('|')+'" /></div>');
				jrow.addClass("conflicting");
			} else {
				edit_class_row_property(jrow, i_conflicts_index, '');
				jrow.removeClass("conflicting");
			}
		}
	}

	// creates a pop-up with the conflicting classes
	this.show_conflicting_classes = function(element, row_id) {
		var i_conflicts_index = get_index_of_header("conflicts", headers);
		var s_crn_index = get_crn_index_from_headers(headers);
		var i_days_index = get_index_of_header("days", headers);
		var i_time_index = get_index_of_header("time", headers);
		var i_title_index = get_index_of_header("title", headers);
		var jrow = $("#"+row_id);
		var s_text = '';
		var button_pos = $(element).position();
		var button_width = $(element).width();
		var a_to_show = [s_crn_index, i_days_index, i_time_index, i_title_index];
		var a_conflicts = $(element).siblings("input[name=conflicting_classes]").val().split('|');
		var a_classes = o_courses.getCurrentClasses();
		var a_conflicting_todraw = [];
		var a_headers = [];

		// get properties of the conflicting classes
		for (var i = 0; i < a_conflicts.length; i++) {
			var a_current = [];
			var s_crn = a_conflicts[i];
			var a_class = jQuery.grep(a_classes, function(a_value, index) {
				return a_value[s_crn_index] == s_crn;
			})[0];
			for (var j = 0; j < a_to_show.length; j++) {
				a_current.push(a_class[a_to_show[j]]);
			}
			a_conflicting_todraw.push(a_current);
		}
		// get the headers
		for (var i = 0; i < a_to_show.length; i++)
			a_headers.push(headers[a_to_show[i]]);
		
		var s_id = "conflicting_table_popup";
		var s_text_head = '<div id="'+s_id+'" class="popup" style="left:'+(button_pos.left+button_width)+'px;" onmouseover="conflicting_object.show_conflicting_classes_popup();" onmouseout="conflicting_object.hide_conflicting_classes_popup();">';
		var s_text_body = create_table(a_headers, a_conflicting_todraw);
		var s_text_foot = '</div>';
		if ($("#"+s_id).length > 0) {
			var jpopup = $("#"+s_id);
			kill_children(jpopup);
			jpopup.append(s_text_body);
			this.show_conflicting_classes_popup();
		} else {
			var s_text = s_text_head + s_text_body + s_text_foot;
			$(s_text).appendTo($('table')[0]);
		}
		this.move_conflicting_classes_popup_y(element);
	}

	this.show_conflicting_classes_popup = function() {
		var jpopup = $("#conflicting_table_popup");
		jpopup.stop(true,true);
		jpopup.css({opacity:1});
		jpopup.show();
	}

	this.hide_conflicting_classes_popup = function() {
		var jpopup = $("#conflicting_table_popup");
		jpopup.stop(true, true);
		jpopup.animate(
			{opacity:0},
			500,
			function() {
				$("#conflicting_table_popup").hide();
			}
		);
	}

	this.move_conflicting_classes_popup_y = function(element) {
		var jpopup = $("#conflicting_table_popup");
		var i_height = jpopup.height();
		var d_button_pos = $(element).position();
		var i_y = d_button_pos.top-i_height/2;
		
		if ($(window).length > 0)
			i_y = Math.min(i_y, $(window).height());
		i_y = Math.max(i_y, 0);
		
		jpopup.stop(true, true);
		jpopup.animate({top:i_y}, 300);
	}

	// adds one new user class to the conflicting classes
	this.calculate_conflicting_classes_add_class = function(s_crn, function_call) {
		var a_conflicts = this.get_conflicting_classes_of_class(s_crn);
		for (var i = 0; i < a_conflicts.length; i++) {
			var i_conflicting = a_conflicts[i];
			current_conflicting_classes[i_conflicting].push(s_crn);
			if (function_call != null)
				function_call(i_conflicting);
		}
		this.afterConflictionsCalculated();
	}

	// removes one old user class from the conflicting classes
	this.calculate_conflicting_classes_remove_class = function(s_crn, function_call) {
		var a_conflicts = this.get_conflicting_classes_of_class(s_crn);
		for (var i = 0; i < a_conflicts.length; i++) {
			var i_conflicting = a_conflicts[i];
			current_conflicting_classes[i_conflicting] = current_conflicting_classes[i_conflicting].filter(function(value) {
				return value != s_crn;
			});
			if (function_call != null)
				function_call(i_conflicting);
		}
		this.afterConflictionsCalculated();
	}

	// searches through all classes and finds ones that conflict with the given class
	this.get_conflicting_classes_of_class = function(s_class_crn) {
		var s_crn_index = get_crn_index_from_headers(headers);
		var i_day_index = get_index_of_header("days", headers);
		var i_time_index = get_index_of_header("time", headers);
		var a_class = o_courses.getClassByCRN(s_class_crn).course;
		var a_classes = o_courses.getCurrentClasses();
		var d_class_stats = {};
		var a_retval = [];

		if (a_class == null)
			return [];

		d_class_stats = get_class_stats_from_class_array(a_class, s_crn_index, i_day_index, i_time_index);
		for (var i = 0; i < a_classes.length; i++) {
			d_other_stats = get_class_stats_from_class_array(a_classes[i], s_crn_index, i_day_index, i_time_index);
			if (d_other_stats['id'] == d_class_stats['id'])
				continue;
			if (d_class_stats['days'].filter(function(value){
				return (value != '' && a_classes[i][i_day_index].indexOf(value) != -1);
			}).length == 0)
				continue;
			if (d_class_stats['st'] < d_other_stats['et'] && d_class_stats['et'] > d_other_stats['st'])
				a_retval.push(d_other_stats['id']);
		}

		return a_retval;
	}

	// searches through all classes and finds the ones that conflict with user selected classes
	this.calculate_conflicting_classes = function() {
		this.init_conflicting_array();
		var a_user_classes = o_courses.getUserClasses();
		for (var i = 0; i < a_user_classes.length; i++) {
			var s_class_crn = a_user_classes[i].trim();
			var a_conflicts = this.get_conflicting_classes_of_class(s_class_crn);
			for (var j = 0; j < a_conflicts.length; j++) {
				var i_conflicting = a_conflicts[j];
				current_conflicting_classes[i_conflicting].push(s_class_crn);
			}
		}
		this.afterConflictionsCalculated();
	}
	
	this.afterConflictionsCalculated = function() {
		o_classes.afterConflictionsCalculated(this.getConflictingClasses());
	}

	// should be called upon table creation
	this.draw_all_conflicts = function() {
		this.calculate_conflicting_classes();
		var a_courses_with_conflicts = [];
		$.each(current_conflicting_classes, function(key, value) {
			if (value.length > 0)
				a_courses_with_conflicts.push(key.trim());
		});
		for (var i = 0; i < a_courses_with_conflicts.length; i++) {
			s_crn = a_courses_with_conflicts[i];
			this.update_class_show_conflictions(s_crn);
		}
	}

	this.init_conflicting_array = function() {
		var s_crn_index = get_crn_index_from_headers(headers);
		var a_classes = o_courses.getCurrentClasses();
		current_conflicting_classes = {};
		for (var i = 0; i < a_classes.length; i++)
			current_conflicting_classes[a_classes[i][s_crn_index].trim()] = [];
	}
}