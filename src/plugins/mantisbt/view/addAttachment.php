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

$boxTitle = 'Ajouter un fichier (<a style="color:#FFFFFF;font-size:0.8em;" href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$idBug.'&view=viewIssue">Retour Ticket '.$idBug.'</a>)';
echo $HTML->boxTop($boxTitle,InTextBorder);

if ($errorPage){
	echo 	'<div>Un problème est survenu lors de la récupération des données</div>';
	echo $HTML->boxBottom();
}else {
	echo '<form method="POST" Action="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&idBug='.$idBug.'&action=addAttachment&view=viewIssue" enctype="multipart/form-data">';
	echo	'<table class="innertabs">';
	echo '<tr><td>';
	echo '     Fichier : <input type="file" name="attachment">';
	echo '</td><td>';
	echo '     <input type="submit" name="envoyer" value="Envoyer le fichier">';
	echo '</td></tr></table>';
	echo '</form>';
	echo $HTML->boxBottom();
}
?>
