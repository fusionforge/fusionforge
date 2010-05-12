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

if (!isset($defect)){
	try {
		$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
	}catch (SoapFault $soapFault) {
		echo $soapFault->faultstring;
		echo "<br/>";
		$errorPage = true;
	}
}
$boxTitle = 'Détail Ticket : '.sprintf($format,$defect->id).' (<a style="color:#FFFFFF;font-size:0.8em" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.sprintf($format,$defect->id).'&view=editIssue">Editer</a>)';
echo $HTML->boxTop($boxTitle,InTextBorder);
if ($errorPage){
	echo 	'<div>Un problème est survenu lors de la récupération des données</div>';
	echo $HTML->boxBottom();
}else {
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="14%" class="FullBoxTitle">Catégorie</td>';
	echo 			'<td width="14%" class="FullBoxTitle">Sévérité</td>';
	echo 			'<td width="14%" class="FullBoxTitle">Reproductibilité</td>';
	echo 			'<td width="14%" class="FullBoxTitle">Date de soumission</td>';
	echo 			'<td width="14%" class="FullBoxTitle">Date mise à jour</td>';
	echo 			'<td width="14%" class="FullBoxTitle">Version de détection</td>';
	echo 			'<td width="14%" class="FullBoxTitle">Milestone</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBox">'.$defect->category.'</td>';
	echo 			'<td class="FullBox">'.$defect->severity->name.'</td>';
	echo 			'<td class="FullBox">'.$defect->reproducibility->name.'</td>';
	// TODO a revoir le problème des dates
	date_default_timezone_set("UTC");
	echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->date_submitted)).'</td>';
	echo 			'<td class="FullBox">'.date("Y-m-d G:i",strtotime($defect->last_updated)).'</td>';
	echo 			'<td class="FullBox">'.$defect->version.'</td>';
	echo 			'<td class="FullBox">'.$defect->target_version.'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBoxTitle">Rapporteur</td>';
	echo 			'<td class="FullBoxTitle">Assigné à</td>';
	echo 			'<td class="FullBoxTitle">Priorité</td>';
	echo 			'<td class="FullBoxTitle">Résolution</td>';
	echo 			'<td class="FullBoxTitle">Etat</td>';
	echo 			'<td class="FullBoxTitle">Corrigé en version</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td class="FullBox">'.$defect->reporter->name.'</td>';
	echo 			'<td class="FullBox">'.$defect->handler->name.'</td>';
	echo 			'<td class="FullBox">'.$defect->priority->name.'</td>';
	echo 			'<td class="FullBox">'.$defect->resolution->name.'</td>';
	echo 			'<td class="FullBox">'.$defect->status->name.'</td>';
	echo 			'<td class="FullBox">'.$defect->fixed_in_version.'</td>';
	echo		'</tr>';
	echo	'</table>';
	echo	'<br />';
	echo	'<table class="innertabs">';
	echo		'<tr>';
	echo 			'<td width="25%" class="FullBoxTitle">Résumé</td>';
	echo			'<td width="75%" class="FullBox">'.$defect->summary.'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td width="25%" class="FullBoxTitle">Description</td>';
	echo			'<td width="75%" class="FullBox">'.$defect->description.'</td>';
	echo		'</tr>';
	echo		'<tr>';
	echo 			'<td width="25%" class="FullBoxTitle">Informations complémentaires</td>';
	echo			'<td width="75%" class="FullBox">'.$defect->additional_information.'</td>';
	echo		'</tr>';
	echo	'</table>';
	echo $HTML->boxBottom();
	}
?>
