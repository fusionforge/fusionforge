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

/* update a version action page */
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

$version_id = $_POST['version_id'];

$version_data = array();
if ( $_POST['version_release'] == 1 ) {
	$version_data['released'] = 1;
} else {
	$version_data['released'] = 0;
}
$version_data['project_id'] = $mantisbtConf['id_mantisbt'];
$version_data['name'] = $_POST['version_name'];
$version_data['description'] = $_POST['version_description'];
list($day, $month, $year) = split('[/.-]', $_POST['version_date_order']);
$version_data['date_order'] = $month."/".$day."/".$year;

try {
    $clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
    $clientSOAP->__soapCall('mc_project_version_update', array("username" => $username, "password" => $password, "version_id" => $version_id, "version" => $version_data));
    if (isset($_POST['transverse'])) {
        $listChild = $clientSOAP->__soapCall('mc_project_get_all_subprojects', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));
        foreach ($listChild as $key => $child) {
            $listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $child));
            foreach ($listVersions as $key => $version) {
                if ($version->name == $_POST['version_old_name'])
                    $child_version_id = $version->id;
            }
            $version_data['project_id'] = $child;
            $clientSOAP->__soapCall('mc_project_version_update', array("username" => $username, "password" => $password, "version_id" => $child_version_id, "version" => $version_data));
        }
    }
} catch (SoapFault $soapFault) {
	$error_msg = _('Task failed:').' '.$version_data['name'].' '.$soapFault->faultstring;
	session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&error_msg='.urlencode($error_msg));
}
$feedback = _('Task succeeded');
session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&feedback='.urlencode($feedback));

?>
