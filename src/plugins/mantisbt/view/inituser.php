<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011,2014,2016, Franck Villaume - TrivialDev
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
global $validProjects;

$urlToSetup = getStringFromRequest('urlsetup');
$uniqueMantisBTUrls = array();

if (!strlen($urlToSetup)) {
	foreach ($validProjects as $validProject) {
		$localConf = $mantisbt->getMantisBTConf($validProject->getID());
		$uniqueMantisBTUrls[] = $localConf['url'];
	}
	$uniqueMantisBTUrls = array_unique($uniqueMantisBTUrls);
} else {
	$uniqueMantisBTUrls[] = $urlToSetup;
}

echo html_e('p', array(), _('You need to setup your mantisbt account per URL.'));

foreach ($uniqueMantisBTUrls as $uniqueMantisBTUrl) {
	echo $HTML->boxTop(_('User configuration for URL')._(': ').$uniqueMantisBTUrl);
	echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type='.$type.'&action=inituser'));
	echo $HTML->listTableTop();
	$cells[] = array(_('MantisBT User').utils_requiredField()._(':'), 'class' => 'align-right');
	$cells[][] = html_e('input', array('title' => _('Specify your MantisBT user to be used. This user MUST already exists.'), 'id' => 'mantisbt_user', 'type' => 'text', 'size' => 50, 'maxlength' => 255, 'name' => 'mantisbt_user', 'required' => 'required'));
	$cells[] = array(_('MantisBT Password').utils_requiredField()._(':'), 'class' => 'align-right');
	$cells[][] = html_e('input', array('title' => _('Specify the password of your user.'), 'id' => 'mantisbt_password', 'type' => 'password', 'size' => 50, 'maxlength' => 255, 'name' => 'mantisbt_password', 'required' => 'required'));
	$cells[][] = html_e('input', array('type' => 'hidden', 'name' => 'mantisbt_url', 'value' => $uniqueMantisBTUrl)).
			html_e('input', array('type' => 'submit', 'value' => _('Initialize')));
	echo $HTML->multiTableRow(array(), $cells);
	echo $HTML->listTableBottom();
	echo $HTML->closeForm();
	echo $HTML->boxBottom();
}
echo $HTML->addRequiredFieldsInfoBox();
