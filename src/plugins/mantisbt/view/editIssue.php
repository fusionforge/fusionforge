<?php

/*
 * Copyright 2010, Capgemini
 * Author: Franck Villaume - Capgemini
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

// included the hability to use $HTML tool to create box
GLOBAL $HTML;

$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array("username" => $username, "password" => $password, "project_id" => $defect->project->id));
$listSeverities = $clientSOAP->__soapCall('mc_enum_severities', array("username" => $username, "password" => $password));
$listReproducibilities = $clientSOAP->__soapCall('mc_enum_reproducibilities', array("username" => $username, "password" => $password));
$listViewStates = $clientSOAP->__soapCall('mc_enum_view_states', array("username" => $username, "password" => $password));
$listReporters = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $defect->project->id, "acces" => 10));
$listDevelopers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $defect->project->id, "acces" => 25));
$listPriorities = $clientSOAP->__soapCall('mc_enum_priorities', array("username" => $username, "password" => $password));
$listResolutions= $clientSOAP->__soapCall('mc_enum_resolutions', array("username" => $username, "password" => $password));
$listStatus= $clientSOAP->__soapCall('mc_enum_status', array("username" => $username, "password" => $password));
$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $defect->project->id));
$listVersionsMilestone = $clientSOAP->__soapCall('mc_project_get_unreleased_versions', array("username" => $username, "password" => $password, "project_id" => $defect->project->id));

$boxTitle = 'Edition Ticket : '.sprintf($format,$defect->id);
echo $HTML->boxTop($boxTitle,InTextBorder);
echo 	'<form Method="POST" Action="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&action=updateIssue">';
echo	'<table class="innertabs">';
echo		'<tr>';
echo 			'<td width="20%" class="FullBoxTitle">Catégorie</td>';
echo 			'<td width="20%" class="FullBoxTitle">Sévérité</td>';
echo 			'<td width="20%" class="FullBoxTitle">Reproductibilité</td>';
echo 			'<td width="20%" class="FullBoxTitle">Date de soumission</td>';
echo 			'<td width="20%" class="FullBoxTitle">Date mise à jour</td>';
echo		'</tr>';
echo		'<tr>';
echo 			'<td class="FullBox">';
echo				'<select name="categorie" class="sirhen" >';
foreach ($listCategories as $key => $category){
	if($defect->category == $category){
		echo			"<option selected>".$category."</option>";
	}else{
		echo			"<option>".$category."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo 			'<td class="FullBox">';
echo				'<select name="severite" class="sirhen">';
foreach ($listSeverities as $key => $severity){
	if($defect->severity->id == $severity->id){
		echo			"<option selected>".$severity->name."</option>";
	}else{
		echo			"<option>".$severity->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo 			'<td class="FullBox">';
echo				'<select name="reproductibilite" class="sirhen">';
foreach ($listReproducibilities as $key => $reproducibility){
	if($defect->reproducibility->id == $reproducibility->id){
		echo			"<option selected>".$reproducibility->name."</option>";
	}else{
		echo			"<option>".$reproducibility->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
// TODO a revoir le problème des dates
date_default_timezone_set("UTC");
echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->date_submitted)).'</td>';
echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->last_updated)).'</td>';
echo		'</tr>';
echo		'<tr>';
echo 			'<td class="FullBoxTitle">Rapporteur</td>';
echo 			'<td class="FullBoxTitle">Assigné à</td>';
echo 			'<td class="FullBoxTitle">Priorité</td>';
echo 			'<td class="FullBoxTitle">Résolution</td>';
echo 			'<td class="FullBoxTitle">Etat</td>';
echo		'</tr>';
echo		'<tr>';
echo 			'<td class="FullBox">';
echo				'<select name="reporter" class="sirhen">';
foreach ($listReporters as $key => $user){
	if($defect->reporter->id == $user->id){
		echo			"<option selected>".$user->name."</option>";
	}else{
		echo			"<option>".$user->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo 			'<td class="FullBox">';
echo				'<select name="handler" class="sirhen">';
echo					"<option></option>";
foreach ($listDevelopers as $key => $user){
	if($defect->handler->id == $user->id){
		echo			"<option selected>".$user->name."</option>";
	}else{
		echo			"<option>".$user->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo 			'<td class="FullBox">';
echo				'<select name="priorite" class="sirhen">';
foreach ($listPriorities as $key => $priority){
	if($defect->priority->id == $priority->id){
		echo			"<option selected>".$priority->name."</option>";
	}else{
		echo			"<option>".$priority->name."</option>";
	}
}
echo				'</select>';
echo 			'</td>';
echo 			'<td class="FullBox">';
echo				'<select name="resolution" class="sirhen">';
foreach ($listResolutions as $key => $resolution){
	if($defect->resolution->id == $resolution->id){
		echo			"<option selected>".$resolution->name."</option>";
	}else{
		echo			"<option>".$resolution->name."</option>";
	}
}
echo				'</select>';
echo 			'</td>';
echo 			'<td class="FullBox">';
echo				'<select name="etat" class="sirhen">';
foreach ($listStatus as $key => $status){
	if($defect->status->id == $status->id){
		echo			"<option selected>".$status->name."</option>";
	}else{
		echo			"<option>".$status->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo		'</tr>';
echo		'<tr>';
echo 			'<td class="FullBoxTitle">Detecté en</td>';
echo 			'<td class="FullBoxTitle">Corrigé en</td>';
echo 			'<td colspan="3" class="FullBoxTitle">Milestone</td>';
echo		'</tr>';
echo 			'<td class="FullBox">';
echo				'<select name="version" class="sirhen">';
echo					"<option></option>";
foreach ($listVersions as $key => $version){
	if($defect->version == $version->name){
		echo			"<option selected>".$version->name."</option>";
	}else{
		echo			"<option>".$version->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo 			'<td class="FullBox">';
echo				'<select name="fixed_in_version" class="sirhen">';
echo					"<option></option>";
foreach ($listVersions as $key => $fixed_version){
	if($defect->fixed_in_version == $fixed_version->name){
		echo			"<option selected>".$fixed_version->name."</option>";
	}else{
		echo			"<option>".$fixed_version->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo 			'<td colspan="3" class="FullBox">';
echo				'<select name="target_version" class="sirhen">';
echo					"<option></option>";
foreach ($listVersionsMilestone as $key => $target_version){
	if($defect->target_version == $target_version->name){
		echo			"<option selected>".$target_version->name."</option>";
	}else{
		echo			"<option>".$target_version->name."</option>";
	}
}
echo				'</select>';
echo			'</td>';
echo		'<tr>';
echo	'</table>';
echo	'<br/>';
echo	'<table class="innertabs">';
echo		'<tr>';
echo 			'<td width="20%" class="FullBoxTitle">Résumé <span style="font-weight:normal">(128 caractères max)</span></td>';
echo			'<td class="FullBox"><input type="text" value="'.$defect->summary.'" name="resume" MAXLENGTH="128" style="width:99%"></td>';
echo		'</tr>';
echo		'<tr>';
echo 			'<td width="20%" class="FullBoxTitle">Description</td>';
echo			'<td class="FullBox"><textarea name="description" style="width:99%;" rows=12>'.$defect->description.'</textarea></td>';
echo		'</tr>';
echo		'<tr>';
echo 			'<td width="20%" class="FullBoxTitle">Informations complémentaires</td>';
echo			'<td class="FullBox"><textarea name="informations" style="width:99%;" rows=12>'.$defect->additional_information.'</textarea></td>';
echo		'</tr>';
echo	'</table>';
echo	'<br/>';
echo 	'<div align="center">';
echo 		'<input type=submit value="Mettre à jour l\'information">';
echo 		'<input type="button" name="Annuler" value="Annuler" onclick="window.location.href=\'?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'\'">';
echo 	'</div>';
echo 	'</form>';
echo $HTML->boxBottom();
?>
