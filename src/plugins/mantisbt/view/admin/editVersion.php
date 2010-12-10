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

$idVersion=getIntFromRequest('idVersion');

try {
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$detailVersion = $clientSOAP->__soapCall('mc_project_get_version_details', array("username" => $username, "password" => $password, "version_id" => $idVersion));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	echo $HTML->boxTop(_('Version Detail'));
	echo '<form method="POST" name="updateVersion'.$detailVersion->id.'" action="index.php?type=admin&id='.$id.'&pluginname=mantisbt&action=updateVersion">';
	echo '<table class="innertabs">';
	echo	'<thead>';
	echo	'<tr>';
	echo		'<th class="FullBoxTitle">Version</th>';
	echo		'<th class="FullBoxTitle">Date Livraison</th>';
	echo		'<th class="FullBoxTitle">Type</th>';
	echo	'</tr>';
	echo	'</thead>';
	echo	'<tbody>';
	echo	'<tr>';
	echo		'<td><input type="text" name="version_name" value="'.htmlspecialchars($detailVersion->name,ENT_QUOTES).'" /></td>';
	echo		'<td><input type="text" name="version_date_order" size="32" value="'.strftime("%d/%m/%Y",strtotime($detailVersion->date_order)).'" />(format : DD/MM/YYYY)</td>';
	echo		'<td>';
	echo			'<select name="version_release">';
	if ( $detailVersion->released ) {
		echo		'<option value="1" selected>Release</option>';
		echo		'<option value="0" >Milestone</option>';
	} else {
		echo		'<option value="1" >Release</option>';
		echo		'<option value="0" selected>Milestone</option>';
	}
	echo			'</select>';
	echo		'</td>';
	echo	'</tr>';
	echo	'</tbody>';
	echo '</table>';
	if ($group->usesPlugin('projects_hierarchy')) {
		echo '<input type="checkbox" name="transverse" value="1">mise à jour transverse (fils inclus)</input>';
	}
	echo '<input type="hidden" name="version_id" value="'.$detailVersion->id.'"></input>';
	echo '<input type="hidden" name="version_old_name" value="'.$detailVersion->name.'"></input>';
	echo '<br/>';
	echo '<input type="submit" value="'. _('Add') .'" />';
	echo '</form>';
	echo $HTML->boxBottom();

	echo '<form method="POST" name="deleteVersion'.$detailVersion->id.'" action="index.php?type=admin&id='.$id.'&pluginname=mantisbt&action=deleteVersion">';
	echo '<input type="hidden" name="deleteVersion" value="'.$detailVersion->id.'"></input>';
	echo '<input type="submit" value="'. _('Delete') .'" />';
	echo '</form>';
}
?>