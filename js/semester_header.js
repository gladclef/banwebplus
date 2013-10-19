typeSemesterHeader = function() {
	this.draw = function() {
		var currentSemester = o_courses.getCurrentSemester();
		var i_semester = currentSemester.year.school_year+currentSemester.value;
		var s_semester = currentSemester.name+' '+currentSemester.year.year;
		var jcontainer = $('#semester_header');
		var s_print = '';
		var a_previous_semester = [];
		var a_next_semester = [];
		var previous_semester = '';
		var next_semester = '';
		var s_previous = '';
		var s_next = '';
		var a_semesters = o_courses.getAvailableSemesters();

		a_previous_semester = $.grep(a_semesters, function(v, k) {
			return (typeof(a_semesters[k+1]) != 'undefined' && a_semesters[k+1][0] == i_semester+'');
		});
		a_next_semester = $.grep(a_semesters, function(v, k) {
			return (typeof(a_semesters[k-1]) != 'undefined' && a_semesters[k-1][0] == i_semester+'');
		});
		if (a_previous_semester.length == 0) {
			previous_semester = i_semester+'';
			s_previous = s_semester;
		} else {
			previous_semester = a_previous_semester[0][0];
			s_previous = a_previous_semester[0][1];
		}
		if (a_next_semester.length == 0) {
			next_semester = i_semester+'';
			s_next = s_semester;
		} else {
			next_semester = a_next_semester[0][0];
			s_next = a_next_semester[0][1];
		}
		
		s_print += '<a href="#" onclick="o_semesterHeader.setSemester(\''+previous_semester+'\');">'+s_previous+'<img src="/images/left_arrow.png" style="height:20px; padding:0 5px; cursor:pointer; border:none;" /></a>';
		s_print += '<big><big><big>'+s_semester+'</big></big></big>';
		s_print += '<a href="#" onclick="o_semesterHeader.setSemester(\''+next_semester+'\');"><img src="/images/right_arrow.png" style="height:20px; padding:0 5px; cursor:pointer; border:none;" />'+s_next+'</a>';
		
		kill_children(jcontainer);
		jcontainer.html('');
		jcontainer.append(s_print);
	}
	
	this.setSemester = function(s_semester) {
		var currentSemester = o_courses.getCurrentSemester();
		var s_current = currentSemester.year.school_year+currentSemester.value;

		if (s_current == s_semester)
			return;
		o_courses.setSemester(s_semester);
		this.draw();
	}
}

o_semesterHeader = new typeSemesterHeader();