typeCoursesList = function() {
	var current_user_classes = [];
	var current_blacklist = [];
	var current_whitelist = [];
	var current_schedule = [];
	var current_conflicting_classes = [];
	var full_course_list = [];
	var current_course_list = [];
	var recently_selected_classes = [];
	var semester = '201330';

	this.setSemester = function(sem) {
		semester = sem;
		if (typeof(full_course_list[semester]) != 'undefined')
			return true;
		loadFullCourseList(semester);
	}
	
	this.loadAllSemesters = function() {
		$.ajax({
			url: '/resources/ajax_calls.php',
			async: true,
			cache: false,
			data: { command: 'list_available_semesters' },
			success: function(message) {
				parts = message.split('|');
				if (parts[0] != 'success')
					return;
				for (var i = 1; i < parts.length; i++)
					loadFullCourseList(parts[i], true);
			}
		});
	}

	this.getCurrentClasses = function() {
		return current_course_list[semester];
	}

	this.getUserClasses = function() {
		return current_user_classes[semester];
	}

	// returns the number of classes added
	this.addUserClass = function(CRN) {
		if (current_user_classes[semester].indexOf(CRN) == -1) {
			current_user_classes[semester].push(CRN);
			recently_selected_classes = removeFromArray(recently_selected_classes, CRN);
			return 1;
		}
		return 0;
	}

	// returns the number of classes removed
	this.removeUserClass = function(CRN) {
		if (current_user_classes[semester].indexOf(CRN) > -1) {
			current_user_classes[semester].pop(CRN);
			recently_selected_classes = appendToArray(recently_selected_classes, CRN);
			return 1;
		}
		return 0;
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
	// @semester, eg '201330'
	// @async, if not provided defaults to false
	loadFullCourseList = function(semester, async) {
		if (typeof(async) == 'undefined') async = false;
		current_user_classes[semester] = [];
		current_blacklist[semester] = [];
		current_whitelist[semester] = [];
		current_schedule[semester] = [];
		current_conflicting_classes[semester] = [];
		full_course_list[semester] = [];
		current_course_list[semester] = [];
		recently_selected_classes[semester] = [];

		// initialize the classes
		var a_postvars = { "command": "load_semester_classes", "year": semester.slice(0,4), "semester": semester.slice(4,7) };
		$.ajax({
			url: '/resources/ajax_calls.php',
			cache: false,
			data: a_postvars,
			async: async,
			success: function(message) {
				courses = message.split('[*course*]');
				if (courses[0] == 'failed')
					return;
				for (var i = 0; i < courses.length; i++) {
					course = courses.split('|*|');
					full_course_list[semester][i] = course.splice(0,0,'','');
				}
			}
		});
		// get user data
		var a_postvars = {"command": "load_user_classes", "year": current_year, "semester": current_semester};
		var user_data = send_ajax_call("/resources/ajax_calls.php", a_postvars);
		var user_classes = user_data.split('[*classes*]')[1].split('|');
		var user_whitelist = user_data.split('[*whitelist*]')[1].split('[*rule*]');
		var user_blacklist = user_data.split('[*blacklist*]')[1].split('[*rule*]');
		for (var i = 0; i < user_classes.length; i++)
			current_user_classes[semester][i] = parseInt(user_classes[i]);
		for (var i = 0; i < user_whitelist.length; i++)
			current_whitelist[semester][i] = user_whitelist[i].split('[*part*]');
		for (var i = 0; i < user_blacklist.length; i++)
			current_blacklist[semester][i] = user_blacklist[i].split('[*part*]');
		// analyze the whitelist/blacklist
		analyzeBlacklist(semester, false);
		analyzeWhitelist(semester, false);
		// initialize conflicting classes
		init_conflicting_array();
	}
	// if do_init_conflicting isn't set, then defaults to true
	analyzeBlacklist = function(semester, do_init_conflicting) {
		if (typeof(do_init_conflicting) == 'undefined') do_init_conflicting = true;
		// todo
		if (do_init_conflicting)
			init_conflicting_array();
	}
	analyzeWhiteList = function(semester, do_init_conflicting) {
		if (typeof(do_init_conflicting) == 'undefined') do_init_conflicting = true;
		// todo
		if (do_init_conflicting)
			init_conflicting_array();
	}
}