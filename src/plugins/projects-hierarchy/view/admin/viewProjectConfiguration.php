<?php
/**
 * Projects Hierarchy plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
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

global $HTML;
global $projectsHierarchy;
global $use_tooltips;
global $group_id;

$projectsHierarchyProjectConf = $projectsHierarchy->getConf($group_id);
if (!$projectsHierarchyProjectConf) {
	echo '<div class="error">'._('Cannot retrieve data from DB').'</div>';
} else {
	echo $HTML->boxTop(_('Manage project configuration'));
	echo '<form method="post" action="?type=admin&&pluginname='.$projectsHierarchy->name.'&action=updateProjectConf">';
	echo '<table>';

	echo '<tr><td><label id="projectsHierarchy-tree" ';
	if ($use_tooltips)
		echo 'title="'._('Enable visibily in hierarchy tree.').'"';
	echo ' >'._('Enable tree').'</label></td><td><input type="checkbox" name="tree" value="1"';
	if ($projectsHierarchyProjectConf['tree'])
		echo 'checked="checked" ';

	echo '/></td></tr>';

	echo '<tr><td><label id="projectsHierarchy-delegate" ';
	if ($use_tooltips)
		echo 'title="'._('Enable full rights and configuration delegation to parent.').'"';
	echo ' >'._('Enable delegate').'</label></td><td><input type="checkbox" name="delegate" value="1"';
	if ($projectsHierarchyProjectConf['delegate'])
		echo 'checked="checked" ';

	echo '/></td></tr>';
	echo '<tr><td><label id="projectsHierarchy-globalconf" ';
	if ($use_tooltips)
		echo 'title="'._('Use forge global configuration. Superseed any configuration done at project level.').'"';
	echo ' >'._('Use forge global configuration').'</label></td><td><input type="checkbox" name="globalconf" value="1"';
	if ($projectsHierarchyProjectConf['globalconf'])
		echo 'checked="checked" ';

	echo '/></td></tr>';

	echo '</table>';
	echo '<input type="submit" value="'._('Update').'" />';
	echo '</form>';
	echo $HTML->boxBottom();
}
?>
