<?php
/**
  *
  * SourceForge Survey Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('vote_function.php');
require_once('www/survey/survey_utils.php');

// Check to make sure they're logged in.
if (!session_loggedin()) {
	exit_not_logged_in();
}

survey_header(array('title'=>'Survey','pagename'=>'survey_survey'));

if (!$survey_id || !$group_id) {
	echo "<H1>For some reason, the Group ID or Survey ID did not make it to this page</H1>";
} else {
	show_survey($group_id,$survey_id);
}

survey_footer(array());

?>
