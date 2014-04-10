<?php

function init_custom() {
	return '<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Add Custom Class</div>
</td></tr></table>
<div class=\'centered\'>Add a custom class to show time conflictions<br />between already scheduled activities and courses from banweb.</div>
<div id=\'custom_add_class\' class=\'centered\'>&nbsp;</div><br />
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Modify Custom Class</div>
</td></tr></table>
<div id=\'custom_modify_class\' class=\'centered\'>&nbsp;</div><br />
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Share Custom Classes</div>
</td></tr></table>
<div id=\'custom_share_class\' class=\'centered\'>&nbsp;</div><br />
<input type=\'button\' style=\'display:none;\' name=\'onselect\' />
';
}

$tab_init_function = 'init_custom';

?>