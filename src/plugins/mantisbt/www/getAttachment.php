<?php
/*
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

$no_gz_buffer=true;

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'ldap/ldapUtils.php';
require_once $gfconfig.'plugins/mantisbt/config.php';


$arr=explode('/',getStringFromServer('REQUEST_URI'));
$idAttachment=$arr[4];

$user = session_get_user(); // get the session user

if (!$user || !is_object($user)) {
        exit_error(_('Invalid User'),'mantisbt');
} else if ( $user->isError() ) {
        exit_error($user->isError(),'mantisbt');
} else if ( !$user->isActive()) {
        exit_error(_('Invalid User not active'),'mantisbt');
}

$password = getPasswordFromLDAP($user);
$username = $user->getUnixName();

if ($idAttachment) {
	try {
		$clientSOAP = new SoapClient("http://".forge_get_config('server','mantisbt')."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$content = $clientSOAP->__soapCall('mc_issue_attachment_get', array("username" => $username, "password" => $password, "issue_attachment_id" => $idAttachment));
	} catch (SoapFault $soapFault) {
		session_redirect('plugins/mantisbt/?type=group&id='.$id.'&pluginname=mantisbt&error_msg='.urlencode($soapFault->faultstring));
	}

	$data = unserialize($content);
	header( 'Content-Type: ' . $data['file_type'] );
	header( 'Content-Disposition: filename="'.urlencode($data['filename']).'"' );
	echo base64_decode($data['payload']);
} else {
	exit_missing_params($_SERVER['HTTP_REFERER'],array(_('No idAttachment')),'mantisbt');
}
?>
