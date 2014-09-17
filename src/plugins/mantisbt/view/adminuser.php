<?php
/**
 * MantisBT plugin
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

/* view version of a dedicated group in MantisBt */

/* main display */
global $HTML;
global $mantisbt;
global $mantisbtConf;

echo $HTML->boxTop(_('Manage your account'));
echo $HTML->openForm(array('method' => 'post', 'action' => util_make_uri('/plugins/'.$mantisbt->name.'/?type=user&action=updateuserConf')));
echo '<table>';
echo '<tr><td><label id="mantisbtinit-user" title="'._('Specify your mantisbt user.').'" >'._('MantisBT User').'</label></td><td><input type="text" size="50" maxlength="255" name="mantisbt_user" value="'.$mantisbtConf['user'].'" required="required" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-password" title="'._('Specify the password of this user.').'" >'._('MantisBT Password.').'</label></td><td><input type="password" size="50" maxlength="255" name="mantisbt_password" value="'.$mantisbtConf['password'].'" required="required" /></td></tr>';
echo '</table>';
echo '<input type="submit" value="'._('Update').'" />';
echo $HTML->closeForm();
echo $HTML->boxBottom();
