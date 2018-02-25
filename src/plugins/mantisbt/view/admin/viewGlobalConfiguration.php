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

$mantisbtGlobalConf = $mantisbt->getGlobalconf();

echo $HTML->boxTop(_('Manage configuration'));
echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type=globaladmin&action=updateGlobalConf'));
echo $HTML->listTableTop();
$cells = array();
$cells[] = array(_('URL').utils_requiredField()._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('type' => 'url', 'title' => _('Specify the Full URL of the MantisBT Web Server.'), 'size' => 50, 'maxlength' => 255, 'name' => 'url', 'value' => $mantisbtGlobalConf['url'], 'required' => 'required'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('SOAP User').utils_requiredField()._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('type' => 'text', 'title' => _('Specify the user with admin right to be used thru SOAP API.'), 'size' => 50, 'maxlength' => 255, 'name' => 'soap_user', 'value' => $mantisbtGlobalConf['soap_user'], 'required' => 'required'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('SOAP Password').utils_requiredField()._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('type' => 'password', 'title' => _('Specify the password of this user.'), 'size' => 50, 'maxlength' => 255, 'name' => 'soap_password', 'value' => $mantisbtGlobalConf['soap_password'], 'required' => 'required'));
echo $HTML->multiTableRow(array(), $cells);
// this need to be implemented using the real RBAC system.
// $cells = array();
// $cells[] = array(_('Sync Roles')._(':'), 'class' => 'align-right');
// $cells[][] = html_e('input', array('type' => 'checkbox', 'title' => _('Do you want to sync FusionForge -> MantisBT roles?'), 'name' => 'sync_roles'));
// echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo html_e('input', array('type' => 'submit', 'value' => _('Update')));
echo $HTML->closeForm();
echo $HTML->boxBottom();
echo $HTML->addRequiredFieldsInfoBox();
