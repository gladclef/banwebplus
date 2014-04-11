window.tabCustomClasses = {
	init: function() {
		this.drawAddClasses();
		this.drawModifyClasses();
		this.drawShareClasses();
	},
	
	drawAddClasses: function() {
		// get the default description for a property
		var getDefaultDescription = function(propertyName) {
			var randVal = o_listsTab.populateValuePlaceholder($("<input value='"+propertyName+"'>"), $("<input value='contains'>"), "", true, []);
			var retval = randVal;
			var defaults = { '*Campus':'M', Days:'W', Time:'1600-1800', Location:'Field', Hrs:'0', Title:'Soccer Practice', Instructor:'Soccer Coach', Seats:'20', Limit:'20', Enroll:'20' }
			if (defaults[propertyName]) {
				retval = defaults[propertyName];
			}
			return retval;
		}
		
		var jcontainer = $("#custom_add_class");
		var thead = "<table cellpadding='0px' cellspacing='0px'><tr>";
		var tbody = "<tr>";
		var customHeaders = headers;
		
		// get the properties to draw
		customHeaders = $.grep(customHeaders, function(v,k) {
			var dontAdd = ["Select", "Conflicts", "Course", "Seats", "Enroll"];
			if (dontAdd.indexOf(v) == -1) {
				return true;
			} else {
				return false;
			}
		});
		
		// draw each property
		for(var col = 1; col < customHeaders.length; col++) {
			var header = customHeaders[col];
			var placeholder = getDefaultDescription(header);
			thead += "<th>"+header+"</th>";
			tbody += "<td><input type='textbox' name='"+header+"' placeholder='"+placeholder+"'></input></td>";
			if ((col % 5 == 0) && (customHeaders.length - col > 1)) {
				thead += "</tr>";
				tbody += "</tr>";
				thead = thead + tbody;
				thead += "<tr>";
				tbody = "<tr>";
			}
		}
		
		// close the table
		thead += "</tr>";
		tbody += "</tr>";
		var table = thead+tbody+"</table>";

		// draw the "add new" button
		var addNewButton = "<input type='button' name='createCustomClass' value='Add Class' onclick='tabCustomClasses.addClass();'></input>";

		kill_children(jcontainer);
		jcontainer.html("");
		jcontainer.append("<form id='createCustomClassForm'><label class='errors'></label><br />"+table+addNewButton+"</form>");
	},

	addClass: function() {

		// some common values
		var jform = $("#createCustomClassForm");
		var a_course = jform.serializeArray();
		var sem = o_courses.getCurrentSemester();
		var semester = sem.value;
		var year = sem.year.school_year;
		var values = JSON.stringify(a_course);
		
		// check that the class hass all its parts
		for(var i = 0; i < a_course.length; i++) {
			if (a_course[i].value == "") {
				draw_error(jform, "Fill in all parts of the class before submitting", false);
				return;
			}
		}
		
		// contact the server to try and add the class
		send_ajax_call("/resources/ajax_calls.php", {command:"add_custom_class", values:values, semester:semester, year:year}, function(success) {
			if (success != "success") {
				if (success == "failure") {
					draw_error(jform, "Failed to add class", false);
				} else {
					draw_error(jform, success, false);
				}
				return;
			}
			reload_classes();
		});
	},

	// draws the form to modify custom classes
	drawModifyClasses: function() {

		// get the container and classes
		var jcontainer = $("#custom_modify_class");
		var classes = o_courses.getClassesBySubject("CUSTOM");
		kill_children(jcontainer);
		jcontainer.html("");
		
		// filter out only classes with write access
		classes = classes.filter(function(course) {
			return (course.accesses.indexOf("w") > -1);
		});

		// check that there are any custom classes
		if (classes.length == 0) {
			jcontainer.append("There are no custom classes you have permission to edit. Try creating one, above, or having a friend share their class with you.");
			return;
		}

		// draw each class in the table
		var table = "<table class='centered custom_classes'><thead><tr>";
		var cheaders = ["*Campus", "Days", "Time", "Location", "Hrs", "Title", "Instructor", "Limit"];
		$.each(cheaders, function(k,header) {
			table += "<th>"+header+"</th>";
		});
		table += "<th></th></tr></thead><tbody>";
		$.each(classes, function(k,course) {
			table += "<tr>";
			crn = course[get_crn_index_from_headers(headers)];
			$.each(cheaders, function(k,header) {
				hindex = get_index_of_header(header, headers);
				value = course[hindex];
				table += "<td><span>"+value+" <img src='/images/pencil.png' style='height:12px; width:12px; padding:0; cursor:pointer; border:none;' onclick='tabCustomClasses.editCourseValue(\""+crn+"\","+hindex+",this);'></img></span></td>";
			});
			table += "<td style='background-color:white;'><img src='/images/trash.png' style='height:16px; width:16px; padding:0; cursor:pointer; border:none;' onclick='tabCustomClasses.removeCourseAccess(\""+crn+"\",this);'></img></td>";
			table += "</tr>";
		});
		table += "</tbody></table>";
		
		// add the content to the page
		jcontainer.append(table);
	},

	// draws the form to share custom classes
	drawShareClasses: function() {

		// get the container and classes
		var jcontainer = $("#custom_share_class");
		var classes = o_courses.getClassesBySubject("CUSTOM");
		kill_children(jcontainer);
		jcontainer.html("");
		
		// filter out only classes with share access
		classes = classes.filter(function(course) {
			return (course.accesses.indexOf("x") > -1);
		});

		// check that there are any custom classes
		if (classes.length == 0) {
			jcontainer.append("There are no custom classes you have permission to share. Try creating one, above, or having a friend share their class with you.");
			return;
		}

		// draw each class in the table
		var table = "<table class='centered custom_classes'><thead><tr>";
		var cheaders = ["Class To Share", "Permissions To Assign", "Friend's Username"];
		$.each(cheaders, function(k,header) {
			table += "<th>"+header+"</th>";
		});
		table += "<th></th></tr></thead><tbody><tr>";
		table += "<td><select name='crn' onchange='tabCustomClasses.showPermissions(this);'>";
		$.each(classes, function(k,course) {
			crn = course[get_crn_index_from_headers(headers)];
			title = course[get_index_of_header("Title", headers)];
			table += "<option value='"+crn+"'>";
			table += title;
			table += "</option>";
		});
		table += "</td><td class='permissions'><input type='checkbox' name='r' checked disabled> View</input><input type='checkbox' name='w' disabled></input> Edit <input type='checkbox' name='x' checked></input> Share</td>";
		table += "<td><input type='textbox' name='username' placeholder='eg "+get_username()+"' size='20' onkeydown='form_enter_press(this,event);'></input></td>";
		table += "<td><input type='button' value='Submit' onclick='get_parent_by_tag(\"form\",$(this)).submit();'></input></td>";
		table += "</tr></tbody></table>";

		// draw the form
		var form = "<div style='display:inline-block;' class='centered'><form style='display:inline-block; margin: 0; padding: 0;'><label class='errors'></label><br />"+table+"</form></div>";
		var jdiv = $(form);
		var jform = jdiv.find("form");
		var jselect = jform.find("select");

		// submit the share
		var jusername = jform.find("input[name=username]");
		jform.submit(function(e) {
			e.stopPropagation();
			if (jusername.val() == "") {
				draw_error(jform, "You must provide a banwebplus username.", false);
				return false;
			}
			var varsArray = jform.serializeArray();
			var vars = {};
			for (var i = 0; i < varsArray.length; i++) {
				varsValue = varsArray[i];
				vars[varsValue.name] = varsValue.value;
			}
			var sem = o_courses.getCurrentSemester();
			var semester = sem.value;
			var year = sem.year.school_year;
			vars["command"] = "share_custom_class";
			vars["semester"] = semester;
			vars["year"] = year;
			draw_error(jform, "Contacting server...", null);
			send_ajax_call("/resources/ajax_calls.php", vars, function(success) {
				if (success == "success") {
					draw_error(jform, "Successfully shared the class with "+jusername.val()+".", true);
				} else {
					draw_error(jform, success, false);
				}
			});
			return false;
		});
		
		// add the content to the page
		jcontainer.append(jdiv);
		tabCustomClasses.showPermissions(jselect);
	},

	showPermissions: function(select_element) {
		var crn = $(select_element).val();
		var course = o_courses.getClassByCRN(crn);
		var jtd = $("#custom_share_class").find(".permissions");
		var jwrite = jtd.find("input[name=w]");
		
		// check if the user has write access (and can grant write access)
		var has_write_access = false;
		if (course.course.accesses.indexOf("w") > -1) {
			has_write_access = true;
		}

		// modify the checkbox accordingly
		if (has_write_access) {
			jwrite[0].checked = true;
			jwrite[0].disabled = false;
		} else {
			jwrite[0].checked = false;
			jwrite[0].disabled = true;
		}
	},

	// tries to remove access to the class by this user
	removeCourseAccess: function(crn, element) {
		
		// get some common values
		var jtd = get_parent_by_tag("td", $(element));
		var course = o_courses.getClassByCRN(crn);
		var sem = o_courses.getCurrentSemester();
		var semester = sem.value;
		var year = sem.year.school_year;
		var title = course.course[get_index_of_header("Title", headers)];
		
		// try to submit
		if (confirm("Are you sure you want to remove the class \""+title+"?\"")) {
			var vars = {command:"remove_custom_course_access", semester:semester, year:year, crn:crn};
			send_ajax_call("/resources/ajax_calls.php", vars, function(success) {
				if (success == "success") {
					reload_classes();
				} else {
					alert(success);
				}
			});
		}
	},

	// provide a form to change one of the values in a course
	editCourseValue: function(crn, hindex, element) {
		
		// get some common values
		var jtd = get_parent_by_tag("td", $(element));
		var jspan = jtd.children("span");
		var course = o_courses.getClassByCRN(crn);
		var value = course.course[hindex];
		var sem = o_courses.getCurrentSemester();
		var semester = sem.value;
		var year = sem.year.school_year;

		// hide the old form
		jspan.hide();
		
		// create the new form
		jform = $("<form><input type='textbox' value='"+value+"' name='value' onkeydown='form_enter_press(this,event);'></input><input type='button' value='Submit' onclick='get_parent_by_tag(\"form\", $(this)).submit();'></input></form>");
		var restore = function(val) {
			if (val != value) {
				reload_classes();
			} else {
				jform.remove();
				jspan.show();
			}
		}
		jtd.append(jform);
		var jval = jform.find("input[name=value]");
		jval.focus();
		
		// submit the new value
		jform.submit(function(e) {
			e.stopPropagation();
			var val = jval.val();
			if (val == value) {
				restore(val);
				return false;
			}
			var vars = {command:"edit_custom_course", attribute:headers[hindex], value:val, semester:semester, year:year, crn:crn};
			send_ajax_call("/resources/ajax_calls.php", vars, function(success) {
				if (success == "success") {
					restore(val);
				} else {
					alert(success);
				}
			});
			return false;
		});
	}
}