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

global $mantisbt;
global $mantisbtConf;
global $username;
global $password;
global $group_id;
global $idBug;

$data = file_get_contents($_FILES['attachment']['tmp_name'] );
$content = base64_encode($data);
try {
	if (!isset($clientSOAP))
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));

	$clientSOAP->__soapCall('mc_issue_attachment_add', array("username" => $username, "password" => $password, "issue_id" => $idBug, "name" => $_FILES['attachment']['name'], "file_type" => $_FILES['attachment']['type'], "content" => $content ));
	$feedback = _('Task Successed');
} catch (SoapFault $soapFault) {
	$error_msg = _('Task failed:').' '.$soapFault->faultstring;
	session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&error_msg='.urlencode($error_msg));
}
session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&feedback='.urlencode($feedback));
?>
