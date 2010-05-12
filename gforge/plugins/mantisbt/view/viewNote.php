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
	$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
	$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
}

$boxTitle = 'Notes (<a style="color:#FFFFFF;font-size:0.8em" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&view=addNote">Ajouter une note</a>)';
echo $HTML->boxTop($boxTitle,InTextBorder);

echo	'<table class="innertabs">';
if (isset($defect->notes)){
	foreach ($defect->notes as $key => $note){
		echo	'<tr>';
		echo		'<td width="10%" class="FullBoxTitle">';
		echo 			'('.sprintf($format,$note->id).')';
		echo 			'<br/>';
		echo			$note->reporter->name;
		echo 			'<br/>';
		// TODO
		//date_default_timezone_set("UTC");
		echo 			date("Y-m-d G:i",strtotime($note->date_submitted));
		echo 		'</td>';
		echo		'<td width="13%" class="FullBoxTitle">';
		echo 			'<input type=button name="upNote" value="Modifier" onclick="window.location.href=\'?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&idNote='.$note->id.'&view=editNote\'">';
		echo 			'<input type=button name="delNote" value="Supprimer" onclick="window.location.href=\'?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$defect->id.'&idNote='.$note->id.'&action=deleteNote&view=viewIssue\'">';
		echo 		"</td>";
		echo 		'<td class="FullBox">';
		echo			$note->text;
		echo 		"</td>";
		echo 	'</tr>';
	}
}
echo $HTML->boxBottom();
?>
