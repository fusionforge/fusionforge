<?php
/*
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

/* addCategory action page */
global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;

$nameCategory = $_POST['nameCategory'];

if (!empty($nameCategory)) {
	try {
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$clientSOAP->__soapCall('mc_project_add_category', array("username" => $username, "password" => $password, "p_project_id" => $mantisbtConf['id_mantisbt'], "p_category_name" => $nameCategory));
	} catch (SoapFault $soapFault) {
		$error_msg = _('Task failed:').' '.$soapFault->faultstring;
		session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&error_msg='.urlencode($error_msg));
	}
	$feedback = _('Category added successfully');
	session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&feedback='.urlencode($feedback));
}
$warning_msg = _('Missing category name');
session_redirect('plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&warning_msg='.urlencode($warning_msg));
?>
