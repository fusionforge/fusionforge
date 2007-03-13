<?php

/**
 * GForge Monitored Forums Track Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* my monitored forums
	by Daniel Perez - 2005
*/

require_once('../env.inc.php');
require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('common/forum/Forum.class');
require_once('common/forum/ForumFactory.class');
require_once('common/forum/ForumMessageFactory.class');
require_once('common/forum/ForumMessage.class');



if (!session_loggedin()) {
	exit_permission_denied();	
}

$user_id = user_getid();
$group_id = getIntFromRequest("group_id");
//get the user monitored forums
$sql = "SELECT mon.forum_id, fg.group_id FROM forum_monitored_forums mon,forum_group_list fg where mon.user_id='$user_id' and fg.group_forum_id=mon.forum_id";
$result = db_query($sql);
if (!$result || db_numrows($result) < 1) {
	exit_error(_('You have no monitored forums'),_('You have no monitored forums').' '.db_error());
}

//now, i need to create a forum object per each forum that the user is monitoring

$monitored_forums = array();
for ($i=0;$i<db_numrows($result);$i++) {
	$monitored_forums[$i] = db_fetch_array($result);
}

//if the link comes from the project, display the project header. If it comes from the user page, display the normal site header
if ($group_id) {
	forum_header(array('title'=>_('My Monitored Forums')));
}	else {
	site_header(array('title'=>_('My Monitored Forums'), 'user_id' => $user_id));
}

echo "<h4>" . _('My Monitored Forums') . "</h4></p>";
$tablearr=array(_('Project'),_('Project'),
				_('Description'),_('Description'),
				_('Posts'), _('Posts'), _('Posts'));
echo $HTML->listTableTop($tablearr);

$i = 0;


$f = array();
//CHECK : if we won�t ever be needing to store each forum/fmf, etc for each pass, don�t use an array and use the same variable like $fmf instead of $fmf[$i], etc
for($i=0;$i<sizeof($monitored_forums);$i++) {
	$g =& group_get_object($monitored_forums[$i]["group_id"]);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}
	$f = new Forum($g,$monitored_forums[$i]["forum_id"]);
	if (!$f || !is_object($f) || $f->isError()) {
		exit_error(_('Error'));
	}
	if (!is_object($f)) {
		//just skip it - this object should never have been placed here
	}	elseif ($f->isError()) {
		echo $f->getErrorMessage();
	}	else {
		//check if the forum has new content
		
		$fh = new ForumHTML($f);
		if (!$fh || !is_object($fh)) {
			exit_error(_('Error'), "Error getting new ForumHTML");
		}	elseif ($fh->isError()) {
			exit_error(_('Error'),$fh->getErrorMessage());
		}
		
		$fmf = new ForumMessageFactory($f);
		if (!$fmf || !is_object($fmf)) {
			exit_error(_('Error'), "Error getting new ForumMessageFactory");
		}	elseif ($fmf->isError()) {
			exit_error(_('Error'),$fmf->getErrorMessage());
		}
		$fmf->setUp($offset,$style,$max_rows,$set);
		$style=$fmf->getStyle();
		$max_rows=$fmf->max_rows;
		$offset=$fmf->offset;
		$msg_arr =& $fmf->nestArray($fmf->getNested());
		if ($fmf->isError()) {
			echo $fmf->getErrorMessage();
		}
		$rows=count($msg_arr[0]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}
		$j=0;
		$newcontent = "<center>---</center>";
		//this loops through every message AND followup, in search of new messages.
		//anything that�s new ( new thread or followup) is considered to be a "new thing" and the forum 
		//is considered to have new contents
		foreach ($msg_arr as $forum_msg_arr) {
			foreach ($forum_msg_arr as $forum_msg) {
				if ($f->getSavedDate() < $forum_msg->getPostDate()) {
				//we�ve got ourselves a new message or followup for this forum. note that, exit the search
				$newcontent = "<center>" . html_image("ic/new.png","25","11",array("border"=>"0")) . "</center>";
				break;
				}
			}
			if ($newcontent!="<center>---</center>") {
				break;
			}
		}
		/*while (($j < $rows) && ($total_rows < $max_rows)) {
			$msg =& $msg_arr["0"][$j];
			$total_rows++;
			if ($f->getSavedDate() < $msg->getPostDate()) {
				//we�ve got ourselves a new message for this forum. note that, exit the search
				$newcontent = "<center>" . html_image("ic/new.png","25","11",array("border"=>"0")) . "</center>";
				break;
			}
			$j++;
		}*/
		
		$this_forum_group = $f->getGroup();
		echo '<tr '. $HTML->boxGetAltRowStyle($j) . '>
			<td>' . $this_forum_group->getPublicName() . '</td>
			<td><a href="forum.php?forum_id='. $f->getID() .'">'.
			html_image("ic/forum20w.png","20","20",array("border"=>"0")) .
			'&nbsp;' .
			$f->getName() .'</a></td>
			<td>'.$f->getDescription().'</td>
			<td align="center">'.$f->getThreadCount().'</td>
			<td align="center">'. $f->getMessageCount() .'</td>
			<td>'.  date($sys_datefmt,$f->getMostRecentDate()) .'</td>
			<td>' . $newcontent . '</td></tr>';
	}
}

echo $HTML->listTableBottom();
forum_footer(array());
?>
