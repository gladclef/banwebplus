current_user_classes = [];
current_blacklist = [];
current_whitelist = [];
current_schedule = [];
current_year = 2013;
current_semester = 30;
current_conflicting_classes = [];
full_course_list = [];
headers = [];
subjects = [];
recently_selected_classes = [];

$(
	function() {
		init_course_list();
		draw_subject_selector("subject_selector");
		$("#subject_selector").change();
		draw_tab("Classes");
		setTimeout('draw_tab("Classes");', 100);
		setTimeout('scroll_to_center();', 200);
	}
);

// draws additional selector by cloning the old selector
add_extra_subject_index = 0;
function add_extra_subject(add_subject_button) {
	var jbutton = $(add_subject_button);
	var s_new_subject = $("#subject_selector").outerHTML();
	s_new_subject = s_new_subject.replace("subject_selector", "subject_selector_"+add_extra_subject_index);
	s_new_subject = s_new_subject.replace('<option','<option value="remove">Remove</option><option value="-1">&nbsp;</option><option');
	jbutton.before(s_new_subject);
	$("#subject_selector_"+add_extra_subject_index).val(-1);
	add_extra_subject_index++;
}

// draws the original selector (and ONLY the original selector)
function draw_subject_selector(index) {
	var jselect = $("#subject_selector");
	kill_children($("#subject_selector"));
	var options = '';
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
	for (var i = 0; i < full_course_list.length; i++) {
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
	for (var i = 0; i < a_indices.length; i++) {
		var subject_index = a_indices[i];
		if (subject_index >= 0)
			a_classes = $.merge(a_classes, full_course_list[subject_index]);
	}
	if (a_classes.length == 0)
		return;
	// draw the table
	var jclasses_content = $("#classes_content");
	kill_children(jclasses_content);
	//create_table(a_col_names, a_rows, wt_class, row_click_function)
	jclasses_content.append(create_table(headers, a_classes, null, 'add_remove_class'));
	set_selected_classes(jclasses_content);
	jclasses_content.stop(true,true);
	jclasses_content.css({opacity:0});
	jclasses_content.animate({opacity:1},500);
	// draw the conflicts
	draw_all_conflicts();
}

// sets the "selected" class for selected classes
function set_selected_classes(jcontainer_of_table) {
	var jtable = $(jcontainer_of_table.children("table")[0]);
	var a_rows = jtable.children();
	var i_select_index = get_index_of_header("select", headers);
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
		if (jQuery.inArray(i_crn_of_class,current_user_classes) == -1) {
			jrow.removeClass("selected");
		} else {
			edit_class_row_property(jrow, i_select_index, '<div class="centered"><img src="/images/blue_sphere.png" style="width:21px;height:21px"></div>');
			jrow.addClass("selected");
		}
	}
	set_conflicting_classes(jcontainer_of_table);
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
		if (jQuery.inArray(i_crn_of_class,current_conflicting_classes) == -1) {
			jrow.removeClass("conflicting");
		} else {
			jrow.addClass("conflicting");
		}
	}
}

// puts all courses in full_course_list into a single array
function get_array_of_all_classes() {
	var a_all = [];
	for (var i = 0; i < full_course_list.length; i++) {
		var a_subject = full_course_list[i];
		for (var j = 0; j < a_subject.length; j++)
			a_all.push(a_subject[j]);
	}
	return a_all;
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

function edit_class_row_property(jclass_row, i_index, s_newval) {
	var i_crn_index = get_crn_index_from_headers(headers);
	var a_tds = jclass_row.children();
	var i_crn = parseInt($(a_tds[i_crn_index]).text());
	var jtd = $(a_tds[i_index]);
	jtd.html(s_newval);
	for (var i = 0; i < full_course_list.length; i++) {
		var a_classes = full_course_list[i];
		var b_found = false;
		for (var j = 0; j < a_classes.length; j++) {
			var a_class = a_classes[j];
			if (i_crn == parseInt(a_class[i_crn_index])) {
				a_class[i_index] = s_newval;
				b_found = true;
				break;
			}
		}
		if (b_found)
			break;
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
		current_user_classes.push(i_crn);
		recently_selected_classes = jQuery.grep(recently_selected_classes, function(value) {
			return value != i_crn;
		});
		jclass_row.addClass("selected");
		if (i_select_index > -1)
			edit_class_row_property(jclass_row, i_select_index, '<div class="centered"><img src="/images/blue_sphere.png" style="width:21px;height:21px"></div>');
		// calculate conflicting classes
		calculate_conflicting_classes_add_class(i_crn, update_class_show_conflictions);
	} else {
		recently_selected_classes.push(i_crn);
		current_user_classes = jQuery.grep(current_user_classes, function(value) {
			return value != i_crn;
		});
		jclass_row.removeClass("selected");
		if (i_select_index > -1)
			edit_class_row_property(jclass_row, i_select_index, "");
		// calculate conflicting classes
		calculate_conflicting_classes_remove_class(i_crn, update_class_show_conflictions);
	}
	// save the schedule
	save_semester_classes();
}

// gets a class from the list of all classes by crn
function get_class(i_class_crn) {
	var i_crn_index = get_crn_index_from_headers(headers);
	var a_class = [];
	var a_classes = get_array_of_all_classes();
	var b_found = false;

	for (var i = 0; i < a_classes.length; i++) {
		a_class = a_classes[i];
		if (parseInt(a_class[i_crn_index]) == i_class_crn) {
			b_found = true;
			break;
		}
	}
	if (b_found)
		return a_class;
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
	var temp_user_classes = current_user_classes;
	var a_postvars = {};
	a_postvars["timestamp"] = get_date();
	a_postvars["year"] = current_year;
	a_postvars["semester"] = current_semester;
	a_postvars["classes"] = temp_user_classes.join("|");
	a_postvars["command"] = "save_classes";
	send_async_ajax_call("/resources/ajax_calls.php", a_postvars);
}

// initializes the course info when changeing semesters
function init_course_list() {
	// initialize the classes
	s_semester = current_year+""+current_semester;
	full_course_list = eval("full_course_list_"+s_semester);
	headers = eval("headers_"+s_semester);
	subjects = eval("subjects_"+s_semester);
	for(var i = 0; i < full_course_list.length; i++){
		var course_list = full_course_list[i];
		for (var j = 0; j < course_list.length; j++) {
			course_list[j].splice(0,0,"","");
		}
		full_course_list[i] = course_list;
	}
	// get user data
	var a_postvars = {"command": "load_classes", "year": current_year, "semester": current_semester};
	current_user_classes = send_ajax_call("/resources/ajax_calls.php", a_postvars).split("|");
	for (var i = 0; i < current_user_classes.length; i++)
		current_user_classes[i] = parseInt(current_user_classes[i]);
	// initialize conflicting classes
	init_conflicting_array();
}