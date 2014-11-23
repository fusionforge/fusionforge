<?php

/**
 * FusionForge Monitored Forums Track Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2005 (c) - Daniel Perez
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';

global $HTML;

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
$MonitorElementObject = new MonitorElement('forum');
$monitoredForumsIdsArray = $MonitorElementObject->getMonitedByUserIdInArray($user_id);

if (!$monitoredForumsIdsArray) {
	echo $HTML->error_msg($MonitorElementObject->getErrorMessage());
	forum_footer();
	exit;
}

if (count($monitoredForumsIdsArray) < 1) {
	echo $HTML->information(_('You have no monitored forums'));
	forum_footer();
	exit;
}

$tablearr = array(_('Project'),_('Forum'), _('Threads'), _('Posts'), _('Last Post'), _('New Content?'));
echo $HTML->listTableTop($tablearr);

$i = 0;
$j = 0;

$f = array();
//CHECK : if we won't ever be needing to store each forum/fmf, etc for each pass, don't use an array and use the same variable like $fmf instead of $fmf[$i], etc
for($i = 0; $i < sizeof($monitoredForumsIdsArray); $i++) {
	if (forge_check_perm('forum', $monitoredForumsIdsArray[$i], 'read')) {
		$forumObject = forum_get_object($monitoredForumsIdsArray[$i]);
		if ($forumObject->isError()) {
			echo $forumObject->getErrorMessage();
		} else {
			//check if the forum has new content

			$fh = new ForumHTML($forumObject);
			if (!$fh || !is_object($fh)) {
				exit_error(_('Error getting new ForumHTML'), 'forums');
			} elseif ($fh->isError()) {
				exit_error($fh->getErrorMessage(), 'forums');
			}

			$fmf = new ForumMessageFactory($forumObject);
			if (!$fmf || !is_object($fmf)) {
				exit_error(_('Error getting new ForumMessageFactory'), 'forums');
			} elseif ($fmf->isError()) {
				exit_error($fmf->getErrorMessage(), 'forums');
			}

			$fmf->setUp($offset,$style,$max_rows,$set);
			$style=$fmf->getStyle();
			$max_rows=$fmf->max_rows;
			$offset=$fmf->offset;
			$msg_arr = $fmf->nestArray($fmf->getNested());
			if ($fmf->isError()) {
				exit_error($fmf->getErrorMessage(), 'forums');
			}
			$rows=count($msg_arr[0]);
			$avail_rows=$fmf->fetched_rows;
			if ($rows > $max_rows) {
				$rows=$max_rows;
			}

			$new_content = '&nbsp;';
			//this loops through every message AND followup, in search of new messages.
			//anything that's new ( new thread or followup) is considered to be a "new thing" and the forum
			//is considered to have new contents
			if (!empty($msg_arr)) {
				foreach ($msg_arr as $forum_msg_arr) {
					foreach ($forum_msg_arr as $forum_msg) {
						if ($forumObject->getSavedDate() < $forum_msg->getPostDate()) {
							//we've got ourselves a new message or followup for this forum. note that, exit the search
							$new_content = html_image('ic/add.png','', '', array('alt' => 'new'));
							break;
						}
					}
					if ($new_content != '&nbsp;') {
						break;
					}
				}
			}

			$this_forum_group = $forumObject->getGroup();
			$date = $forumObject->getMostRecentDate()? date(_('Y-m-d H:i'),$forumObject->getMostRecentDate()) : '';
			$cells = array();
			$cells[][] = $this_forum_group->getPublicName();
			$cells[][] = util_make_link('/forum/forum.php?forum_id='.$forumObject->getID().'&group_id='.$this_forum_group->getID(), html_image('ic/forum20w.png').'&nbsp;'.$forumObject->getName());
			$cells[] = array($forumObject->getThreadCount(), 'class' => 'align-center');
			$cells[] = array($forumObject->getMessageCount(), 'class' => 'align-center');
			$cells[] = array($date, 'class' => 'align-center');
			$cells[] = array($new_content, 'class' => 'align-center');
			echo $HTML->multiTableRow(array(), $cells);
		}
	} else {
		// Oh ho! we found some monitored elements where user has no read access. Let's clean the situation
		$monitorElementObject->disableMonitoringByUserId($monitoredForumsIdsArray[$i], user_getid());
	}
}

echo $HTML->listTableBottom();
forum_footer();
