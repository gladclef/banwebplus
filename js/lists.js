function draw_lists_tab() {
	o_listsTab.draw();
}

typeListsTab = function() {
	var rules_table_headers = ['Attribute', 'Operator', 'Match'];
	
	this.draw = function() {
		this.drawBlacklist();
		this.drawWhitelist();
		var o_this = this;
		$.each(['blacklist', 'whitelist'], function(k, v) {
			var jselect = $($('#'+v+'_add_row_form').find('[name=Attribute]'));
			o_this.populateOperators(jselect, v);
		});
	}
	
	// a blacklist rule is an array[columnIndex "/[0-9]*/", condition "/[=><(=>)(<=)(contains)]/", value]	
	this.drawBlacklist = function() {
		var a_blacklist = o_courses.getBlacklist();
		this.drawList('blacklist', a_blacklist);
	}
	
	this.drawWhitelist = function() {
		var a_whitelist = o_courses.getWhitelist();
		this.drawList('whitelist', a_whitelist);
	}

	this.drawList = function(listName, a_list){
		var table_html = '';
		var div_id = listName+'_table_div';
		if (a_list.length > 0)
			table_html = '<div id="'+div_id+'" style="margin:0 auto;">'+this.drawRulesTable(a_list, listName)+'</div>';
		var add_row_form = getAddRowForm(listName);
		
		var jcontainer = getListContainer(listName);
		kill_children(jcontainer);
		jcontainer.html('');
		jcontainer.stop(true, true);
		jcontainer.css({ opacity: 0 });
		jcontainer.append(add_row_form+table_html);
		jcontainer.animate({ opacity: 1 }, 500);
		
		var jdiv = $('#'+div_id);
		jdiv.width(jdiv.find('table').width());
	}
	
	this.drawRulesTable = function(a_rules_list, classes) {
		return create_table(rules_table_headers, a_rules_list, [classes, classes, classes], "o_listsTab.add_remove_row");
	}
	
	this.populateOperators = function(jselect, listName) {
		var a_classes = o_courses.getCurrentClasses();
		var column = get_index_of_header(jselect.val(), headers);
		var types = [
			{ typename: 'int', match: /^[0-9]+?/, count: 0 },
			{ typename: 'float', match: /^[0-9]+\.[0-9]*?/, count: 0 },
			{ typename: 'float', match: /^[0-9]*\.[0-9]+?/, count: 0 },
			{ typename: 'time', match: /^[0-9]+-[0-9]+?/, count: 0 },
			{ typename: 'string', match: /.+/, count: 0 },
			{ typename: 'none', match: /.*/, count: 0 },
		];
		var typesToOperators = {
			'int': ['>', '<', '>=', '<=', '=', 'regex'],
			'float': ['>', '<', '>=', '<=', '=', 'regex'],
			'time': ['>', '<', '>=', '<=', '=', 'regex'],
			'string': ['contains', 'starts with', 'ends with', 'regex'],
		};

		$.each(a_classes, function(k, v) {
			for (var i = 0; i < types.length; i++) {
				if (v[column].match(types[i]['match']) !== null) {
					types[i]['count']++;
					break;
				}
			}
		});
		
		var i_most_popular_count = -1;
		var s_most_popular_name = '';
		$.each(types, function(k, v) {
			if (v['count'] > i_most_popular_count && v['typename'] != 'none') {
				i_most_popular_count = v['count'];
				s_most_popular_name = v['typename'];
			}
		});
		
		var jOps = $('#'+listName+'_add_row_form').find('select[name=Operator]');
		kill_children(jOps);
		jOps.html('');
		$.each(typesToOperators[s_most_popular_name], function(k, v) {
			jOps.append('<option>'+v+'</option>');
		});
		
		o_listsTab.populateValuePlaceholder(jselect, jOps, listName);
	}
	
	this.populateValuePlaceholder = function(jAttrsSelect, jOpsSelect, listName) {
		var s_operator = jOpsSelect.val();
		var s_attribute = jAttrsSelect.val();
		var index = get_index_of_header(s_attribute, headers);
		var a_classes = o_courses.getCurrentClasses();
		var s_placeholder = '';
		var jtextarea = $($('#'+listName+'_add_row_form').find('input[type=textarea]'));
		
		for (var i = 0; i < a_classes.length; i++) {
			if (a_classes[i][index] != '') {
				s_placeholder = a_classes[i][index];
				break;
			}
		}
		if (s_placeholder != '')
			s_placeholder = 'eg: '+s_placeholder;
		else
			s_placeholder = 'Value';

		jtextarea.prop('placeholder', s_placeholder);
	}
	
	this.submitRule = function(jform) {
		var inputs = get_values_in_form(jform);
		var rule = [];
		
		$.each(inputs, function(k, v) {
			var name = $(v).prop('name');
			switch(name) {
			case 'Attribute':
				rule[0] = $(v).val();
				break;
			case 'Operator':
				rule[1] = $(v).val();
				break;
			case 'Match':
				rule[2] = $(v).val();
				break;
			}
		});

		var b_rule_is_good = true;
		for (var i = 0; i < 3; i++) {
			if (typeof(rule[i]) == 'undefined' || rule[i] == '') {
				b_rule_is_good = false;
				break;
			}
		}
		
		if (b_rule_is_good) {
			var rules_added = 0;
			var jlabel = $(jform.find('[name=errors]'));
			var o_this = this;
			set_html_and_fade_in(jlabel, '', '<font style="color:gray;">Calculating, please wait...</font>');
			setTimeout(function() {
				if (jform.prop('id').indexOf('blacklist') > -1) {
					rules_added = o_courses.addBlacklistRule(rule);
				} else {
					rules_added = o_courses.addWhitelistRule(rule);
				}
				if (rules_added > 0) {
					o_this.draw();
				} else {
					set_html_and_fade_in(jlabel, '', '<font style="color:red;">That rule already exists</font>');
				}
			}, 300);
		}
	}
	
	getListContainer = function(listName) {
		return $("#"+listName+"_content_container");
	}

	getAddRowForm = function(listName) {
		var formid = listName+'_add_row_form';
		var buttonid = listName+'_add_row_button';
		var s_retval = '<div id="'+formid+'" class="centered"><table class="centered">';
		s_retval += '<tr><td>Attribute</td><td>Operator</td><td>Match</td></tr>';
		s_retval += '<tr><td>';
		s_retval += '<select name="Attribute" onchange="o_listsTab.populateOperators($(this), \''+listName+'\');">';
		$.each(headers, function(i, value) {
			s_retval += '<option value="'+value+'">'+value+'</option>';
		});
		s_retval += '</select>';
		s_retval += '</td><td>';
		s_retval += '<select name="Operator" onchange="o_listsTab.populateValuePlaceholder($($(this).parent().find(\'[name=attribute]\')), $(this), \''+listName+'\');"></select>';
		s_retval += '</td><td>';
		s_retval += '<input name="Match" type="textarea" size="40" placeholder="" onkeydown="if (event.keyCode == 13) { var jbutton = $(\'#'+buttonid+'\'); jbutton.click(); }" />';
		s_retval += '</td><td>';
		s_retval += '<input id="'+buttonid+'" type="button" value="Add Rule" onclick="o_listsTab.submitRule($(\'#'+formid+'\'));" />';
		s_retval += '</td></tr>';
		s_retval += '</tr><tr><td colspan="980">';
		s_retval += '<label name="errors">&nbsp;</label>';
		s_retval += '</td></tr>';
		s_retval += '</table></div>';
		return s_retval;
	}
}

o_listsTab = new typeListsTab();