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

$projectsHierarchyGlobalConf = $projectsHierarchy->getGlobalconf();

echo $HTML->boxTop(_('Manage configuration'));
echo '<form method="POST" action="?type=globaladmin&pluginname='.$projectsHierarchy->name.'&action=updateGlobalConf">';
echo '<table>';

echo '<tr><td><label id="projectsHierarchy-tree" ';
if ($use_tooltips)
	echo 'title="'._('Enable Tree in projects tab.').'"';
echo ' >'._('Enable Tree').'</label></td><td><input type="checkbox" name="tree" value="1"';
if ($projectsHierarchyGlobalConf['tree'])
	echo 'checked="checked" ';

echo '/></td></tr>';

echo '<tr><td><label id="projectsHierarchy-docman" ';
if ($use_tooltips)
	echo 'title="'._('Enable hierarchical view for browsing in document manager.').'"';
echo ' >'._('Enable docman browsing').'</label></td><td><input type="checkbox" name="docman" value="1"';
if ($projectsHierarchyGlobalConf['docman'])
	echo 'checked="checked" ';

echo '/></td></tr>';
echo '</table>';
echo '<input type="submit" value="'._('Update').'" />';
echo '</form>';
echo $HTML->boxBottom();
?>
