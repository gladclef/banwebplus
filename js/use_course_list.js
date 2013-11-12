headers = ['Conflicts', 'Select', 'CRN', 'Course', '*Campus', 'Days', 'Time', 'Location', 'Hrs', 'Title', 'Instructor', 'Seats', 'Limit', 'Enroll'];
classes_table_classes = ['auto_table_classes', 'auto_header_classes', 'auto_row_classes'];

o_courses = null;

$(
	function() {
		o_courses = new typeCoursesList();
		o_courses.getAvailableSemesters();
		s_default_semester = o_courses.getDefaultSemester();
		o_courses.setSemester(s_default_semester);
		draw_subject_selector("subject_selector");
		updateClassesTab();
		draw_tab("Classes");
		o_semesterHeader.draw();
		setTimeout('draw_tab("Classes");', 100);
		setTimeout('scroll_to_center();', 200);
	}
);

function updateClassesTab() {
	$("#subject_selector").change();
}

// draws additional selector by cloning the old selector
add_extra_subject_index = 0;
function add_extra_subject(add_subject_button) {
	var jbutton = $(add_subject_button);
	var s_new_subject = $("#subject_selector").outerHTML();
	var s_id = "subject_selector_"+add_extra_subject_index;
	s_new_subject = s_new_subject.replace("subject_selector", s_id);
	s_new_subject = s_new_subject.replace('<option','<option value="remove">Remove</option><option value="-1">&nbsp;</option><option');
	jbutton.before(s_new_subject);
	$("#subject_selector_"+add_extra_subject_index).val(-1);
	add_extra_subject_index++;
	return s_id;
}

// gets the id and name of the subject selector
function get_subject_selectors_values() {
	var jclasses = $("#Classes");
	var jselectors = jclasses.children("select[id^=subject_selector]");
	var retval = [];
	$.each(jselectors, function(k,v) {
		name = $(v).children("option[value="+v.value+"]").text();
		retval.push({id: v.id, value: v.value, name: name});
	});
	return retval;
}

// rebuilds the subject selectors from their values
// @param a_values array An array in the form [{id:selectorid, value:selectorvalue, name:nameofvalue}, ...]
function rebuild_subject_selectors_from_values(a_values) {
	
	// get some values
	var jbutton = $("#add_subject_button");
	
	// get the subjects and their indices
	var jsubjects = $("#subject_selector").children("option");
	var a_index_by_subject = [];
	$.each(jsubjects, function(k,v) {
		var jsubject = $(v);
		a_index_by_subject[jsubject.text()] = jsubject.val();
	});

	// draw each subject in turn
	$.each(a_values, function(k,v) {
		var jselector = $("#"+v.id);
		if (typeof(a_index_by_subject[v.name]) !== 'undefined') {
			console.log(v.name+", "+a_index_by_subject[v.name]);
			if (jselector.length > 0 && v.id != "subject_selector") {
				console.log(v.id);
				jselector.remove();
				var id = add_extra_subject(jbutton, a_index_by_subject[v.name]);
				jselector = $("#"+id);
			} else if (jselector.length == 0) {
				var id = add_extra_subject(jbutton, a_index_by_subject[v.name]);
				jselector = $("#"+id);
			}
			jselector.val(a_index_by_subject[v.name]);
		}
	});
	
	draw_course_table();
}

// draws the original selector (and ONLY the original selector)
function draw_subject_selector(index) {
	var jselect = $("#subject_selector");
	kill_children($("#subject_selector"));
	var options = '';
	var subjects = o_courses.getAvailableSubjects();
	for(var i = 0; i < subjects.length; i++) {
		var selected = '';
		if (i == index)
			selected = " selected";
		options += '<option value="'+i+'"'+selected+'>'+subjects[i][1]+'</option>';
	}
	jselect.append(options);
}

// returns an array of indices from each selector
// only returns unique indices
function get_course_table_indices() {
	// get indices
	var a_retval = [];
	var a_to_remove = [];
	var a_selectors = $("select[id^=subject_selector]");
	for (var i = 0; i < a_selectors.length; i++) {
		var jselector = $(a_selectors[i]);
		if (jselector.length == 0)
			break;
		var i_index = parseInt(jselector.val());
		if (jselector.val() == "remove") {
			a_to_remove.push(jselector);
			i_index = -1;
		}
		if (jQuery.inArray(i_index, a_retval) == -1)
			a_retval.push(i_index);
	}
	// remove selectors with a value="remove"
	for (var i = 0; i < a_to_remove.length; i++)
		a_to_remove[i].remove()

	return a_retval;
}

// FUN!!! :D
// creates a subject selector for every subject
function add_extra_subject_all() {
	var a_selectors = $("select[id^=subject_selector]");
	var a_subjects = o_courses.getAvailableSubjects();
	for (var i = 0; i < a_subjects.length; i++) {
		if (a_selectors.length <= i) {
			$("#add_subject_button").click();
			var a_selectors = $("select[id^=subject_selector]");
		}
		$(a_selectors[i]).val(i);
	}
	if (a_selectors.length > 0)
		$(a_selectors[0]).change();
}

function draw_course_table() {
	// get the course lists
	var a_indices = get_course_table_indices();
	var a_classes = [];
	var a_subjects = o_courses.getAvailableSubjects();
	for (var i = 0; i < a_indices.length; i++) {
		var subject_index = a_indices[i];
		if (subject_index >= 0)
			a_classes = $.merge(a_classes, o_courses.getCurrentClasses(a_subjects[subject_index][0]));
	}
	var jclasses_content = $("#classes_content");
	kill_children(jclasses_content);
	jclasses_content.html('');
	if (a_classes.length == 0) {
		// draw an empy message
		var s_message = (a_subjects.length > 1) ? 'These subjects have no classes.' : 'This subject has no classes.';
		jclasses_content.append('<div class="centered" style="width:'+(jclasses_content.width()-100)+'px; padding:12px; border:1px solid gray; border-radius:5px; color:black; background-color:#ddd;">'+s_message+'</div>');
	} else {
		// draw the table
		//create_table(a_col_names, a_rows, wt_class, row_click_function)
		jclasses_content.append(create_table(headers, a_classes, classes_table_classes, 'add_remove_class'));
		set_selected_classes(jclasses_content);
		// draw the conflicts
		conflicting_object.draw_all_conflicts();
	}
	jclasses_content.stop(true,true);
	jclasses_content.css({opacity:0});
	jclasses_content.animate({opacity:1},500);
	// update the select boxes
	o_classes.updateSelectClasses();
}

// sets the "selected" class for selected classes
function set_selected_classes(jcontainer_of_table) {
	//var jtable = $(jcontainer_of_table.children("table")[0]);
	var a_tables = $("table."+classes_table_classes[0]);
	if (a_tables.length == 0)
		return;
	for (table_index = 0; table_index < a_tables.length; table_index++) {
		var jtable = $(a_tables[table_index]);
		var a_rows = jtable.children();
		var i_select_index = get_index_of_header("select", headers);
		while (! $(a_rows[0]).is("tr") && a_rows.length > 0)
			a_rows = $(a_rows[0]).children();
		if (a_rows.length == 0)
			return;
		var i_crn_index = get_crn_index($(a_rows[0]));
		if (i_crn_index < 0)
			return;
		var current_user_classes = o_courses.getUserClasses();
		for(var i = 1; i < a_rows.length; i++) {
			var jrow = $(a_rows[i]);
			var i_crn_of_class = parseInt($(jrow.children()[i_crn_index]).text());
			if (jQuery.inArray(i_crn_of_class,current_user_classes) == -1) {
				edit_class_row_property(jrow, i_select_index, '');
				jrow.removeClass("selected");
			} else {
				edit_class_row_property(jrow, i_select_index, '<div class="centered"><img src="/images/blue_sphere.png" style="width:21px;height:21px"></div>');
				jrow.addClass("selected");
			}
		}
	}
}

// sets the "conflicting" classes of the given
function set_conflicting_classes(jcontainer_of_table) {
	var jtable = $(jcontainer_of_table.children("table")[0]);
	var a_rows = jtable.children();
	while (! $(a_rows[0]).is("tr") && a_rows.length > 0)
		a_rows = $(a_rows[0]).children();
	if (a_rows.length == 0)
		return;
	var i_crn_index = get_crn_index($(a_rows[0]));
	if (i_crn_index < 0)
		return;
	for(var i = 1; i < a_rows.length; i++) {
		var jrow = $(a_rows[i]);
		var i_crn_of_class = parseInt($(jrow.children()[i_crn_index]).text());
		var a_con_classes = conflicting_object.getConflictingClasses();
		if (jQuery.inArray(i_crn_of_class, a_con_classes) == -1) {
			jrow.removeClass("conflicting");
		} else {
			jrow.addClass("conflicting");
		}
	}
}

// puts all courses in from all subjects into a single array
function get_array_of_all_classes() {
	return o_courses.getCurrentClasses();
}

function create_courses_table(jcontainer, a_col_names, a_rows, wt_class, row_click_function) {
	var s_table = create_table(a_col_names, a_rows, wt_class, row_click_function);
	if (jcontainer !== null) {
		jcontainer.append(s_table);
		set_selected_classes(jcontainer);
	}
	return s_table;
}

function get_crn_index(jheader_row) {
	var i_crn_index = -1;
	for (var i = 0; i < jheader_row.children().length; i++)
		if ($(jheader_row.children()[i]).text().toLowerCase() == "crn") {
			i_crn_index = i;
			break;
		}
	return i_crn_index;
}

function get_crn_index_from_headers(a_headers) {
	return get_index_of_header("crn", a_headers);
}

function get_index_of_header(s_name, a_headers) {
	s_name = s_name.toLowerCase();
	var i_index = -1;
	for (var i = 0; i < a_headers.length; i++)
		if (a_headers[i].toLowerCase() == s_name) {
			i_index = i;
			break;
		}
	return i_index;
}

// finds the row and changes the value at i_index to s_newval
function edit_class_row_property(jclass_row, i_index, s_newval) {
	var i_crn_index = get_crn_index_from_headers(headers);
	var a_tds = jclass_row.children();
	var s_crn = $(a_tds[i_crn_index]).text();
	var jtd = $(a_tds[i_index]);
	jtd.html(s_newval);
	var a_class = o_courses.getClassByCRN(s_crn);
	if (s_crn == a_class[i_crn_index]) {
		a_class[i_index] = s_newval;
	}
}

function add_remove_class(class_row) {
	// get the index of "CRN" in the table header
	var jclass_row = $(class_row);
	var jheader_row = $(jclass_row.siblings()[0]);
	var i_crn_index = get_crn_index(jheader_row);
	var i_select_index = get_index_of_header("select", headers);
	if (i_crn_index == -1)
		return;
	// get the CRN of the class
	var i_crn = parseInt(
		$(jclass_row.children()[i_crn_index]).text()
	);
	// add or remove the class from the user's schedule
	if (!jclass_row.hasClass("selected")) {
		// saves/updates conflicts automatically
		o_courses.addUserClass(i_crn);
		// update the gui
		jclass_row.addClass("selected");
		if (i_select_index > -1)
			edit_class_row_property(jclass_row, i_select_index, '<div class="centered"><img src="/images/blue_sphere.png" style="width:21px;height:21px"></div>');
	} else {
		o_courses.removeUserClass(i_crn);
		jclass_row.removeClass("selected");
		if (i_select_index > -1)
			edit_class_row_property(jclass_row, i_select_index, "");
	}
	// mark classes as selected
	set_selected_classes();
	// update the select boxes
	o_classes.updateSelectClasses();
}

// gets a class from the list of all classes by crn
function get_class(i_class_crn) {
	var a_class_vars = o_courses.getClassByCRN(i_class_crn);
	if (typeof(a_class_vars) != 'undefined' && typeof(a_class_vars['course']) != 'undefined')
		return a_class_vars['course'];
	return null;
}

// a_class should be the row from full_course_list
function get_class_stats_from_class_array(a_class, i_crn_index, i_day_index, i_time_index) {
	i_id = parseInt(a_class[i_crn_index]);
	a_days = a_class[i_day_index].split(' ');
	i_st = parse_int(a_class[i_time_index].split('-')[0]);
	i_et = parse_int(a_class[i_time_index].split('-')[1]);
	return {'id': i_id, 'days': a_days, 'st': i_st, 'et': i_et}
}

function save_semester_classes() {
	var temp_user_classes = o_courses.getUserClasses();
	var a_postvars = {};
	a_postvars["timestamp"] = get_date();
	// todo: continue replacing global course listings from here
	a_postvars["year"] = o_courses.getCurrentYear()['school_year'];
	a_postvars["semester"] = o_courses.getCurrentSemester()['value'];
	a_postvars["classes"] = JSON.stringify(temp_user_classes);
	a_postvars["command"] = "save_classes";
	send_async_ajax_call("/resources/ajax_calls.php", a_postvars);
}

typeClassesTab = function() {
	this.afterConflictionsCalculated = function(a_conflicting) {
		var a_conflicting_subject_indexes = conflicting_object.getConflictingSubjects();
		var a_empty_subject_indexes = o_courses.getEmptySubjects();
		var a_all_selected_indexes = o_courses.getSelectedSubjects();
		var a_selects = this.getSelects();
		
		$.each($('option'), function(k, v) {
			var jelement = $(v);
			jelement.removeClass('empty');
			jelement.removeClass('conflicting');
			jelement.removeClass('selected');
		});
		$.each(a_conflicting_subject_indexes, function(k, option_index) {
			$.each(a_selects, function(k, select) {
				$(select).find('option[value='+option_index+']').addClass('conflicting');
			});
		});
		$.each(a_empty_subject_indexes, function(k, option_index) {
			$.each(a_selects, function(k, select) {
				$(select).find('option[value='+option_index+']').addClass('empty');
			});
		});
		$.each(a_all_selected_indexes, function(k, option_index) {
			$.each(a_selects, function(k, select) {
				$(select).find('option[value='+option_index+']').addClass('selected');
			});
		});
	}
	
	this.getSelects = function() {
		return $("select[id^=subject_select]");
	}
	
	this.updateSelectClasses = function() {
		var a_selects = this.getSelects();
		
		$.each(a_selects, function(k, select) {
			var jselect = $(select);
			var joption = $(jselect.find('option[value='+jselect.val()+']'));
			if (joption.length > 0) {
				var a_option_classes = joption.prop('class').split(' ');
				var a_select_classes = jselect.prop('class').split(' ');
				$.each(a_select_classes, function(k, v) {
					jselect.removeClass(v);
				});
				$.each(a_option_classes, function(k, v) {
					jselect.addClass(v);
				});
			}
		});
	}
}

o_classes = new typeClassesTab();