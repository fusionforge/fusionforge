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

$noteEdit;
$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
if ($view == "editNote"){
	foreach($defect->notes as $key => $note){
		if ($note->id == $idNote){
			$noteEdit = $note;
			break;
		}
	}
}
if($view == "editNote"){
	$labelboxTitle = 'Modifier la note';
	$actionform = 'updateNote';
	$labelButtonSubmit = 'Mettre à jour';
} else {
	$labelboxTitle = 'Ajouter la note';
	$actionform = 'addNote';
	$labelButtonSubmit = 'Valider';
}

$boxTitle = $labelboxTitle.' (<a style="color:#FFFFFF;font-size:0.8em" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&view=viewIssue">Retour Ticket '.$defect->id.'</a>)';

echo $HTML->boxTop($boxTitle,InTextBorder);
echo 		'<div align="center" id="add_edit_note">';
echo 		'<form Method="POST" Action="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&idNote='.$idNote.'&action='.$actionform.'&view=viewIssue">';
echo				'<table class="innertabs">';
echo 					'<tr>';
echo						'<td class="FullBox" ><textarea name="edit_texte_note" style="width:99%;" rows=12>'.$noteEdit->text.'</textarea></td>';
echo 					'</tr>';
echo				'</table>';
echo 				'<input type=submit value="'.$labelButtonSubmit.'">';
echo 			'</form>';
echo 		'</div>';
echo $HTML->boxBottom();

?>
