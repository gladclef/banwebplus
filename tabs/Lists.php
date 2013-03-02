<?php

function init_lists_tab() {
	return '<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Blacklist</div>
    <div id=\'blacklist_content_container\'>&nbsp;</div>
</td></tr></table>
<table class=\'table_title\'><tr><td>
    <div class=\'centered\'>Whitelist</div>
    <div id=\'whitelist_content_container\'>&nbsp;</div>
</td></tr></table>';
}

$tab_init_function = 'init_lists_tab';

?>