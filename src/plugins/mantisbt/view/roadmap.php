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

echo $HTML->boxTop("Feuille de route",InTextBorder);
echo	'<form name="roadmap" method="post" action="?type=group&id='.$id.'&pluginname=mantisbt&view=roadmap">';
echo	'<div>';
echo	'<input type="checkbox" name="addReleases" value="1" ';
if ( $_POST['addReleases'] ) {
	echo 'checked';
}
echo	'>Afficher les releases</input>';
echo	'<input type="checkbox" name="addChild" value="1" ';
if ( $_POST['addChild'] ) {
	echo 'checked';
}
echo	'>Afficher les sous-projets</input>';
echo	'<input type="checkbox" name="addChildReleases" value="1" ';
if ( $_POST['addChildReleases'] ) {
	echo 'checked';
}
echo	'>Afficher les releases des sous-projets</input>';
echo	'</div>';
print'<div style="float:left"><img src="'.util_make_url('themes/gforge/images/bouton_gauche.png').'"></img></div>
	<div style="background: url('.util_make_url('themes/gforge/images/bouton_centre.png').');vertical-align:top;display:inline;font-size:15px">
	<a href="javascript:document.roadmap.submit();" style="color:white;font-size:0.8em;font-weight:bold;">Recharger</a>
	</div>
	<div style="display:inline"><img src="'.util_make_url('themes/gforge/images/bouton_droit.png').'"></img></div>';
echo $HTML->boxBottom();

$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('exceptions'=>true));
$listFullVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));
if (!empty($listFullVersions)){
	arsort($listFullVersions);
	if ( $_POST['addReleases'] ) {
		$listPrintVersions = $listFullVersions;
	} else {
		foreach ($listFullVersions as $key => $version) {
			if ( $version->released == 0 ) {
				$listPrintVersions[] = $version;
			}
		}
	}
	if (isset($listPrintVersions) && !empty($listPrintVersions)) {
		foreach ($listPrintVersions as $key => $version){
			$idsBug = $clientSOAP->__soapCall('mc_issue_get_list_by_project_for_specific_version', array("username" => $username, "password" => $password, "project" => $idProjetMantis, "version" => $version->name ));
			echo	'<fieldset>';
			$typeVersion = "Milestone";
			if ( $version->released == 1 ) {
				$typeVersion = "Release";
			}
			echo	'Version : '.$version->name.' (<i>'.strftime("%d/%m/%Y",strtotime($version->date_order)).'</i> '.$typeVersion.') - <i>'.count($idsBug).' ticket(s)</i>';
			echo	'<ul>';
			foreach ( $idsBug as $key => $idBug ) {
			$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
			if ( !array_key_exists('handler', $defect) || !array_key_exists('name', $defect->handler) ) {
				$defect_handler_name = "non-affecte";
			} else {
				$defect_handler_name = $defect->handler->name;
			}
			echo	'<li>';
			if ( $defect->status->id >= 80 ) {
				echo '<strike>';
			}
			echo	'<a href="?type=group&id='.$id.'&pluginname=mantisbt&idBug='.$defect->id.'&view=viewIssue">'.$defect->id.'</a>: '.$defect->summary.' ('.$defect->resolution->name.') - ('.$defect_handler_name.')';
			if ( $defect->status->id >= 80 ) {
				echo '</strike>';
			}
			echo	'</li>';
			}
			echo	'</ul>';
			echo	'</fieldset>';
		}
	}
}

if ( $_POST['addChild'] ) {
	$listChild = $clientSOAP->__soapCall('mc_project_get_subprojects', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));
	echo '<fieldset>';
	echo '<legend>Sous-projets</legend>';
	foreach ( $listChild as $key => $child ) {
		$projectInfo = $clientSOAP->__soapCall('mc_project_as_array_by_id', array("username" => $username, "password" => $password, "project_id" => $child));
		echo $HTML->boxTop($projectInfo[1]);
		echo '<fieldset>';
		$listChildFullVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $child));
		if (!empty($listChildFullVersions)){
        		arsort($listChildFullVersions);
			$listChildPrintVersions = array();
        		if ( $_POST['addChildReleases'] ) {
                		$listChildPrintVersions = $listChildFullVersions;
        		} else {
                		foreach ($listChildFullVersions as $key => $childversion) {
                        		if ( $childversion->released == 0 ) {
                                		$listChildPrintVersions[] = $childversion;
                        		}
                		}
        		}
			if (isset($listChildFullVersions) && !empty($listChildFullVersions)) {
        			foreach ($listChildPrintVersions as $key => $childversion){
                			echo    '<fieldset>';
                			$idsBug = $clientSOAP->__soapCall('mc_issue_get_list_by_project_for_specific_version', array("username" => $username, "password" => $password, "project" => $child, "version" => $childversion->name ));
                			$typeVersion = "Milestone";
                			if ( $childversion->released == 1 ) {
                        			$typeVersion = "Release";
                			}
                			echo    'Version : '.$childversion->name.' (<i>'.strftime("%d/%m/%Y",strtotime($childversion->date_order)).'</i> '.$typeVersion.') - <i>'.count($idsBug).'</i>';
                			echo    '<ul>';
                			foreach ( $idsBug as $key => $idBug ) {
                				$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
                				if ( !array_key_exists('handler', $defect) || !array_key_exists('name', $defect->handler) ) {
                        				$defect_handler_name = "non-affecte";
                				} else {
                        				$defect_handler_name = $defect->handler->name;
                				}
                				echo    '<li>';
                				if ( $defect->status->id >= 80 ) {
                        				echo '<strike>';
                				}
                				echo    '<a href="?type=group&id='.$id.'&pluginname=mantisbt&idBug='.$defect->id.'&view=viewIssue">'.$defect->id.'</a>: '.$defect->summary.' ('.$defect->resolution->name.') - ('.$defect_handler_name.')';
                				if ( $defect->status->id >= 80 ) {
                        				echo '</strike>';
                				}
                				echo    '</li>';
                			}
                			echo    '</ul>';
                			echo    '</fieldset>';
        			}
			}
		}

		echo '</fieldset>';
		echo $HTML->boxBottom();
	}
	echo '</fieldset>';
}
?>
