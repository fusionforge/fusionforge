<?php

/**
 * FusionForge Monitored Forums Track Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2005 (c) - Daniel Perez
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
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

session_require_login();

$user_id = user_getid();
$group_id = getIntFromRequest("group_id");

// If the link comes from the project, display the project header. If it comes from the user page, display the normal site header
if ($group_id) {
    forum_header(array('title'=>_('My Monitored Forums')));
} else {
    site_header(array('title'=>_('My Monitored Forums'), 'user_id' => $user_id));
}

//get the user monitored forums
$result = db_query_params ('SELECT mon.forum_id, fg.group_id FROM forum_monitored_forums mon,forum_group_list fg where mon.user_id=$1 and fg.group_forum_id=mon.forum_id',
			   array ($user_id));
if (!$result) {
    echo '<div class="error">Database error :'.db_error().'</div>';
    forum_footer(array());
    exit;
}
if ( db_numrows($result) < 1) {
    echo '<div class="feedback">'._('You have no monitored forums').'</div>';
    forum_footer(array());
    exit;
}

//now, i need to create a forum object per each forum that the user is monitoring
$monitored_forums = array();
for ($i=0;$i<db_numrows($result);$i++) {
	$monitored_forums[$i] = db_fetch_array($result);
}

$tablearr=array(_('Project'),_('Forum'), _('Threads'),
				_('Posts'), _('Last Post'), _('New Content?'));
echo $HTML->listTableTop($tablearr);

$i = 0;
$j = 0;

$f = array();
//CHECK : if we won't ever be needing to store each forum/fmf, etc for each pass, don't use an array and use the same variable like $fmf instead of $fmf[$i], etc
for($i=0;$i<sizeof($monitored_forums);$i++) {
	$g = group_get_object($monitored_forums[$i]["group_id"]);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}
	$f = new Forum($g,$monitored_forums[$i]["forum_id"]);
	if (!$f || !is_object($f) || $f->isError()) {
		exit_error($f->isError(),'forums');
	}
	if (!is_object($f)) {
		//just skip it - this object should never have been placed here
	}	elseif ($f->isError()) {
		echo $f->getErrorMessage();
	}	else {
		//check if the forum has new content

		$fh = new ForumHTML($f);
		if (!$fh || !is_object($fh)) {
			exit_error(_('Error getting new ForumHTML'),'forums');
		}	elseif ($fh->isError()) {
			exit_error($fh->getErrorMessage(),'forums');
		}

		$fmf = new ForumMessageFactory($f);
		if (!$fmf || !is_object($fmf)) {
			exit_error(_('Error getting new ForumMessageFactory'),'forums');
		}	elseif ($fmf->isError()) {
			exit_error($fmf->getErrorMessage(),'forums');
		}

		$fmf->setUp($offset,$style,$max_rows,$set);
		$style=$fmf->getStyle();
		$max_rows=$fmf->max_rows;
		$offset=$fmf->offset;
		$msg_arr = $fmf->nestArray($fmf->getNested());
		if ($fmf->isError()) {
			exit_error($fmf->getErrorMessage(),'forums');
		}
		$rows=count($msg_arr[0]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}

		$newcontent = '&nbsp;';
		//this loops through every message AND followup, in search of new messages.
		//anything that's new ( new thread or followup) is considered to be a "new thing" and the forum
		//is considered to have new contents
		if (!empty($msg_arr)) {
		foreach ($msg_arr as $forum_msg_arr) {
			foreach ($forum_msg_arr as $forum_msg) {
				if ($f->getSavedDate() < $forum_msg->getPostDate()) {
				//we've got ourselves a new message or followup for this forum. note that, exit the search
						$newcontent = "<center>" . html_image('ic/new.png','', '', array('alt' => 'new')) . "</center>";
				break;
				}
			}
				if ($newcontent != '&nbsp;') {
				break;
			}
		}
		}
		/*while (($j < $rows) && ($total_rows < $max_rows)) {
			$msg =& $msg_arr["0"][$j];
			$total_rows++;
			if ($f->getSavedDate() < $msg->getPostDate()) {
				//we've got ourselves a new message for this forum. note that, exit the search
				$newcontent = "<center>" . html_image('ic/new.png','', '', array('alt' => 'new')) . "</center>";
				break;
			}
			$j++;
		}*/

		$this_forum_group = $f->getGroup();
		$date = $f->getMostRecentDate()? date(_('Y-m-d H:i'),$f->getMostRecentDate()) : '';
		echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
			<td>' . $this_forum_group->getPublicName() . '</td>
			<td><a href="forum.php?forum_id='. $f->getID() .'&amp;group_id='.$this_forum_group->getID().'">'.
			html_image('ic/forum20w.png') .
			'&nbsp;' .
			$f->getName() .'</a></td>
			<td style="text-align:center">'.$f->getThreadCount().'</td>
			<td style="text-align:center">'. $f->getMessageCount() .'</td>
			<td style="text-align:center">'. $date .'</td>
			<td>' . $newcontent . '</td></tr>';
	}
}

echo $HTML->listTableBottom();
forum_footer(array());
?>
