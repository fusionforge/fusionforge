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

$version_name = $_POST['version_name'];
$version_date_order = $_POST['version_date_order'];
$version_release = $_POST['version_release'];
$version_id = $_POST['version_id'];

$version_data = array();
if ( $version_release == 1 ) {
	$version_data['released'] = 1;
} else {
	$version_data['released'] = 0;
}
$version_data['project_id'] = $idProjetMantis;
$version_data['name'] = $version_name;
list($day, $month, $year) = split('[/.-]', $version_date_order);
$version_data['date_order'] = $month."/".$day."/".$year;
$version_data['description'] = '';

$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
$clientSOAP->__soapCall('mc_project_version_update', array("username" => $username, "password" => $password, "version_id" => $version_id, "version" => $version_data));

?>
