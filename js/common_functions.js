// scrolls the horizontal scrollbar so that the screen is centered
function scroll_to_center() {
	var docwidth = $(document).width()+parseInt($('body').css("margin-left"));
	var winwidth = $(window).width();
	if (docwidth <= winwidth)
		return;
	var scrollto = (docwidth-winwidth)/2;
	$('body, html').animate({scrollLeft: scrollto}, 500);
}

function remove_from_array_by_index(arr, i) {
	return $.grep(arr, function(value, index) {
		return (index != i);
	});
}

function console_log(wt_log) {
	if (window.console)
		console.log(wt_log);
}

function get_unique_id() {
	var retval = "id";
	for (var i = 0; i < 1000000; i++) {
		if ($("#"+retval+i).length == 0)
			return retval+i;
	}
}

function get_set_of_unique_ids(i_count) {
	var i_tid = get_unique_id();
	$("<table id='"+i_tid+"'><tr><td>&nbsp;</td></tr></table>").appendTo("body");
	var jtable = $("#"+i_tid);
	var jrow = $(jtable.children()[0]);
	var a_retval = [i_tid];
	while (a_retval.length < i_count) {
		var i_nextid = get_unique_id();
		$("<td id='"+i_nextid+"'>&nbsp;</td>").appendTo(jrow);
		a_retval.push(i_nextid);
	}
	jtable.remove();
	return a_retval;
}

function kill_children(jobject) {
	var children = jobject.children();
	for(var i = 0; i < children.length; i++)
		$(children[i]).remove();
}

function get_parent_by_tag(s_tagname, jobject) {
	if (jobject.parent().length == 0)
		return null;
	var jparent = $(jobject.parent()[0]);
	while (jparent.prop("tagName").toLowerCase() != s_tagname.toLowerCase()) {
		if (jparent.parent().length > 0) {
			jparent = jparent.parent();
		} else {
			return null;
		}
	}
	return jparent;
}

jQuery.fn.outerHTML = function(s) {
    return s
        ? this.before(s).remove()
        : jQuery("<p>").append(this.eq(0).clone()).html();
};

$.strPad = function(string,length,character) {
	var retval = string.toString();
	if (!character) { character = '0'; }
	while (retval.length < length) {
		retval = character + retval;
	}
	return retval;
}

function parse_int(s_value) {
	if (typeof(s_value) == "number")
		return s_value;
	if (!s_value)
		return 0;
	if (s_value.length == 0)
		return 0;
	s_value = s_value.replace(/^0*/, '');
	return parseInt(s_value);
}

function get_date() {
	var d = new Date();
	var s_retval = "";
	s_retval += $.strPad(d.getFullYear(),4)+"-";
	s_retval += $.strPad(d.getMonth(),2)+"-";
	s_retval += $.strPad(d.getDate(),2)+" ";
	s_retval += $.strPad(d.getHours(),2)+":";
	s_retval += $.strPad(d.getMinutes(),2)+":";
	s_retval += $.strPad(d.getSeconds(),2);
	return s_retval;
}