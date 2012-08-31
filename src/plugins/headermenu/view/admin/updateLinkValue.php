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
$linkId = getIntFromRequest('linkid');

$linkValues = $headermenu->getLink($linkId);
if (is_array($linkValues)) {
	echo '<form method="POST" name="updateLink" action="index.php?type=globaladmin&action=updateLinkValue">';
	echo '<table><tr>';
	echo $HTML->boxTop(_('Update this link'));
	echo '<td>'._('URL').'</td><td><input name="link" type="text" maxsize="255" value="'.$linkValues['url'].'" /></td>';
	echo '</tr><tr>';
	echo '<td>'._('Displayed Name').'</td><td><input name="name" type="text" maxsize="255" value="'.$linkValues['name'].'" /></td>';
	echo '</tr><tr>';
	echo '<td>'._('Description').'</td><td><input name="description" type="text" maxsize="255" value="'.$linkValues['description'].'" /></td>';
	echo '</tr><tr>';
	echo '<td>';
	echo '<input type="hidden" name="linkid" value="'.$linkId.'" />';
	echo '<input type="submit" value="'. _('Update') .'" />';
	echo '<a href="/plugins/'.$headermenu->name.'/?type=globaladmin"><input type="button" value="'. _('Cancel') .'" /></a>';
	echo '</td>';
	echo $HTML->boxBottom();
	echo '</tr></table>';
	echo '</form>';
} else {
	$error_msg = _('Cannot retrieve value for this link:').' '.$linkId;
	session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&error_msg='.urlencode($error_msg));
}

?>
