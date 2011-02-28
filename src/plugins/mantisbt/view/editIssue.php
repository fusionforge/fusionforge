<?php
/**
 * MantisBT Plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $idBug;
global $type;

//validate function : to be sure needed informations are set before submit
print   ('
	<script language="javacript" type="text/javascript">
	function validate() {
		if ( document.issue.resume.value.length == 0 ) {
		alert ("champ Résumé obligatoire");
		} else if ( document.issue.description.value.length == 0 ) {
		alert ("champ Description obligatoire");
		} else {
		document.issue.submit();
		document.issue.submitbutton.disabled="true";
		}
	}
	</script>
	');

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
	$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array("username" => $username, "password" => $password, "project_id" => $defect->project->id));
	$listSeverities = $clientSOAP->__soapCall('mc_enum_severities', array("username" => $username, "password" => $password));
	$listReproducibilities = $clientSOAP->__soapCall('mc_enum_reproducibilities', array("username" => $username, "password" => $password));
	$listReporters = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $defect->project->id, "acces" => 10));
	$listDevelopers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $defect->project->id, "acces" => 25));
	$listPriorities = $clientSOAP->__soapCall('mc_enum_priorities', array("username" => $username, "password" => $password));
	$listResolutions= $clientSOAP->__soapCall('mc_enum_resolutions', array("username" => $username, "password" => $password));
	$listStatus= $clientSOAP->__soapCall('mc_enum_status', array("username" => $username, "password" => $password));
	$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $defect->project->id));
	$listVersionsMilestone = $clientSOAP->__soapCall('mc_project_get_unreleased_versions', array("username" => $username, "password" => $password, "project_id" => $defect->project->id));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
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

	$boxTitle = _('Edit Ticket: ').sprintf($format,$defect->id);
	echo 	'<form name="issue" Method="POST" Action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$defect->id.'&action=updateIssue&view=viewIssue">';
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="20%" class="FullBoxTitle">'._('Category').'</td>';
	echo 			'<td width="20%" class="FullBoxTitle">'._('Severity').'</td>';
	echo 			'<td width="20%" class="FullBoxTitle">'._('Reproducibility').'</td>';
	echo 			'<td width="20%" class="FullBoxTitle">'._('Submit Date').'</td>';
	echo 			'<td width="20%" class="FullBoxTitle">'._('Update Date').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBox">';
	echo				'<select name="categorie" class="sirhen" >';
	echo					'<option></option>';
	echo					'<option selected>'. $category_value .'</option>';
	foreach ($listCategories as $key => $category){
		echo			    '<option>'.$category.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="severite" class="sirhen">';
		echo    			    '<option selected>'. $severity_value .'</option>';
	foreach ($listSeverities as $key => $severity){
		echo			    '<option>'.$severity->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="reproductibilite" class="sirhen">';
	echo			        '<option selected>'. $reproducibility_value .'</option>';
	foreach ($listReproducibilities as $key => $reproducibility){
		echo			    '<option>'.$reproducibility->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	// TODO a revoir le problème des dates
	date_default_timezone_set("UTC");
	echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->date_submitted)).'</td>';
	echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->last_updated)).'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBoxTitle">'._('Reporter').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Assigned to').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Priority').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Resolution').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Status').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBox">';
	echo				'<select name="reporter" class="sirhen">';
		echo			        '<option selected>'. $reporter_value.'</option>';
	foreach ($listReporters as $key => $user){
			echo			    '<option>'.$user->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="handler" class="sirhen">';
	echo			        '<option selected>'. $handler_value .'</option>';
	echo					'<option></option>';
	foreach ($listDevelopers as $key => $user){
			echo			    '<option>'.$user->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="priorite" class="sirhen">';
		echo			        '<option selected>'. $priority_value .'</option>';
	foreach ($listPriorities as $key => $priority){
		echo			    '<option>'.$priority->name.'</option>';
	}
	echo				'</select>';
	echo 			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="resolution" class="sirhen">';
		echo			        '<option selected>'. $resolution_value .'</option>';
	foreach ($listResolutions as $key => $resolution){
		echo			    '<option>'.$resolution->name.'</option>';
	}
	echo				'</select>';
	echo 			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="etat" class="sirhen">';
		echo			        '<option selected>'. $status_value .'</option>';
	foreach ($listStatus as $key => $status){
			echo			    '<option>'.$status->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBoxTitle">'._('Found in').'</td>';
	echo 			'<td class="FullBoxTitle">'._('Fixed in').'</td>';
	echo 			'<td colspan="3" class="FullBoxTitle">'._('Target').'</td>';
	echo		'</tr>';
	echo 			'<td class="FullBox">';
	echo				'<select name="version" class="sirhen">';
		echo			        '<option selected>'. $version_value .'</option>';
	echo					'<option></option>';
	foreach ($listVersions as $key => $version){
		echo			    '<option>'.$version->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="fixed_in_version" class="sirhen">';
		echo			        '<option selected>'. $fixed_value .'</option>';
	echo					'<option></option>';
	foreach ($listVersions as $key => $fixed_version){
			echo			    '<option>'.$fixed_version->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td colspan="3" class="FullBox">';
	echo				'<select name="target_version" class="sirhen">';
		echo			        '<option selected>'. $target_value .'</option>';
	echo					'<option></option>';
	foreach ($listVersionsMilestone as $key => $target_version){
			echo			    '<option>'.$target_version->name.'</option>';
	}
	echo				'</select>';
	echo			'</td>';
	echo		'<tr>';
	echo	'</table>';
	echo	'<br/>';
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo			'<td width="20%" class="FullBoxTitle">'._('Summary').'&nbsp;<span style="font-weight:normal">'._('(max128 char )').'</span></td>';
	echo			'<td class="FullBox"><input type="text" value="'.htmlspecialchars($defect->summary,ENT_QUOTES).'" name="resume" MAXLENGTH="128" style="width:99%"></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo			'<td width="20%" class="FullBoxTitle">'._('Description').'</td>';
	echo			'<td class="FullBox"><textarea name="description" style="width:99%;" rows="6">'.htmlspecialchars($defect->description, ENT_QUOTES).'</textarea></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo			'<td width="20%" class="FullBoxTitle">'._('Additional Informations').'</td>';
	echo			'<td class="FullBox"><textarea name="informations" style="width:99%;" rows="6">'. $additional_value .'</textarea></td>';
	echo		'</tr>';
	echo	'</table>';
	echo	'<br/>';
	echo	'<div align="center">';
	echo		'<input type="button" name="submitbutton" onclick="validate();" value="'._('Update').'">';
	echo	'</div>';
	echo	'</form>';
}
?>
