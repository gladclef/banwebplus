function get_name_of_focused_tab() {
	return $(".tab.selected").children("[name=tab_non_printed_name]").val();
}

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
	setTimeout(function() {
		eval("o_tabInitializations."+s_tabname+"()");
	}, 10);
}

function click_tab_by_tabname(s_tabname) {
	var jtab = get_tab_by_tabname(s_tabname);
	if (jtab !== null)
		jtab.click();
}

typeTabInitializations = function() {
	this.Calendar = function() {
		o_calendar_preview.tabFocus();
	};
	this.Schedule = function() {
		o_schedule.draw();
	};
	this.Custom = function() {
		tabCustomClasses.init();
	};
	this.Classes = function() {
	};
	this.Lists = function() {
	};
	this.Settings = function() {
	};
	this.Feedback = function() {
	};
	this.Users = function() {
	};
	this.Account = function() {
	};

	// returns the currently selected tab, as {jtab:jquery object, name:string}
	this.getSelectedTab = function() {
		var jtab = $(".tab.selected");
		var jinput = jtab.children("input[name=tab_non_printed_name]");
		var s_tabname = jinput.val();
		return {jtab:jtab, name:s_tabname};
	};
	
	this.initSelectedTab = function() {
		var tab = this.getSelectedTab();
		draw_tab(tab.name);
	};
};

o_tabInitializations = new typeTabInitializations();