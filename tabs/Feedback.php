<?php

function init_feedback_tab() {
	return '<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Email Developer</div>
    <div id=\'email_developer_container\'><table style=\'font-size:normal;font-weight:normal;text-align:center;\' class=\'centered\'><tr><td>
        <form id=\'email_developer\' style=\'text-align:left;\'>
        Subject<br />
        <input type=\'text\' size=\'50\' name=\'email_subject\' /><br />
        Body<br />
        <textarea rows=\'5\' cols=\'50\' name=\'email_body\'></textarea><br />
        <input type=\'button\' onclick=\'send_ajax_from_form\' value=\'Send\' /><br />
        </form>
    </td></tr></table></div>
</td></tr></table>
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Comments</div>
    <div id=\'comments_container\'>&nbsp;</div>
</td></tr></table>';
}

$tab_init_function = 'init_feedback_tab';

?>