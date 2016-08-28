<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011-2012,2014,2016 Franck Villaume - TrivialDev
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

/* please do not add include here, use index.php to do so */
/* global variables */
global $type;
global $group_id;
global $mantisbt;
global $HTML;
?>

<script type="text/javascript">
var controller;

jQuery(document).ready(function() {
	controllerMantisBTInit = new MantisBTInitController({
		checkboxGlobalConf:	jQuery('#mantisbtglobalconf'),
		checkboxCreate:		jQuery('#mantisbtcreate'),
		inputName:		jQuery('#mantisbtname'),
		inputUrl:		jQuery('#mantisbturl'),
		inputUser:		jQuery('#mantisbtuser'),
		inputPassword:		jQuery('#mantisbtpassword'),
	});
});

</script>

<?php

echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type='.$type.'&group_id='.$group_id.'&action=init'));
echo $HTML->listTableTop();
$cells = array();
$cells[] = array(_('Use global configuration')._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('type' => 'checkbox', 'title' => _('Use the global configuration defined at forge level'), 'id' => 'mantisbtglobalconf', 'name' => 'global_conf', 'value' => 1));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('URL')._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('id' => 'mantisbturl', 'title' => _('Specify the Full URL of the MantisBT Web Server.'), 'type' => 'url', 'size' => 50, 'maxlength' => 255, 'name' => 'url'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('SOAP User')._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('id' => 'mantisbtuser', 'title' => _('Specify the user with admin right to be used thru SOAP API.'), 'type' => 'text', 'size' => 50, 'maxlength' => 255, 'name' => 'soap_user'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('SOAP Password')._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('id' => 'mantisbtpassword', 'title' => _('Specify the password of this SOAP User.'), 'type' => 'password', 'size' => 50, 'maxlength' => 255, 'name' => 'soap_password'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Create the project in MantisBT')._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('title' => _('If this project does NOT exist in MantisBT, do you want to create it ? The current project name will be used.'), 'id' => 'mantisbtcreate', 'type' => 'checkbox', 'name' => 'mantisbtcreate', 'value' => 1));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Link with an existing project in MantisBT')._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('title' => _('Specify the name of the project already created in MantisBT'), 'id' => 'mantisbtname', 'type' => 'text', 'size' => 50, 'maxlength' => 255, 'name' => 'mantisbtname'));
echo $HTML->multiTableRow(array(), $cells);
// echo '<tr><td><label id="mantisbtinit-syncroles" title="'._('Do you want to sync FusionForge -> MantisBT roles ?').'" >Sync Roles</label></td><td><input disabled="disabled" type="checkbox" name="sync_roles" /></td></tr>';
echo $HTML->listTableBottom();
echo html_e('input', array('type' => 'submit', 'value' => _('Initialize')));
echo $HTML->closeForm();
