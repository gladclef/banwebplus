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