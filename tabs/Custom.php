<?php

function init_custom() {
	return '<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Add Custom Class</div>
</td></tr></table>
<div id=\'custom_add_class\' class=\'centered\'>&nbsp;</div><br />
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Remove Custom Class</div>
</td></tr></table>
<div id=\'custom_remove_class\' class=\'centered\'>&nbsp;</div><br />
<input type=\'button\' style=\'display:none;\' name=\'onselect\' />
';
}

$tab_init_function = 'init_custom';

?>