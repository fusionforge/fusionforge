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
global $pluginExtSubProj;
global $use_tooltips;
global $group_id;

$subProjects = $pluginExtSubProj->getSubProjects($group_id);

if(is_array($subProjects)) {
	$tablearr = array(_('Subproject URL'),'');
	echo $HTML->listTableTop($tablearr);

	foreach ($subProjects as $url) {
		echo '
		<tr>
			<td>'.$url.'
			</td>
			<td>
				<a href="'. $pluginExtSubProj->getProjectAdminDelExtSubProjAction($group_id, $url) .'">'._('delete').'</a>
			</td>
		</tr>';
	}

	echo $HTML->listTableBottom();
}

echo $HTML->boxTop(_("Manage project's external subprojects"));

echo '<form method="post" action="'.$pluginExtSubProj->getProjectAdminAddExtSubProjAction($group_id).'">';
echo '<table>';

echo '<tr><td><label id="extSubProj-newsubprojecturl" ';
if ($use_tooltips)
	echo 'class="tabtitle-nw" title="'._('URL of the new subproject.').'"';
echo ' >'._('URL').'</label></td><td><input type="text" name="newsubprojecturl"';
echo '/></td></tr>';

echo '</table>';
echo '<input type="submit" value="'._('Add').'" />';
echo '</form>';

echo $HTML->boxBottom();

?>
