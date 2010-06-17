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

$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
$data = file_get_contents ($_FILES['attachment']['tmp_name'] );
$content = base64_encode ($data);
$clientSOAP->__soapCall('mc_issue_attachment_add', array("username" => $username, "password" => $password, "issue_id" => $idBug, "name" => $_FILES['attachment']['name'], "file_type" => $_FILES['attachment']['type'], "content" => $content ));

?>
