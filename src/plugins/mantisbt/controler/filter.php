<?php
/*
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

$bugfilter = array();
$bugfilter['_view_type'] = "advanced";

if (isset($_POST['sort'])) {
	$bugfilter['sort'] = $_POST['sort'];
} else {
	$bugfilter['sort'] = "last_updated";
}
if (isset($_POST['dir'])) {
	$bugfilter['dir'] = $_POST['dir'];
} else {
	$bugfilter['dir'] = "DESC";
}
echo	'<form name="viewissues" id="mainform" method="post" action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'" >';
if (isset($_POST['sort'])) {
	echo '<input type="hidden" name="sort" value="'.$_POST['sort'].'" />';
}
if (isset($_POST['dir'])) {
	echo '<input type="hidden" name="dir" value="'.$_POST['dir'].'" />';
}

try {
	$listStatus= $clientSOAP->__soapCall('mc_enum_status', array("username" => $username, "password" => $password));
} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)) {
	echo '<div>';
	echo '<div style="float: left;margin-right: 10px; width: 160px">';
	echo '<h4 style="border-bottom: 1px solid #DAE0EA">'. _('With Status:') .'</h4>';

	echo '<select name="projectStatus[]" id="projectStatus" multiple size="'.sizeof($listStatus).'" style="width: 160px; height: 100px">';
	foreach ($listStatus as $key => $status){
		echo '<option value="'.$status->id.'"';
		if (isset($_POST['projectStatus'])) {
			$flipped_projectStatus = array_flip($_POST['projectStatus']);
			if (isset($flipped_projectStatus[$status->id])) {
				echo 'selected="selected"';
			}
		}
		echo '>'.$status->name.'</option>';
	}
	echo '</select>';
	echo '</div>';

	if ($type == "group"){
		$bugfilter['project_id'][] = $mantisbtConf['id_mantisbt'];
		try {
			$listChild = $clientSOAP->__soapCall('mc_project_get_all_subprojects', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));
			$mantisbtMembers =  $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt'], "access" => "ANYBODY"));
		} catch (SoapFault $soapFault) {
			echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
			$errorPage = true;
		}

		if (!isset($errorPage)) {
			if ( 0 != sizeof($listChild) ) {
				echo '<div style="float: left; margin-right: 10px; width: 160px">';
				echo '<h4 style="border-bottom: 1px solid #DAE0EA">'. _('Include child projects:') .'</h4>';

				echo '<select name="projectChildId[]" id="projectChildId" multiple style="width: 160px; height: 100px">';
				foreach ($listChild as $key => $child) {
					// on en profite pour récupérer tous les membres de tous les projets
					$mantisbtMembersChild = $clientSOAP->__soapCall('mc_project_get_users', array("username" => $username, "password" => $password, "project_id" => $child, "access" => "ANYBODY"));
					foreach ($mantisbtMembersChild as $key => $mantisbtMemberChild) {
						$found = 0;
						foreach ($mantisbtMembers as $key => $mantisbtMember) {
							if ( $mantisbtMemberChild->id == $mantisbtMember->id )
								$found = 1;
						}
						if (!$found)
							$mantisbtMembers[] = $mantisbtMemberChild;
					}
					$resultGroupNameFusionForge = db_query_params('select groups.group_name from groups,plugin_mantisbt where groups.group_id = plugin_mantisbt.id_group and plugin_mantisbt.id_mantisbt = $1',
											array($child));
					$rowGroupNameFusionForge = db_fetch_array($resultGroupNameFusionForge);
					echo '<option value="'.$child.'"';
					if (isset($_POST['projectChildId'])) {
						$flipped_projectChildId = array_flip($_POST['projectChildId']);
						if (isset($flipped_projectChildId[$child])) {
							echo 'selected="selected"';
						}
					}
					echo '>'.$rowGroupNameFusionForge['group_name'].'</option>';
				}
				echo '</select>';
				echo '</div>';
			}
			echo '<div style="float: left; margin-right: 10px; width: 160px">';
			echo '<h4 style="border-bottom: 1px solid #DAE0EA">'. _('Submitted by:') .'</h3>';
			echo '<select name="projectReporters[]" id="projectReporters" multiple style="width: 160px; height: 100px">';
			foreach ($mantisbtMembers as $key => $mantisbtMember) {
				echo '<option value="'.$mantisbtMember->id.'"';
				if (isset($_POST['projectReporters'])) {
					$flipped_projectReporters = array_flip($_POST['projectReporters']);
					if (isset($flipped_projectReporters[$mantisbtMember->id])) {
						echo 'selected="selected"';
					}
				}
				echo '>'.htmlspecialchars($mantisbtMember->name,ENT_QUOTES).'</option>';
			}
			echo '</select>';
			echo '</div>';

			echo '<div style="float: left; margin-right: 10px; width: 160px">';
			echo '<h4 style="border-bottom: 1px solid #DAE0EA">'. _('Assigned to:') .'</h3>';
			echo '<select name="projectFixers[]" id="projectFixers" multiple style="width: 160px; height: 100px">';
			foreach ($mantisbtMembers as $key => $mantisbtMember) {
				echo '<option value="'.$mantisbtMember->id.'"';
				if (isset($_POST['projectFixers'])) {
					$flipped_projectFixers = array_flip($_POST['projectFixers']);
					if (isset($flipped_projectFixers[$mantisbtMember->id])) {
						echo 'selected="selected"';
					}
				}
				echo '>'.htmlspecialchars($mantisbtMember->name,ENT_QUOTES).'</option>';
			}
			echo '</select>';
			echo '</div>';

			if (isset($_POST['projectChildId'])) {
				foreach ($_POST['projectChildId'] as $key => $child) {
					$bugfilter['project_id'][] = $child;
				}
			}

			if (isset($_POST['projectReporters'])) {
				foreach ($_POST['projectReporters'] as $key => $projectReporter) {
					$bugfilter['reporter_id'][] = $projectReporter;
				}
			}

			if (isset($_POST['projectFixers'])) {
				foreach ($_POST['projectFixers'] as $key => $projectFixer) {
					$bugfilter['handler_id'][] = $projectFixer;
				}
			}
		}
	}
	if (isset($_POST['projectStatus'])) {
		foreach ($_POST['projectStatus'] as $key => $statusId) {
			$bugfilter['show_status'][] = $statusId;
		}
	} else {
		foreach ($listStatus as $key => $status){
			if ( $status->id != 90 ) {
				$bugfilter['show_status'][] = $status->id;
			}
		}
	}
	echo <<< EOT
		<script>
		function reinit() {
			document.getElementById("projectStatus").selectedIndex = -1;
			if (document.getElementById("projectChildId"))
				document.getElementById("projectChildId").selectedIndex = -1;
			if (document.getElementById("projectFixers"))
				document.getElementById("projectFixers").selectedIndex = -1;
			if (document.getElementById("projectReporters"))
				document.getElementById("projectReporters").selectedIndex = -1;
			document.getElementById("mainform").submit();
		}
		</script>
EOT;
	echo    '</div>';
	echo '<br/><div style="clear:both;width:100%; text-align: right">'.
		'<input type="button" value="'._('Clear filter').'" onclick="reinit();"/> '.
		'<input type="submit" value="'._('Apply filter').'" /></div>';
	echo '</form>';
}
?>
