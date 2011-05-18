<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$listChild = $clientSOAP->__soapCall('mc_project_get_all_subprojects', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));

} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)) {
	GLOBAL $HTML;
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
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
	jQuery("#expandable_filter").hide();
<?php
	}
?>
	});
</script>
<p class="notice_title" onclick='jQuery("#expandable_filter").slideToggle(300)'><?php echo _('Display filter rules') ?></p>

<div id='expandable_filter' class="notice_content" style='clear: both'>
<?php
	include('mantisbt/controler/filter_roadmap.php');
?>
</div>

<?php
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
			try {
				$idsBug = $clientSOAP->__soapCall('mc_issue_get_list_by_project_for_specific_version', array("username" => $username, "password" => $password, "project" => $mantisbtConf['id_mantisbt'], "version" => $version->name ));
			} catch (SoapFault $soapFault) {
				echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
				break;
			}
			echo	'<fieldset>';
			$typeVersion = _('Milestone');
			if ( $version->released ) {
				$typeVersion = _('Release');
			}
			echo	_('Version:').' '.$version->name.' (<i>'.strftime("%d/%m/%Y",strtotime($version->date_order)).'</i> '.$typeVersion.') - <i>'.count($idsBug).' ticket(s)</i>';
			echo	'<ul>';
			foreach ( $idsBug as $key => $idBug ) {
				$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
				if ( !array_key_exists('handler', $defect) || !array_key_exists('name', $defect->handler) ) {
					$defect_handler_name = _('no-handler');
				} else {
					$defect_handler_name = $defect->handler->name;
				}
				echo	'<li>';
				if ( $defect->status->id >= 80 ) {
					echo '<strike>';
				}
				echo	'<a href="?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$defect->id.'&view=viewIssue">'.$defect->id.'</a>: '.$defect->summary.' ('.$defect->resolution->name.') - ('.$defect_handler_name.')';
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
					$resultGroupNameFusionForge = db_query_params('select groups.group_name, groups.group_id from groups,plugin_mantisbt
											where groups.group_id = plugin_mantisbt.id_group and plugin_mantisbt.id_mantisbt = $1',
											array($child));
					$rowGroupNameFusionForge = db_fetch_array($resultGroupNameFusionForge);
					echo $HTML->boxTop('<a style="color:white;" href="?type=group&group_id='.$rowGroupNameFusionForge['group_id'].'&pluginname='.$mantisbt->name.'">'.$rowGroupNameFusionForge['group_name'].'</a>');
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
								$typeVersion = _('Milestone');
								if ( $childprintversion->released == 1 ) {
									$typeVersion = _('Release');
								}
								echo	_('Version:').' '.$childprintversion->name.' (<i>'.strftime("%d/%m/%Y",strtotime($childprintversion->date_order)).'</i> '.$typeVersion.') - <i>'.count($idsBug).'</i>';
								echo	'<ul>';
								foreach ( $idsBug as $key => $idBug ) {
									$defect = $clientSOAP->__soapCall('mc_issue_get', array("username" => $username, "password" => $password, "issue_id" => $idBug));
									if ( !array_key_exists('handler', $defect) || !array_key_exists('name', $defect->handler) ) {
										$defect_handler_name = _('no-handler');
									} else {
										$defect_handler_name = $defect->handler->name;
									}
									echo    '<li>';
									if ( $defect->status->id >= 80 ) {
										echo '<strike>';
									}
									echo    '<a href="?type=group&group_id='.$rowGroupNameFusionForge['group_id'].'&pluginname='.$mantisbt->name.'&idBug='.$defect->id.'&view=viewIssue">'.$defect->id.'</a>: '.$defect->summary.' ('.$defect->resolution->name.') - ('.$defect_handler_name.')';
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
	} else {
		echo '<p class="warning">'._('No versions to display').'</p>';
	}
}
?>
