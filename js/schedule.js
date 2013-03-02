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
	var all_classes = get_array_of_all_classes();
	var current_classes = [];
	var recent_classes = [];
	for (var i = 0; i < all_classes.length; i++) {
		var a_class = all_classes[i];
		var i_crn = parseInt(a_class[i_crn_index]);
		if (jQuery.inArray(i_crn, current_user_classes) >= 0)
			current_classes.push(a_class);
		if (jQuery.inArray(i_crn, recently_selected_classes) >= 0)
			recent_classes.push(a_class);
	}
	// add the new tables
	jcurrent_cont.append(create_table(headers, current_classes, null, "delayed_schedule_click();add_remove_class"));
	jrecent_cont.append(create_table(headers, recent_classes, null, "delayed_schedule_click();add_remove_class"));
	set_selected_classes(jcurrent_cont);
}

// it's delayed so that the javascript has time to add and remove classes
function delayed_schedule_click() {
	//setTimeout("click_tab_by_tabname('Schedule');",100);
}