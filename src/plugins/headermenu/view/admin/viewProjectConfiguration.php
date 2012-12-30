<?php
/**
 * headermenu : viewProjectConfiguration page
 *
 * Copyright 2012 Franck Villaume - TrivialDev
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
global $headermenu;
global $group_id;

?>

<?php
$linksArray = $headermenu->getAvailableLinks('groupmenu');

echo '<form method="POST" name="addLink" action="index.php?type=projectadmin&group_id='.$group_id.'&action=addLink">';
echo '<table><tr>';
echo $HTML->boxTop(_('Add a new link'));
echo '<td>'._('Displayed Name').'</td><td><input name="name" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Description').'</td><td><input name="description" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<tr id="urlcode" >';
echo '<td>'._('URL').'</td><td><input name="link" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>';
echo '<input type="hidden" name="linkmenu" value="groupmenu" />';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo $HTML->boxBottom();
echo '</tr></table>';
echo '</form>';
