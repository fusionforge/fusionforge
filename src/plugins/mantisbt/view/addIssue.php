<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;
global $HTML;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP)) {
		$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));
	}
	$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt']));
	$listSeverities = $clientSOAP->__soapCall('mc_enum_severities', array('username' => $username, 'password' => $password));
	$listReproducibilities = $clientSOAP->__soapCall('mc_enum_reproducibilities', array('username' => $username, 'password' => $password));
	$listViewStates = $clientSOAP->__soapCall('mc_enum_view_states', array('username' => $username, 'password' => $password));
	// acces = 25 is hardcoded value from MantisBT Role permissions...
	$listDevelopers = $clientSOAP->__soapCall('mc_project_get_users', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt'], 'acces' => 25));
	$listPriorities = $clientSOAP->__soapCall('mc_enum_priorities', array('username' => $username, 'password' => $password));
	$listResolutions= $clientSOAP->__soapCall('mc_enum_resolutions', array('username' => $username, 'password' => $password));
	$listStatus= $clientSOAP->__soapCall('mc_enum_status', array('username' => $username, 'password' => $password));
	$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array('username' => $username, 'password' => $password, 'project_id' => $mantisbtConf['id_mantisbt']));
} catch (SoapFault $soapFault) {
	echo $HTML->warning_msg(_('Technical error occurs during data retrieving')._(': ').$soapFault->faultstring);
	$errorPage = true;
}

if (!isset($errorPage)){
	echo $HTML->openForm(array('name' => 'issue', 'method' => 'post', 'action' => '/plugins/'.$mantisbt->name.'/?type='.$type.'&group_id='.$group_id.'&action=addIssue'));
	echo	'<table>';
	echo		'<tr>';
	echo			'<td width="16%">'._('Category').'</td>';
	echo			'<td width="16%">'._('Reproducibility').'</td>';
	echo			'<td width="16%">'._('Severity').'</td>';
	echo			'<td width="16%">'._('Priority').'</td>';
	echo			'<td width="16%">'._('Assigned to').'</td>';
	echo			'<td width="16%">'._('Found in').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo			'<td>';
	echo				'<select name="categorie">';
	foreach ($listCategories as $key => $category){
		echo				"<option>".$category."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="reproductibilite">';
	foreach ($listReproducibilities as $key => $reproducibility){
		echo				"<option>".$reproducibility->name."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="severite">';
	foreach ($listSeverities as $key => $severity){
		echo				"<option>".$severity->name."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="priorite">';
	foreach ($listPriorities as $key => $priority){
		echo				"<option>".$priority->name."</option>";
	}
	echo				'</select>';
	echo 			'</td>';
	echo 			'<td>';
	echo				'<select name="handler">';
	echo					"<option></option>";
	foreach ($listDevelopers as $key => $user){
		echo				"<option>".$user->name."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	if (sizeof($listVersions)) {
		echo		'<td>';
		echo			'<select name="version">';
		echo				"<option></option>";
		foreach ($listVersions as $key => $version){
			echo			"<option>".$version->name."</option>";
		}
		echo			'</select>';
		echo		'</td>';
	} else {
		echo		'<td>';
		echo			_('No version defined');
		echo		'</td>';
	}
	echo		'</tr>';
	echo	'</table>';
	echo	'<br/>';
	echo	'<table>';
	echo		'<tr>';
	echo 			'<td width="20%">'._('Summary').' * <span style="font-weight:normal">'._('(128 char max)').'</span></td>';
	echo			'<td><input type="text" name="resume" maxlength="128" style="width:99%;" required="required" /></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td>'._('Description').' *</td>';
	echo			'<td><textarea name="description" style="width:99%;" rows="12" required="required" ></textarea></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td>'._('Additional Informations').'</td>';
	echo			'<td><textarea name="informations" style="width:99%;" rows="12"></textarea></td>';
	echo		'</tr>';
	echo	'</table>';
	echo 	'<div align="center">';
	echo 		'<input type="submit" name="submitbutton" value="'._('Submit').'" />';
	echo 	'</div>';
	echo $HTML->closeForm();
	echo $HTML->addRequiredFieldsInfoBox();
}
