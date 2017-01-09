<?php

/**
 * repositoryapiPlugin Class
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

class repositoryapiPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "repositoryapi";
		$this->text = "Repository API"; // To show in the tabs, use...
		$this->_addHook('register_soap');
	}

	public function register_soap(&$params) {
		$server = &$params['server'];
		$uri = 'http://'.forge_get_config('web_host');

		$server->wsdl->addComplexType(
			'RepositoryAPIRepositoryInfo',
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
			'ArrayOfRepositoryAPIRepositoryInfo',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:RepositoryAPIRepositoryInfo[]')),
			'tns:RepositoryAPIRepositoryInfo');
               
		$server->register(
			'repositoryapi_repositoryList',
			array('session_ser'=>'xsd:string',
				  'limit'=>'xsd:int',
				  'offset'=>'xsd:int',
				),
			array('return'=>'tns:ArrayOfRepositoryAPIRepositoryInfo'),
			$uri,
                       $uri.'#repositoryapi_repositoryList','rpc','encoded');

		$server->register(
			'repositoryapi_repositoryInfo',
			array('session_ser'=>'xsd:string',
				'repository_id'=>'xsd:string'),
			array('return'=>'tns:RepositoryAPIRepositoryInfo'),
			$uri,
                       $uri.'#repositoryapi_repositoryInfo','rpc','encoded');

		$server->wsdl->addComplexType(
			'RepositoryAPIActivity',
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
			'ArrayOfRepositoryAPIActivity',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:RepositoryAPIActivity[]')),
			'tns:RepositoryAPIActivity');
               
		$server->register(
			'repositoryapi_repositoryActivity',
			array('session_ser'=>'xsd:string',
				  't0'=>'xsd:int',
				  't1'=>'xsd:int',
				  'limit'=>'xsd:int',
				  'offset'=>'xsd:int',
				),
			array('return'=>'tns:ArrayOfRepositoryAPIActivity'),
			$uri,
                       $uri.'#repositoryapi_repositoryActivity','rpc','encoded');

	}


}

function &repositoryapi_repositoryList($session_ser, $limit=0, $offset=0) {
	continue_session($session_ser);

	$maxnum = 1000;
	if ($limit > $maxnum || $limit <= 0) {
		$limit = $maxnum;
	}

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
	$res = $res2;

	$skipped = 0;
	$res2 = array();
	foreach ($res as $r) {
		if (count($res2) >= $limit) {
			break;
		}
		if ($skipped >= $offset) {
			$res2[] = $r;
		} else {
			$skipped = $skipped + 1;
		}
	}
	$res = $res2;

	return $res2;
}

function &repositoryapi_repositoryInfo($session_ser, $repository_id) {
	continue_session($session_ser);

	$params = array();
	$results = NULL;
	$params['repository_id'] = $repository_id;
	$params['results'] = &$results;
	plugin_hook('get_scm_repo_info',$params);

	if ($params['results'] == NULL) {
		$sf = new soap_fault('','repositoryapi_repositoryInfo',_('Error when fetching repository info'),_('Error when fetching repository info'));
		return $sf;
	}

	return $params['results'];
}

function &repositoryapi_repositoryActivity($session_ser, $t0, $t1, $limit=0, $offset=0) {
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
	$maxnum = 1000;
	if ($limit > $maxnum || $limit <= 0) {
		$limit = $maxnum;
	}

	$results = array();
	$res = db_query_params("SELECT tstamp, repository_id, group_id FROM scm_activities WHERE tstamp BETWEEN $1 AND $2 ORDER BY tstamp, group_id, repository_id",
						   array($t0,
								 $t1,
							   ));
	$skipped = 0;
	while ((count($results) < $limit) && ($arr = db_fetch_array($res))) {
		if (!forge_check_perm('scm',$arr['group_id'],'read')) {
			continue;
		}

		if ($skipped >= $offset) {
			$results[] = array('timestamp' => $arr['tstamp'],
							   'repository_id' => $arr['repository_id'],
							   'group_id' => $arr['group_id']);
		} else {
			$skipped = $skipped + 1;
		}
	}

	return $results;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
