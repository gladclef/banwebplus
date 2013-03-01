function kill_children(jobject) {
	var children = jobject.children();
	for(var i = 0; i < children.length; i++)
		$(children[i]).remove();
}

