// creates a completely sortable table
// a_col_names should be an array of names for all the columns
// a_rows should be an array of rows, and each row should be an array of data
// wt_class can be either null,
//     a classname for the rows,
//     or an array of classnames (first for the table, second for the header, and third for the row)
// row_click_html can be either null or the html to call upon the row being clicked
///    *NOTE* not yet implemented
// retval: a string representing the new table
function create_table(a_col_names, a_rows, wt_class, row_click_html) {
	var table_class = "auto_table_table";
	var header_class = "auto_table_header";
	var row_class = "auto_table_row";

	if (jQuery.type(wt_class) == "string") {
		row_class = wt_class;
	} else if (jQuery.type(wt_class) == "array" && wt_class.length == 3) {
		table_class = wt_class[0];
		header_class = wt_class[1];
		row_class = wt_class[2];
	}

	var s_retval = '<table class="'+table_class+'"><tr class="'+header_class+'">';
	for(var i = 0; i < a_col_names.length; i++) {
		s_retval += '<th onclick="sort_table_by_header(this);" onmouseover="$(this).addClass(\'mouse_hover\');" onmouseout="$(this).removeClass(\'mouse_hover\');">'+a_col_names[i]+'</th>';
	}
	s_retval += '</tr>';

	for(var i = 0; i < a_rows.length; i++) {
		s_retval += '<tr class="'+row_class+'" onmouseover="get_by_html(\'tr\',this.innerHTML).addClass(\'mouse_hover\');" onmouseout="get_by_html(\'tr\',this.innerHTML).removeClass(\'mouse_hover\');">';
		a_row = a_rows[i];
		for(var j = 0; j < a_row.length; j++) {
			s_retval += '<td>'+a_row[j]+'</td>';
		}
		s_retval += '</tr>';
	}

	return s_retval;
}

function get_by_html(s_tag, s_html) {
	var a_tags = $(s_tag);
	for(var i = 0; i < a_tags.length; i++) {
		if ($(a_tags[i]).html() == s_html) {
			return $(a_tags[i]);
		}
	}
	return null;
}

function get_parent_by_type(jobject, s_type_name) {
	jparent = jobject.parent();
	while (jparent.get(0).tagName != s_type_name) {
		jparent = jparent.parent();
		if (jparent.length == 0)
			return null;
	}
	return jparent;
}

function sort_table_by_header(header) {
	var jheader = $(header);
	var jheader_parent = jheader.parent().parent();
	var a_all_rows = jheader_parent.children();
	var jheader_row = $(a_all_rows[0]);
	var a_rows = [];
	for(var i = 1; i < a_all_rows.length; i++)
		a_rows.push(a_all_rows[i]);
	var a_headers = jheader_row.children();
	var column_index = 0;
	for(var i = 0; i < a_headers.length; i++) {
		if ($(a_headers[i]).html() == jheader.html()) {
			column_index = i;
			break;
		}
	}
	// assign ascending/descending
	var ascending = jheader.hasClass("sort_asc");
	var a_headers = jheader.siblings();
	a_headers.push(jheader.get(0));
	for(var i = 0; i < a_headers.length; i++) {
		a_headers.removeClass("sort_asc");
		a_headers.removeClass("sort_desc");
	}
	if (ascending)
		jheader.addClass("sort_desc");
	else
		jheader.addClass("sort_asc");
	ascending = jheader.hasClass("sort_asc");
	// sort
	var rows_sorted = a_rows.sort(function(a,b) {
		var a_col = $($(a).children()[column_index]).html().toLowerCase();
		var b_col = $($(b).children()[column_index]).html().toLowerCase();
		if (jQuery.isNumeric(a_col) && jQuery.isNumeric(b_col)) {
			a_col = parseInt(a_col);
			b_col = parseInt(b_col);
		}
		if (ascending)
			return a_col > b_col ? 1 : -1;
		else
			return a_col > b_col ? -1 : 1;
	});
	// preserve old content
	var header_row = jheader_row.get(0);
	for(var i = 0; i < rows_sorted.length; i++) {
		rows_sorted[i] = $(rows_sorted[i]).get(0);
	}
	// remove old content
	for(var i = 0; i < a_all_rows.length; i++) {
		$(a_all_rows[i]).remove();
	}
	// add new content
	jheader_parent.append(header_row);
	for(var i = 0; i < rows_sorted.length; i++) {
		jheader_parent.append(rows_sorted[i]);
	}
}
