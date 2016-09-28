<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012,2014,2016, Franck Villaume - TrivialDev
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

/* add a new version */

global $HTML;
global $group;
global $group_id;
global $mantisbt;
echo $HTML->boxTop(_('Add a new version'));
echo $HTML->openForm(array('method' => 'post', 'name' => 'addVersion', 'action' => '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&action=addVersion'));
echo $HTML->listTableTop();
$cells = array();
$cells[] = array(_('Name').utils_requiredField()._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('name' => 'version', 'type' => 'text', 'size' => 10, 'required' => 'required'));
$cells[] = array(_('Description')._(':'), 'class' => 'align-right');
$cells[][] = html_e('input', array('name' => 'description', 'type' => 'text', 'size' => 20));
$cells[][] = html_e('input', array('type' => 'submit', 'value' => _('Add')));
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
echo $HTML->boxBottom();
