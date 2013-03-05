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
		var temp_list = [];
		if (a_list.length > 0) {
			$.each(a_list, function(k,v) {
				var list_item = [];
				for (var i = 0; i < 3; i++) {
					list_item[i] = v[i];
				}
				list_item[0] = headers[list_item[0]];
				temp_list.push(list_item);
			});
			table_html = '<div id="'+div_id+'" style="margin:0 auto;">'+this.drawRulesTable(temp_list, listName)+'</div>';
		}
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
		return create_table(rules_table_headers, a_rules_list, [classes, classes, classes], "o_listsTab.removeRule");
	}
	
	this.removeRule = function(element) {
		var jelement = $(element);
		var children = jelement.children();
		var listName = '';
		var rule = []
		var a_rules = null;
		var o_courses_func = null;

		rule.push(get_index_of_header($(children[0]).text(), headers));
		rule.push($(children[1]).text());
		rule.push($(children[2]).text());
		listName = jelement.hasClass('blacklist') ? 'blacklist' : 'whitelist';
		
		a_rules = (listName == 'blacklist') ? o_courses.getBlacklist() : o_courses.getWhitelist();
		o_courses_func = (listName == 'blacklist') ? o_courses.removeBlacklistRule : o_courses.removeWhitelistRule;
		a_matched_rule = null;
		$.each(a_rules, function(k, v) {
			var b_matches = true;
			for (var i = 0; i < rule.length; i++)
				if (rule[i] != v[i])
					b_matches = false;
			if (b_matches)
				a_matched_rule = v;
		});
		if (a_matched_rule != null)
			if (o_courses_func(a_matched_rule) > 0)
				this.draw();
	}
	
	this.populateOperators = function(jselect, listName) {
		var a_classes = o_courses.getCurrentClasses();
		var column = get_index_of_header(jselect.val(), headers);
		var s_type_name = o_courses.getTypeAtIndex(column);
		var typesToOperators = {
			'int': ['>', '<', '>=', '<=', '=', 'regex'],
			'float': ['>', '<', '>=', '<=', '=', 'regex'],
			'time': ['starts after', 'starts before', 'starts at', 'ends after', 'ends before', 'ends at'],
			'string': ['contains', 'starts with', 'ends with', 'regex'],
		};
		
		var jOps = $('#'+listName+'_add_row_form').find('select[name=Operator]');
		kill_children(jOps);
		jOps.html('');
		$.each(typesToOperators[s_type_name], function(k, v) {
			jOps.append('<option>'+v+'</option>');
		});
		
		o_listsTab.populateValuePlaceholder(jselect, jOps, listName);
	}
	
	var populateValuePlaceholder_examplevVal = '';
	this.populateValuePlaceholder = function(jAttrsSelect, jOpsSelect, listName, findNewExample) {
		var s_operator = jOpsSelect.val();
		var s_attribute = jAttrsSelect.val();
		var index = get_index_of_header(s_attribute, headers);
		var a_classes = o_courses.getCurrentClasses();
		var startAt = parseInt(Math.random(1)*a_classes.length);
		var s_placeholder = '';
		var s_exampleValue = '';
		var jtextarea = $($('#'+listName+'_add_row_form').find('input[type=textarea]'));
		var a_specialOps = {
			contains: {
				'function': function(example) {
					return example;
				}
			},
			'starts with': {
				'function': function(example) {
					if (example.indexOf(' ') > 0) {
						return example.split(' ')[0];
					}
					return example;
				}
			},
			'ends with': {
				'function': function(example) {
					if (example.indexOf(' ') > 0) {
						var a_parts = example.split(' ');
						return a_parts[a_parts.length-1];
					}
					return example;
				}
			},
			regex: {
				'function': function(example) {
					return '/[0-9]*/';
				}
			}
		};
		findNewExample = (typeof(findNewExample) == 'undefined') ? true : findNewExample;
		
		// find an example
		if (findNewExample) {
			for (var half = 1; half > -1; half--) {
				var minIndex = startAt*half;
				var maxIndex = (half == 0) ? startAt : a_classes.length;
				for (var i = minIndex; i < maxIndex; i++) {
					if (a_classes[i][index] != '') {
						s_exampleValue = a_classes[i][index];
						break;
					}
				}
				if (s_exampleValue != '')
					break;
			}
			populateValuePlaceholder_examplevVal = s_exampleValue;
		} else {
			s_exampleValue = populateValuePlaceholder_examplevVal;
		}
		
		// modify based on operator
		if (typeof(a_specialOps[s_operator]) !== 'undefined') {
			s_placeholder = a_specialOps[s_operator]['function'](s_exampleValue);
		} else {
			s_placeholder = s_exampleValue;
		}

		// prepend 'eg: '
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
				rule[0] = get_index_of_header($(v).val(), headers);
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
		s_retval += '<select name="Operator" onchange="o_listsTab.populateValuePlaceholder(get_parent_by_tag(\'tr\', $(this)).find(\'[name=Attribute]\'), $(this), \''+listName+'\', false);"></select>';
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