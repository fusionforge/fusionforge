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

global $HTML;
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;
global $group;

$idVersion=getIntFromRequest('idVersion');

try {
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));

	$arrVersions = $clientSOAP->__soapCall('mc_project_get_versions', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt']));
} catch (SoapFault $soapFault) {
	echo $HTML->warning_msg(_('Technical error occurs during data retrieving')._(': ').$soapFault->faultstring);
	$errorPage = true;
}

// select the right version until the soap call is ported.
$detailVersion = array();
foreach ($arrVersions as $key => $currentVersion) {
	if ($currentVersion->id == $idVersion) {
		$detailVersion = $currentVersion;
	}
}

if (!isset($errorPage)){
	echo $HTML->boxTop(_('Version Detail'));
	echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&action=updateVersion'));
	$titleArr = array(_('Version'), _('Description'), _('Target Date'), _('Type'));
	echo $HTML->listTableTop($titleArr);
	echo	'<tr>';
	echo		'<td><input type="text" name="version_name" value="'.htmlspecialchars($detailVersion->name,ENT_QUOTES).'" /></td>';
	(isset($detailVersion->description)) ? $description_value = htmlspecialchars($detailVersion->description,ENT_QUOTES) : $description_value = '';
	echo		'<td><input type="text" name="version_description" value="'.$description_value.'" /></td>';
	echo		'<td><input type="text" name="version_date_order" size="32" value="'.strftime("%d/%m/%Y",strtotime($detailVersion->date_order)).'" />(format : DD/MM/YYYY)</td>';
	echo		'<td>';
	echo			'<select name="version_release">';
	if ($detailVersion->released) {
		echo		'<option value="1" selected>'._('Release').'</option>';
		echo		'<option value="0" >'._('Milestone').'</option>';
	} else {
		echo		'<option value="1" >'._('Release').'</option>';
		echo		'<option value="0" selected>'._('Milestone').'</option>';
	}
	echo			'</select>';
	echo		'</td>';
	echo	'</tr>';
	echo $HTML->listTableBottom();
// need to be implemented
// 	if ($group->usesPlugin('projects-hierarchy')) {
// 		echo '<input type="checkbox" name="transverse" value="1">'._('Cross version (son included)').'</input>';
// 	}
	echo '<input type="hidden" name="version_id" value="'.$idVersion.'" />';
	echo '<input type="hidden" name="version_old_name" value="'.$detailVersion->name.'" />';
	echo '<br/>';
	echo '<input type="submit" value="'. _('Update') .'" />';
	echo $HTML->closeForm();
	echo $HTML->boxBottom();

	echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&action=deleteVersion'));
	echo '<input type="hidden" name="deleteVersion" value="'.$idVersion.'" />';
	echo '<input type="submit" value="'. _('Delete') .'" />';
	echo $HTML->closeForm();
}
