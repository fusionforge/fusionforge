<?php
/**
 * MantisBT Plugin
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
global $idBug;
global $type;
global $HTML;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP)) {
		$clientSOAP = new SoapClient($mantisbtConf['url'].'/api/soap/mantisconnect.php?wsdl', array('trace' => true, 'exceptions' => true));
	}
	$defect = $clientSOAP->__soapCall('mc_issue_get', array('username' => $username, 'password' => $password, 'issue_id' => $idBug));
	$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array('username' => $username, 'password' => $password, 'project_id' => $defect->project->id));
	$listSeverities = $clientSOAP->__soapCall('mc_enum_severities', array('username' => $username, 'password' => $password));
	$listReproducibilities = $clientSOAP->__soapCall('mc_enum_reproducibilities', array('username' => $username, 'password' => $password));
	$listReporters = $clientSOAP->__soapCall('mc_project_get_users', array('username' => $username, 'password' => $password, 'project_id' => $defect->project->id, 'acces' => 10));
	$listDevelopers = $clientSOAP->__soapCall('mc_project_get_users', array('username' => $username, 'password' => $password, 'project_id' => $defect->project->id, 'acces' => 25));
	$listPriorities = $clientSOAP->__soapCall('mc_enum_priorities', array('username' => $username, 'password' => $password));
	$listResolutions= $clientSOAP->__soapCall('mc_enum_resolutions', array('username' => $username, 'password' => $password));
	$listStatus= $clientSOAP->__soapCall('mc_enum_status', array('username' => $username, 'password' => $password));
	$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array('username' => $username, 'password' => $password, 'project_id' => $defect->project->id));
	$listVersionsMilestone = $clientSOAP->__soapCall('mc_project_get_unreleased_versions', array('username' => $username, 'password' => $password, 'project_id' => $defect->project->id));
} catch (SoapFault $soapFault) {
	echo $HTML->warning_msg(_('Technical error occurs during data retrieving')._(': ').$soapFault->faultstring);
	$errorPage = true;
}

if (!isset($errorPage)){
	global $additional_value; //retrieve from viewIssue.php
	global $category_value;
	global $severity_value;
	global $reproducibility_value;
	global $reporter_value;
	global $handler_value;
	global $priority_value;
	global $resolution_value;
	global $status_value;
	global $version_value;
	global $fixed_value;
	global $target_value;
	global $additional_value;

	$boxTitle = _('Edit ticket')._(': ').sprintf($format,$defect->id);
	echo $HTML->openForm(array('name' => 'issue', 'method' => 'post', 'action' => 'plugins/'.$mantisbt->name.'/?type='.$type.'&group_id='.$group_id.'&idBug='.$defect->id.'&action=updateIssue&view=viewIssue'));
	echo $HTML->listTableTop();
	echo		'<tr>';
	echo 			'<td width="20%">'._('Category').'</td>';
	echo 			'<td width="20%">'._('Severity').'</td>';
	echo 			'<td width="20%">'._('Reproducibility').'</td>';
	echo 			'<td width="20%">'._('Submit Date').'</td>';
	echo 			'<td width="20%">'._('Update Date').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td>';
	echo				'<select name="categorie">';
	echo					'<option></option>';
	echo					'<option selected>'. $category_value .'</option>';
	foreach ($listCategories as $key => $category){
		echo			    '<option>'.$category.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="severite">';
		echo    			    '<option selected>'. $severity_value .'</option>';
	foreach ($listSeverities as $key => $severity){
		echo			    '<option>'.$severity->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="reproductibilite">';
	echo			        '<option selected>'. $reproducibility_value .'</option>';
	foreach ($listReproducibilities as $key => $reproducibility){
		echo			    '<option>'.$reproducibility->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	// TODO a revoir le problème des dates
	date_default_timezone_set("UTC");
	echo 			'<td>'.date("Y-m-d G:i",strtotime($defect->date_submitted)).'</td>';
	echo 			'<td>'.date("Y-m-d G:i",strtotime($defect->last_updated)).'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td>'._('Reporter').'</td>';
	echo 			'<td>'._('Assigned to').'</td>';
	echo 			'<td>'._('Priority').'</td>';
	echo 			'<td>'._('Resolution').'</td>';
	echo 			'<td>'._('Status').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td>';
	echo				'<select name="reporter">';
		echo			        '<option selected>'. $reporter_value.'</option>';
	foreach ($listReporters as $key => $user){
			echo			    '<option>'.$user->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="handler">';
	echo			        '<option selected>'. $handler_value .'</option>';
	echo					'<option></option>';
	foreach ($listDevelopers as $key => $user){
			echo			    '<option>'.$user->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="priorite">';
		echo			        '<option selected>'. $priority_value .'</option>';
	foreach ($listPriorities as $key => $priority){
		echo			    '<option>'.$priority->name.'</option>';
	}
	echo				'</select>';
	echo 			'</td>';
	echo 			'<td>';
	echo				'<select name="resolution">';
		echo			        '<option selected>'. $resolution_value .'</option>';
	foreach ($listResolutions as $key => $resolution){
		echo			    '<option>'.$resolution->name.'</option>';
	}
	echo				'</select>';
	echo 			'</td>';
	echo 			'<td>';
	echo				'<select name="etat">';
		echo			        '<option selected>'. $status_value .'</option>';
	foreach ($listStatus as $key => $status){
			echo			    '<option>'.$status->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td>'._('Found in').'</td>';
	echo 			'<td>'._('Fixed in').'</td>';
	echo 			'<td colspan="3">'._('Target').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td>';
	echo				'<select name="version">';
		echo			        '<option selected>'. $version_value .'</option>';
	echo					'<option></option>';
	foreach ($listVersions as $key => $version){
		echo			    '<option>'.$version->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td>';
	echo				'<select name="fixed_in_version">';
		echo			        '<option selected>'. $fixed_value .'</option>';
	echo					'<option></option>';
	foreach ($listVersions as $key => $fixed_version){
			echo			    '<option>'.$fixed_version->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td colspan="3">';
	echo				'<select name="target_version">';
		echo			        '<option selected>'. $target_value .'</option>';
	echo					'<option></option>';
	foreach ($listVersionsMilestone as $key => $target_version){
			echo			    '<option>'.$target_version->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo		'<tr>';
	echo $HTML->listTableBottom();
	echo	'<br/>';
	echo $HTML->listTableTop();
	echo		'<tr>';
	echo			'<td width="20%">'._('Summary').'&nbsp;<span style="font-weight:normal">'._('(128 char max)').'</span></td>';
	echo			'<td><input type="text" value="'.htmlspecialchars($defect->summary,ENT_QUOTES).'" name="resume" maxlength="128" style="width:99%" required="required" ></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo			'<td width="20%">'._('Description').'</td>';
	echo			'<td><textarea name="description" style="width:99%;" rows="6" required="required" >'.htmlspecialchars($defect->description, ENT_QUOTES).'</textarea></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo			'<td width="20%">'._('Additional Informations').'</td>';
	echo			'<td><textarea name="informations" style="width:99%;" rows="6">'. $additional_value .'</textarea></td>';
	echo		'</tr>';
	echo $HTML->listTableBottom();
	echo	'<br/>';
	echo	'<div align="center">';
	echo		'<input type="submit" name="submitbutton" value="'._('Update').'" />';
	echo	'</div>';
	echo $HTML->closeForm();
}
