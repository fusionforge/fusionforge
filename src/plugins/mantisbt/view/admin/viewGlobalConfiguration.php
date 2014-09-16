<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011-2014, Franck Villaume - TrivialDev
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

$mantisbtGlobalConf = $mantisbt->getGlobalconf();

echo $HTML->boxTop(_('Manage configuration'));
echo $HTML->openForm(array('method' => 'post', 'action' => util_make_uri('/plugins/'.$mantisbt->name.'/?type=globaladmin&action=updateGlobalConf')));
echo $HTML->listTableTop();
$cells = array();
$cells[][] = '<label id="mantisbtinit-url" >'._('URL').'</label>';
$cells[][] = '<input type="url" title="'._('Specify the Full URL of the MantisBT Web Server.').'" size="50" maxlength="255" name="url" value="'.$mantisbtGlobalConf['url'].'" />';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = '<label id="mantisbtinit-user" >'._('SOAP User').'</label>';
$cells[][] = '<input type="text" title="'._('Specify the user with admin right to be used thru SOAP API.').'" size="50" maxlength="255" name="soap_user" value="'.$mantisbtGlobalConf['soap_user'].'" />';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = '<label id="mantisbtinit-password" >'._('SOAP Password').'</label>';
$cells[][] = '<input type="password" title="'._('Specify the password of this user.').'" size="50" maxlength="255" name="soap_password" value="'.$mantisbtGlobalConf['soap_password'].'" />';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = '<label id="mantisbtinit-syncroles" >Sync Roles</label>';
$cells[][] = '<input disabled="disabled" type="checkbox" title="'._('Do you want to sync FusionForge -> MantisBT roles ? Not implemented yet.').'" name="sync_roles" />';
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo html_e('input', array('type' => 'submit', 'value' => _('Update')));
echo $HTML->closeForm();
echo $HTML->boxBottom();
