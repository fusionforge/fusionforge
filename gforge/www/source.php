<?php
/**
  *
  * Show Source Code of a Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

// Check for valid page
if (!isset($page_url)) {
	exit_error('ERROR','No page specified');
}

// Check for invalid characters
if (strstr($page_url,'..')) {
	exit_error('ERROR','You cannot have .. in your page_name url');
}

// Truncate project names
if (strstr($page_url,'projects')) {
	$page_url = '/projects';
}

$page_name = $DOCUMENT_ROOT . $page_url;

//echo("<!-- $page_name -->\n\n\n");

if (file_exists($page_name) && !is_dir($page_name)) {
	$HTML->header(array('title'=>'View Source','pagename'=>'viewsource'));
	show_source($page_name);
} else if (is_dir($page_name)) {
	exit_error('ERROR','Trying to show source for a directory');
} else {
        exit_error('ERROR','Trying to show source for inadequate URL');
}

$HTML->footer(array());

?>
