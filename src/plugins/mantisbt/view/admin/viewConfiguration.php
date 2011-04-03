<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
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

global $HTML;
global $mantisbt;
global $mantisbtConf;
global $use_tooltips;

?>
<script type="text/javascript">
var controller;

jQuery(document).ready(function() {
	controllerMantisBTAdminViewControler = new MantisBTAdminViewController({
		tipsyElements:		[
						{selector: '#mantisbtinit-global', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#mantisbtinit-url', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#mantisbtinit-user', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#mantisbtinit-password', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#mantisbtinit-syncroles', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
					],
		checkboxGlobalConf:	jQuery('#mantisbtglobalconf'),
		inputUrl:		jQuery('#mantisbturl'),
		inputUser:		jQuery('#mantisbtuser'),
		inputPassword:		jQuery('#mantisbtpassword'),
	});
});

</script>
<?php

echo $HTML->boxTop(_('Manage configuration'));
echo '<form method="POST" Action="?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=updateConf">';
echo '<table>';
echo '<tr><td><label id="mantisbtinit-global" ';
if ($use_tooltips)
	echo 'title="'._('Use the global configuration defined at forge level').'"';
echo ' >'._('Use global configuration').'</label></td><td><input id="mantisbtglobalconf" type="checkbox" name="global_conf" ';
if ($mantisbtConf['use_global']) {
	echo 'checked="checked" ';
}
echo '/></td></tr>';
echo '<tr><td><label id="mantisbtinit-url" ';
if ($use_tooltips)
	echo 'title="'._('Specify the Full URL of the MantisBT Web Server.').'"';
echo ' >URL</label></td><td><input id="mantisbturl" type="text" size="50" maxlength="255" name="url" value="'.$mantisbtConf['url'].'" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-user" ';
if ($use_tooltips)
	echo 'title="'._('Specify the user with admin right to be used thru SOAP API.').'"';
echo ' >SOAP User</label></td><td><input id="mantisbtuser" type="text" size="50" maxlength="255" name="soap_user" value="'.$mantisbtConf['soap_user'].'" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-password" ';
if ($use_tooltips)
	echo 'title="'._('Specify the password of this user.').'"';
echo ' >SOAP Password</label></td><td><input id="mantisbtpassword" type="text" size="50" maxlength="255" name="soap_password" value="'.$mantisbtConf['soap_password'].'" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-syncroles" ';
if ($use_tooltips)
	echo 'title="'._('Do you want to sync FusionForge -> MantisBT roles ? Not implemented yet.').'"';
echo ' >Sync Roles</label></td><td><input disabled="disabled" type="checkbox" name="sync_roles" /></td></tr>';
echo '</table>';
echo '<input type="submit" value="'._('Update').'" />';
echo '</form>';
echo $HTML->boxBottom();
?>
