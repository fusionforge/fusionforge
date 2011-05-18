<?php
/**
 * Forums Facility
 *
 * Copyright 1999-2001, Tim Perdue - Sourceforge
 * Copyright 2002, Tim Perdue - GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'forum/AttachManager.class.php'; //attachent manager
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

$forum_id = getIntFromRequest('forum_id');
$style = getStringFromRequest('style');
$thread_id = getIntFromRequest('thread_id');
$offset = getIntFromRequest('offset');
$max_rows = getIntFromRequest('max_rows');
$set = getStringFromRequest('set');

if ($forum_id) {

	/*
		Get the group_id based on this forum_id
	*/
	$result=db_query_params('SELECT group_id
		FROM forum_group_list
		WHERE group_forum_id=$1',
			array($forum_id));
	if (!$result || db_numrows($result) < 1) {
		exit_error(_('Error forum not found: ').db_error(), 'forums');
	}
	$group_id = db_result($result,0,'group_id');

	//
	//	Set up local objects
	//
	$g = group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$f=new Forum($g, $forum_id);
	if (!$f || !is_object($f)) {
		exit_error(_('Error getting new Forum'), 'forums');
	} elseif ($f->isError()) {
		exit_error($f->getErrorMessage(), 'forums');
	}

	/*
		if necessary, insert a new message into the forum
	*/
	if (getStringFromRequest('post_message')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('forums');
		}
		$subject = getStringFromRequest('subject');
		$body = getStringFromRequest('body');
		$is_followup_to = getStringFromRequest('is_followup_to');

		$fm = new ForumMessage($f);
		if (!$fm || !is_object($fm)) {
			form_release_key(getStringFromRequest("form_key"));
			exit_error(_('Error getting new ForumMessage'), 'forums');
		} elseif ($fm->isError()) {
			form_release_key(getStringFromRequest("form_key"));
			exit_error(_('Error getting new ForumMessage:'.' '.$fm->getErrorMessage()), 'forums');
		}

		if (getStringFromRequest('_body_content_type') == 'html') {
			$body = TextSanitizer::purify($body);
		} else {
			$body = htmlspecialchars($body);
		}

		$attach = getUploadedFile("attachment1");
		if ($attach['size']) {
			$has_attach = true;
		} else {
			$has_attach = false;
		}

		if (!$fm->create($subject, $body, $thread_id, $is_followup_to,$has_attach) || $fm->isError()) {
			form_release_key(getStringFromRequest("form_key"));
			exit_error(_('Error creating ForumMessage:').' '.$fm->getErrorMessage(), 'forums');
		} else {
			if ($fm->isPending() ) {
				$feedback = _('Message Queued for moderation -> Please wait until the admin approves/rejects it');
			} else {
				$feedback = _('Message Posted Successfully');
			}
			$am = new AttachManager();//object that will handle and insert the attachment into the db
			$am->SetForumMsg($fm);
			$am->attach($attach, $group_id, 0, $fm->getID());
			foreach ($am->Getmessages() as $item) {
				$feedback .= "<br />" . $item;
			}
			$style='';
			$thread_id='';
			if (getStringFromRequest('monitor')) {
				$f->setMonitor();
			}
		}
	}


	$fmf = new ForumMessageFactory($f);
	if (!$fmf || !is_object($fmf)) {
		form_release_key(getStringFromRequest("form_key"));
		exit_error(_('Error getting new ForumMessageFactory'), 'forums');
	} elseif ($fmf->isError()) {
		form_release_key(getStringFromRequest("form_key"));
		exit_error($fmf->getErrorMessage(), 'forums');
	}

//echo "<br /> style: $style|max_rows: $max_rows|offset: $offset+";
	$fmf->setUp($offset, $style, $max_rows, $set);

	$style=$fmf->getStyle();
	$max_rows=$fmf->max_rows;
	$offset=$fmf->offset;

//echo "<br /> style: $style|max_rows: $max_rows|offset: $offset+";

	$fh = new ForumHTML($f);
	if (!$fh || !is_object($fh)) {
		exit_error(_('Error getting new ForumHTML'), 'forums');
	} elseif ($fh->isError()) {
		exit_error($fh->getErrorMessage(), 'forums');
	}

	forum_header(array('title'=>_('Forum: ') . $f->getName(), 'forum_id'=>$forum_id));

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
	$texts=array(_('Nested'), _('Flat'), _('Threaded'), _('Ultimate'));

	$options_popup=html_build_select_box_from_arrays ($vals,$texts,'style',$style,false);

	//create a pop-up select box showing options for max_row count
	$vals=array(25,50,75,100);
	$texts=array(_('Show').' 25',_('Show').' 50',_('Show').' 75',_('Show').' 100');

	$max_row_popup=html_build_select_box_from_arrays ($vals,$texts,'max_rows',$max_rows,false);

	//now show the popup boxes in a form
	$ret_val = '
	<form action="'. getStringFromServer('PHP_SELF') .'" method="get">
	<table border="0" width="33%">
		<tr><td>
	<input type="hidden" name="set" value="custom" />
	<input type="hidden" name="forum_id" value="'.$forum_id.'" />
			'. $options_popup .
			'</td><td>'. $max_row_popup .
			'</td><td><input type="submit" name="submit" value="'.
			_('Change View').'" />
		</td></tr>
	</table></form>';

	$am = new AttachManager();
	$ret_val .= $am->PrintHelperFunctions();
	if ($style=='nested') {

		$msg_arr =& $fmf->nestArray($fmf->getNested());

		if ($fmf->isError()) {
			echo '<div class="error">'.$fmf->getErrorMessage().'</div>';
	        forum_footer(array());
            exit;
		}

		$rows=count($msg_arr["0"]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}

		$i=0;
		$total_rows = 0;
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
			echo '<div class="error">'.$fmf->getErrorMessage().'</div>';
	        forum_footer(array());
            exit;
		}

		$title_arr=array();
		$title_arr[]=_('Thread');
		$title_arr[]=_('Author');
		$title_arr[]=_('Date');

		$ret_val .= $GLOBALS['HTML']->listTableTop ($title_arr);

		$rows=count($msg_arr[0]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}
		$i=0;
		$total_rows = 0;
		while (($i < $rows) && ($total_rows < $max_rows)) {
			$msg =& $msg_arr["0"][$i];
			$total_rows++;

			$ret_val .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($total_rows) .'>
				<td><a href="'.util_make_url ('/forum/message.php?msg_id='.$msg->getID().
							      '&amp;group_id='.$group_id).'&amp;reply=0">'.
				html_image('ic/msg.png').' ';
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
				'<td>'.util_display_user($msg->getPosterName(), $msg->getPosterID(), $msg->getPosterRealName()).'</td>'.
				'<td>'. date(_('Y-m-d H:i'),$msg->getPostDate()) .'</td></tr>';

			if ($msg->hasFollowups()) {
				$ret_val .= $fh->showSubmessages($msg_arr,$msg->getID(),1);
			}
			$i++;
		}

		$ret_val .= $GLOBALS['HTML']->listTableBottom();

	} else if (($style=='flat' && $thread_id) || ($style=='ultimate' && $thread_id)) {

		$msg_arr =& $fmf->getFlat($thread_id);
		if ($fmf->isError()) {
			echo '<div class="error">'.$fmf->getErrorMessage().'</div>';
	        forum_footer(array());
            exit;
		}
		$avail_rows=$fmf->fetched_rows;

		for ($i=0; ($i<count($msg_arr) && ($i < $max_rows)); $i++) {
			$ret_val .= $fh->showNestedMessage ( $msg_arr[$i] ).'<br />';
		}

	} else {
		/*
			This is the view that is most similar to the "Ultimate BB view"
		*/

		$result = db_query_params ('SELECT f.most_recent_date,users.user_name,users.realname,users.user_id,f.msg_id,f.subject,f.thread_id,
(count(f2.thread_id)-1) AS followups,max(f2.post_date) AS recent
FROM forum f, forum f2, users
WHERE f.group_forum_id=$1
AND f.is_followup_to=0
AND users.user_id=f.posted_by
AND f.thread_id=f2.thread_id
GROUP BY f.most_recent_date,users.user_name,users.realname,users.user_id,f.msg_id,f.subject,f.thread_id
ORDER BY f.most_recent_date DESC',
					   array ($forum_id),
					   $max_rows+1,
					   $offset);

		$avail_rows=db_numrows($result);

		echo db_error();

		if ($avail_rows > 0) {
			$title_arr=array();
			$title_arr[]=_('Topic');
			$title_arr[]=_('Topic Starter');
			$title_arr[]=_('Replies');
			$title_arr[]=_('Last Post');

			$ret_val .= $GLOBALS['HTML']->listTableTop ($title_arr);
			$i=0;
			while (($row=db_fetch_array($result)) && ($i < $max_rows)) {
				$ret_val .= '
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="'.util_make_uri('/forum/forum.php?thread_id='.
															$row['thread_id'].'&amp;forum_id='.$forum_id.'&amp;group_id='.$group_id).'">'.
					html_image('ic/cfolder15.png') . '  &nbsp; ';
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
				'<td>'.util_display_user($row['user_name'], $row['user_id'], $row['realname']).'</td>'.
					'<td>'. $row['followups'] .'</td>'.
					'<td>'.date(_('Y-m-d H:i'),$row['recent']).'</td></tr>';
				$i++;
			}

			$ret_val .= $GLOBALS['HTML']->listTableBottom();

		}
	}

	/*
		This code puts the nice next/prev.
	*/
	if (($offset != 0) or ($avail_rows > $max_rows)) {
		$ret_val .= '<table width="100%" border="0">
			<tr class="tablecontent"><td width="50%">';
		if ($offset != 0) {
			$ret_val .= '<span class="prev">
			<a href="javascript:history.back()"><strong>' .
				html_image('t2.png',"15","15") ._('Newer Messages').'</strong></a></span>';
		} else {
			$ret_val .= '&nbsp;';
		}

		$ret_val .= '</td><td>&nbsp;</td><td align="right" width="50%">';

		if ($avail_rows > $max_rows) {
			$ret_val .= '<span class="next">
			<a href="'.util_make_uri('/forum/forum.php?max_rows='.$max_rows.'&amp;style='.$style.'&amp;offset='.($offset+$i).
						  '&amp;forum_id='.$forum_id.'&amp;group_id='.$group_id).'">
			<strong> '._('Older Messages') .
				html_image('t.png',"15","15") . '</strong></a></span>';
		} else {
			$ret_val .= '&nbsp;';
		}
		$ret_val .= '</td></tr></table>';
	}

	echo $ret_val;
/*
	echo '<p>&nbsp;<p>';

	if (!$thread_id) {
		//
		//	Viewing an entire message forum in a given format
		//
		echo '<center><h3>'._('Start New Thread').'</h3></center>';
		$fh->showPostForm();
	}
*/
	forum_footer(array());

} else {

	exit_error(_('No forum chosen'),'forums');

}

?>
