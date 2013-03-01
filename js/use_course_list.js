$(
	function() {
		init_course_list();
		draw_subject_selector(-1);
		draw_tab("Classes");
	}
);

function draw_subject_selector(index) {
	var jselect = $("#subject_selector");
	kill_children($("#subject_selector"));
	var options = '<option value="-1">&nbsp;</option>';
	for(var i = 0; i < subjects.length; i++) {
		var selected = '';
		if (i == index)
			selected = " selected";
		options += '<option value="'+i+'"'+selected+'>'+subjects[i][1]+'</option>';
	}
	jselect.append(options);
}

function draw_course_table(input_id) {
	// draw the table
	var index = parseInt($("#"+input_id).val());
	if (index < 0)
		return;
	var jclasses_content = $("#classes_content");
	kill_children(jclasses_content);
	jclasses_content.append(create_table(headers,
										 full_course_list[index]));
	jclasses_content.stop(false,true);
	jclasses_content.css({opacity:0});
	jclasses_content.animate({opacity:1},500);
	// draw the selecter
	draw_subject_selector(index);
}

function init_course_list() {
	for(var i = 0; i < full_course_list.length; i++){
		var course_list = full_course_list[i];
		for (var j = 0; j < course_list.length; j++) {
			course_list[j].splice(0,0,"","");
		}
		full_course_list[i] = course_list;
	}
}