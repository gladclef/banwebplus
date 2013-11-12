<?php

require_once(dirname(__FILE__)."/../../objects/user.php");

class icalendarFunctions {
	private $b_exists = FALSE;
	private $o_user = NULL;
	private $a_generated_settings = NULL;
	private $o_classList = NULL;

	function __construct($s_username, $s_key) {
		global $maindb;

		$a_users = db_query("SELECT * FROM `[database]`.`students` WHERE `username`='[username]'", array('database'=>$maindb, 'username'=>$s_username));
		if (count($a_users) == 0)
				return;
		$this->o_user = new user($s_username, NULL, $a_users[0]['pass']);
		if (!$this->o_user->exists_in_db())
				return;
		$this->a_generated_settings = db_query("SELECT * FROM `[database]`.`generated_settings` WHERE `user_id`='[id]' AND `private_icalendar_key`='[key]'", array('database'=>$maindb, 'id'=>$this->o_user->get_id(), 'key'=>$s_key));
		if (count($this->a_generated_settings) == 0)
				return;
		$this->b_exists = TRUE;
	}
	
	/********************************************************************************
	 *                      P U B L I C   F U N C T I O N S                         *
	 *******************************************************************************/

	public function exists() {
		return $this->b_exists;
	}

	public function calendarToString() {
		$s_calHeader = $this->headerToString();
		$s_calBody = $this->bodyToString();
		$s_calFooter = $this->footerToString();
		$s_cal = "{$s_calHeader}\n{$s_calBody}\n{$s_calFooter}";
		$s_cal = str_replace(array("\n","\r","\n\r","\r\n"), "\n", $s_cal);
		$a_cal = explode("\n", $s_cal);
		$a_new_cal = array();
		for($i = 0; $i < count($a_cal); $i++) {
				$s_line = $a_cal[$i];
				if (strlen($s_line) > 75) {
						$s_indent = "\t";
						while (strlen($s_line) > 75) {
								$s_part_line = substr($s_line, 0, 75);
								$a_new_cal[] = $s_part_line;
								$s_line = $s_indent.substr($s_line, 75);
						}
						$a_new_cal[] = $s_line;
				} else {
						$a_new_cal[] = $s_line;
				}
		}
		$s_cal = implode("\r\n", $a_new_cal);
		return $s_cal;
	}
	
	public static function calendarLinkToString($s_linktype) {
		global $global_user;
		global $maindb;

		$s_id = $global_user->get_id();
		$s_username = $global_user->get_name();

		create_row_if_not_existing(array("database"=>$maindb, "table"=>"generated_settings", "user_id"=>$s_id));
		$a_settings_rows = db_query("SELECT `private_icalendar_key` FROM `[database]`.`generated_settings` WHERE `user_id` = '[user_id]'", array('database'=>$maindb, 'user_id'=>$s_id));
		
		if ($s_linktype == "web")
				return "http://www.banwebplus.com/pages/icalendar/calendars/{$s_username}/".$a_settings_rows[0]['private_icalendar_key']."/ClassSchedule.ics";
		else if ($s_linktype = "view")
				return "http://www.banwebplus.com/pages/icalendar/calendars/{$s_username}/pretty/".$a_settings_rows[0]['private_icalendar_key']."/ClassSchedule.ics";
		else if ($s_linktype = "download")
				return "http://www.banwebplus.com/pages/icalendar/calendars/{$s_username}/download/".$a_settings_rows[0]['private_icalendar_key']."/ClassSchedule.ics";
		else
				return self::calendarLinkToString("view");
	}

	/********************************************************************************
	 *                      P R I V A T E   F U N C T I O N S                       *
	 *******************************************************************************/
	
	private function headerToString() {
		
		// get some values
		$s_username = '"'.str_replace('"', '', $this->o_user->get_name()).'"';

		return "BEGIN:VCALENDAR
PRODID:-//Banwebplus//Banwebplus icalendar 1.0//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:{$s_username}@banwebplus.com
X-WR-TIMEZONE:America/Denver
BEGIN:VTIMEZONE
TZID:America/Denver
X-LIC-LOCATION:America/Denver
BEGIN:DAYLIGHT
TZOFFSETFROM:-0700
TZOFFSETTO:-0600
TZNAME:MDT
DTSTART:19700308T020000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0600
TZOFFSETTO:-0700
TZNAME:MST
DTSTART:19701101T020000
RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU
END:STANDARD
END:VTIMEZONE";
	}

	private function bodyToString() {
		include(dirname(__FILE__)."/../../scraping/banweb_terms.php");
		$s_retval = "";
		
		foreach($terms as $a_term) {
				$s_term = $a_term[0];
				$s_year = substr($s_term, 0, 4);
				$s_semester = substr($s_term, 4);
				
				error_log("{$s_year}:{$s_semester}");

				$o_classes = $this->getListOfClasses($s_year, $s_semester);
				if ($s_semester == "10") {
						$s_sem_year = (string)((int)$s_year - 1);
						$i_semester_startday = strtotime(date("Y-m-d 00:00:00",strtotime("Jun 1, $s_sem_year")));
						$i_semester_endday = strtotime(date("Y-m-d 00:00:00",strtotime("Jul 31, $s_sem_year")));
				} else if ($s_semester == "20") {
						$s_sem_year = (string)((int)$s_year - 1);
						$i_semester_startday = strtotime(date("Y-m-d 00:00:00",strtotime("Aug 1, $s_sem_year")));
						$i_semester_endday = strtotime(date("Y-m-d 00:00:00",strtotime("Dec 31, $s_sem_year")));
				} else {
						$i_semester_startday = strtotime(date("Y-m-d 00:00:00",strtotime("Jan 1, $s_year")));
						$i_semester_endday = strtotime(date("Y-m-d 00:00:00",strtotime("May 31, $s_year")));
				}
				$a_retval = array();
				foreach($o_classes as $s_crn=>$a_class) {
						$a_retval[] = $this->classToString($a_class, $i_semester_startday, $i_semester_endday);
				}
				
				$s_semester_retval = "\n".implode("\n", $a_retval)."\n";
				$s_retval .= $s_semester_retval;
		}
		
		while (strpos($s_retval, "\n\n") !== FALSE)
				$s_retval = str_replace("\n\n", "\n", $s_retval);
		while (substr($s_retval, 0, 1) === "\n")
				$s_retval = substr($s_retval, 1);
		return $s_retval;
	}
	
	private function getListOfClasses($s_year, $s_semester) {
		$o_retval = new stdClass();
		$a_classes = $this->o_user->get_user_classes($s_year, $s_semester);
		$o_classlist = $this->getClassList($s_year, $s_semester);
		foreach($a_classes as $o_class) {
				$s_crn = $o_class->crn;
				if (isset($o_classlist->$s_crn))
						$o_retval->$s_crn = $o_classlist->$s_crn;
		}
		return $o_retval;
	}
	
	private function getClassList($s_year, $s_semester) {
		if ($this->o_classList === NULL)
				$this->o_classList = new stdClass();
		if (!isset($this->o_classList->$s_year))
				$this->o_classList->$s_year = new stdClass();
		
		// load the semester
		if (!isset($this->o_classList->$s_year->$s_semester)) {
				$this->o_classList->$s_year->$s_semester = new stdClass();
				$o_class = $this->o_classList->$s_year->$s_semester;
				require(dirname(__FILE__)."/../../scraping/sem_{$s_year}{$s_semester}.php");
				foreach($semesterData['classes'] as $a_class) {
						$s_crn = $a_class['CRN'];
						$o_class->$s_crn = $a_class;
				}
		}

		return $this->o_classList->$s_year->$s_semester;
	}

	private function quotes($s_string) {
		return '"'.str_replace('"', '', $s_string).'"';
	}

	private function classToString($a_class, $i_semester_startday, $i_semester_endday) {
		
		// get some values
		$s_username = $this->quotes($this->o_user->get_name());
		$s_semester_startday = date("Ymd", $i_semester_startday);
		$s_semester_endday = date("Ymd", $i_semester_endday);
		$a_weekdays = array("U"=>"Sunday", "M"=>"Monday", "T"=>"Tuesday", "W"=>"Wednesday", "R"=>"Thursday", "F"=>"Friday", "S"=>"Saturday");
		$s_class_firstday = strtoupper(substr(trim($a_class['Days']), 0, 1));
		$s_class_firstweekday = $a_weekdays[$s_class_firstday];
		$s_class_starttime = substr($a_class['Time'], 0, 4)."00";
		$s_class_endtime = substr($a_class['Time'], 5, 4)."00";
		$s_class_location = $this->quotes( $a_class['Location'].($a_class['*Campus'] == 'M' ? '' : 'Other Campus ('.$a_class['*Campus'].')') );
		$s_class_summary = $this->quotes( $a_class['Course'] );
		$s_class_uid = $i_semester_startday.str_replace(array('"',' ','-'),'',$a_class['Course']);
		
		// find the start day of the class
		if (date("l", $i_semester_startday) == $s_class_firstweekday)
				$i_class_startday = $i_semester_startday;
		else
				$i_class_startday = strtotime("next {$s_class_firstweekday}", $i_semester_startday);
		$s_class_startday = date("Ymd", $i_class_startday);
		
		// get all of the days associated with a class
		$a_class_days = array();
		for ($i = 0; $i < strlen($a_class['Days']); $i++) {
				$s_day = strtoupper(substr($a_class['Days'], $i, 1));
				if ($s_day == "" || !isset($a_weekdays[$s_day]))
						continue;
				$a_class_days[] = strtoupper(substr($a_weekdays[$s_day], 0, 2));
		}
		$s_class_weekdays = implode(",",$a_class_days);
		
		// get a description of the class
		$a_description = array();
		foreach($a_class as $k=>$v) {
				$a_description[] = trim($k).": ".trim($v);
		}
		$s_description = $this->quotes( implode(", ", $a_description) );
		
		return "BEGIN:VEVENT
DTSTART;TZID=America/Denver:{$s_class_startday}T{$s_class_starttime}
DTEND;TZID=America/Denver:{$s_class_startday}T{$s_class_endtime}
RRULE:FREQ=WEEKLY;UNTIL={$s_semester_endday}T235900Z;BYDAY={$s_class_weekdays}
DTSTAMP:".date("Ymd")."T".date("His")."Z
UID:{$s_class_uid}@banwebplus.com
CATEGORIES:CLASS
CREATED:{$s_semester_startday}T000000Z
DESCRIPTION:{$s_description}
LAST-MODIFIED:".date("Ymd")."T".date("His")."Z
LOCATION:{$s_class_location}
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:{$s_class_summary}
TRANSP:OPAQUE
END:VEVENT";
	}

	private function footerToString() {
		return "END:VCALENDAR
";
	}
}

?>
