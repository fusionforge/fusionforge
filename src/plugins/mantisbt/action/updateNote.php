<?php
/*
 * Copyright 2010, Franck Villaume - Capgemini
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

$noteEdit;
$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
foreach($defect->notes as $key => $note){
	if ($note->id == $idNote){
		$noteEdit = $note;
		break;
	}
}

if (isset($_POST['edit_texte_note'])){
	$noteEdit->text = $_POST['edit_texte_note'];
	try {
		$clientSOAP->__soapCall('mc_issue_note_update', array("username" => $username, "password" => $password, "issue_id" => $idBug, "issue_note_id" => $idNote, "note" => $noteEdit, "type_update" => "text"));
	} catch (SoapFault $soapFault) {
		$error_msg = _('Task failed:').' '.$soapFault->faultstring;
		session_redirect('plugins/mantisbt/?type=group&id='.$id.'&pluginname=mantisbt&idBug='.$idBug.'&view=viewIssue&error_msg='.urlencode($error_msg));
	}
} else {
	$listViewStates = $clientSOAP->__soapCall('mc_enum_view_states', array("username" => $username, "password" => $password));
	foreach($listViewStates as $state){
		if (($state->id == 50 && $actionNote == "private") || ($state->id == 10 && $actionNote == "public")){
			$noteEdit->view_state->name = $state->name;
			$noteEdit->view_state->id = $state->id;
			break;
		}
	}

	try {
		$clientSOAP->__soapCall('mc_issue_note_update', array("username" => $username, "password" => $password, "issue_id" => $idBug, "issue_note_id" => $idNote, "note" => $noteEdit, "type_update" => "state"));
	} catch (SoapFault $soapFault) {
		$error_msg = _('Task failed:').' '.$soapFault->faultstring;
		session_redirect('plugins/mantisbt/?type=group&id='.$id.'&pluginname=mantisbt&idBug='.$idBug.'&view=viewIssue&error_msg='.urlencode($error_msg));
	}
}

$feedback = _('Task succeeded');
session_redirect('plugins/mantisbt/?type=group&id='.$id.'&pluginname=mantisbt&idBug='.$idBug.'&view=viewIssue&feedback='.urlencode($feedback));

?>
