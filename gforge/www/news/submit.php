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
require_once('www/news/news_utils.php');
require_once('common/forum/Forum.class');


if (session_loggedin()) {

	if (!user_ismember($group_id,'A')) {
		exit_permission_denied($Language->getText('news_submit','cannot'));
	}

	if ($group_id == $sys_news_group) {
		exit_permission_denied($Language->getText('news_submit','cannotadmin'));
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

				db_begin();
				$f=new Forum(group_get_object($sys_news_group));
				if (!$f->create($summary,$summary,1,'',0,0)) {
					db_rollback();
					exit_error('Error',$f->getErrorMessage());
				}
	   			$new_id=$f->getID();
	   			$sql="INSERT INTO news_bytes (group_id,submitted_by,is_approved,post_date,forum_id,summary,details) ".
	   				" VALUES ('$group_id','".user_getid()."','0','".time()."','$new_id','".htmlspecialchars($summary)."','".htmlspecialchars($details)."')";
	   			$result=db_query($sql);
	   			if (!$result) {
					db_rollback();
	   				$feedback .= ' '.$Language->getText('news_submit', 'errorinsert').' ';
	   			} else {
					db_commit();
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
		<p>
		'. $Language->getText('news_submit', 'post_blurb', $GLOBALS['sys_name']) .'</p>
		<p>
		<form action="'.$PHP_SELF.'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<strong>'.$Language->getText('news_submit', 'forproject').': '. group_getname($group_id) .'</strong>
		<input type="hidden" name="post_changes" value="y" /></p>
		<p>
		<strong>'.$Language->getText('news_submit', 'subject').':</strong>'.utils_requiredField().'<br />
		<input type="text" name="summary" value="" size="30" maxlength="60" /></p>
		<p>
		<strong>'.$Language->getText('news_submit', 'details').':</strong>'.utils_requiredField().'<br />
		<textarea name="details" rows="5" cols="50" wrap="soft"></textarea><br />
		<input type="submit" name="submit" value="'.$Language->getText('news_submit', 'submit').'" />
		</form></p>';

	news_footer(array());

} else {

	exit_not_logged_in();

}
?>
