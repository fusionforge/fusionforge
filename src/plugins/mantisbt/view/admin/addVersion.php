<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

/* add a new version */

global $HTML;
global $group;
global $group_id;
global $mantisbt;

echo '<form method="POST" name="addVersion" action="index.php?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=addVersion">';
echo '<table><tr>';
echo $HTML->boxTop(_('Add a new version'));
echo '<td>';
echo '<label>'._('Name').'</label><input name="version" type="text" size="10" />';
if ($group->usesPlugin('projects_hierarchy')) {
	echo '<input name="transverse" type="checkbox" value="1" >'. _('Cross version (son included)') .'</input>';
}
echo '</td>';
echo '</tr><tr>';
echo '<td>';
echo '<label>'._('Description').'</label><input name="description" type="text" size="20" />';
echo '</td>';
echo '<td>';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo $HTML->boxBottom();
echo '</tr></table>';
echo '</form>';
?>
