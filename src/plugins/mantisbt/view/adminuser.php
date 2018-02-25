<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2016, Franck Villaume - TrivialDev
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
global $userMantisBTConf;

foreach ($userMantisBTConf as $userConf) {
	echo $HTML->boxTop(_('Manage your account for the URL')._(': ').$userConf['url']);
	echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type=user&action=updateuserConf'));
	echo $HTML->listTableTop();
	$cells = array();
	$cells[] = array(_('MantisBT User').utils_requiredField()._(':'), 'class' => 'align-right');
	$cells[][] = html_e('input', array('type' => 'text', 'title' => _('Specify your MantisBT user.'), 'size' => 50, 'maxlength' => 255, 'name' => 'mantisbt_user', 'value' => $userConf['user'], 'required' => 'required'));
	$cells[] = array(_('MantisBT Password').utils_requiredField()._(':'), 'class' => 'align-right');
	$cells[][] = html_e('input', array('type' => 'password', 'title' => _('Specify the password of this MantisBT user.'), 'size' => 50, 'maxlength' => 255, 'name' => 'mantisbt_password', 'value' => $userConf['password'], 'required' => 'required'));
	$cells[][] = html_e('input', array('type' => 'hidden', 'name' => 'mantisbt_url', 'value' => $userConf['url'])).
			html_e('input', array('type' => 'submit', 'value' => _('Update'))).
			html_e('input', array('type' => 'button', 'value' => _('Delete'), 'onclick' => 'location.href=\''.util_make_uri('/plugins/'.$mantisbt->name.'/?type=user&action=deleteuserConf&mantisbt_url='.urlencode($userConf['url'])).'\''));
	echo $HTML->multiTableRow(array(), $cells);
	echo $HTML->listTableBottom();
	echo $HTML->closeForm();
	echo $HTML->boxBottom();
}
echo $HTML->addRequiredFieldsInfoBox();
