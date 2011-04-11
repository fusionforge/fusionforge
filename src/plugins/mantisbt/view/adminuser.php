<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
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

/* view version of a dedicated group in MantisBt */

/* main display */
global $HTML;
global $mantisbt;
global $mantisbtConf;

echo $HTML->boxTop(_('Manage your account'));
echo '<form method="POST" Action="?type=user&pluginname='.$mantisbt->name.'&action=updateuserConf">';
echo '<table>';
echo '<tr><td><label id="mantisbtinit-user" ';
if ($use_tooltips)
	echo 'title="'._('Specify your mantisbt user.').'"';
echo ' >MantisBT User</label></td><td><input type="text" size="50" maxlength="255" name="mantisbt_user" value="'.$mantisbtConf['user'].'" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-password" ';
if ($use_tooltips)
	echo 'title="'._('Specify the password of this user.').'"';
echo ' >MantisBT Password</label></td><td><input type="text" size="50" maxlength="255" name="mantisbt_password" value="'.$mantisbtConf['password'].'" /></td></tr>';
echo '</table>';
echo '<input type="submit" value="'._('Update').'" />';
echo '</form>';
echo $HTML->boxBottom();
?>
