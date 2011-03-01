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

/* please do not add include here, use index.php to do so */
/* global variables */
global $use_tooltips;
global $type;

?>

<script type="text/javascript">
var controller;

jQuery(document).ready(function() {
	controllerMantisBTInitUser = new MantisBTInitUserController({
		tipsyElements:		[
						{selector: '#mantisbtinit-user', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#mantisbtinit-password', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#mantisbtinit-create', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}},
					],
	});
});

</script>

<?

echo '<form method="POST" Action="?type='.$type.'&pluginname='.$mantisbt->name.'&action=inituser" >';
echo '<table>';
echo '<tr><td><label id="mantisbtinit-user" ';
if ($use_tooltips)
	echo 'title="'._('Specify your mantisbt user to be used.').'"';
echo ' >SOAP User</label></td><td><input type="text" size="50" maxlength="255" name="mantisbt_user" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-password" ';
if ($use_tooltips)
	echo 'title="'._('Specify the password of your user.').'"';
echo ' >SOAP Password</label></td><td><input type="text" size="50" maxlength="255" name="mantisbt_password" /></td></tr>';
echo '<tr><td><label id="mantisbtinit-create" ';
if ($use_tooltips)
	echo 'title="'._('If your user does NOT exist in MantisBT, do you want to create it ? NOT YET IMPLEMENTED').'"';
echo ' >Create the user in MantisBT</label></td><td><input id="mantisbtcreate" type="checkbox" name="mantisbtcreate" value="1" disabled="disabled" ></td></tr>';
echo '</table>';
echo '<input type="submit" value="'._('Initialize').'" />';
echo '</form>';
?>
