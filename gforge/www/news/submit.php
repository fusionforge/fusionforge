<?php
/**
  *
  * SourceForge News Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/forum/forum_utils.php');

if (user_isloggedin()) {

	if (!user_ismember($group_id,'A')) {
		exit_permission_denied($Language->getText('news_submit','cannot'));
        }

	if ($post_changes) {
		//check to make sure both fields are there
		if ($summary && $details) {
			/*
				Insert the row into the db if it's a generic message
				OR this person is an admin for the group involved
			*/

       			/*
       				create a new discussion forum without a default msg
       				if one isn't already there
       			*/

       			$new_id=forum_create_forum($sys_news_group,$summary,1,0);
       			$sql="INSERT INTO news_bytes (group_id,submitted_by,is_approved,date,forum_id,summary,details) ".
       				" VALUES ('$group_id','".user_getid()."','0','".time()."','$new_id','".htmlspecialchars($summary)."','".htmlspecialchars($details)."')";
       			$result=db_query($sql);
       			if (!$result) {
       				$feedback .= ' '.$Language->getText('news_submit', 'errorinsert').' ';
       			} else {
       				$feedback .= ' '.$Language->getText('news_submit', 'newsadded').' ';
       			}
		} else {
			$feedback .= ' '.$Language->getText('news_submit', 'errorboth').' ';
		}
	}

	//news must now be submitted from a project page - 

	if (!$group_id) {
		exit_no_group();
	}
	/*
		Show the submit form
	*/
	news_header(array('title'=>'News','pagename'=>'news_submit','titlevals'=>array(group_getname($group_id))));

	echo '
		<P>
		'. $Language->getText('news_submit', 'post_blurb', $GLOBALS[sys_name]) .'
		<P>
		You may include URLs, but not HTML in your submissions.
		<P>
		URLs that start with http:// are made clickable.
		<P>
		<FORM ACTION="'.$PHP_SELF.'" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">
		<B>'.$Language->getText('news_submit', 'forproject').': '. group_getname($group_id) .'</B>
		<INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
		<P>
		<B>'.$Language->getText('news_submit', 'subject').':</B><BR>
		<INPUT TYPE="TEXT" NAME="summary" VALUE="" SIZE="30" MAXLENGTH="60">
		<P>
		<B>'.$Language->getText('news_submit', 'details').':</B><BR>
		<TEXTAREA NAME="details" ROWS="5" COLS="50" WRAP="SOFT"></TEXTAREA><BR>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="SUBMIT">
		</FORM>';

	news_footer(array());

} else {

	exit_not_logged_in();

}
?>
