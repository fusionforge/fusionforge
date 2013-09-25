<?php
/**
 * Tracker Front Page
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Franck Villaume - TrivialDev
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

use_javascript('/js/sortable.js');

$atf->header();

if (!$at_arr || count($at_arr) < 1) {
	echo '<p class="information">'._('No trackers have been set up, or you cannot view them.').'</p>';
	echo '<p>';
	echo sprintf(_('The Admin for this project will have to set up data types using the %1$s admin page %2$s'), '<a href="'.util_make_url ('/tracker/admin/?group_id='.$group_id).'">', '</a>');
	echo "</p>";
} else {

	plugin_hook ("blocks", "tracker index");

	echo '<p>'._('Choose a tracker and you can browse/edit/add items to it.').'</p>';

	/*
		Put the result set (list of trackers for this group) into a column with folders
	*/
	$tablearr = array(_('Tracker'),_('Description'),_('Open'),_('Total'));

	echo $HTML->listTableTop($tablearr, false, 'full sortable_table_tracker', 'sortable_table_tracker');

	for ($j = 0; $j < count($at_arr); $j++) {
		if (!is_object($at_arr[$j])) {
			//just skip it
		} elseif ($at_arr[$j]->isError()) {
			echo $at_arr[$j]->getErrorMessage();
		} else {
			echo '
		<tr '. $HTML->boxGetAltRowStyle($j) . '>
			<td><a href="'.util_make_uri('/tracker/?atid='.$at_arr[$j]->getID().'&amp;group_id='.$group_id.'&amp;func=browse').'">'.
 				html_image("ic/tracker20w.png","20","20").' '.
				$at_arr[$j]->getName() .'</a>
			</td>
			<td>' .  $at_arr[$j]->getDescription() .'
			</td>
			<td class="align-center">'. (int) $at_arr[$j]->getOpenCount() . '
			</td>
			<td class="align-center">'. (int) $at_arr[$j]->getTotalCount() .'
			</td>
		</tr>';
		}
	}
	echo $HTML->listTableBottom();
}

$atf->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
