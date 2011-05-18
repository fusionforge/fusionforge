<?php
/**
 * SOAP SCM Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2004 (c) GForge, LLC
 * http://gforge.org
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

/**
 * getSCMData
 */
$server->wsdl->addComplexType(
	'GroupSCMData',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
		'type' => array('name' => 'type', 'type' => 'xsd:string'),
		'allow_anonymous' => array('name' => 'allow_anonymous', 'type' => 'xsd:int'),
		'public' => array('name' => 'public', 'type' => 'xsd:int'),
		'box' => array('name' => 'bpx', 'type' => 'xsd:string'),
		'root' => array('name' => 'root', 'type' => 'xsd:string'),
		'module' => array('name' => 'module', 'type' => 'xsd:string'),
		'connection_string' => array('name' => 'connection_string', 'type' => 'xsd:string')
	)
);

$server->register(
	'getSCMData',
	array('session_ser'=>'xsd:string','group_id'=>'xsd:int'),
	array('getSCMDataResponse'=>'tns:GroupSCMData'),
	$uri,
	$uri.'#getSCMData','rpc','encoded'
);

function getSCMData($session_ser, $group_id) {
	$grp = group_get_object($group_id);
	if (!$grp || !is_object($grp)) {
		return new soap_fault ('','getSCMData','Could Not Get Project','Could Not Get Project');
	} elseif ($grp->isError()) {
		return new soap_fault ('','getSCMData',$grp->getErrorMessage(),$grp->getErrorMessage());
	}
	if (!$grp->usesSCM()) {
		return new soap_fault ('','getSCMData','SCM is not enabled in this project');
	}
	
	$res = array();
	//TODO: Get SCM type from plugins
	if ($grp->usesPlugin("scmcvs")) {
		$res["type"] = "CVS";
		$res["allow_anonymous"] = $grp->enableAnonSCM();
		$res["public"] = $grp->enablePserver();
		$res["box"] = $grp->getSCMBox();
		$res["module"] = $grp->getUnixName();
		$res["connection_string"] = "";	// this doesn't apply to CVS
		
		// Note: This was taken from CVS plugin. Maybe we shouldn't hardcode this?
		$res["root"] = "/cvsroot/".$grp->getUnixName();		
	} else if ($grp->usesPlugin("scmsvn")) {
		$res["type"] = "SVN";
		$res["allow_anonymous"] = $grp->enableAnonSCM();
		$res["public"] = $grp->enablePserver();
		$res["box"] = $grp->getSCMBox();
		$res["root"] = $GLOBALS["svn_root"]."/".$grp->getUnixName();
		$res["module"] = "";		// doesn't apply to SVN
		
		// Note: This is an ugly hack. We can't access SVN plugin object for this project
		// directly. Currently this is being rewritten, but for now we must make this.
		include $gfconfig.'plugins/scmsvn/config.php';
		$res["connection_string"] = "http".(($use_ssl) ? "s" : "")."://".$grp->getSCMBox()."/".$svn_root."/".$grp->getUnixName();
	}
	return $res;

}
?>
