<?php

require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");

// returns a string for the login page
function draw_reset_password_page() {

	$s_username = $_GET['username'];

	ob_start();
	?>
	<script type="text/javascript">
		dont_check_session_expired = true;

		/**
		 * Verifies that the password is valid and the password fields match
		 */
		function verify_passwords() {

			var jp1 = $("#p1");
			var jp2 = $("#p2");
			var jlabel = $("#password_verification");

			if (jp1.val() == '') {
				jlabel.stop(true,true);
				jlabel.css({ color:'red', opacity:0 });
				jlabel.animate({ opacity:1 }, 300);
				jlabel.text('It is advised that your password not be blank.');
				return;
			}

			if (jp1.val() != jp2.val()) {
				jlabel.stop(true,true);
				jlabel.css({ color:'red', opacity:0 });
				jlabel.animate({ opacity:1 }, 300);
				jlabel.text('Passwords do not match.');
				return;
			}

			jlabel.stop(true,true);
			jlabel.css({ color:'green', opacity:0 });
			jlabel.animate({ opacity:1 }, 300);
			jlabel.text('OK!');
		}
	</script>

	<form id='reset_password_form'>
		<input type='hidden' name='command' value='reset_password_ajax' />
		<label class='errors'>Reset password for <?php echo $s_username; ?>:</label><br />

		Password: <input type='password' name='password' id='p1' /><br />
		Verify: <input type='password' id='p2' onkeyup='verify_passwords();' /><br />
		<input type='hidden' name='key' value='<?php echo $_GET['key']; ?>'></input>
		<input type='hidden' name='username' value='<?php echo $_GET['username']; ?>'></input>
		<label id='password_verification' style='font-weight:bold;'></label>
		<br /><br />

		<div style='float:right;'>
			<input type='button' value='Set Password' onclick='send_ajax_call_from_form("/pages/users/ajax.php","reset_password_form");' />
		</div><br />
	</form>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	$a_page[] = draw_page_head('<div><a class="black_link" href="/">To login page</a></div>');
	$a_page[] = $s_page;
	$a_page[] = draw_page_foot();
	return implode("\n", $a_page);
}

echo manage_output(draw_reset_password_page());

?>