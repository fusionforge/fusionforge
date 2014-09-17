<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011,2014 Franck Villaume - TrivialDev
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
global $mantisbt;
global $HTML;

echo $HTML->openForm(array('method' => 'post', 'action' => util_make_uri('/plugins/'.$mantisbt->name.'/?type='.$type.'&action=inituser')));
echo $HTML->listTableTop();
// disabled until MantisBT support user creation thru SOAP API
// $cells = array();
// $cells[] = array(_('Create the user in MantisBT').utils_requiredField()._(':'), 'class' => 'align-right');
// $cells[][] = html_e('input', array('title' => _('If your user does NOT exist in MantisBT, do you want to create it ?'), 'type' => 'radio', 'name' => 'mantisbt_configtype', 'value' => 1));
// echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Link with already created user in MantisBT').utils_requiredField()._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('title' => _('If your user DOES exist in MantisBT, do you want to link with it ?'), 'type' => 'radio', 'name' => 'mantisbt_configtype', 'value' => 2));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('MantisBT User').utils_requiredField()._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('title' => _('Specify your mantisbt user to be used.'), 'id' => 'mantisbt_user', 'type' => 'text', 'size' => 50, 'maxlength' => 255, 'name' => 'mantisbt_user', 'required' => 'required'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Your Password').utils_requiredField()._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('title' => _('Specify the password of your user.'), 'id' => 'mantisbt_password', 'type' => 'password', 'size' => 50, 'maxlength' => 255, 'name' => 'mantisbt_password', 'required' => 'required'));
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo html_e('input', array('type' => 'submit', 'value' => _('Initialize')));
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
