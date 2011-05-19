<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/* main display */
global $HTML;
global $mantisbt;
global $group_id;
global $mantisbtConf;
global $username;
global $password;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$stats = $clientSOAP->__soapCall('mc_project_get_statistiques', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt'], "level" => 0));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	$total = array('open' => 0, 'resolved' => 0, 'resolved' => 0, 'closed' => 0, 'total' => 0);
	echo $HTML->boxTop(_('Tickets oer Status'));
	echo	'<tr>';
	echo		'<th>'._('Project').'</th>';
	echo		'<th>'._('Open').'</th>';
	echo		'<th>'._('Fixed').'</th>';
	echo		'<th>'._('Closed').'</th>';
	echo		'<th>'._('Total').'</th>';
	echo	'</tr>';
	$nbligne = 1;
	foreach ($stats as $stat) {
		$indentation = "";
		for($i = 1; $i < $stat->project_level; $i++){
			$indentation = $indentation .'&nbsp;&nbsp;';
		}
		echo '<tr '.$HTML->boxGetAltRowStyle($nbligne).'">';

		if ($stat->project_level > 1){
			echo sprintf('<td>%s >> <a class="DataLink" href="?type=group&group_id=%s&pluginname=%s">%s</a></td>',$indentation,group_get_objectid_by_publicname($stat->project_name), $mantisbt->name, $stat->project_name);
		}else{
			echo sprintf('<td><a class="DataLink" href="?type=group&group_id=%s&pluginname=%s">%s</a></td>',group_get_objectid_by_publicname($stat->project_name), $mantisbt->name, $stat->project_name);
		}
		echo		'<td>'.$stat->open.'</td>';
		echo		'<td>'.$stat->resolved.'</td>';
		echo		'<td>'.$stat->closed.'</td>';
		echo		'<td>'.$stat->total.'</td>';
		echo	'</tr>';

		// calcul du total
		$total['open'] += $stat->open;
		$total['resolved'] += $stat->resolved;
		$total['closed'] += $stat->closed;
		$total['total'] += $stat->total;
		$nbligne++;
	}
	echo	'<tr>';
	echo		'<td></td>';
	echo		'<td>'.$total['open'].'</td>';
	echo		'<td>'.$total['resolved'].'</td>';
	echo		'<td>'.$total['closed'].'</td>';
	echo		'<td>'.$total['total'].'</td>';
	echo	'</tr>';
echo $HTML->boxBottom();

}
?>
