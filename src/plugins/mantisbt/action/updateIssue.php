<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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
global $idBug;
global $type;
global $user_id;

$redirect_url = 'plugins/mantisbt/?type='.$type;
switch ($type) {
	case "group": {
		$redirect_url .= '&group_id='.$group_id;
		break;
	}
	case "type": {
		$redirect_url .= '&user_id='.$user_id;
		break;
	}
	default: {
		$error_msg = _('No type found');
		session_redirect('plugins/mantisbt/&error_msg='.urlencode($error_msg));
	}
}

$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
if ($defect->category != $_POST['categorie']) {
	$defect->category = $_POST['categorie'];
}

if ($defect->severity->name != $_POST['severite']) {
	$listSeverities = $clientSOAP->__soapCall('mc_enum_severities', array("username" => $username, "password" => $password));
	foreach($listSeverities as $key => $severity) {
		if ($_POST['severite'] == $severity->name) {
			$defect->severity->id = $severity->id;
			$defect->severity->name = $severity->name;
			break;
		}
	}
}

if ($defect->reproducibility->name != $_POST['reproductibilite']) {
	$listReproducibilities = $clientSOAP->__soapCall('mc_enum_reproducibilities', array("username" => $username, "password" => $password));
	foreach($listReproducibilities as $key => $reproducibility) {
		if ($_POST['reproductibilite'] == $reproducibility->name) {
			$defect->reproducibility->id = $reproducibility->id;
			$defect->reproducibility->name = $reproducibility->name;
			break;
		}
	}
}

if ($defect->reporter->name != $_POST['reporter']) {
	$listUsers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $defect->project->id, "acces" => 10));
	foreach($listUsers as $key => $usermantis) {
		if ($_POST['reporter'] == $usermantis->name) {
			$defect->reporter->id = $usermantis->id;
			$defect->reporter->name = $usermantis->name;
			$defect->reporter->real_name = $usermantis->real_name;
			$defect->reporter->email = $usermantis->email;
			break;
		}
	}
}

if ($defect->view_state->name != $_POST['viewstate']) {
	$listViewStates = $clientSOAP->__soapCall('mc_enum_view_states', array("username" => $username, "password" => $password));
	foreach($listViewStates as $key => $viewState) {
		if ($_POST['viewstate'] == $viewState->name) {
			$defect->view_state->id = $viewState->id;
			$defect->view_state->name = $viewState->name;
			break;
		}
	}
}

if ($defect->handler->name != $_POST['handler']) {
	if ($_POST['handler'] != ""){
		$listUsers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $defect->project->id, "acces" => 10));
		foreach($listUsers as $key => $usermantis) {
			if ($_POST['handler'] == $usermantis->name) {
				$defect->handler->id = $usermantis->id;
				$defect->handler->name = $usermantis->name;
				$defect->handler->real_name = $usermantis->real_name;
				$defect->handler->email = $usermantis->email;
				break;
			}
		}
	}else{
		$defect->handler = null;
	}
}

if ($defect->priority->name != $_POST['priorite']) {
	$listPriorities = $clientSOAP->__soapCall('mc_enum_priorities', array("username" => $username, "password" => $password));
	foreach($listPriorities as $key => $priority) {
		if ($_POST['priorite'] == $priority->name) {
			$defect->priority->id = $priority->id;
			$defect->priority->name = $priority->name;
			break;
		}
	}
}

if ($defect->resolution->name != $_POST['resolution']) {
	$listResolutions= $clientSOAP->__soapCall('mc_enum_resolutions', array("username" => $username, "password" => $password));
	foreach($listResolutions as $key => $resolution) {
		if ($_POST['resolution'] == $resolution->name) {
			$defect->resolution->id = $resolution->id;
			$defect->resolution->name = $resolution->name;
			break;
		}
	}
}

if ($defect->status->name != $_POST['etat']) {
	$listStatus= $clientSOAP->__soapCall('mc_enum_status', array("username" => $username, "password" => $password));
	foreach($listStatus as $key => $status) {
		if ($_POST['etat'] == $status->name) {
			$defect->status->id = $status->id;
			$defect->status->name = $status->name;
			break;
		}
	}
}

if ($defect->description != $_POST['description']) {
	$defect->description = $_POST['description'];
}

if ($defect->additional_information != $_POST['informations']) {
	$defect->additional_information = $_POST['informations'];
}

if ($defect->summary != $_POST['resume']){
	$defect->summary = $_POST['resume'];
}

if ($defect->version != $_POST['version']) {
	$defect->version = $_POST['version'];
}

if ($defect->fixed_in_version != $_POST['fixed_in_version']) {
	$defect->fixed_in_version = $_POST['fixed_in_version'];
}

if ($defect->target_version != $_POST['target_version']) {
	$defect->target_version = $_POST['target_version'];
}

try {
	$clientSOAP->__soapCall('mc_issue_update', array("username" => $username, "password" => $password, "issue_id" => $idBug, "issue" => $defect));
} catch (SoapFault $soapFault) {
	$error_msg = _('Task failed:').' '.$soapFault->faultstring;
	session_redirect($redirect_url.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&error_msg='.urlencode($feedback));
}

$feedback = _('Task succeeded');
session_redirect($redirect_url.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&feedback='.urlencode($feedback));

?>