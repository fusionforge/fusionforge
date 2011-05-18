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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $HTML;
global $group_id;
global $mantisbt;
/* add category to a dedicated project */

echo '<form method="POST" name="addCategory" action="index.php?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=addCategory">';
echo '<table><tr>';
echo $HTML->boxTop(_('Add a new category'));
echo '<td><label>'._('Name').'<input name="nameCategory" type="text"></input></td>';
echo '<td>';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo $HTML->boxBottom();
echo '</tr></table>';
echo '</form>';
?>
