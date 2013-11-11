<?php

require_once(dirname(__FILE__)."/../../objects/user.php");

class icalendarFunctions {
	private $b_exists = FALSE;
	private $o_user = NULL;
	private $a_generated_settings = NULL;

	function __construct($s_username, $s_key) {
		global $maindb;

		$a_users = db_query("SELECT * FROM `[database]`.`students` WHERE `username`='[username]'", array('database'=>$maindb, 'username'=>$s_username));
		if (count($a_users) == 0)
				return;
		$this->o_user = new user($s_username, NULL, $a_users[0]['pass']);
		if (!$this->o_user->exists_in_db())
				return;
		$this->a_generated_settings = db_query("SELECT * FROM `[database]`.`generated_settings` WHERE `user_id`='[id]'", array('id'=>$this->o_user->get_id()));
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
		$s_cal = $s_calHeader.$s_calBody.$s_calFooter;
		$s_cal = str_replace(array("\n","\r","\n\r"), "\n", $s_cal);
		$s_cal = str_replace("\n", "\n\r", $s_cal);
		return $s_cal;
	}

	/********************************************************************************
	 *                      P R I V A T E   F U N C T I O N S                       *
	 *******************************************************************************/
	
	private function headerToString() {
		return "BEGIN:VCALENDAR
PRODID:-//Google Inc//Google Calendar 70.9054//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:gladclef@gmail.com
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
		return "BEGIN:VEVENT
DTSTART;TZID=America/Denver:20131112T090000
DTEND;TZID=America/Denver:20131112T100000
RRULE:FREQ=WEEKLY;UNTIL=20131217T160000Z;BYDAY=SU,MO,TU,WE,TH,FR,SA
DTSTAMP:20131115T091740Z
UID:7trd6kv2fmlgo2p917as557348@google.com
CREATED:20131115T091706Z
DESCRIPTION:
LAST-MODIFIED:20131115T091706Z
LOCATION:
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:test
TRANSP:OPAQUE
END:VEVENT";
	}

	private function footerToString() {
		return "END:VCALENDAR";
	}
}

?>