window.tabCustomClasses = {
	init: function() {
		this.drawAddClasses();
		this.drawRemoveClasses();
	},
	
	drawAddClasses: function() {
		// get the default description for a property
		var getDefaultDescription = function(propertyName) {
			var randVal = o_listsTab.populateValuePlaceholder($("<input value='"+propertyName+"'>"), $("<input value='contains'>"), "", true, []);
			var retval = randVal;
			var defaults = { '*Campus':'M', Days:'W', Time:'1600-1800', Location:'Field', Hrs:'2', Title:'Soccer Practice', Instructor:'Soccer Coach', Seats:'20', Limit:'20', Enroll:'20' }
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
			tabCustomClasses.drawAddClasses();
			sem = o_courses.getCurrentSemester();
			sem = sem.year.school_year+sem.value;
			o_courses.loadFullCourseList(sem, false);
			o_courses.setSemester(sem);
			draw_tab(get_name_of_focused_tab());
		});
	},

	drawRemoveClasses: function() {
	}
}