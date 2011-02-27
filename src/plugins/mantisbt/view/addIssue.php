<?php
/*
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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
global $group_id;

//validate function : to be sure needed informations are set before submit
print	('
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

	$listCategories = $clientSOAP->__soapCall('mc_project_get_categories', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));
	$listSeverities = $clientSOAP->__soapCall('mc_enum_severities', array("username" => $username, "password" => $password));
	$listReproducibilities = $clientSOAP->__soapCall('mc_enum_reproducibilities', array("username" => $username, "password" => $password));
	$listViewStates = $clientSOAP->__soapCall('mc_enum_view_states', array("username" => $username, "password" => $password));
	$listDevelopers = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt'], "acces" => 25));
	$listPriorities = $clientSOAP->__soapCall('mc_enum_priorities', array("username" => $username, "password" => $password));
	$listResolutions= $clientSOAP->__soapCall('mc_enum_resolutions', array("username" => $username, "password" => $password));
	$listStatus= $clientSOAP->__soapCall('mc_enum_status', array("username" => $username, "password" => $password));
	$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	echo 	'<form name="issue" method="POST" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=addIssue" >';
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="16%" class="FullBoxTitle">'._('Category').'</td>';
	echo 			'<td width="16%" class="FullBoxTitle">'._('Reproducibility').'</td>';
	echo 			'<td width="16%" class="FullBoxTitle">'._('Severity').'</td>';
	echo 			'<td width="16%" class="FullBoxTitle">'._('Priority').'</td>';
	echo 			'<td width="16%" class="FullBoxTitle">'._('Assigned to').'</td>';
	echo 			'<td width="16%" class="FullBoxTitle">'._('Found in').'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBox">';
	echo				'<select name="categorie" class="sirhen">';
	foreach ($listCategories as $key => $category){
		echo				"<option>".$category."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="reproductibilite" class="sirhen">';
	foreach ($listReproducibilities as $key => $reproducibility){
		echo				"<option>".$reproducibility->name."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="severite" class="sirhen">';
	foreach ($listSeverities as $key => $severity){
		echo				"<option>".$severity->name."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="priorite" class="sirhen">';
	foreach ($listPriorities as $key => $priority){
		echo				"<option>".$priority->name."</option>";
	}
	echo				'</select>';
	echo 			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="handler" class="sirhen">';
	echo					"<option></option>";
	foreach ($listDevelopers as $key => $user){
		echo				"<option>".$user->name."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo 			'<td class="FullBox">';
	echo				'<select name="version" class="sirhen">';
	echo					"<option></option>";
	foreach ($listVersions as $key => $version){
		echo				"<option>".$version->name."</option>";
	}
	echo				'</select>';
	echo			'</td>';
	echo		'</tr>';
	echo	'</table>';
	echo	'<br/>';
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="20%" lass="FullBoxTitle">Résumé * <span style="font-weight:normal">(128 caractères max)</span></td>';
	echo			'<td class="FullBox"><input type="text" name="resume" MAXLENGTH="128" style="width:99%;"></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBoxTitle">'.('Description').' *</td>';
	echo			'<td class="FullBox"><textarea name="description" style="width:99%;" rows="12"></textarea></td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBoxTitle">'._('Additional informations').'</td>';
	echo			'<td class="FullBox"><textarea name="informations" style="width:99%;" rows="12"></textarea></td>';
	echo		'</tr>';
	echo	'</table>';
	echo 	'<div align="center">';
	echo 		'<input type="button" name="submitbutton" value="'._('Submit').'" onclick="validate();">';
	echo 	'</div>';
	echo	'* '._('Mandatory fields');
	echo 	'</form>';
}
?>
