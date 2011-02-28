<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

global $HTML;
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;
global $group;

$idVersion=getIntFromRequest('idVersion');

try {
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	// currently this soap call is not included in mantisbt 1.2.x
	//$detailVersion = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "version_id" => $idVersion));
	$arrVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

// select the right version until the soap call is ported.
$detailVersion = array();
foreach ($arrVersions as $key => $currentVersion) {
	if ($currentVersion->id == $idVersion) {
		$detailVersion = $currentVersion;
	}
}

if (!isset($errorPage)){
	echo $HTML->boxTop(_('Version Detail'));
	echo '<form method="POST" action="?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=updateVersion">';
	echo '<table>';
	echo	'<thead>';
	echo	'<tr>';
	echo		'<th>'._('Version').'</th>';
	echo		'<th>'._('Description').'</th>';
	echo		'<th>'._('Target date').'</th>';
	echo		'<th>'._('Type').'</th>';
	echo	'</tr>';
	echo	'</thead>';
	echo	'<tbody>';
	echo	'<tr>';
	echo		'<td><input type="text" name="version_name" value="'.htmlspecialchars($detailVersion->name,ENT_QUOTES).'" /></td>';
	echo		'<td><input type="text" name="version_description" value="'.htmlspecialchars($detailVersion->description,ENT_QUOTES).'" /></td>';
	echo		'<td><input type="text" name="version_date_order" size="32" value="'.strftime("%d/%m/%Y",strtotime($detailVersion->date_order)).'" />(format : DD/MM/YYYY)</td>';
	echo		'<td>';
	echo			'<select name="version_release">';
	if ($detailVersion->released) {
		echo		'<option value="1" selected>'._('Release').'</option>';
		echo		'<option value="0" >'._('Milestone').'</option>';
	} else {
		echo		'<option value="1" >'._('Release').'</option>';
		echo		'<option value="0" selected>'._('Milestone').'</option>';
	}
	echo			'</select>';
	echo		'</td>';
	echo	'</tr>';
	echo	'</tbody>';
	echo '</table>';
	if ($group->usesPlugin('projects_hierarchy')) {
		echo '<input type="checkbox" name="transverse" value="1">'._('Cross version (son included)').'</input>';
	}
	echo '<input type="hidden" name="version_id" value="'.$idVersion.'"></input>';
	echo '<input type="hidden" name="version_old_name" value="'.$detailVersion->name.'"></input>';
	echo '<br/>';
	echo '<input type="submit" value="'. _('Update') .'" />';
	echo '</form>';
	echo $HTML->boxBottom();

	echo '<form method="POST" action="?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&action=deleteVersion">';
	echo '<input type="hidden" name="deleteVersion" value="'.$idVersion.'"></input>';
	echo '<input type="submit" value="'. _('Delete') .'" />';
	echo '</form>';
}
?>