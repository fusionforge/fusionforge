<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011-2014,2016, Franck Villaume - TrivialDev
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
global $mantisbt;
global $mantisbtConf;
global $use_tooltips;
global $group_id;

?>
<script type="text/javascript">
var controller;

jQuery(document).ready(function() {
	controllerMantisBTAdminViewControler = new MantisBTAdminViewController({
		checkboxGlobalConf:	jQuery('#mantisbtglobalconf'),
		inputUrl:		jQuery('#mantisbturl'),
		inputUser:		jQuery('#mantisbtuser'),
		inputPassword:		jQuery('#mantisbtpassword'),
	});
});

</script>
<?php

echo $HTML->boxTop(_('Manage configuration'));
echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&action=updateConf'));
echo '<table>';
echo '<tr><td><label id="mantisbtinit-global" title="'._('Use the global configuration defined at forge level').'"';
echo ' >'._('Use global configuration').'</label></td><td><input id="mantisbtglobalconf" type="checkbox" name="global_conf" value="1" ';
if ($mantisbtConf['use_global']) {
	echo 'checked="checked" ';
}
echo '/></td></tr>';
echo '<tr><td><label id="mantisbtinit-url" title="'._('Specify the Full URL of the MantisBT Web Server.').'"';
echo ' >URL</label></td><td><input id="mantisbturl" type="url" size="50" maxlength="255" name="url" value="'.$mantisbtConf['url'].'" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-user" title="'._('Specify the user with admin right to be used thru SOAP API.').'"';
echo ' >SOAP User</label></td><td><input id="mantisbtuser" type="text" size="50" maxlength="255" name="soap_user" value="'.$mantisbtConf['soap_user'].'" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-password" title="'._('Specify the password of this user.').'"';
echo ' >SOAP Password</label></td><td><input id="mantisbtpassword" type="password" size="50" maxlength="255" name="soap_password" value="'.$mantisbtConf['soap_password'].'" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-syncroles" title="'._('Do you want to sync FusionForge -> MantisBT roles ? Not implemented yet.').'"';
echo ' >Sync Roles</label></td><td><input disabled="disabled" type="checkbox" name="sync_roles" /></td></tr>';
echo '</table>';
echo '<input type="submit" value="'._('Update').'" />';
echo $HTML->closeForm();
echo $HTML->boxBottom();
