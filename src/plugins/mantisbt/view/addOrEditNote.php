<?php

/*
 * Copyright 2010, Capgemini
 * Authors: Franck Villaume - capgemini
 *          Antoine Mercadal - capgemini
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

$noteEdit;
try {
    /* do not recreate $clientSOAP object if already created by other pages */
    if (!isset($clientSOAP))
        $clientSOAP = new SoapClient("http://".forget_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

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
    $msg = $soapFault->faultstring;
    $errorPage = true;
}

if ($errorPage){
    echo    '<div class="warning">Un probl&egrave;me est survenu lors de la r&eacute;cup&eacute;ration des donn&eacute;es : '.$msg.'</div>';
} else {
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
    echo 		'<form Method="POST" Action="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&idNote='.$idNote.'&action='.$actionform.'&view=viewIssue">';
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
