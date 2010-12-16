<?php
/*
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$listChild = $clientSOAP->__soapCall('mc_project_get_all_subprojects', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));

} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)) {
	GLOBAL $HTML;
?>
<script type="text/javascript">
	$(document).ready(function() {
<?php
	$view = 0;
	foreach ($listChild as $key => $child) {
		if ( isset($_POST['project'.$child.'VersionId'])) {
			$view = 1;
		}
	}
	if ( isset($_POST['projectVersionId']) ) {
		$view = 1;
	}
	if ( $view == 0 ) {
?>
	$("#expandable_filter").hide();
<?php
	}
?>
	});
</script>

<style>
.notice_title {
	background-color: #D7E0EB;
	padding: 10px;
	font-weight: bold;
	margin-bottom:0px;
	cursor: pointer;
	color: #4F93C3;
}

.notice_content {
	border: 1px solid #D7E0EB;
	padding: 10px;
	font-weight: bold;
	-moz-border-radius-bottomright: 8px;
	-moz-border-radius-bottomleft: 8px;
	-webkit-border-bottom-right-radius: 8px;
	-webkit-border-bottom-left-radius: 8px;
	margin-top:0px;
}
</style>

<h2 style='border-bottom: 1px solid black'>Filtres</h2>
<p class="notice_title" onclick='$("#expandable_filter").slideToggle(300)'>Afficher les r&egrave;gles de filtrage</p>

<div id='expandable_filter' class="notice_content" style='clear: both'>
<?php
	include('mantisbt/controler/filter_roadmap.php');
?>
</div>

<?php
	echo '<h2 style="border-bottom: 1px solid black">Feuille de route</h2>';
	if (!isset($_POST['projectVersionId'])) {
		if (isset($listVersions) && !empty($listVersions)) {
			$listPrintVersions = $listVersions;
		}
	} else {
		$flipped_projectVersionId = array_flip($_POST['projectVersionId']);
		foreach ($listVersions as $key => $version) {
			if (isset($flipped_projectVersionId[$version->id])) {
				$listPrintVersions[] = $version;
			}
		}
	}
	if (isset($listPrintVersions) && !empty($listPrintVersions)) {
		foreach ($listPrintVersions as $key => $version) {
			$idsBug = $clientSOAP->__soapCall('mc_issue_get_list_by_project_for_specific_version', array("username" => $username, "password" => $password, "project" => $idProjetMantis, "version" => $version->name ));
			echo	'<fieldset>';
			$typeVersion = "Milestone";
			if ( $version->released ) {
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

		if (sizeof($listChild)) {
			foreach ($listChild as $key => $child) {
				if (isset($_POST['project'.$child.'VersionId'])) {
					$resultGroupNameFusionForge = db_query_params('select groups.group_name, groups.group_id from groups,group_mantisbt
											where groups.group_id = group_mantisbt.id_group and group_mantisbt.id_mantisbt = $1',
											array($child));
					$rowGroupNameFusionForge = db_fetch_array($resultGroupNameFusionForge);
					echo $HTML->boxTop('<a style="color:white;" href="?type=group&id='.$rowGroupNameFusionForge['group_id'].'&pluginname=mantisbt">'.$rowGroupNameFusionForge['group_name'].'</a>');
					echo '<fieldset>';
					$listChildVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $child));
					if (!empty($listChildVersions)){
						$flipped_projectChildVersionId = array_flip($_POST['project'.$child.'VersionId']);
						$listChildPrintVersions = array();
						foreach ($listChildVersions as $key => $childVersion) {
							if (isset($flipped_projectChildVersionId[$childVersion->id])) {
								$listChildPrintVersions[] = $childVersion;
							}
						}
						if (isset($listChildPrintVersions) && !empty($listChildPrintVersions)) {
							foreach ($listChildPrintVersions as $key => $childprintversion){
								echo	'<fieldset>';
								$idsBug = $clientSOAP->__soapCall('mc_issue_get_list_by_project_for_specific_version', array("username" => $username, "password" => $password, "project" => $child, "version" => $childprintversion->name ));
								$typeVersion = "Milestone";
								if ( $childprintversion->released == 1 ) {
									$typeVersion = "Release";
								}
								echo	'Version : '.$childprintversion->name.' (<i>'.strftime("%d/%m/%Y",strtotime($childprintversion->date_order)).'</i> '.$typeVersion.') - <i>'.count($idsBug).'</i>';
								echo	'<ul>';
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
									echo    '<a href="?type=group&id='.$rowGroupNameFusionForge['group_id'].'&pluginname=mantisbt&idBug='.$defect->id.'&view=viewIssue">'.$defect->id.'</a>: '.$defect->summary.' ('.$defect->resolution->name.') - ('.$defect_handler_name.')';
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
			}
		}
	}
}
?>
