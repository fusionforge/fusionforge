<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Message Forums
	By Tim Perdue, Sourceforge, 11/99

	Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

	Complete OO rewrite by Tim Perdue 12/2002
*/

require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('common/forum/Forum.class');
require_once('common/forum/ForumFactory.class');
require_once('common/forum/ForumMessageFactory.class');
require_once('common/forum/ForumMessage.class');

if ($msg_id) {
 
	/*
		Figure out which group this message is in, for the sake of the admin links
	*/
	$result=db_query("SELECT forum_group_list.group_id,forum_group_list.group_forum_id
		FROM forum_group_list,forum 
		WHERE forum_group_list.group_forum_id=forum.group_forum_id 
		AND forum.msg_id='$msg_id'");

	if (!$result || db_numrows($result) < 1) {
		/*
			Message not found
		*/
		exit_error("Message Not Found",
				"This message does not (any longer) exist.");
	}

	$group_id=db_result($result,0,'group_id');
	$forum_id=db_result($result,0,'group_forum_id');

	//
	//  Set up local objects
	//
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$f=new Forum($g,$forum_id);
	if (!$f || !is_object($f)) {
		exit_error('Error','Error Getting Forum');
	} elseif ($f->isError()) {
		exit_error('Error',$f->getErrorMessage());
	}

	$fm=new ForumMessage($f,$msg_id);
	if (!$fm || !is_object($fm)) {
		exit_error('Error','Error Getting ForumMessage');
	} elseif ($fm->isError()) {
		exit_error('Error',$fm->getErrorMessage());
	}

	$fmf = new ForumMessageFactory($f);
	if (!$fmf || !is_object($fmf)) {
		exit_error('Error','Error Getting New ForumMessageFactory');
	} elseif ($fmf->isError()) {
		exit_error('Error',$fmf->getErrorMessage());
	}

	$fmf->setUp(0,'threaded',200,'');
	$style=$fmf->getStyle();
	$max_rows=$fmf->max_rows;
	$offset=$fmf->offset;

	$fh = new ForumHTML($f);
	if (!$fh || !is_object($fh)) {
		exit_error('Error','Error Getting New ForumHTML');
	} elseif ($fh->isError()) {
		exit_error('Error',$fh->getErrorMessage());
	}

	forum_header(array('title'=>db_result($result,0,'subject'),'pagename'=>'forum_message','forum_id'=>$forum_id));

	$title_arr=array();
	$title_arr[]='Message: '.$msg_id;

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	echo "<td><td style=\"background-color:#e3e3e3\">\n";
	echo "BY: ". $fm->getPosterRealName() ." (". $fm->getPosterName() .")<br />";
	echo "DATE: ". date($sys_datefmt, $fm->getPostDate()) ."<br />";
	echo "SUBJECT: ". $fm->getSubject() ."<p>&nbsp;</p>";
	echo util_make_links(nl2br( $fm->getBody() ));
	echo "</td></tr>";

	echo $GLOBALS['HTML']->listTableBottom();

	/*

		Show entire thread

	*/
	echo '<br /><br />
		<h3>Thread View</h3>';

	$msg_arr =& $fmf->nestArray($fmf->getThreaded($fm->getThreadID()));
	if ($fmf->isError()) {
		echo $fmf->getErrorMessage();
	}

	$title_arr=array();
	$title_arr[]=$Language->getText('forum_forum','thread');
	$title_arr[]=$Language->getText('forum_forum','author');
	$title_arr[]=$Language->getText('forum_forum','date');

	$ret_val .= $GLOBALS['HTML']->listTableTop ($title_arr);

	$rows=count($msg_arr[0]);

	if ($rows > $max_rows) {
		$rows=$max_rows;
	}

	$current_message=$msg_id;
	$i=0;
	while (($i < $rows) && ($total_rows < $max_rows)) {
		$msg =& $msg_arr["0"][$i];
		$total_rows++;

		if ($fm->getID() != $msg->getID()) {
			$ah_begin='<a href="/forum/message.php?msg_id='.$msg->getID().'">';
			$ah_end='</a>';
		} else {
			$ah_begin='';
			$ah_end='';
		}
		$ret_val .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($total_rows) .'>
			<td>'. $ah_begin .
			html_image('ic/msg.png',"10","12",array("border"=>"0"));
		/*
			See if this message is new or not
			If so, highlite it in bold
		*/
		if ($f->getSavedDate() < $msg->getPostDate()) {
			$bold_begin . '<strong>';
			$bold_end . '</strong>';
		} else {
			$bold_begin='';
			$bold_end='';
		}
		/*
			show the subject and poster
		*/
		$ret_val .= $bold_begin . $msg->getSubject() . $bold_end.$ah_end.'</td>'.
			'<td>'. $msg->getPosterRealName() .'</td>'.
			'<td>'. date($sys_datefmt,$msg->getPostDate()) .'</td></tr>';

		if ($msg->hasFollowups()) {
			$ret_val .= $fh->showSubmessages($msg_arr,$msg->getID(),1);
		}
		$i++;
	}

	$ret_val .= $GLOBALS['HTML']->listTableBottom();

	echo $ret_val;

	/*
		Show post followup form
	*/

	echo '<p>&nbsp;<p>';
	echo '<div align="center"><h3>Post a followup to this message</h3></div>';

	$fh->showPostForm($fm->getThreadID(), $msg_id, $fm->getSubject());

} else {

	forum_header(array('title'=>'Must choose a message first','pagename'=>'forum_message'));
	echo '<h1>You must choose a message first</h1>';

}

forum_footer(array()); 

?>
