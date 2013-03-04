typeCoursesList = function() {
	var current_user_classes = [];
	var current_blacklist = [];
	var current_whitelist = [];
	var current_subjects = [];
	var whitelist_rules_count = 0; // used to determine if the whitelist needs to be re-evaluated or just the last rule
	var blacklist_rules_count = 0; // used to determine if the blacklist needs to be re-evaluated or just the last rule
	var current_schedule = [];
	var full_course_list = [];
	var current_course_list = [];
	var recently_selected_classes = [];
	var semester = '201330';
	var available_semesters = [];
	var conflicting_objects = [];
	conflicting_object = null;

	this.setSemester = function(sem) {
		semester = sem;
		if (typeof(full_course_list[semester]) != 'undefined')
			return true;
		loadFullCourseList(semester);
	}
	
	// returns ['value': integer representation, 'name': the season]
	this.getCurrentSemester = function() {
		var sem = semester.slice(4, 7);
		var season = '';
		switch(sem) {
		case '10':
			season = 'Fall';
			break;
		case '20':
			season = 'Spring';
			break;
		case '30':
			season = 'Summer';
			break;
		}
		return {'value': sem, 'name': season};
	}

	// returns ['year': the actual year, 'school_year': the school's year (starts in fall and ends in summer)]
	this.getCurrentYear = function() {
		var school_year = semester.slice(0, 4);
		var sem = this.getCurrentSemester();
		var year = '';
		if (sem['season'] != 'Fall') {
			year = school_year;
		} else {
			year = parse_int(school_year)-1;
			year = year+'';
		}
		return { year: year, school_year: school_year };
	}
	
	this.getAvailableSemesters = function() {
		if (typeof(available_semesters) == 'undefined' || available_semesters.length == 0)
			available_semesters = $.parseJSON(send_ajax_call('/resources/ajax_calls.php', {command:'list_available_semesters'}));
		for (var i = 0; i < available_semesters.length; i++) {
			sem = available_semesters[i][0];
			conflicting_objects[sem+''] = new typeConflictingCourses(this);
		}
		return available_semesters;
	}

	this.getAvailableSubjects = function() {
		return current_subjects[semester];
	}

	this.getCurrentClasses = function(s_subject) {
		if (typeof(s_subject) == 'undefined') {
			var a_retval = [];
			$.each(current_subjects[semester], function(i, a_subject) {
				a_retval = $.merge(a_retval, current_course_list[semester][a_subject[0]]);
			});
			return a_retval;
		}
		return current_course_list[semester][s_subject];
	}

	this.getUserClasses = function() {
		return current_user_classes[semester];
	}
	this.getRecentlySelected = function() {
		return recently_selected_classes[semester];
	}
	
	// returns an array('subject'=>subject, 'index'=>index in subject, 'course'=>array of the course)
	this.getClassByCRN = function(crn) {
		var a_retval = {};
		$.each(current_subjects[semester], function(s_subject_index, a_subject) {
			a_courses = current_course_list[semester][a_subject[0]];
			for(i = 0; i < a_courses.length; i++) {
				if (a_courses[i][2] == crn) {
					a_retval = {'subject': a_subject, 'index': i, 'course': a_courses[i]};
					break;
				}
			}
		});
		return a_retval;
	}
	
	this.loadAllSemesters = function() {
		if (typeof(available_semesters) == 'undefined' || available_semesters.length == 0) {
			$.ajax({
				type: 'POST',
				url: '/resources/ajax_calls.php',
				async: true,
				cache: false,
				data: { command: 'list_available_semesters' },
				success: function(message) {
					if (message.slice(0,7) == 'failed|')
						return;
					available_semesters = $.parseJSON(message);
					loadAllSemesters_part2()
				}
			});
		} else {
			loadAllSemesters_part2();
		}
	}
	
	loadAllSemesters_part2 = function() {
		for (var i = 0; i < available_semesters.length; i++) {
			sem = available_semesters[i][0];
			if (typeof(full_course_list[sem]) == 'undefined') {
				loadFullCourseList(sem, true);
			}
		}		
	}
	
	// returns the number of classes added
	this.addUserClass = function(CRN) {
		if (current_user_classes[semester].indexOf(CRN) == -1) {
			current_user_classes[semester].push(CRN);
			recently_selected_classes = removeFromArray(recently_selected_classes, CRN);
			this.saveUserClasses();
			return 1;
		}
		return 0;
	}

	// returns the number of classes removed
	this.removeUserClass = function(CRN) {
		var index = current_user_classes[semester].indexOf(CRN)
		if (index > -1) {
			current_user_classes[semester] = remove_from_array_by_index(current_user_classes[semester], index);
			recently_selected_classes = appendToArray(recently_selected_classes, CRN);
			this.saveUserClasses();
			return 1;
		}
		return 0;
	}

	// does an asyncronous call to the server to save the classes
	this.saveUserClasses = function() {
		var a_postvars = {};
		var tempUserClasses = [];
		$.each(this.getUserClasses(), function(index, value) {
			tempUserClasses.push({'crn': value});
		});
		
		a_postvars["timestamp"] = get_date();
		a_postvars["year"] = semester.slice(0,4);
		a_postvars["semester"] = semester.slice(4,6);
		a_postvars["classes"] = JSON.stringify(tempUserClasses);
		a_postvars["command"] = "save_classes";
		$.ajax({
			url: "/resources/ajax_calls.php",
			cache: false,
			async: true,
			data: a_postvars,
			type: "POST",
			success: function(message) {
				console_log(message);
			}
		});
	}

	// a blacklist rule is an array[columnIndex "/[0-9]*/", condition "/[=><(=>)(<=)(contains)]/", value]
	this.addBlacklistRule = function(a_new_rule) {
		if (arrayInArray(a_new_rule, current_blacklist) > -1)
			return 0;
		current_blacklist.push(a_new_rule);
		analyzeBlacklist(semester);
		return 1;
	}
	this.removeBlacklistRule = function(a_rule) {
		var index = arrayInArray(a_rule, current_blacklist);
		if (index == -1)
			return 0;
		current_blacklist.splice(index,1);
		analyzeBlacklist(semester);
		return 1;
	}
	// a whitelist rule is an array[columnIndex "/[0-9]*/", condition "/[=><(=>)(<=)(contains)]/", value]
	this.addWhitelistRule = function(a_new_rule) {
		if (arrayInArray(a_new_rule, current_whitelist) > -1)
			return 0;
		current_whitelist.push(a_new_rule);
		analyzeWhitelist(semester);
		return 1;
	}
	this.removeWhitelistRule = function(a_rule) {
		var index = arrayInArray(a_rule, current_whitelist);
		if (index == -1)
			return 0;
		current_whitelist.splice(index,1);
		analyzeWhitelist(semester);
		return 1;
	}
	
	appendToArray = function(array, item) {
		if (array.indexOf(item) == -1)
			array.push(item);
		return array;
	}
	removeFromArray = function(array, item) {
		if (array.indexOf(item) > -1)
			array.pop(item)
		return array;
	}
	arrayInArray = function(needle, haystack) {
		for (var i = 0; i < haystack.length; i++) {
			comp = haystack[i];
			if ($(comp).not(needle).length == 0 && $(needle).not(comp).length == 0)
				return i;
		}
		return -1;
	}

	// instantiates all of the arrays needed for the given course
	// should only be called from setSemester() and loadAllSemesters()
	// @sem, eg '201330'
	// @async, if not provided defaults to false
	loadFullCourseList = function(sem, async) {
		if (typeof(async) == 'undefined') async = false;
		current_user_classes[sem] = [];
		current_blacklist[sem] = [];
		current_whitelist[sem] = [];
		current_schedule[sem] = [];
		full_course_list[sem] = [];
		current_subjects[sem] = [];
		current_course_list[sem] = [];
		recently_selected_classes[sem] = [];
		conflicting_object = conflicting_objects[sem+''];

		// initialize the classes
		var a_postvars = { "command": "load_semester_classes", "year": sem.slice(0,4), "semester": sem.slice(4,7) };
		$.ajax({
			type: 'POST',
			url: '/resources/ajax_calls.php',
			cache: false,
			data: a_postvars,
			async: async,
			success: function(message) {
				if (message.slice(0,7) == 'failed|')
					return;
				var a_semester_data = jQuery.parseJSON(message);
				var a_courses = a_semester_data['classes'];
				var a_subjects = a_semester_data['subjects'];
				$.each(a_subjects, function(s_index, s_subject) {
					current_subjects[sem].push([s_index, s_subject]);
					full_course_list[sem][s_index] = [];
				});
				for (var i = 0; i < a_courses.length; i++) {
					var s_subject = a_courses[i]['subject'];
					var course = [];
					course[0] = ''; // conflicts
					course[1] = ''; // select
					course[2] = a_courses[i]['CRN'];
					course[3] = a_courses[i]['Course'];
					course[4] = a_courses[i]['*Campus'];
					course[5] = a_courses[i]['Days'];
					course[6] = a_courses[i]['Time'];
					course[7] = a_courses[i]['Location'];
					course[8] = a_courses[i]['Hrs'];
					course[9] = a_courses[i]['Title'];
					course[10] = a_courses[i]['Instructor'];
					course[11] = a_courses[i]['Seats'];
					course[12] = a_courses[i]['Limit'];
					course[13] = a_courses[i]['Enroll'];
					full_course_list[sem][s_subject].push(course);
				}
				loadFullCourseListPart2(sem, async);
			}
		});
	}
	
	loadFullCourseListPart2 = function(sem, async) {
		// get user data
		var a_postvars = {command: 'load_user_classes', 'year': sem.slice(0,4), 'semester': sem.slice(4,7) };
		var user_data = '';
		$.ajax({
			type: 'POST',
			url: '/resources/ajax_calls.php',
			cache: false,
			data: a_postvars,
			async: async,
			success: function(message) {
				if (message.slice(0,7) == 'failed|')
					return;
				user_data = jQuery.parseJSON(message);
				var user_classes = user_data.user_classes;
				var user_whitelist = user_data.user_whitelist;
				var user_blacklist = user_data.user_blacklist;
				for (var i = 0; i < user_classes.length; i++)
					current_user_classes[sem][i] = parseInt(user_classes[i].crn);
				for (var i = 0; i < user_whitelist.length; i++)
					current_whitelist[sem][i] = user_whitelist[i].split('[*part*]');
				for (var i = 0; i < user_blacklist.length; i++)
					current_blacklist[sem][i] = user_blacklist[i].split('[*part*]');
				// analyze the whitelist/blacklist
				analyzeBlacklist(sem, true);
				analyzeWhitelist(sem);
				// initialize conflicting classes
				conflicting_object.init_conflicting_array();
			}
		});
	}

	// if do_init_conflicting isn't set, then defaults to true
	// if do_init_conflicting, use the full_course_list instead of the current_course_list
	analyzeWhitelist = function(sem) {
		if (typeof(do_init_conflicting) == 'undefined')
			do_init_conflicting = true;
		if (current_whitelist[sem].length == whitelist_rules_count && !do_init_conflicting)
			return true;
		
		courses = current_course_list[sem];
		if (do_init_conflicting)
			courses = full_course_list[sem];
		for(i = 0; i < current_whitelist[sem].length; i++) {
			rule = current_whitelist[sem][i];
			courses = $.grep(courses, function(item, index) {
				return itemMatchesRule(item[rule[0]], rule);
			});
		}
		current_course_list[sem] = courses;
	}
	analyzeBlacklist = function(sem, do_init_conflicting) {
		if (typeof(do_init_conflicting) == 'undefined')
			do_init_conflicting = true;
		if (current_blacklist[sem].length == blacklist_rules_count && !do_init_conflicting)
			return true;

		courses = current_course_list[sem];
		if (do_init_conflicting)
			courses = full_course_list[sem];
		for(i = 0; i < current_blacklist[sem].length; i++) {
			rule = current_blacklist[sem][i];
			courses = $.grep(courses, function(item, index) {
				return itemMatchesRule(item[rule[0]], rule);
			}, true);
		}
		current_course_list[sem] = courses;
	}
	
	// check if the given rule matches the provided course
	// syntax for a rule: [indexOfCourse, comparitor '/[<>=(<=)(>=)(cont)/', value]
	//     cont = course[indexOfCourse] contains value
	//     if comparing times, then it splits the 'val' by '-'
	//         for '<', '<=', or '=' it looks at the lower time
	//         for '>', '>=', or '=' it looks at the upper time
	// rules are always checked case-insenstive
	itemMatchesRule = function(val, rule) {
		// time comparison
		if (rule[0] == 6) {
			if (rule[1] == '<' || rule[1] == '<=' || rule[1] == '=') {
				val = val.split('-');
				if (itemMatchesRule(val[0], rule)) return true;
				val = val[1];
			} else {
				val = val.split('-')[1];
			}
		}

		switch(rule[1]) {
		case '<':
			return (val < rule[2]);
		case '>':
			return (val < rule[2]);
		case '=':
			return (val == rule[2]);
		case '<=':
			return (val <= rule[2]);
		case '>=':
			return (val >= rule[2]);
		case 'cont':
			return (val.indexOf(rule[2]) != -1);
		}
	}
}