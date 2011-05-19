<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010, Franck Villaume - Capgemini
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
global $group_id;
global $idBug;

$idAttachment = getIntFromRequest('idAttachment');
if ($idAttachment) {
	try {
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$clientSOAP->__soapCall('mc_issue_attachment_delete', array("username" => $username, "password" => $password, "issue_attachment_id" => $idAttachment));
		$feedback = _('Attachment deleted successfully');
	} catch (SoapFault $soapFault) {
		$error_msg = _('Task failed:').' '.$soapFault->faultstring;
		session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&error_msg='.urlencode($error_msg));
	}
	session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&feedback='.urlencode($feedback));
}
$warning_msg = _('Missing Attachment ID to delete');
session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&view=viewIssue&warning_msg='.urlencode($warning_msg));
?>
