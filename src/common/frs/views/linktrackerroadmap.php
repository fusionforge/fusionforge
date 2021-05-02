<?php
/**
 * FusionForge FRS: Link Tracker Roadmaps to FRS Release
 *
 * Copyright 2016,2021, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $HTML; // html object
global $group_id; // id of group
global $g; // group object
global $frsr; // FRS release object

echo html_e('hr');
echo html_e('h2', array(), _('Attach/Detach Roadmaps To This Release'));
$rf = new RoadmapFactory($g);
$enabledRoadmaps = $rf->getRoadmaps(true);

if (count($enabledRoadmaps)) {
	$title_arr = array();
	//$title_arr[] = html_e('input', array('id' => 'checkallroadactive', 'type' => 'checkbox', 'title' => _('Select / Deselect all roadmaps for massaction'), 'onClick' => 'controllerFRS.checkAll("checkedrelidroadactive", "roadactive")'));
	$title_arr[] = _('Name');
	$title_arr[] = _('Actions');
	echo $HTML->listTableTop($title_arr, array(), '', '', array(), array(), array(array('style' => 'width: 30%')));
	echo '<tr><td colspan="3" style="padding:0;">';
	$i = 1;
	foreach ($enabledRoadmaps as $enabledRoadmap) {
		$releaseRoadmaps = $enabledRoadmap->getReleases();
		if (count($releaseRoadmaps)) {
			foreach ($releaseRoadmaps as $releaseRoadmap) {
				if ($frsr->isLinkedRoadmapRelease($releaseRoadmap)) {
					$type = 'del';
					$labelInput = _('Detach this roadmap from this release');
				} else {
					$type = 'add';
					$labelInput = _('Attach this roadmap from this release');
				}
				echo $HTML->openForm(array('action' => '/frs/?group_id='.$group_id.'&release_id='.$release_id.'&package_id='.$package_id.'&roadmap_id='.$enabledRoadmap->getID().'&action=linkroadmap&type='.$type, 'method' => 'post', 'id' => 'roadmap'.$enabledRoadmap->getID()));
				echo $HTML->listTableTop();
				$cells = array();
				$inputAttr =
				//$cells[] = array(html_e('input', array('type' => 'checkbox', 'value' => $enabledRoadmap->getID(), 'class' => 'checkedrelidroadactive', 'title' => _('Select / Deselect this roadmap for massaction'), 'onClick' => 'controllerFRS.checkgeneral("roadactive")')), 'style' => 'width: 2%; padding: 0px;');
				$cells[] = array($enabledRoadmap->getName().' - '.$releaseRoadmap, 'style' => 'white-space: nowrap; width: 30%');
				$cells[][] = '<input type="hidden" name="roadmap_release" value="'.$releaseRoadmap.'"><input type="submit" name="submit" value="'.$labelInput.'" />';
				echo $HTML->multiTableRow(array(), $cells);
				echo $HTML->listTableBottom();
				echo $HTML->closeForm();
				$i++;
			}
		}
	}
	echo '</td></tr>';
	echo $HTML->listTableBottom();
} else {
	echo $HTML->information(_('No Roadmap available'));
}
