<?php

/*
 * Copyright 2010, Capgemini
 * Authors: Franck Villaume - capgemini
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

$data = file_get_contents ($_FILES['attachment']['tmp_name'] );
$content = base64_encode ($data);
try {
    $clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
    $clientSOAP->__soapCall('mc_issue_attachment_add', array("username" => $username, "password" => $password, "issue_id" => $idBug, "name" => $_FILES['attachment']['name'], "file_type" => $_FILES['attachment']['type'], "content" => $content ));
    $feedback = 'Op&eacute;ration r&eacute;ussie';
} catch (SoapFault $soapFault) {
    $feedback = 'Error : '.$soapFault->faultstring;
    session_redirect('plugins/mantisbt/?type=group&id='.$id.'&pluginname=mantisbt&idBug='.$idBug.'&view=viewIssue&error_msg='.urlencode($feedback));
}
session_redirect('plugins/mantisbt/?type=group&id='.$id.'&pluginname=mantisbt&idBug='.$idBug.'&view=viewIssue&feedback='.urlencode($feedback));
?>
