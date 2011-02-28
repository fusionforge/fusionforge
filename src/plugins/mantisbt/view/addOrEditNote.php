<?php
/*
 * MantisBT plugin
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

$noteEdit;
try {
    /* do not recreate $clientSOAP object if already created by other pages */
    if (!isset($clientSOAP))
        $clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

    $defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
    if ($view == "editNote"){
	    foreach($defect->notes as $key => $note){
		    if ($note->id == $idNote){
			    $noteEdit = $note;
			    break;
		    }
	    }
    }
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	if($view == "editNote"){
		$labelboxTitle = 'Modifier la note';
		$actionform = 'updateNote';
		$labelButtonSubmit = 'Mettre à jour';
	} else {
		$labelboxTitle = 'Ajouter la note';
		$actionform = 'addNote';
		$labelButtonSubmit = 'Valider';
	}

	echo 		'<div align="center" id="add_edit_note">';
	echo 		'<form Method="POST" Action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$defect->id.'&idNote='.$idNote.'&action='.$actionform.'&view=viewIssue">';
	echo				'<table class="innertabs">';
	echo 					'<tr>';
	echo						'<td class="FullBox" ><textarea name="edit_texte_note" style="width:99%;" rows=12>'.$noteEdit->text.'</textarea></td>';
	echo 					'</tr>';
	echo				'</table>';
	echo 				'<input type=button onclick="this.form.submit();this.disabled=true;" value="'.$labelButtonSubmit.'">';
	echo 			'</form>';
	echo 		'</div>';
}
?>
