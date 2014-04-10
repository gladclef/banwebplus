<?php

require_once(dirname(__FILE__)."/../../resources/globals.php");
require_once(dirname(__FILE__)."/../../resources/common_functions.php");

// returns a string for the login page
function draw_forgot_password_page() {

	ob_start();
	?>
	<script type="text/javascript">
		dont_check_session_expired = true;

		/**
		 * Changes the input from "email" to "username", or vice versa
		 * @select_element dom object The selectbox with the "email" and "username" options
		 */
		function change_input(select_element) {

			// get the selection option name
			var jselect = $(select_element);
			var option = jselect.val().toLowerCase();
			var all = $(".credential_type");
			var others = [];
			var joption = $("#"+option);

			// get a list of the other options and store values
			$.each(all, function(k,v) {
				if (v.id != option)
					others.push(v);
				var input = $(v).children("input");
				if (input.val() != "") {
					$("#"+v.id+"_bk").val(input.val());
					input.val("");
				}
			});

			// retrieve the value for the newly selected option
			joption.children("input").val($("#"+option+"_bk").val());

			// show only the selection option credentials
			$.each(others, function(k,v) {
				$(v).hide();
			});
			joption.show();
			joption.css({ display:'inline-block' });
		}
	</script>

	<form id='reset_password_form'>
		<input type='hidden' name='command' value='forgot_password_ajax' />
		<label class='errors'></label><br />

		I remember my
		<select onchange='change_input(this);'>
			<option>Username</option>
			<option>Email</option>
		</select>:
		<br /><br />

		<div id='username' class='credential_type' style='display:inline-block;'>
			<label name='username'>Username</label>
			<input type='textbox' size='20' name='username'><br />
		</div>
		<div id='email' class='credential_type' style='display:none;'>
			<label name='email'>Email</label>
			<input type='textbox' size='20' name='email'><br />
		</div>
		<br /><br />

		<div style='float:right;'>
			<input type='button' value='Send Email' onclick='send_ajax_call_from_form("/pages/users/ajax.php","reset_password_form");' />
		</div><br />
	</form>

	<input id='username_bk' type='hidden' value=''>
	<input id='email_bk' type='hidden' value=''>
	<?php
	$s_page = ob_get_contents();
	ob_end_clean();

	$a_page[] = draw_page_head('<div><a class="black_link" href="/">Back to login page</a></div>');
	$a_page[] = $s_page;
	$a_page[] = draw_page_foot();
	return implode("\n", $a_page);
}

echo manage_output(draw_forgot_password_page());

?>