<?php

function init_account() {
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
	
	return "<table class='table_title'><tr><td>
    <div class='centered'>Account Management</div>
</td></tr></table>
<div class='centered' style='text-align:left;'>
    ".str_replace("__TITLE__", "Change Password", 
				  str_replace("__BODY__", "<form>
                <label class='errors'></label><br />
                New Password: <input class='p1' type='password' size='20'></input><br />
                Verify: <input class='p2' type='password' size='20' onkeyup='o_account_manager.verifyPasswords(this);'></input><br />
                <input type='button' onclick='o_account_manager.changePassword(this);' value='Submit'></input><br />
                <label class='password_verification'></label>
            </form>", $section_head))."
    ".str_replace("__TITLE__", "Change Username",
				  str_replace("__BODY__", "<form>
                <label class='errors'></label><br />
                New Username: <input class='username' type='textbox' size='20'></input><br />
                <input type='button' onclick='o_account_manager.changeUsername(this);' value='Submit'></input><br />
                <label class='username_verification'></label>
            </form>", $section_break))."
    ".str_replace("__TITLE__", "Disable Account",
				  str_replace("__BODY__", "<form>
                <label class='errors'></label><br />
                <input class='user_verification' type='checkbox'></input> Yes, I want to disable my account.<br />
                <input type='button' onclick='o_account_manager.disableAccount(this);' value='Disable'></input><br />
                <label class='disable_verification'></label>
            </form>", $section_break))."
    {$section_footer}
</div>
";
}

$tab_init_function = 'init_account';

?>