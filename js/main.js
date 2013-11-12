/**
 * Gets the current scroll value for the window and
 * scrolls the window to that value once every 10
 * milliseconds for one third of a second.
 */
function scrollWindowCurrent() {
	var scrollFunc = function(iteration, scrollVal) {
		if (iteration == 0)
			return;
		$(window).scrollTop(scrollVal);
		iteration--;
		setTimeout(function() {
			scrollFunc(iteration, scrollVal);
		}, 10);
	}
	var scrollVal = parseInt($(window).scrollTop());
	setTimeout(function() {
		scrollFunc(30, scrollVal);
	}, 10);
}

function moveScrollToElement() {
	$("#scroll_to_element").css("top", $(window).scrollTop()+"px");
}

function getUsername() {
	var jname = $(".username_label");
	if (jname.length == 0) {
		return "";
	}
	return jname.text();
}

setTimeout(function() {
	$(document).ready(function() {
		$("body").append("<div id='scroll_to_element' style='position:absolute; left:0; top:0; width:100px; height:100px; visibility:none;><a name='scroll_to_element'>&nbsp;</a></div>");
		$(window).scroll(function() {
			setTimeout(moveScrollToElement, 150);
			return true;
		});
	});
}, 300);