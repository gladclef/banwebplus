function get_tab_by_tabname(s_tabname) {
	var a_tabs = $(".tab");
	var jtab = null;
	for(var i = 0; i < a_tabs.length; i++){
		if ($(a_tabs[i]).text() == s_tabname) {
			jtab = $(a_tabs[i]);
		}
	}
	return jtab;
}

function draw_tab(s_tabname) {
	// hide the previous tab
	var jprevious_tab = $(".tab.selected");
	if (jprevious_tab && jprevious_tab.length > 0) {
		var s_previous_tabname = jprevious_tab.text();
		var jprevious_tab_contents = $("#"+s_previous_tabname+".tab_contents_div");
		jprevious_tab_contents.stop(false,true);
		//jprevious_tab_contents.animate({opacity:0},500,function(){
			jprevious_tab_contents.hide();
		//});
	}
	// get the tab and it's contents
	var jtab_contents = $("#"+s_tabname+".tab_contents_div");
	var jtab = get_tab_by_tabname(s_tabname);
	var tab_contents = $(jtab.children("input")[0]).val();
	jtab_contents.stop(false,true);
	jtab_contents.css({opacity:0});
	jtab_contents.show();
	jtab_contents.animate({opacity:1},500);
	// set the tab class
	var a_tabs = $(".tab");
	for(var i = 0; i < a_tabs.length; i++)
		$(a_tabs[i]).removeClass("selected");
	jtab.addClass("selected");
}

