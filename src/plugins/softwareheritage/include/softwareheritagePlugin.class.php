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
	}

	public function register_soap(&$params) {
		$server = &$params['server'];
		$uri = 'http://'.forge_get_config('web_host');

		$server->wsdl->addComplexType(
			'SoftwareheritageEntry',
			'complexType',
			'struct',
			'sequence',
			'',
			array(
				'group_id' => array('name'=>'group_id', 'type' => 'xsd:int'),
				'section' => array('name'=>'section', 'type' => 'xsd:string'),
				'ref_id' => array('name'=>'ref_id', 'type' => 'xsd:string'),
				'subref_id' => array('name'=>'subref_id', 'type' => 'xsd:string'),
				'description' => array('name'=>'description', 'type' => 'xsd:string'),
				'activity_date' => array('name'=>'activity_date', 'type' => 'xsd:int')
				)
			);

		$server->wsdl->addComplexType(
			'ArrayOfSoftwareheritageEntry',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:SoftwareheritageEntry[]')),
			'tns:SoftwareheritageEntry');
               
		$server->register(
			'softwareheritage_getActivity',
			array('session_ser'=>'xsd:string',
				  'begin'=>'xsd:int',
				  'end'=>'xsd:int',
				  'show'=>'tns:ArrayOfstring',),
			array('return'=>'tns:ArrayOfSoftwareheritageEntry'),
			$uri,
                       $uri.'#softwareheritage_getActivity','rpc','encoded');
	}


	function &softwareheritage_getActivity($session_ser,$begin,$end,$show=array()) {
		continue_session($session_ser);

		$plugin = plugin_get_object('softwareheritage');
		if (!forge_get_config('use_activity')
			|| !$plugin) {
			return new soap_fault ('','softwareheritage_getActivity','Software Heritage not available','Software Heritage not available');
		}

		$ids = array();
		$texts = array();
       
		$results = $plugin->getData($begin,$end,$show,$ids,$texts);

		$keys = array(
			'group_id',
			'section',
			'ref_id',
			'subref_id',
			'description',
			'activity_date',
			);


		$res2 = array();
		foreach ($results as $res) {
			$r = array();
               
			foreach ($keys as $k) {
				$r[$k] = $res[$k];
			}
			$res2[] = $r;
		}

		return $res2;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
