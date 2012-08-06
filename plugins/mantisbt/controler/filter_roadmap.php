<?php
/**
 * MantisBT Plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $listChild;

echo '<form id="mainform" method="post" action="?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&view=roadmap">';

try {
	/* do not recreate $clientSOAP object if already created by other pages */
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $mantisbtConf['id_mantisbt']));

} catch (SoapFault $soapFault) {
	echo '<div class="warning" >'. _('Technical error occurs during data retrieving:'). ' ' .$soapFault->faultstring.'</div>';
	$errorPage = true;
}

if (!isset($errorPage)) {
	if (sizeof($listVersions)) {
		echo '<div style="float: left;margin-right: 10px; width: 145px; height: 140px;">';
		echo '<h4 style="border-bottom: 1px solid #DAE0EA">'._('Versions:').'</h4>';
		echo '<select name="projectVersionId[]" id="projectVersionId" multiple style="width: 145px; height: 100px">';
		foreach ($listVersions as $key => $version) {
			echo '<option value="'.$version->id.'"';
			if (isset($_POST['projectVersionId'])) {
				$flipped_projectVersionId = array_flip($_POST['projectVersionId']);
				if (isset($flipped_projectVersionId[$version->id])) {
					echo 'selected="selected"';
				}
			}
			echo '>'.$version->name;
			if ($version->released) {
				echo '(<i>'._('Release').'</i>)';
			} else {
				echo '(<i>'._('Milestone').'</i>)';
			}
			echo '</option>';
		}
		echo '</select>';
		echo '</div>';
	}

	if ( sizeof($listChild)) {
	// nous avons deja un bloc
	$nbblock = 1;
		foreach ($listChild as $key => $child) {
			$listChildVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $child));
			if (sizeof($listChildVersions)) {
				$resultGroupNameFusionForge = db_query_params('select groups.group_name from groups,plugin_mantisbt where groups.group_id = plugin_mantisbt.id_group and plugin_mantisbt.id_mantisbt = $1',
										array($child));
				$rowGroupNameFusionForge =& db_fetch_array($resultGroupNameFusionForge);
				echo '<div id="childVersion'.$child.'" style="float: left;margin-right: 10px; width: 145px; height: 140px;">';
				echo '<h4 style="border-bottom: 1px solid #DAE0EA; width: 145px;">'.$rowGroupNameFusionForge['group_name'].'</h4>';
				echo '<select name="project'.$child.'VersionId[]" id="project'.$child.'VersionId" multiple style="width: 145px; height: 100px">';
				foreach ( $listChildVersions as $key => $version ) {
					echo '<option value="'.$version->id.'"';
					if (isset($_POST['project'.$child.'VersionId'])) {
					$flipped_projectVersionId = array_flip($_POST['project'.$child.'VersionId']);
					if (isset($flipped_projectVersionId[$version->id])) {
						echo 'selected="selected"';
					}
					}
					echo '>'.$version->name;
					if ( $version->released ) {
					echo '(<i>'._('Release').'</i>)';
					} else {
					echo '(<i>'._('Milestone').'</i>)';
					}
					echo '</option>';
				}
				echo '</select>';
				echo '</div>';
				$nbblock++;
				if ( $nbblock == 7 ) {
					echo '<div style="clear:both; width:100%"></div>';
					$nbblock = 0;
				}
			}
		}
	}

	echo <<< EOT
	<script>
		function reinit()
		{
		if (document.getElementById("projectVersionId"))
			document.getElementById("projectVersionId").selectedIndex = -1;
EOT;
	if (sizeof($listChild)) {
		foreach ($listChild as $key => $child) {
			echo 'if (document.getElementById("project'.$child.'VersionId"))
				document.getElementById("project'.$child.'VersionId").selectedIndex = -1;';
		}
	}
	echo <<< EOT
		document.getElementById("mainform").submit();
		}
	</script>
EOT;
	echo '<br/><div style="clear:both;width:100%; text-align: right">'.
	'<input type="button" value="'._('Clear filter').'" onclick="reinit();"/> '.
	'<input type="submit" value="'._('Apply filter').'" /></div>';
	echo '</form>';
}

?>
