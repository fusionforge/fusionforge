<?php
/*
 * Copyright 2010, Franck Villaume - Capgemini
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

/* main display */
global $HTML;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$stats = $clientSOAP->__soapCall('mc_project_get_statistiques', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis, "level" => 0));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	$total = array('open' => 0, 'resolved' => 0, 'resolved' => 0, 'closed' => 0 , 'total' => 0);
	echo $HTML->boxTop("Répartition par état");
	echo	'<tr>';
	echo                    '<th class="InTextBrown">Projet</th>';
	echo                    '<th class="InTextBrown">Ouvert</th>';
	echo                    '<th class="InTextBrown">Résolu</th>';
	echo                    '<th class="InTextBrown">Fermé</th>';
	echo                    '<th class="InTextBrown">Total</th>';
	echo    '</tr>';
	$nbligne = 0;
	foreach ($stats as $stat) {
		$indentation = "";
		for($i = 1; $i < $stat->project_level; $i++){
			$indentation =  $indentation .'&nbsp;&nbsp;';
		}
		if ( $nbligne % 2 == 0 ) {
			echo	'<tr class="LignePaire">';
		} else {
			echo	'<tr class="LigneImpaire">';
		}

		if ($stat->project_level > 1){
			echo sprintf('<td class="InTextBrown">%s >> <a class="DataLink" href="?type=group&id=%s&pluginname=mantisbt">%s</a></td>',$indentation,group_get_objectid_by_publicname($stat->project_name), $stat->project_name);
		}else{
			echo sprintf('<td class="InTextBrown"><a class="DataLink" href="?type=group&id=%s&pluginname=mantisbt">%s</a></td>',group_get_objectid_by_publicname($stat->project_name), $stat->project_name);
		}
		echo		'<td class="InTextBrown">'.$stat->open.'</td>';
		echo		'<td class="InTextBrown">'.$stat->resolved.'</td>';
		echo		'<td class="InTextBrown">'.$stat->closed.'</td>';
		echo		'<td class="InTextBrown">'.$stat->total.'</td>';
		echo	'</tr>';

		// calcul du total
		$total['open'] += $stat->open;
		$total['resolved'] += $stat->resolved;
		$total['closed'] += $stat->closed;
		$total['total'] += $stat->total;
		$nbligne++;
	}
	echo	'<tr>';
	echo		'<td class="InTextBrown"></td>';
	echo		'<td class="InTextBrown">'.$total['open'].'</td>';
	echo		'<td class="InTextBrown">'.$total['resolved'].'</td>';
	echo		'<td class="InTextBrown">'.$total['closed'].'</td>';
	echo		'<td class="InTextBrown">'.$total['total'].'</td>';
	echo	'</tr>';
echo $HTML->boxBottom();

}
?>
