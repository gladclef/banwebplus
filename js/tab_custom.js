window.tabCustomClasses = {
	init: function() {
		var jcontainer = $("#custom_add_class");
		var thead = "<table cellpadding='0px' cellspacing='0px'><tr>'";
		var tbody = "<tr>";
		var customHeaders = headers;
		
		customHeaders = $.grep(customHeaders, function(v,k) {
			if (v != "Select" && v != "Conflicts") {
				return true;
			} else {
				return false;
			}
		});
	
		for(var col = 1; col < customHeaders.length; col++) {
			header = customHeaders[col];
			thead += "<th>"+header+"</th>";
			tbody += "<td><input type='textbox' name='"+header+"'></input></td>";
			if ((col % 5 == 0) && (customHeaders.length - col > 1)) {
				thead += "</tr>";
				tbody += "</tr>";
				thead = thead + tbody;
				thead += "<tr>";
				tbody = "<tr>";
			}
		}

		thead += "</tr>";
		tbody += "</tr>";
		var table = thead+tbody+"</table>";

		jcontainer.html("");
		jcontainer.append(table);
	}
}