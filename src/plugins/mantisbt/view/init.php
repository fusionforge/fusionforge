<?php
/*
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

/* please do not add include here, use index.php to do so */
/* global variables */
global $type;
global $group_id;
global $mantisbt;
global $use_tooltips;

?>
	<script type="text/javascript" >
	function doMantisBTName() {
		if (jQuery('#mantisbtcreate').is(':checked')) {
			jQuery('#mantisbtname').attr('disabled',true);
		} else {
			jQuery('#mantisbtname').attr('disabled',false);
		}
	}
	</script>
<?

echo '<form method="POST" Action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=init" >';
echo '<table>';
echo '<tr><td><label title="'._('Specify the Full URL of the MantisBT Web Server.').'" >URL</label></td><td><input type="text" size="50" maxlength="255" name="url" /></td></tr>';
echo '<tr><td><label title="'._('Specify the user with admin right to be used thru SOAP API.').'">SOAP User</label></td><td><input type="text" size="50" maxlength="255" name="soap_user" /></td></tr>';
echo '<tr><td><label title="'._('Specify the password of this user.').'">SOAP Password</label></td><td><input type="text" size="50" maxlength="255" name="soap_password" /></td></tr>';
echo '<tr><td><label title="'._('If this project does NOT exist in MantisBT, do you want to create it ?').'" >Create the project in MantisBT</label></td><td><input id="mantisbtcreate" type="checkbox" name="mantisbtcreate" onclick="javascript:doMantisBTName()" value="1" ></td></tr>';
echo '<tr><td><label title="'._('Specify the name of the project in MantisBT if already created in MantisBT').'" >Name of the project in MantisBT</label></td><td><input id="mantisbtname" type="text" size="50" maxlength="255" name="mantisbtname" /></td></tr>';
echo '<tr><td><label title="'._('Do you want to sync FusionForge -> MantisBT users ?').'">Sync Users</label></td><td><input disabled="disabled" type="checkbox" name="sync_user" /></td></tr>';
echo '<tr><td><label title="'._('Do you want to sync FusionForge -> MantisBT roles ?').'">Sync Roles</label></td><td><input disabled="disabled" type="checkbox" name="sync_roles" /></td></tr>';
echo '</table>';
echo '<input type="submit" value="'._('Initialize').'" />';
echo '</form>';
?>
