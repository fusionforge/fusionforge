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

GLOBAL $HTML;

if (!isset($defect)){
	try{
		$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
	}catch (SoapFault $soapFault) {
		echo $soapFault->faultstring;
		echo "<br/>";
		$errorPage = true;
	}
}

$boxTitle = 'Fichiers attachés (<a style="color:#FFFFFF;font-size:0.8em" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$idBug.'&view=addAttachment">Ajouter un fichier</a>)';
echo $HTML->boxTop($boxTitle,InTextBorder);

if ($errorPage){
	echo 	'<div>Un problème est survenu lors de la récupération des données</div>';
	echo $HTML->boxBottom();
}else {
	if ($defect->attachments) {
		echo	'<table class="innertabs">';
		echo '<tr>';
		echo '<td class="FullBoxTitle">Nom du fichier</td>';
		echo '<td class="FullBoxTitle">Actions</td>';
		echo '</tr>';
		foreach ($defect->attachments as $key => $attachement){
			echo	'<tr>';
			echo		'<td class="FullBox">'.$attachement->filename.'</td>';
			echo 		'<td class="FullBox">';
			echo			'<input type=button value="Télécharger" onclick="window.location.href=\'getAttachment.php/'.$attachement->id.'/'.$attachement->filename.'\'">';
			echo			'<input type=button value="Supprimer" onclick="window.location.href=\'?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$idBug.'&idAttachment='.$attachement->id.'&action=deleteAttachment&view=viewIssue\'">';
			echo		'</td>';
			echo 	'</tr>';
		}
	}
	echo $HTML->boxBottom();
}
?>
