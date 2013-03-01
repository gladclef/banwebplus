current_user_classes = [];
current_blacklist = [];
current_whitelist = [];
current_schedule = [];
current_semester = [2013, 30];
full_course_list = [];
headers = [];
subjects = [];

$(
	function() {
		init_course_list();
		draw_subject_selector("subject_selector");
		$("#subject_selector").change();
		draw_tab("Classes");
		setTimeout('draw_tab("Classes");', 100);
	}
);

// draws additional selector by cloning the old selector
add_extra_subject_index = 0;
function add_extra_subject(add_subject_button) {
	var jbutton = $(add_subject_button);
	var s_new_subject = $("#subject_selector").outerHTML();
	s_new_subject = s_new_subject.replace("subject_selector", "subject_selector_"+add_extra_subject_index);
	s_new_subject = s_new_subject.replace('<option','<option value="-1">&nbsp;</option><option');
	jbutton.before(s_new_subject);
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
	var a_retval = [];
	var a_selectors = $("select[id^=subject_selector]");
	for (var i = 0; i < a_selectors.length; i++) {
		var jselector = $(a_selectors[i]);
		if (jselector.length == 0)
			break;
		var i_index = parseInt(jselector.val());
		if (jQuery.inArray(i_index, a_retval) == -1)
			a_retval.push(i_index);
	}
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
	jclasses_content.append(create_table(headers,
										 a_classes,
										 null,
										 'add_remove_class'));
	jclasses_content.stop(false,true);
	jclasses_content.css({opacity:0});
	jclasses_content.animate({opacity:1},500);
}

function add_remove_class(class_row) {
	// get the index of "CRN" in the table header
	var jclass_row = $(class_row);
	var jheader_row = $(jclass_row.siblings()[0]);
	var i_crn_index = -1;
	for (var i = 0; i < jheader_row.children().length; i++)
		if ($(jheader_row.children()[i]).text().toLowerCase() == "crn") {
			i_crn_index = i;
			break;
		}
	if (i_crn_index == -1)
		return;
	// get the CRN of the class
	var i_crn = parseInt(
		$(jclass_row.children()[i_crn_index]).text()
	);
	// add or remove the class from the user's schedule
	if (!jclass_row.hasClass("selected")) {
		current_user_classes.push(i_crn);
		jclass_row.addClass("selected");
	} else {
		current_user_classes = jQuery.grep(current_user_classes, function(value) {
			return value != i_crn;
		});
		jclass_row.removeClass("selected");
	}
}

function init_course_list() {
	s_semester = current_semester[0]+""+current_semester[1];
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
}