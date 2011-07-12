<?php
/**
 * Forums Facility
 *
 * Copyright 1999-2001, Tim Perdue - Sourceforge
 * Copyright 2002, Tim Perdue - GForge, LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
require_once $gfcommon.'forum/AttachManager.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';

$msg_id = getIntFromRequest('msg_id');
$total_rows = getIntFromRequest('total_rows');
$ret_val = getIntFromRequest('ret_val');
$reply = getIntFromRequest('reply', 1);

if ($msg_id) {

	/*
		Figure out which group this message is in, for the sake of the admin links
	*/
	$result=db_query_params ('SELECT forum_group_list.group_id,forum_group_list.group_forum_id
		FROM forum_group_list,forum
		WHERE forum_group_list.group_forum_id=forum.group_forum_id
		AND forum.msg_id=$1',
			array($msg_id));

	if (!$result || db_numrows($result) < 1) {
		/*
			Message not found
		*/
		exit_error(_('This message does not (any longer) exist'),'forums');
	}

	$group_id=db_result($result,0,'group_id');
	$forum_id=db_result($result,0,'group_forum_id');

	//
	//  Set up local objects
	//
	$g = group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$f=new Forum($g,$forum_id);
	if (!$f || !is_object($f)) {
		exit_error(_('Error getting new Forum'),'forums');
	} elseif ($f->isError()) {
		if ($f->isPermissionDeniedError()) {
			exit_permission_denied();
		}
		exit_error($f->getErrorMessage(),'forums');
	}

	$fm=new ForumMessage($f,$msg_id);
	if (!$fm || !is_object($fm)) {
		exit_error(_('Error getting new ForumMessage'),'forums');
	} elseif ($fm->isError()) {
		exit_error($fm->getErrorMessage(),'forums');
	}

	$fmf = new ForumMessageFactory($f);
	if (!$fmf || !is_object($fmf)) {
		exit_error(_('Error getting new ForumMessageFactory'),'forums');
	} elseif ($fmf->isError()) {
		exit_error($fmf->getErrorMessage(),'forums');
	}

	$fmf->setUp(0,'threaded',200,'');
	$style=$fmf->getStyle();
	$max_rows=$fmf->max_rows;
	$offset=$fmf->offset;

	$fh = new ForumHTML($f);
	if (!$fh || !is_object($fh)) {
		exit_error(_('Error getting new ForumHTML'),'forums');
	} elseif ($fh->isError()) {
		exit_error($fh->getErrorMessage(),'forums');
	}

	if ($reply) {
		session_require_perm ('forum', $f->getID(), 'post') ;
	}

	forum_header(array('title'=>$fm->getSubject(),'forum_id'=>$forum_id));

//	$title_arr=array();
//	$title_arr[]=_('Message').': '.$msg_id;

//	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$url = util_make_url('/forum/message.php?msg_id='. $msg_id .'&amp;group_id='.$group_id);

	echo '<br /><br /><table border="0" width="100%" cellspacing="0">';
	echo '<tr class="tablecontent"><td valign="top">'."\n";
	echo '<strong>'.$fm->getSubject() .'</strong>';
	if (!$reply) {
		echo ' <a href="'.$url.'&amp;reply=1">[ '._("reply").' ]</a>';
	}
	echo '<br />';
	echo _("By:").' '. util_make_link_u($fm->getPosterName(), $fm->getPosterID(), $fm->getPosterRealName());
	echo ' on '. date(_('Y-m-d H:i'), $fm->getPostDate()) .'</td><td align="right">';
	echo '<a href="'.$url.'">[forum:'.$msg_id.']</a><br/>';
	$fa = new ForumAdmin($f->Group->getID());
	if (forge_check_perm ('forum_admin', $f->Group->getID())) {
		echo $fa->PrintAdminMessageOptions($msg_id,$group_id,0,$forum_id); // 0 in thread id because that tells us to go back to message.php instead of forum.php
	}
	$am = new AttachManager();
	echo $am->PrintHelperFunctions();
	echo $am->PrintAttachLink($fm,$group_id,$forum_id) . '</td></tr><tr><td colspan="2"><br/><br />';


	if (strpos($fm->getBody(), '>') === false) {
		echo util_make_links(nl2br($fm->getBody())); //backwards compatibility for non html messages
	} else {
		echo util_make_links($fm->getBody());
	}
	echo '</td></tr></table>';

//	echo $GLOBALS['HTML']->listTableBottom();

	/*

		Show entire thread

	*/
	echo '<h2>'._('Thread View').'</h2>';

	$msg_arr = $fmf->nestArray($fmf->getThreaded($fm->getThreadID()));
	if ($fmf->isError()) {
		echo $fmf->getErrorMessage();
	}

	$title_arr=array();
	$title_arr[]=_('Thread');
	$title_arr[]=_('Author');
	$title_arr[]=_('Date');

	$ret_val = $GLOBALS['HTML']->listTableTop ($title_arr);

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
			$ah_begin='<a href="'.util_make_url ('/forum/message.php?msg_id='.$msg->getID().
							     '&amp;group_id='.$group_id).'">';
			$ah_end='</a>';
		} else {
			$ah_begin='';
			$ah_end='';
		}
		$ret_val .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($total_rows) .'>
			<td>'. $ah_begin .
			html_image('ic/msg.png').' ';
		/*
			See if this message is new or not
			If so, highlite it in bold
		*/
		$bold_begin='';
		$bold_end='';
		if ($f->getSavedDate() < $msg->getPostDate()) {
			$bold_begin = '<strong>';
			$bold_end = '</strong>';
		}
		/*
			show the subject and poster
		*/
		$ret_val .= $bold_begin . $msg->getSubject() . $bold_end.$ah_end.'</td>'.
			'<td><a href="/users/'.$msg->getPosterName().'">'. $msg->getPosterRealName() .'</a></td>'.
			'<td>'. date(_('Y-m-d H:i'),$msg->getPostDate()) .'</td></tr>';

		if ($msg->hasFollowups()) {
			$ret_val .= $fh->showSubmessages($msg_arr,$msg->getID(),1);
		}
		$i++;
	}

	$ret_val .= $GLOBALS['HTML']->listTableBottom();

	echo $ret_val;

	if ($reply) {
		/*
			Show post followup form
		*/
		echo '<h3>'._('Post a followup to this message').'</h3>';
		$fh->showPostForm($fm->getThreadID(), $msg_id, $fm->getSubject());
	}

} else {
	forum_header(array('title'=>_('You Must Choose a Message First')));
	echo '<p class="error">'._('You Must Choose a Message First').'</p>';

}

forum_footer(array());

?>
