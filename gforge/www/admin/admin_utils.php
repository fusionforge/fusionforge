<?php
/**
  * Module of support routines for Site Admin
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  */


function site_admin_header($params) {
	global $feedback,$HTML;
	$HTML->header($params);
	echo html_feedback_top($feedback);
}

function site_admin_footer($vals=0) {
	GLOBAL $HTML;
	echo html_feedback_bottom($feedback);
	$HTML->footer(array());
}

?>
