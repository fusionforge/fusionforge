<?php

require("pre.php");

if (!isset($page_url)) {
	exit_error('ERROR','No page specified');
}

if (strstr($page_url,'..')) {
	exit_error('ERROR','You cannot have .. in your page_name url');
}

$page_name = $DOCUMENT_ROOT . $page_url;

//echo("<!-- $page_name -->\n\n\n");

if (file_exists($page_name) && !is_dir($page_name)) {
	echo $HTML->header(array('title'=>'View Source'));
	show_source($page_name);
} else if (is_dir($page_name)) {
	exit_error('ERROR','Trying to show source for a directory');
} else {
        exit_error('ERROR','Trying to show source for inadequate URL');
}

echo $HTML->footer(array());

?>
