<?php
/**
  *
  * SourceForge Forums Facility
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
require_once('www/forum/forum_utils.php');

if ($msg_id) {
 
	/*
		Figure out which group this message is in, for the sake of the admin links
	*/
	$result=db_query("SELECT forum_group_list.send_all_posts_to,forum_group_list.group_id,".
		"forum_group_list.allow_anonymous,forum_group_list.forum_name,forum.group_forum_id,forum.thread_id ".
		"FROM forum_group_list,forum WHERE forum_group_list.group_forum_id=forum.group_forum_id AND forum.msg_id='$msg_id'");

	if (!$result || db_numrows($result) < 1) {
		/*
			Message not found
		*/
                exit_error("Message Not Found",
                           "This message does not (any longer) exist.");
	}

	$group_id=db_result($result,0,'group_id');
	$forum_id=db_result($result,0,'group_forum_id');
	$thread_id=db_result($result,0,'thread_id');
	$forum_name=db_result($result,0,'forum_name');
	$allow_anonymous=db_result($result,0,'allow_anonymous');
	$send_all_posts_to=db_result($result,0,'send_all_posts_to');

	forum_header(array('title'=>db_result($result,0,'subject'),'pagename'=>'forum_message','forum_id'=>$forum_id));

	echo "<P>";

	$sql="SELECT users.user_name,forum.group_forum_id,forum.thread_id,forum.subject,forum.date,forum.body ".
		"FROM forum,users WHERE users.user_id=forum.posted_by AND forum.msg_id='$msg_id';";

	$result = db_query ($sql);

	if (!$result || db_numrows($result) < 1) {
		/*
			Message not found
		*/
		return 'message not found.\n';
	}

	$title_arr=array();
	$title_arr[]='Message: '.$msg_id;

	echo html_build_list_table_top ($title_arr);

	echo "<TR><TD BGCOLOR=\"E3E3E3\">\n";
	echo "BY: ".db_result($result,0, "user_name")."<BR>";
	echo "DATE: ".date($sys_datefmt,db_result($result,0, "date"))."<BR>";
	echo "SUBJECT: ". db_result($result,0, "subject")."<P>";
	echo util_make_links(nl2br(db_result($result,0, 'body')));
	echo "</TD></TR></TABLE>";

	/*
		Show entire thread
	*/
	echo '<BR>&nbsp;<P><H3>Thread View</H3>';

	//highlight the current message in the thread list
	$current_message=$msg_id;
	echo show_thread(db_result($result,0, 'thread_id'));

	/*
		Show post followup form
	*/

	echo '<P>&nbsp;<P>';
	echo '<CENTER><h3>Post a followup to this message</h3></CENTER>';

	show_post_form(db_result($result, 0, 'group_forum_id'),db_result($result, 0, 'thread_id'), $msg_id, db_result($result,0, 'subject'));

} else {

	forum_header(array('title'=>'Must choose a message first','pagename'=>'forum_message'));
	echo '<h1>You must choose a message first</H1>';

}

forum_footer(array()); 

?>
