typeCalendarPreview = function() {
	this.init = function() {
		if (this.hasBeenInitialized) {
			return;
		}

		this.jwindow = $(window);
		this.days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		this.weekdayBackgroundHTML = "";
		this.timeRange = {start:0, end:0};
		this.jcalendar_preview = $("#calendar_preview");
		this.jdays = null;
		this.needsUpdate = true;
		this.needsCalendarWeekUpdate = true;
		this.needsCalendarWeekDrawn = true;
		this.hasBeenInitialized = true;
	}
	
	this.drawCalendar = function(events) {
		this.events = events;
		this.init();
		this.needsUpdate = true;
		var times = this.getEventsBoundingTimes();
		if (this.timeRange.start != times.start || this.timeRange.end != times.end) {
			this.needsCalendarWeekUpdate = true;
		}
		if (this.tabHasFocus()) {
			this.updateCalendar();
		}
	}
	
	// returns the bounding start and end time of all user events
	// returns {start:0, end:0} if there are no events
	this.getEventsBoundingTimes = function() {
		var retval = {start:2401, end:-1};
		var events = this.events;
		for(var i = 0; i < events.length; i++) {
			var times = events[i].times;
			retval.start = Math.min(retval.start, times.start);
			retval.end = Math.max(retval.end, times.end);
		}
		if (retval.start == 2401) {
			retval.start = 0;
		}
		retval.end = Math.max(retval.end, 0);
		return retval;
	}

	this.tabFocus = function() {
		if (this.needsUpdate) {
			this.updateCalendar();
		}
	}

	this.tabHasFocus = function() {
		if (get_name_of_focused_tab() == 'Calendar') {
			return true;
		}
		return false;
	}

	this.eventToJqueryObject = function(event) {
		var retval = $("<div class='calendar_event' onmouseover='o_calendar_preview.mouseOver(this);' onmouseout='o_calendar_preview.mouseOut(this);' onclick='o_calendar_preview.drawEventDetails(this, \""+event.id+"\");' ontouchstart='o_calendar_preview.drawEventDetails(this, \""+event.id+"\");'>"+event.title+"</div>");
		return retval;
	}
	
	this.mouseOver = function(element) {
		var jelement = $(element);
		if (!jelement.hasClass("hover")) {
			jelement.addClass("hover");
		}
	}

	this.mouseOut = function(element) {
		var jelement = $(element);
		if (jelement.hasClass("hover")) {
			jelement.removeClass("hover");
		}
	}
	
	this.drawEventDetails = function(element, eventid) {
		var jelement = $(element);
		var top = parseInt(jelement.offset().top) - 20;
		var left = parseInt(jelement.offset().left) + 20;
		var event = null;
		var events = this.events;
		for(var i = 0; i < events.length; i++) {
			if (events[i].id == eventid) {
				event = events[i];
			}
		}
		if (event === null) {
			return;
		}
		var jdetails = $("#calendar_event_details");
		while (jdetails.length > 0) {
			jdetails.remove();
			jdetails = $("#calendar_event_details");
		}
		jdetails = $("<div id='calendar_event_details'><div class='calendar_event_details_close' onmouseover='o_calendar_preview.mouseOver(this);' onmouseout='o_calendar_preview.mouseOut(this);' onclick='$(this).parent().remove();'>&#x2716</div>"+event.description+"</div>");
		top = top - parseInt(this.jwindow.scrollTop());
		top = Math.max(0, top);
		top = Math.min(parseInt(this.jwindow.height()) - parseInt(jdetails.height()), top);
		left = Math.min(parseInt(this.jwindow.width()) - parseInt(jdetails.width()), left);
		$("#calendar_preview").append(jdetails);
		jdetails.css({ top:(top + "px"), left:(left + "px") });
	}

	this.updateCalendar = function() {
		this.drawWeek();
		
		// draw all the events
		var weekday = "";
		var jday = null;
		var jdays = this.jdays;
		var jListing = null;
		var jBackground = null;
		var hourSize = 0;
		var events = this.events;
		var eventtimeToHours = this.eventtimeToHours;
		var eventToJqueryObject = this.eventToJqueryObject;
		var eventsDrawn = [];
		var hoursOffset = Math.max(this.eventtimeToHours(this.timeRange.start) - 1, 0);
		var getConflictsForDay = function(event, events, weekday) {
			var retval = [];
			for (var i = 0; i < event.conflicts.length; i++) {
				var conflictID = event.conflicts[i];
				for (var j = 0; j < events.length; j++) {
					if (events[j].id != conflictID) {
						continue;
					}
					if (events[j].days.indexOf(weekday) >= 0) {
						retval.push(conflictID);
					}
				}
			}
			return retval;
		}
		var drawEvent = function(k, event) {
			if (event.days.indexOf(weekday) < 0) {
				return;
			}
			var conflicts = getConflictsForDay(event, events, weekday);
			var startTime = eventtimeToHours(event.times.start);
			var endTime = eventtimeToHours(event.times.end);
			var height = (endTime - startTime) * hourSize;
			var top = (startTime - hoursOffset) * hourSize;
		 	var width = (parseInt(jListing.width()) - 2) / (conflicts.length+1);
			var left = 2;
			for(var i = 0; i < conflicts.length; i++) {
				if (eventsDrawn.indexOf(conflicts[i]) >= 0) {
					left += width;
				}
			}
			left = left + "px";
			width = (width - 2) + "px";
			top = (top + 2) + "px";
			var jEvent = eventToJqueryObject(event);
			jListing.append(jEvent);
			jEvent.css({ top:top, left:left, width:width, height:height });
			eventsDrawn.push(event.id);
		}
		var clearListingEvents = function(jListing) {
			children = jListing.children(".calendar_event");
			while(children.length > 0) {
				$(children[0]).remove();
				children = jListing.children(".calendar_event");
			}
		}
		var drawDay = function(k, day) {
			weekday = day;
			eventsDrawn = [];
			jday = jdays[k];
			jListing = jday.children(".weekly_calendar_day_listing");
			jBackground = jListing.children(".weekly_calendar_day_background");
			clearListingEvents(jListing);
			hourSize = parseInt(jBackground.height()) / 24.0;
			$.each(events, drawEvent);
		}
		$.each(this.days, drawDay);
		
		this.needsUpdate = false;
	}

	this.drawWeek = function() {
		if (!this.needsCalendarWeekUpdate) {
			return;
		}
		
		// draw the weekday background
		if (this.weekdayBackgroundHTML === "") {
			var drawHour = function(hour) {
				var hourStr = "";
				if (hour == 0) {
					hourStr = "12:00am";
				} else if (hour < 12) {
					hourStr = hour+":00am";
				} else if (hour == 12) {
					hourStr = "12:00pm";
				} else {
					hourStr = (hour-12)+":00pm"
				}
				return "<div class='weekly_calendar_day_background_hour'>"+hourStr+"</div>";
			}
			var backgroundHTML = "";
			for(var hour = 0; hour < 24; hour++) {
				backgroundHTML += "<table class='weekly_calendar_day_background_table'><tr><td class='hour'>"+drawHour(hour)+"</th></td><tr><td class='halfhour'>&nbsp;</td></tr></table>";
			}
			this.weekdayBackgroundHTML = "<div class='weekly_calendar_day_background'>"+backgroundHTML+"</div>";
		}
		
		// actually draw the calendar
		if (this.needsCalendarWeekDrawn) {
			kill_children(this.jcalendar_preview);
			this.jcalendar_preview.append(this.drawScheduleChoice);
			this.jdays = [];
			var days = this.days;
			for (var dayIndex = 0; dayIndex < days.length; dayIndex++) {
				var day = days[dayIndex];
				var jday = $("<div class='weekly_calendar_day'><div class='weekly_calendar_day_title'>"+day+"</div><div class='weekly_calendar_day_listing'>"+this.weekdayBackgroundHTML+"</div></div>");
				this.jcalendar_preview.append(jday);
				this.jdays.push(jday);
			}
			this.needsCalendarWeekDrawn = false;
		}
		
		// resize the calendar to fit the time boundaries
		this.timeRange = this.getEventsBoundingTimes();
		var timeRange = this.timeRange;
		var eventtimeToHours = this.eventtimeToHours;
		var sizeDay = function(k, jday) {
			var jListing = jday.children(".weekly_calendar_day_listing");
			var jBackground = jListing.children(".weekly_calendar_day_background");
			var hourSize = parseInt(jBackground.height()) / 24.0;
			var startHour = eventtimeToHours(timeRange.start);
			var endHour = eventtimeToHours(timeRange.end);
			var top = (Math.max(startHour - 1, 0) * hourSize) + "px";
			var height = ((endHour - startHour + 2) * hourSize) + "px";
			jBackground.css({ top: "-"+top });
			jListing.css({ height: height });
		}
		$.each(this.jdays, sizeDay);
		
		this.needsCalendarWeekUpdate = false;
	}

	this.drawScheduleChoice = function() {
		if (o_schedule === undefined || o_schedule.otherUserSchedules.length == 0) {
			return "";
		}
		var retval = "View schedule for <select onchange='o_calendar_preview_events.changeUserSchedule(this);'>";
		retval += "<option>"+get_username()+"</option>";
		$.each(o_schedule.otherUserSchedules, function(k, user) {
			var username = user.username;
			retval += "<option>"+username+"</option>";
		});
		retval += "</select><br />";
		return retval;
	}

	// converts a "hhmm" time to hours.parthours
	this.eventtimeToHours = function(time) {
		return Math.floor(time / 100) + ((time % 100) / 60);
	}
}

typeCalendarPreviewEvents = function() {
	/**
	 * Gets all of the events to be drawn on the calendar, including
	 * getting the classes and computing their conflictions, positions,
	 * and data to be drawn.
	 * @specialUserClasses An optional array of crns to draw instead of the
	 *     the current user's classes
	 */
	this.getEvents = function(specialUserClasses) {

		// determine which classes to use
		var classes = o_courses.getUserClasses();
		var conflicts = conflicting_object.getConflictingClasses();
		if (specialUserClasses !== undefined && specialUserClasses !== null) {
			classes = specialUserClasses;
			conflicts = conflicting_object.getConflictingClasses(specialUserClasses);
		}

		var titleIndex = get_index_of_header("Course", headers);
		var daysIndex = get_index_of_header("Days", headers);
		var retval = [];
		var parseCourse = function(k, crn) {
			var times = o_courses.getTimeOfCourse(crn);
			if (!times || (!times.start && times.start !== 0)) {
				return;
			}
			var course = o_courses.getClassByCRN(crn).course;
			var parent = o_courses.getParentClass(crn);
			var title = (parent === null) ? course[titleIndex] : parent.course[titleIndex];
			var description = "";
			for(var i = 0; i < headers.length; i++) {
				if (["Conflicts", "Select"].indexOf(headers[i]) >= 0) {
					continue;
				}
				if (course[i] != "" || headers[i] == "Course") {
					if (description != "") {
						description += "<br />";
					}
					if (headers[i] == "Course" && parent !== null) {
						description += headers[i]+": "+parent.course[i];
					} else {
						description += headers[i]+": "+course[i];
					}
				}
			}
			var conflictions = [];
			if (conflicts[crn] && conflicts[crn].length > 0) {
				for(k in conflicts[crn]) {
					if (classes.indexOf(conflicts[crn][k]) >= 0) {
						conflictions.push(conflicts[crn][k]);
					}
				}
			}
			var days = [];
			for (var i = 0; i < course[daysIndex].length; i++) {
				if (course[daysIndex][i] == "U") days.push("Sunday");
				if (course[daysIndex][i] == "M") days.push("Monday");
				if (course[daysIndex][i] == "T") days.push("Tuesday");
				if (course[daysIndex][i] == "W") days.push("Wednesday");
				if (course[daysIndex][i] == "R") days.push("Thursday");
				if (course[daysIndex][i] == "F") days.push("Friday");
				if (course[daysIndex][i] == "S") days.push("Saturday");
			}
			retval.push({ id:crn, times:times, title:title, description:description, conflicts:conflictions, days:days });
		}
		$.each(classes, parseCourse);
		return retval;
	}

	this.changeUserSchedule = function(selectbox) {
		
		// find the user
		var jselect = $(selectbox);
		var username = jselect.val();
		var users = o_schedule.otherUserSchedules;
		var user = null;
		$.each(users, function(k, v) {
			if (v.username == username) {
				user = v;
			}
		});
		
		// display this user's schedule
		if (username == get_username()) {
			o_calendar_preview.drawCalendar(this.getEvents());
		} else {
			var schedule = [];
			$.each(user.schedule, function(k, crn) {
				if (o_courses.getClassByCRN(crn) == {}) {
					thereExistHiddenClasses = true;
				} else {
					schedule.push(crn);
				}
			});
			o_calendar_preview.drawCalendar(this.getEvents(schedule));
		}
	}
}

o_calendar_preview = new typeCalendarPreview();
o_calendar_preview_events = new typeCalendarPreviewEvents();