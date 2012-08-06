<?php

/**
 * This file is (c) Copyright 2009 by Madhumita DHAR, Olivier BERGER,
 * Institut TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

/* $Id$ */

$model_dir = APPLICATION_PATH.'/models/';
require_once($model_dir . 'ChangeRequests.php');


/**
 * For FusionForge
 *
 * @package FusionForgeModel
 *
 * @TODO: Replace helios_bt by OSLC ontology
 */

/**
 * Example of a FusionForge ChangeRequest that extends its model
 *
 *  Adds a status with the helios_bt ontology (fictional)
 */
class FusionForgeChangeRequest extends ChangeRequest
{
	/**
	 * Adds helios_bt:status as mandatory
	 *
	 * @var array
	 */
	protected $_mandatory = array('title','identifier','helios_bt:status');

	protected $_optional = array('description','creator','modified',);

	// may then add a status ?
	//private $_status;


	/**
	 * Create from XML (RDF) OSLC-CM document
	 *
	 * @param string $xmlstr
	 * @return ChangeRequest
	 *
	 * TODO: Replace with other semantic rdf parser, ex: PHP-ARC2
	 */
	public static function CreateFusionForgeArrayFromXml($xmlstr)
	{
		// will contain an array of fields read in the oslc:ChangeRequest
		$resource = null;

		// we use simplexml PHP library which supports namespaces

		/*******Sample CR*****************************************
		 *
		 * <?xml version="1.0"?>
		 * <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
		 *   <oslc_cm:ChangeRequest xmlns:oslc_cm="http://open-services.net/xmlns/cm/1.0/">
		 *       <dc:title xmlns:dc="http://purl.org/dc/terms/">Provide import</dc:title>
		 *       <dc:identifier xmlns:dc="http://purl.org/dc/terms/">1234</dc:identifier>
		 *       <dc:type xmlns:dc="http://purl.org/dc/terms/">http://myserver/mycmapp/types/Enhancement</dc:type>
		 *       <dc:description xmlns:dc="http://purl.org/dc/terms/">Implement the system's import capabilities.</dc:description>
		 *       <dc:subject xmlns:dc="http://purl.org/dc/terms/">import</dc:subject>
		 *       <dc:creator xmlns:dc="http://purl.org/dc/terms/">mailto:aadams@someemail.com</dc:creator>
		 *       <dc:modified xmlns:dc="http://purl.org/dc/terms/">2008-09-16T08:42:11.265Z</dc:modified>
		 *   </oslc_cm:ChangeRequest>
		 * </rdf:RDF>
		 *
		 */

		$dc_attr = array("title", "identifier", "description","creator","modified","created");
		$fusionforgebt_attr = array("status","priority", "assigned_to");

		$xml = simplexml_load_string($xmlstr);

		$namespace = $xml->getNamespaces(true);
		/*$namespace = array(
			'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
			'oslc_cm' => 'http://open-services.net/xmlns/cm/1.0/',
		    'mantisbt' => 'http://www.mantisbt.org/xmlns/mantisbt/',
		    'dc' => 'http://purl.org/dc/terms/'
		);*/
		if ( ($namespace['rdf'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#') &&
			 ($xml->getName() == 'RDF'))
		{
			foreach ($xml->children('http://open-services.net/xmlns/cm/1.0/') as $changerequest) {
				if ($changerequest->getName() == 'ChangeRequest') {
					$resource = array();
					foreach ($changerequest->children('http://purl.org/dc/terms/') as $child) {
						$field = $child->getName();
						if (in_array($field,$dc_attr))
						{
							$value = (string)$child;
							$resource[$field] = $value;
						}
					}
					//TODO: Replace helios_bt by OSLC ontology
					foreach ($changerequest->children('http://heliosplatform.sourceforge.net/ontologies/2010/05/helios_bt.owl') as $child) {
						$field = $child->getName();
						if(!$field){
							print('No ontology attribute !!!');
						}
						if (in_array($field,$fusionforgebt_attr))
						{
							$value = (string)$child;
							$resource[$field] = $value;
						}
					}
					foreach ($changerequest->children('http://open-services.net/ns/core#') as $child) {
						$field = $child->getName();
						if(!$field){
							print('No ontology attribute !!!');
						}
						if (in_array($field,$fusionforgebt_attr)) {
							$value = (string)$child;
							$resource[$field] = $value;
						}
					}
					foreach ($changerequest->children('http://open-services.net/ns/cm#') as $child) {
						$field = $child->getName();
						if(!$field){
							print('No ontology attribute !!!');
						}
						if (in_array($field,$fusionforgebt_attr)) {
							$value = (string)$child;
							$resource[$field] = $value;
						}
					}
				}
			}
		}

		$changerequest = new FusionForgeChangeRequest();

		// initialize the ChangeRequest attributes
		foreach ($resource as $field => $value) {
			$changerequest->container[$field] = $value;
		}
		//print_r($xml);
		//print_r($resource);
		//print_r($changerequest);

		return $changerequest;
	}

	public static function CreateFusionForgeArrayFromJson($jsonstr) {
		$resource = Zend_Json::decode($jsonstr);

		$changerequest = new FusionForgeChangeRequest();

		// the dublin core elements prefix is removed

		foreach ($resource as $field => $value) {
			$field = str_replace('dcterms:', '', $field);
			$field = str_replace('helios_bt:', '', $field);
			$field = str_replace('oslc_cm:', '', $field);

			$changerequest->container[$field] = $value;
		}

		return $changerequest;
	}

}


// Represents a base of changerequests loaded from FusionForge DB
class ChangeRequestsFusionForgeDb extends ChangeRequests
{
	// created out of fusionforge query results ArtifactFactory::getArtifacts()
	function __construct($art_arr, $fields='')
	{
		parent::__construct();

		$changerequestsdata = $this->convert_artifacts_array($art_arr, $fields);
		foreach ($changerequestsdata as $identifier => $data) {
			$this->_data[$identifier] = ChangeRequest::Create('fusionforge');
			$this->_data[$identifier] = $data;
		}
	}

	/*
	 *
	 * Maps fusionforge tracker fields to ontologies (dc, oslc_cm, oslc, helios_bt, etc)
	 *
	 */
	protected static function convert_artifacts_array($at_arr, $fields_string) {
		$FusionForgeCR_attr = array('artifact_id','group_artifact_id','status_id','priority','submitted_by','assigned_to','open_date','close_date',
			'summary','details','assigned_unixname','assigned_realname','assigned_email','submitted_unixname','submitted_realname','submitted_email',
			'status_name','last_modified_date');

		$return = array();

		if (is_array($at_arr) && count($at_arr) > 0) {
			for ($i=0; $i <count($at_arr); $i++) {
				if ($at_arr[$i]->isError()) {
					//skip if error
				} else {
					//NEEDS THOROUGH COMMENTS AND EXPLANATION
					//***********
					// Retrieving the artifact details
					//**checks whether there is any artifact details exists for this object, if not continue with next loop
					if(count($at_arr[$i]) < 1) { continue; }

					$flddata=array();
					$fldelementdata=array();
					$extrafieldsdata=array();
					$extrafieldsdata=$at_arr[$i]->getExtraFieldData();

					//********
					//** Retrieving the extra field data and the element data
					//** checks whether there is any extra fields data available for this artifact
					//** and checks for the extra element data for the multiselect and checkbox type
					if(is_array($extrafieldsdata) && count($extrafieldsdata)>0) {
						while(list($ky,$vl)=each($extrafieldsdata)) {
							$fldarr=array();
							if(is_array($extrafieldsdata[$ky])) {
								//** Retrieving the multiselect and checkbox type data element
								$fldarr=array('extra_field_id'=>$ky,'field_data'=>implode(",",$extrafieldsdata[$ky]));
							} else {
								//** Retrieving the extra field data
								$fldarr=array('extra_field_id'=>$ky,'field_data'=>$vl);
							}
							$flddata[]=$fldarr;
							unset($fldarr);
						}
					}

					$identifier = $at_arr[$i]->data_array['artifact_id'];

					// If specific fields were requested using a query
					// we only return the requested fields data in the change request.
					if(is_array($fields_string)){
						$fields = $fields_string;
					} else {
						if (strlen($fields_string) > 0) {
							$fields = explode(",", $fields_string);
						}
					}

					if(isset($fields) && is_array($fields) && count($fields) > 0){
						foreach ($fields as $field) {
							switch ($field) {
								case 'dcterms:identifier':
									$return[$identifier]['identifier'] = $identifier;
									break;
								case 'dcterms:title':
									$return[$identifier]['title'] = $at_arr[$i]->data_array['summary'];
									break;
								case 'dcterms:description':
									$return[$identifier]['description'] = $at_arr[$i]->data_array['details'];
									break;
								case 'dcterms:creator':
									$return[$identifier]['creator'] = $at_arr[$i]->data_array['submitted_realname'];
									break;
								case 'oslc_cm:status':
									$return[$identifier]['oslc_cm:status'] = $at_arr[$i]->data_array['status_name'];
									break;
								case 'helios_bt:priority':
									$return[$identifier]['helios_bt:priority'] = $at_arr[$i]->data_array['priority'];
									break;
								case 'helios_bt:assigned_to':
									$return[$identifier]['helios_bt:assigned_to'] = $at_arr[$i]->data_array['assigned_realname'];
									break;
								case 'dcterms:modified':
									$return[$identifier]['modified'] = $at_arr[$i]->data_array['last_modified_date'];
									break;
								case 'dcterms:created':
									$return[$identifier]['created'] = $at_arr[$i]->data_array['open_date'];
									break;
								default:
									throw new ConflictException("The attribute specified ".$field." cannot be found!");
							}
						}
					} else {
						//return the by default set of Change request fields.
						$return[$identifier]=array(
							'identifier'=>$identifier,
							'title'=>$at_arr[$i]->data_array['summary'],
							'description'=>$at_arr[$i]->data_array['details'],
							'oslc_cm:status'=>$at_arr[$i]->data_array['status_name'],
							'helios_bt:priority'=>$at_arr[$i]->data_array['priority'],
							'creator' => $at_arr[$i]->data_array['submitted_realname'],
							'helios_bt:assigned_to' => $at_arr[$i]->data_array['assigned_realname'],
							'modified' => $at_arr[$i]->data_array['last_modified_date'],
							'created' => $at_arr[$i]->data_array['open_date']
						);
					}
				}
			}
		}
		return $return;
	}

}
