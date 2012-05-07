<?php
/**
 * headermenuPlugin Class
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

session_require_global_perm('forge_admin');

$linksArray = $headermenu->getAvailableLinks();
if (sizeof($linksArray)) {
	echo $HTML->boxTop(_('Manage available links'));
	$tabletop = array(_('URL'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('','','','','');
	echo $HTML->listTableTop($tabletop, false, 'sortable_headermenu_listlinks', 'sortable', $classth);
	foreach ($linksArray as $link) {
		echo '<tr>';
		echo '<td>'.$link['url'].'</td>';
		echo '<td>'.$link['name'].'</td>';
		echo '<td>'.$link['description'].'</td>';
		echo '<td>'.$link['is_enable'].'</td>';
		if ($link['is_enable']) {
			$actionsLink = 'disable';
		} else {
			$actionsLink = 'enable';
		}
		echo '<td>'._('to be implemented').'</td>';
		echo '</tr>';
	}
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	echo '</br>';
}

echo '<form method="POST" name="addLink" action="index.php?type=globaladmin&action=addLink">';
echo '<table><tr>';
echo $HTML->boxTop(_('Add a new link'));
echo '<td>'._('URL').'</td><td><input name="link" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Displayed Name').'</td><td><input name="name" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Description').'</td><td><input name="description" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo $HTML->boxBottom();
echo '</tr></table>';
echo '</form>';
?>
