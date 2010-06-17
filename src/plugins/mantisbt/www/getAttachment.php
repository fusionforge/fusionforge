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

$no_gz_buffer=true;

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'ldap/ldapUtils.php';
require_once $gfconfig.'plugins/mantisbt/config.php';


$arr=explode('/',getStringFromServer('REQUEST_URI'));
$idAttachment=$arr[4];

$user = session_get_user(); // get the session user

if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
        exit_error("Invalid User", "Cannot Process your request for this user.");
}

$password = getPasswordFromLDAP($user);
$username = $user->getUnixName();

if ($idAttachment) {
	$clientSOAP = new SoapClient("http://$sys_mantisbt_host/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
	$content = $clientSOAP->__soapCall('mc_issue_attachment_get', array("username" => $username, "password" => $password, "issue_attachment_id" => $idAttachment));
	$data = unserialize($content);
	header( 'Content-Type: ' . $data['file_type'] );
	header( 'Content-Disposition: filename="'.urlencode($data['filename']).'"' );
	echo base64_decode($data['payload']);
} else {
	exit_error("No idAttachment", "Cannot process your request");
}

?>
