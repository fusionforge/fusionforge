<?php
/**
 * MantisBT Plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
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
global $type;

$defect = array();

$defect['category'] = getStringFromRequest('categorie');
$defect['project']['id'] = $mantisbtConf['id_mantisbt'];

try {
	$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
	$listSeverities = $clientSOAP->__soapCall('mc_enum_severities', array("username" => $username, "password" => $password));
	$listReproducibilities = $clientSOAP->__soapCall('mc_enum_reproducibilities', array("username" => $username, "password" => $password));
	$listUsers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt'], "acces" => 10));
	$listViewStates = $clientSOAP->__soapCall('mc_enum_view_states', array("username" => $username, "password" => $password));
	$listPriorities = $clientSOAP->__soapCall('mc_enum_priorities', array("username" => $username, "password" => $password));
	$listResolutions= $clientSOAP->__soapCall('mc_enum_resolutions', array("username" => $username, "password" => $password));
	$listStatus= $clientSOAP->__soapCall('mc_enum_status', array("username" => $username, "password" => $password));
} catch (SoapFault $soapFault) {
	$error_msg = _('Task failed')._(': ').$soapFault->faultstring;
	session_redirect('plugins/mantisbt/?type='.$type.'&group_id='.$group_id.'&pluginname=mantisbt&view=viewIssues&error_msg='.urlencode($feedback));
}
foreach($listSeverities as $key => $severity){
	if (getStringFromRequest('severite') == $severity->name){
		$defect['severity']['id'] = $severity->id;
		$defect['severity']['name'] = $severity->name;
		break;
	}
}

foreach($listReproducibilities as $key => $reproducibility){
	if (getStringFromRequest('reproductibilite') == $reproducibility->name){
		$defect['reproducibility']['id'] = $reproducibility->id;
		$defect['reproducibility']['name'] = $reproducibility->name;
		break;
	}
}

foreach($listUsers as $key => $mantisuser){
	if ($username == $mantisuser->name){
		$defect['reporter']['id'] = $mantisuser->id;
		$defect['reporter']['name'] = $mantisuser->name;
		$defect['reporter']['email'] = $mantisuser->email;
		break;
	}
}

foreach($listViewStates as $key => $viewState){
	if ($viewState->id ==  10){
		$defect['view_state']['id'] = $viewState->id;
		$defect['view_state']['name'] = $viewState->name;
		break;
	}
}

if (getStringFromRequest('handler') != ''){
	$listUsers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis, "acces" => 10));
	foreach($listUsers as $key => $mantisuser){
		if (getStringFromRequest('handler') == $mantisuser->name){
			$defect['handler']['id'] = $mantisuser->id;
			$defect['handler']['name'] = $mantisuser->name;
			$defect['handler']['email'] = $mantisuser->email;
			break;
		}
	}
}

foreach($listPriorities as $key => $priority){
	if (getStringFromRequest('priorite') == $priority->name){
		$defect['priority']['id'] = $priority->id;
		$defect['priority']['name'] = $priority->name;
		break;
	}
}

foreach($listResolutions as $key => $resolution){
	if ($resolution->id == 10){
		$defect['resolution']['id'] = $resolution->id;
		$defect['resolution']['name'] = $resolution->name;
		break;
	}
}

foreach($listStatus as $key => $status){
	if ($status->id == 10){ // status nouveau
		$defect['status']['id'] = $status->id;
		$defect['status']['name'] = $status->name;
		break;
	}
}

$defect['description'] = getStringFromRequest('description');
$defect['summary'] = getStringFromRequest('resume');
$defect['additional_information'] = getStringFromRequest('informations');
$defect['version'] = getStringFromRequest('version');

try {
	$newIdBug = $clientSOAP->__soapCall('mc_issue_add', array("username" => $username, "password" => $password, "issue" => $defect));
	$feedback = _('Ticket '.$newIdBug.' created successfully.');
	session_redirect('plugins/mantisbt/?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$newIdBug.'&view=viewIssue&feedback='.urlencode($feedback));
} catch (SoapFault $soapFault) {
	$error_msg = _('Task failed')._(': ').$soapFault->faultstring;
	session_redirect('plugins/mantisbt/?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&error_msg='.urlencode($error_msg));
}
