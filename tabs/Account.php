<?php

function init_account() {
	global $global_user;

	$section_head = "<div class='account_management_section'>
        <div class='title'>
            __TITLE__
        </div>
        <div class='body'>
            __BODY__
        </div>";
	$section_footer = "</div>";
	$section_break = "{$section_footer}
    {$section_head}";
	
	$s_retval = "<table class='table_title'><tr><td>
    <div class='centered'>Account Management</div>
</td></tr></table>
<div class='centered' style='text-align:left;'>
    ".str_replace("__TITLE__", "Change Password", 
				  str_replace("__BODY__", "<form>
                <label class='errors'></label><br />
                New Password: <input class='p1' type='password' size='20'></input><br />
                Verify: <input class='p2' type='password' size='20' onkeyup='o_account_manager.verifyPasswords(this); o_account_manager.keyPress(event, this);'></input><br />
                <input class='submit' type='button' onclick='o_account_manager.changePassword(this);' value='Submit'></input><br />
                <label class='password_verification'></label>
            </form>", $section_head));

	if ($global_user->has_access("development")) {
			$s_retval .= "
    ".str_replace("__TITLE__", "Change Username",
				  str_replace("__BODY__", "<form>
                <label class='errors'></label><br />
                New Username: <input class='username' type='textbox' size='20'></input><br />
                <input class='submit' type='button' onclick='o_account_manager.changeUsername(this);' value='Submit'></input><br />
                <label class='username_verification'></label>
            </form>", $section_break));
	}

	$s_retval .= "
    ".str_replace("__TITLE__", "Disable Account",
				  str_replace("__BODY__", "<form>
                <label class='errors'></label><br />
                <input class='user_verification_disable' type='radio' onclick='o_account_manager.drawDelete(this);' name='user_verification'></input> I want to disable my account.<br />
                <div style='display:inline-block; display:none;'><input class='user_verification_delete' type='radio' name='user_verification'></input> I want to delete my account.<br /></div>
                <input class='submit' type='button' onclick='o_account_manager.disableAccount(this);' value='Disable'></input><br />
                <label class='disable_verification'></label>
            </form>", $section_break));

	$s_retval .= "
    {$section_footer}
</div>
";

	return $s_retval;
}

$tab_init_function = 'init_account';

?>