<?php
/*
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

/* view version of a dedicated group in MantisBt */

/* main display */
global $HTML;
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));
} catch  (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)){
	echo $HTML->boxTop(_('Manage versions'));
	if (sizeof($listVersions)) {
		echo '<table class="innertabs">';
		echo	'<tr>';
		echo		'<td class="FullBoxTitle">'._('Version').'</td>';
		echo		'<td class="FullBoxTitle">'._('Description').'</td>';
		echo		'<td class="FullBoxTitle">'._('Target Date').'</td>';
		echo		'<td class="FullBoxTitle">'._('Type').'</td>';
		echo 		'<td class="FullBoxTitle">'._('Action').'</td>';
		echo	'</tr>';
		$i = 0;
		foreach ($listVersions as $key => $version){
			echo '<tr '.$HTML->boxGetAltRowStyle($i).'">';
			echo '<td class="InText">'.$version->name.'</td>';
			echo '<td class="InText">'.$version->description.'</td>';
			echo '<td class="InText">'.strftime("%d/%m/%Y",strtotime($version->date_order)).'</td>';
			/* est-ce une version release ? */
			if ( $version->released ) {
				echo '<td class="InText">Release</td>';
			/* juste une milestone alors */
			} else {
				echo '<td class="InText">Milestone</td>';
			}
			echo '<td>';
			print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
				<div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
				<a href="?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'&view=editVersion&idVersion='.$version->id.'" style="color:white;font-size:0.8em;font-weight:bold;">Modifier</a>
				</div>
				<div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
			echo '</td></tr>';
			$i++;
		}
		echo '</table>';
	} else {
		echo '<p class="warning">'._('No versions').'</p>';
	}
	echo $HTML->boxBottom();
}

?>
