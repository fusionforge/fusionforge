<?php
/**
 * Forums Facility
 *
 * Copyright 1999-2001, Tim Perdue - Sourceforge
 * Copyright 2002, Tim Perdue - GForge, LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014,2016, Franck Villaume - TrivialDev
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
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/Forum.class.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
if ($group_id) {
	$g = group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$ff = new ForumFactory($g);
	if (!$ff || !is_object($ff)) {
		exit_error(_('Unable to retrieve forums'), 'forums');
	} elseif ($ff->isError()) {
		exit_error($ff->getErrorMessage(), 'forums');
	}

	$farr = $ff->getForums();
	if (count($farr) == 0) {
		$localInformation = $HTML->information(sprintf(_('No Forums found for %s'), $g->getPublicName()));
	}

	$child_has_f = false;
	if ($g->usesPlugin('projects-hierarchy')) {
		$projectsHierarchy = plugin_get_object('projects-hierarchy');
		$projectIDsArray = $projectsHierarchy->getFamily($group_id, 'child', true, 'validated');
		foreach ($projectIDsArray as $projectid) {
			$childGroupObject = group_get_object($projectid);
			if ($childGroupObject && is_object($childGroupObject) && !$childGroupObject->isError()) {
				if ($childGroupObject->usesForum() && $projectsHierarchy->getForumStatus($childGroupObject->getID())) {
					$childff = new ForumFactory($childGroupObject);
					$child_farr = $childff->getForums();
					if (count($child_farr) > 0) {
						$farr = array_merge($farr, $child_farr);
						$child_has_f = true;
					}
				}
			}
			unset($childGroupObject);
		}
	}

	if ((count($farr) == 1) && ($farr[0]->Group->getID() == $group_id)) {
		session_redirect('/forum/forum.php?forum_id='.$farr[0]->getID(), false);
	}

	html_use_tablesorter();

	forum_header(array('title' => sprintf(_('Forums for %s'), $g->getPublicName()) ));

	if ( count($farr) < 1) {
		echo $localInformation;
		forum_footer();
		exit;
	}

	if (session_loggedin()) {
		echo $HTML->printsubMenu(array(_("My Monitored Forums")), array("/forum/myforums.php?group_id=$group_id"), array());
	}

	plugin_hook("blocks", "forum index");

	$tablearr = array(_('Forum'), _('Description'), _('Threads'), _('Posts'), _('Last Post'));
	$thclass = array(array(), array(), array('class' => 'align-center'), array('class' => 'align-center'), array('class' => 'align-center'));
	if ($child_has_f) {
		$tablearr = array(_('Project'), _('Forum'), _('Description'), _('Threads'), _('Posts'), _('Last Post'));
		$thclass = array(array(), array(), array(), array('class' => 'align-center'), array('class' => 'align-center'), array('class' => 'align-center'));
	}
	if (isset($localInformation)) {
		echo $localInformation;
	}
	/*
		Put the result set (list of forums for this group) into a column with folders
	*/
	echo $HTML->listTableTop($tablearr, array(), 'full sortable sortable_table_forum', 'sortable_table_forum', array(), array(), $thclass);
	for ($j = 0; $j < count($farr); $j++) {
		if (!is_object($farr[$j])) {
			//just skip it - this object should never have been placed here
		} elseif ($farr[$j]->isError()) {
			echo $farr[$j]->getErrorMessage();
		} else {
			$cells = array();
			if ($child_has_f) {
				if ($farr[$j]->Group->getID() != $group_id) {
					$cells[] = array(sprintf(_('Child project %s Forum'), util_make_link('/forum/?group_id='.$farr[$j]->Group->getID(), $farr[$j]->Group->getPublicName())), 'content' => $farr[$j]->Group->getID());
				} else {
					$cells[] = array(sprintf(_('Project %s Forum'), $farr[$j]->Group->getPublicName()), 'content' => $farr[$j]->Group->getID());
				}
			}
			$cells[][] = util_make_link('/forum/forum.php?forum_id='.$farr[$j]->getID().'&group_id='.$farr[$j]->Group->getID(), $HTML->getForumPic().' '.$farr[$j]->getName());
			$cells[][] = $farr[$j]->getDescription();
			$cells[] = array($farr[$j]->getThreadCount(), 'class' => 'align-center');
			$cells[] = array($farr[$j]->getMessageCount(), 'class' => 'align-center');
			$cells[] = array(date(_('Y-m-d H:i'),$farr[$j]->getMostRecentDate()), 'class' => 'align-center');
			echo $HTML->multiTableRow(array(), $cells);
		}
	}
	echo $HTML->listTableBottom();
	forum_footer();

} else {
	exit_no_group();
}
