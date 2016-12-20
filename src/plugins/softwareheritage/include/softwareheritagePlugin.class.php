<?php

/**
 * softwareheritagePlugin Class
 *
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

class softwareheritagePlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "softwareheritage";
		$this->text = "Software Heritage"; // To show in the tabs, use...
		$this->_addHook('register_soap');
	}

	public function register_soap(&$params) {
		$server = &$params['server'];
		$uri = 'http://'.forge_get_config('web_host');

		$server->wsdl->addComplexType(
			'SoftwareheritageRepositoryInfo',
			'complexType',
			'struct',
			'sequence',
			'',
			array(
				'group_id' => array('name'=>'group_id', 'type' => 'xsd:int'),
				'repository_id' => array('name'=>'repository_id', 'type' => 'xsd:string'),
				'repository_urls' => array('name'=>'repository_urls', 'type' => 'tns:ArrayOfstring'),
				'repository_type' => array('name'=>'repository_type', 'type' => 'xsd:string'),
				)
			);

		$server->wsdl->addComplexType(
			'ArrayOfSoftwareheritageRepositoryInfo',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:SoftwareheritageRepositoryInfo[]')),
			'tns:SoftwareheritageRepositoryInfo');
               
		$server->register(
			'softwareheritage_repositoryList',
			array('session_ser'=>'xsd:string'),
			array('return'=>'tns:ArrayOfSoftwareheritageRepositoryInfo'),
			$uri,
                       $uri.'#softwareheritage_repositoryList','rpc','encoded');

		$server->register(
			'softwareheritage_repositoryInfo',
			array('session_ser'=>'xsd:string',
				'repository_id'=>'xsd:string'),
			array('return'=>'tns:SoftwareheritageRepositoryInfo'),
			$uri,
                       $uri.'#softwareheritage_repositoryInfo','rpc','encoded');

		$server->wsdl->addComplexType(
			'SoftwareheritageActivity',
			'complexType',
			'struct',
			'sequence',
			'',
			array(
				'group_id' => array('name'=>'group_id', 'type' => 'xsd:int'),
				'repository_id' => array('name'=>'repository_id', 'type' => 'xsd:string'),
				'timestamp' => array('name'=>'timestamp', 'type' => 'xsd:int'),
				)
			);

		$server->wsdl->addComplexType(
			'ArrayOfSoftwareheritageActivity',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:SoftwareheritageActivity[]')),
			'tns:SoftwareheritageActivity');
               
		$server->register(
			'softwareheritage_repositoryActivity',
			array('session_ser'=>'xsd:string',
				  't0'=>'xsd:int',
				  't1'=>'xsd:int'
				),
			array('return'=>'tns:ArrayOfSoftwareheritageActivity'),
			$uri,
                       $uri.'#softwareheritage_repositoryActivity','rpc','encoded');

	}


}

function &softwareheritage_repositoryList($session_ser) {
	continue_session($session_ser);

	$params = array();
	$results = array();
	$params['results'] = &$results;
	plugin_hook('get_scm_repo_list',$params);

	$res2 = array();
	foreach ($results as $res) {
		if (forge_check_perm('scm',$res['group_id'],'read')) {
			$res2[] = $res;
		}
	}

	return $res2;
}

function &softwareheritage_repositoryInfo($session_ser, $repository_id) {
	continue_session($session_ser);

	$params = array();
	$results = NULL;
	$params['repository_id'] = $repository_id;
	$params['results'] = &$results;
	plugin_hook('get_scm_repo_info',$params);

	if ($params['results'] == NULL) {
		$sf = new soap_fault('','softwareheritage_repositoryInfo',_('Error when fetching repository info'),_('Error when fetching repository info'));
		return $sf;
	}

	return $params['results'];
}

function &softwareheritage_repositoryActivity($session_ser, $t0, $t1) {
	continue_session($session_ser);

	if ($t1 < $t0) {
		$t2 = $t1;
		$t1 = $t0;
		$t0 = $t2;
	}
	$maxspan = 86400*31;
	if ($t1 - $t0 > $maxspan) {
		$t0 = $t1 - $maxspan;
	}

	$params = array('t0' => $t0,
					't1' => $t1);
	$results = array();
	$params['results'] = &$results;
	plugin_hook('get_scm_repo_activity',$params);

	$res2 = array();
	foreach ($results as $res) {
		if (forge_check_perm('scm',$res['group_id'],'read')) {
			$res2[] = $res;
		}
	}

	return $res2;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
