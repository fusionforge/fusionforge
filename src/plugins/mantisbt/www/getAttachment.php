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
require_once $gfconfig.'plugins/mantisbt/config.php';

$arr=explode('/',getStringFromServer('REQUEST_URI'));
$group_id = $arr[4];
$idAttachment=$arr[5];

$user = session_get_user(); // get the session user

if (!$user || !is_object($user)) {
        exit_error(_('Invalid User'), 'mantisbt');
} else if ( $user->isError() ) {
        exit_error($user->isError(), 'mantisbt');
} else if ( !$user->isActive()) {
        exit_error(_('Invalid User not active'), 'mantisbt');
}

$group = group_get_object($group_id);
$mantisbt = plugin_get_object('mantisbt');

if (!$group) {
	exit_no_group();
}
if (!$group->usesPlugin($mantisbt->name)) {//check if the group has the MantisBT plugin active
	exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'), $mantisbt->name), 'home');
}
if ( $group->isError()) {
	$error_msg .= $group->getErrorMessage();
}

$userperm = $group->getPermission($user);//we'll check if the user belongs to the group (optional)
if ( !$userperm->IsMember()) {
	exit_permission_denied(_('You are not a member of this project'), 'home');
}


$mantisbtConf = $mantisbt->getMantisBTConf();

if (!$mantisbtConf['sync_users']) {
	$username = $mantisbtConf['soap_user'];
	$password = $mantisbtConf['soap_password'];
}

if ($idAttachment) {
	try {
		$clientSOAP = new SoapClient($mantisbtConf['url']."/api/soap/mantisconnect.php?wsdl", array('trace'=>true, 'exceptions'=>true));
		$content = $clientSOAP->__soapCall('mc_issue_attachment_get', array("username" => $username, "password" => $password, "issue_attachment_id" => $idAttachment));
	} catch (SoapFault $soapFault) {
		session_redirect('plugins/mantisbt/?type=group&group_id='.$group_id.'&pluginname=mantisbt&error_msg='.urlencode($soapFault->faultstring));
	}

	header( 'Content-Disposition: filename="'.urlencode($arr[6]).'"' );
	// filetype is missing.... now.... so we force application/binary
	header('Content-type: application/binary');
	echo base64_decode($content);
} else {
	if (forge_get_config('use_ssl'))
		$url = "https://";
	else
		$url = "http://";

	$url .= forge_get_config('web_host');
	exit_missing_param(substr($_SERVER['HTTP_REFERER'], strlen($url)), array(_('No idAttachment')), 'mantisbt');
}
?>
