<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

try {
	$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
	$noteEdit;

	$note['text'] = $_POST['edit_texte_note'];

	$note['view_state']['id'] = 10;
	$listViewStates = $clientSOAP->__soapCall('mc_enum_view_states', array("username" => $username, "password" => $password));
	foreach($listViewStates as $state){
		if($state->id == 10){
			$note['view_state']['name'] = $state->name;
		}
	}

	$listUsers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $defect->project->id, "acces" => 10));
	foreach($listUsers as $key => $mantisuser){
		if ($username == $mantisuser->name){
			$note['reporter']['id'] = $mantisuser->id;
			$note['reporter']['name'] = $mantisuser->name;
			$note['reporter']['real_name'] = $mantisuser->real_name;
			$note['reporter']['email'] = $mantisuser->email;
			break;
		}
	}

	$clientSOAP->__soapCall('mc_issue_note_add', array("username" => $username, "password" => $password, "issue_id" => $idBug, "note" => $note));
	$feedback = _('Note added successfully');
} catch (SoapFault $soapFault) {
	$error_msg = _('Task failed:').' '.$soapFault->faultstring;
	session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&error_msg='.urlencode($error_msg));
}
session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&feedback='.urlencode($feedback));
?>
