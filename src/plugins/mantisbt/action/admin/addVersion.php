<?php
/*
 * Copyright 2010, Franck Villaume - Capgemini
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

/* addVersion action page */
if (isset($_POST['version']) && !empty($_POST['version'])) {
	$versionStruct = array();
	$versionStruct['name'] = $_POST['version'];
	$versionStruct['project_id'] = $idProjetMantis;
	$versionStruct['released'] = '';
	$versionStruct['description'] = '';
	$versionStruct['date_order'] = '';
	try {
		$clientSOAP = new SoapClient(forge_get_config('server_url','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$clientSOAP->__soapCall('mc_project_version_add', array("username" => $username, "password" => $password, "version" => $versionStruct));
		if (isset($_POST['transverse'])) {
			$listChild = $clientSOAP->__soapCall('mc_project_get_subprojects', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));
			foreach ($listChild as $key => $child) {
				$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $child));
				$todo = 1;
				foreach ($listVersions as $key => $version ) {
					if ($version->name == $versionStruct['name'])
						$todo = 0;
				}
				if ($todo) {
					try {
						$versionStruct['project_id'] = $child;
						$clientSOAP->__soapCall('mc_project_version_add', array("username" => $username, "password" => $password, "version" => $versionStruct));
					} catch (SoapFault $soapFault) {
						$msg = _('Task failed:').' '.$versionStruct['name'].' '.$soapFault->faultstring;
						session_redirect('plugins/mantisbt/?type=admin&id='.$id.'&pluginname=mantisbt&error_msg='.urlencode($msg));
					}
				}
			}
		}
	} catch (SoapFault $soapFault) {
		$msg = _('Task failed:').' '.$versionStruct['name'].' '.$soapFault->faultstring;
		session_redirect('plugins/mantisbt/?type=admin&id='.$id.'&pluginname=mantisbt&error_msg='.urlencode($msg));
	}
	$feedback = _('Task succeeded');
	session_redirect('plugins/mantisbt/?type=admin&id='.$id.'&pluginname=mantisbt&feedback='.urlencode($feedback));
}
$warning_msg = _('Missing version');
session_redirect('plugins/mantisbt/?type=admin&id='.$id.'&pluginname=mantisbt&warning_msg='.urlencode($warning_msg));
?>
