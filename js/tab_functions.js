function get_tab_by_tabname(s_tabname) {
	var jtab = $(".tab."+s_tabname);
	return jtab;
}

function draw_tab(s_tabname) {
	// hide the previous tab
	var jprevious_tab = $(".tab.selected");
	if (jprevious_tab && jprevious_tab.length > 0) {
		var s_previous_tabname = jprevious_tab.find("input[name=tab_non_printed_name]").val();
		var jprevious_tab_contents = $("#"+s_previous_tabname+".tab_contents_div");
		jprevious_tab_contents.stop(false,true);
		//jprevious_tab_contents.animate({opacity:0},500,function(){
			jprevious_tab_contents.hide();
		//});
	}

	// get the tab and it's contents
	var jtab = get_tab_by_tabname(s_tabname);
	var jtab_contents_container = $("#"+s_tabname+".tab_contents_div");
	var selected_button = jtab_contents_container.children("input[name='onselect']");
	jtab_contents_container.stop(false,true);
	jtab_contents_container.css({opacity:0});
	jtab_contents_container.show();
	jtab_contents_container.animate({opacity:1},500);

	// set the tab class
	var a_tabs = $(".tab");
	for(var i = 0; i < a_tabs.length; i++)
		$(a_tabs[i]).removeClass("selected");
	jtab.addClass("selected");
	if (selected_button.length > 0)
		selected_button.click();
	
	// run specific tab code, if there is any
	var func_name = "draw_tab_"+s_tabname;
	setTimeout(function() {
		eval(func_name+"()");
	}, 10);
}

function click_tab_by_tabname(s_tabname) {
	var jtab = get_tab_by_tabname(s_tabname);
	if (jtab !== null)
		jtab.click();
}

o_tabInitializations = {
	schedule: function() {
	},
	custom: function() {
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
	},
	classes: function() {
	},
	lists: function() {
	},
	settings: function() {
	},
	feedback: function() {
	}
};