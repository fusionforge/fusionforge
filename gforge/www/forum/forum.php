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

if ($forum_id) {

	/*
		Get the group_id based on this forum_id
	*/
	$result=db_query("SELECT group_id
		FROM forum_group_list
		WHERE group_forum_id='$forum_id'");
	if (!$result || db_numrows($result) < 1) {
		exit_error($Language->getText('general','error'),$Language->getText('forum','error_forum_not_found').' '.db_error());
	}
	$group_id=db_result($result,0,'group_id');

	//
	//	Set up local objects
	//
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$f=new Forum($g,$forum_id);
	if (!$f || !is_object($f)) {
		exit_error($Language->getText('general','error'),"Error getting new Forum");
	} elseif ($f->isError()) {
		exit_error($Language->getText('general','error'),$f->getErrorMessage());
	}

	/*
		if necessary, insert a new message into the forum
	*/
	if ($post_message) {
		$fm=new ForumMessage($f);
		if (!$fm || !is_object($fm)) {
			exit_error($Language->getText('general','error'), "Error getting new ForumMessage");
		} elseif ($fm->isError()) {
			exit_error($Language->getText('general','error'),"Error getting new ForumMessage: ".$fm->getErrorMessage());
		}

		if (!$fm->create($subject, $body, $thread_id, $is_followup_to) || $fm->isError()) {
			exit_error($Language->getText('general','error'),'Error creating ForumMessage: '.$fm->getErrorMessage());
		} else {
			$feedback=$Language->getText('forum_forum','postsuccess');
			$style='';
			$thread_id='';
			if ($monitor) {
				$f->setMonitor();
			}
		}
	}


	$fmf = new ForumMessageFactory($f);
	if (!$fmf || !is_object($fmf)) {
		exit_error($Language->getText('general','error'), "Error getting new ForumMessageFactory");
	} elseif ($fmf->isError()) {
		exit_error($Language->getText('general','error'),$fmf->getErrorMessage());
	}

//echo "<br /> style: $style|max_rows: $max_rows|offset: $offset+";
	$fmf->setUp($offset,$style,$max_rows,$set);

	$style=$fmf->getStyle();
	$max_rows=$fmf->max_rows;
	$offset=$fmf->offset;

//echo "<br /> style: $style|max_rows: $max_rows|offset: $offset+";

	$fh = new ForumHTML($f);
	if (!$fh || !is_object($fh)) {
		exit_error($Language->getText('general','error'), "Error getting new ForumHTML");
	} elseif ($fh->isError()) {
		exit_error($Language->getText('general','error'),$fh->getErrorMessage());
	}

	forum_header(array('title'=>$f->getName(),'pagename'=>'forum_forum',
	'sectionvals'=>$g->getPublicName(),'forum_id'=>$forum_id));

/**
 *
 *	Forum styles include Nested, threaded, flat, ultimate
 *
 *	threaded indents and shows subjects/authors of all messages/followups
 *	nested indents and shows the entirety of all messages/followups
 *	flat shows entiretly of messages in date order descending
 *	ultimate is based roughly on "Ultimate BB"
 *
 */

	//create a pop-up select box listing the forums for this project
	//determine if this person can see private forums or not
	if (session_loggedin() && user_ismember($group_id)) {
		$public_flag='0,1';
	} else {
		$public_flag='1';
	}

	//create a pop-up select box showing options for viewing threads

	$vals=array('nested','flat','threaded','ultimate');
	$texts=array($Language->getText('forum_forum','nested'), $Language->getText('forum_forum','flat'), $Language->getText('forum_forum','threaded'), $Language->getText('forum_forum','ultimate'));

	$options_popup=html_build_select_box_from_arrays ($vals,$texts,'style',$style,false);

	//create a pop-up select box showing options for max_row count
	$vals=array(25,50,75,100);
	$texts=array($Language->getText('forum_forum','show').' 25',$Language->getText('forum_forum','show').' 50',$Language->getText('forum_forum','show').' 75',$Language->getText('forum_forum','show').' 100');

	$max_row_popup=html_build_select_box_from_arrays ($vals,$texts,'max_rows',$max_rows,false);

	//now show the popup boxes in a form
	$ret_val .= '
	<form action="'. $PHP_SELF .'" method="get">
	<input type="hidden" name="set" value="custom" />
	<input type="hidden" name="forum_id" value="'.$forum_id.'" />
	<table border="0" width="33%">
		<tr><td><span style="font-size:-1">'. $options_popup .
			'</span></td><td><span style="font-size:-1">'. $max_row_popup .
			'</span></td><td><span style="font-size:-1"><input type="submit" name="submit" value="'.
			$Language->getText('forum_forum','changeview').'" />
		</span></td></tr>
	</table></form>
	<p>&nbsp;</p>';

	if ($style=='nested') {

		$msg_arr =& $fmf->nestArray($fmf->getNested());

		if ($fmf->isError()) {
			echo $fmf->getErrorMessage();
		}

		$rows=count($msg_arr["0"]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}

		$i=0;
		while (($i < $rows) && ($total_rows < $max_rows)) {

			$total_rows++;
			/*
				New slashdot-inspired nested threads,
				showing all submessages and bodies
			*/
			$ret_val .= $fh->showNestedMessage ( $msg_arr["0"][$i] ).'<br />';

			if ( $msg_arr["0"][$i]->hasFollowups() ) {
				//show submessages for this message
				$tempid=$msg_arr["0"][$i]->getID();
//				echo "<p>before showNestedMessages() $tempid | ". count( $msg_arr["$tempid"] );
				$ret_val .= $fh->showNestedMessages ( $msg_arr, $tempid );
			}
			$i++;
		}

	} else if ($style=='threaded') {

		$msg_arr =& $fmf->nestArray($fmf->getThreaded());
		if ($fmf->isError()) {
			echo $fmf->getErrorMessage();
		}

		$title_arr=array();
		$title_arr[]=$Language->getText('forum_forum','thread');
		$title_arr[]=$Language->getText('forum_forum','author');
		$title_arr[]=$Language->getText('forum_forum','date');

		$ret_val .= $GLOBALS['HTML']->listTableTop ($title_arr);

		$rows=count($msg_arr[0]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}
		$i=0;
		while (($i < $rows) && ($total_rows < $max_rows)) {
			$msg =& $msg_arr["0"][$i];
			$total_rows++;

			$ret_val .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($total_rows) .'>
				<td><a href="/forum/message.php?msg_id='.$msg->getID().'">'.
				html_image('ic/msg.png',"10","12",array("border"=>"0"));
			/*
				See if this message is new or not
				If so, highlite it in bold
			*/
			if ($f->getSavedDate() < $msg->getPostDate()) {
				$bold_begin='<strong>';
				$bold_end='</strong>';
			} else {
				$bold_begin='';
				$bold_end='';
			}
			/*
				show the subject and poster
			*/
			$ret_val .= $bold_begin.$msg->getSubject() .$bold_end.'</a></td>'.
				'<td>'. $msg->getPosterRealName() .'</td>'.
				'<td>'. date($sys_datefmt,$msg->getPostDate()) .'</td></tr>';

			if ($msg->hasFollowups()) {
				$ret_val .= $fh->showSubmessages($msg_arr,$msg->getID(),1);
			}
			$i++;
		}

		$ret_val .= $GLOBALS['HTML']->listTableBottom();

	} else if (($style=='flat' && $thread_id) || ($style=='ultimate' && $thread_id)) {

		$msg_arr =& $fmf->getFlat($thread_id);
		if ($fmf->isError()) {
			echo $fmf->getErrorMessage();
		}
		$avail_rows=$fmf->fetched_rows;

		for ($i=0; ($i<count($msg_arr) && ($i < $max_rows)); $i++) {
			$ret_val .= $fh->showNestedMessage ( $msg_arr[$i] ).'<br />';
		}

	} else {
		/*
			This is the view that is most similar to the "Ultimate BB view"
		*/

		$sql="SELECT f.most_recent_date,users.user_name,users.realname,users.user_id,f.msg_id,f.subject,f.thread_id,".
			"(count(f2.thread_id)-1) AS followups,max(f2.post_date) AS recent ".
			"FROM forum f, forum f2, users ".
			"WHERE f.group_forum_id='$forum_id' ".
			"AND f.is_followup_to=0 ".
			"AND users.user_id=f.posted_by ".
			"AND f.thread_id=f2.thread_id ".
			"GROUP BY f.most_recent_date,users.user_name,users.realname,users.user_id,f.msg_id,f.subject,f.thread_id ".
			"ORDER BY f.most_recent_date DESC";

		$result=db_query($sql,($max_rows+1),$offset);

		$avail_rows=db_numrows($result);

		echo db_error();

		$title_arr=array();
		$title_arr[]=$Language->getText('forum_forum','topic');
		$title_arr[]=$Language->getText('forum_forum','topicstarter');
		$title_arr[]=$Language->getText('forum_forum','replies');
		$title_arr[]=$Language->getText('forum_forum','lastpost');

		$ret_val .= $GLOBALS['HTML']->listTableTop ($title_arr);
		$i=0;
		while (($row=db_fetch_array($result)) && ($i < $max_rows)) {
			$ret_val .= '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="/forum/forum.php?thread_id='.
				$row['thread_id'].'&amp;forum_id='.$forum_id.'">'.
				html_image('ic/cfolder15.png',"15","13",array("border"=>"0")) . '  &nbsp; ';
			/*
					See if this message is new or not
					If so, highlite it in bold
			*/
			if ($f->getSavedDate() < $row['recent']) {
				$bold_begin='<strong>';
				$bold_end='</strong>';
			} else {
				$bold_begin='';
				$bold_end='';
			}
			/*
					show the subject and poster
			*/
			$ret_val .= $bold_begin.$row['subject'] .$bold_end.'</a></td>'.
				'<td>'. $row['realname'] .'</td>'.
				'<td>'. $row['followups'] .'</td>'.
				'<td>'.date($sys_datefmt,$row['recent']).'</td></tr>';
			$i++;
		}

		$ret_val .= $GLOBALS['HTML']->listTableBottom();

	}

	/*
		This code puts the nice next/prev.
	*/
	$ret_val .= '<table width="100%" border="0">
		<tr bgcolor="'.$HTML->COLOR_LTBACK1.'"><td width="50%">';
	if ($offset != 0) {
		$ret_val .= '<span style="font-family:arial,helvetica;font-size:small;text-decoration:none">
		<a href="javascript:history.back()"><strong>' .
		html_image('t2.png',"15","15",array("border"=>"0","ALIGN"=>"MIDDLE")) .$Language->getText('forum_forum','previous_messages').'</a></strong></span>';
	} else {
		$ret_val .= '&nbsp;';
	}

	$ret_val .= '</td><td>&nbsp;</td><td align="right" width="50%">';

	if ($avail_rows > $max_rows) {
		$ret_val .= '<span style="font-family:arial,helvetica;font-size:small;text-decoration:none">
		<a href="/forum/forum.php?max_rows='.$max_rows.'&amp;style='.$style.'&amp;offset='.($offset+$i).'&amp;forum_id='.$forum_id.'">
		<strong> '.$Language->getText('forum_forum','next_messages') .
		html_image('t.png',"15","15",array("border"=>"0","ALIGN"=>"MIDDLE")) . '</strong></a>';
	} else {
		$ret_val .= '&nbsp;';
	}

	$ret_val .= '</td></tr></table>';

	echo $ret_val;
/*
	echo '<p>&nbsp;<p>';

	if (!$thread_id) {
		//
		//	Viewing an entire message forum in a given format
		//
		echo '<CENTER><h3>'.$Language->getText('forum_message', 'thread').'</h3></CENTER>';
		$fh->showPostForm();
	}
*/
	forum_footer(array());

} else {

	exit_error($Language->getText('general','error'),$Language->getText('forum_forum','no_forum_chosen'));

}

?>
