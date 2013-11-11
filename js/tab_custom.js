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
			var defaults = { Course:'CUSTOM 001', '*Campus':'M', Days:'W', Time:'1600-1800', Location:'Field', Hrs:'2', Title:'Soccor Practice', Instructor:'Soccor Coach', Seats:'20', Limit:'20', Enroll:'20' }
			if (defaults[propertyName]) {
				retval = defaults[propertyName];
			}
			return retval;
		}
		
		var jcontainer = $("#custom_add_class");
		var thead = "<table cellpadding='0px' cellspacing='0px'><tr>'";
		var tbody = "<tr>";
		var customHeaders = headers;
		
		// get the properties to draw
		customHeaders = $.grep(customHeaders, function(v,k) {
			if (v != "Select" && v != "Conflicts") {
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
		jcontainer.append("<form id='createCustomClassForm'>"+table+addNewButton+"</form>");
	},

	drawRemoveClasses: function() {
	}
}