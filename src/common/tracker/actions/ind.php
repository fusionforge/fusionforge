<?php
/**
 * Tracker Front Page
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012,2014,2016, Franck Villaume - TrivialDev
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

global $group;
global $HTML;

$atf = new ArtifactTypeFactoryHtml($group);
if (!$atf || !is_object($atf) || $atf->isError()) {
	exit_error(_('Could Not Get ArtifactTypeFactory'),'tracker');
}

$at_arr = $atf->getArtifactTypes();
if ($at_arr === false) {
	exit_permission_denied('tracker');
}

html_use_tablesorter();
$atf->header();

if (!$at_arr || count($at_arr) < 1) {
	$localInformation = $HTML->information(_('No trackers have been set up, or you cannot view them.'));
	$localInformation .= html_e('p', array(), sprintf(_('The Admin for this project will have to set up data types using the %1$s admin page %2$s'), '<a href="'.util_make_url ('/tracker/admin/?group_id='.$group_id).'">', '</a>'));
	$at_arr = array();
}
$child_has_at = false;
if ($group->usesPlugin('projects-hierarchy')) {
	$projectsHierarchy = plugin_get_object('projects-hierarchy');
	$projectIDsArray = $projectsHierarchy->getFamily($group->getID(), 'child', true, 'validated');
	foreach ($projectIDsArray as $projectid) {
		$childGroupObject = group_get_object($projectid);
		if ($childGroupObject && is_object($childGroupObject) && !$childGroupObject->isError()) {
			if ($childGroupObject->usesTracker() && $projectsHierarchy->getTrackerStatus($childGroupObject->getID())) {
				$childatf = new ArtifactTypeFactoryHtml($childGroupObject);
				$child_at_arr = $childatf->getArtifactTypes();
				if (is_array($child_at_arr) && count($child_at_arr) > 0) {
					$at_arr = array_merge($at_arr, $child_at_arr);
					$child_has_at = true;
				}
			}
		}
		unset($childGroupObject);
	}
}

if (count($at_arr) < 1) {
	echo $localInformation;
} else {
	plugin_hook ("blocks", "tracker index");
	echo html_e('p', array(), _('Choose a tracker and you can browse/edit/add items to it.'));
	/*
		Put the result set (list of trackers for this group) into a column with folders
	*/
	$tablearr = array(_('Tracker'),_('Description'),_('Open'),_('Total'));
	$thclass = array(array(), array(), array(), array('class' => 'align-center'), array('class' => 'align-center'));
	if ($child_has_at) {
		$tablearr = array(_('Project'), _('Tracker'),_('Description'),_('Open'),_('Total'));
		$thclass = array(array(), array(), array(), array('class' => 'align-center'), array('class' => 'align-center'));
	}

	if (isset($localInformation)) {
		echo $localInformation;
	}
	echo $HTML->listTableTop($tablearr, false, 'full sortable sortable_table_tracker', 'sortable_table_tracker', array(), array(), $thclass);
	for ($j = 0; $j < count($at_arr); $j++) {
		if (!is_object($at_arr[$j])) {
			//just skip it
		} elseif ($at_arr[$j]->isError()) {
			echo $at_arr[$j]->getErrorMessage();
		} else {
			$cells = array();
			if ($child_has_at) {
				if ($at_arr[$j]->Group->getID() != $group->getID()) {
					$cells[] = array(sprintf(_('Child project %s Tracker'), util_make_link('/tracker/?group_id='.$at_arr[$j]->Group->getID(), $at_arr[$j]->Group->getPublicName())), 'content' => $at_arr[$j]->Group->getID());
				} else {
					$cells[] = array('', 'content' => $at_arr[$j]->Group->getID());
				}
			}
			$cells[][] = util_make_link('/tracker/?atid='.$at_arr[$j]->getID().'&group_id='.$at_arr[$j]->Group->getID().'&func=browse', $HTML->getFollowPic().' '.$at_arr[$j]->getName());
			$cells[][] = $at_arr[$j]->getDescription();
			$cells[] = array((int) $at_arr[$j]->getOpenCount(), 'class' => 'align-center');
			$cells[] = array((int) $at_arr[$j]->getTotalCount(), 'class' => 'align-center');
			echo $HTML->multiTableRow(array(), $cells);
		}
	}
	echo $HTML->listTableBottom();
}
$atf->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
