$(
	function() {
		$(".logout_button").click(function(){
			ajax_logout();
		});
		setTimeout('check_session_expired();', 1000);
	}
);

function check_session_expired() {
	if (typeof(dont_check_session_expired) !== "undefined")
		if (dont_check_session_expired)
			return;
	send_ajax_call_retval = send_ajax_call("/pages/login/check_session_expired.php", {command:"check_session_expired"});
	interpret_common_ajax_commands(retval_to_commands(send_ajax_call_retval));
	setTimeout('check_session_expired();', 10000);
}