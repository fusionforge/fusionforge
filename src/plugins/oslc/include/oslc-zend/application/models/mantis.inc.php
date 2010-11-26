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
 * Example of a FusionForge ChangeRequest that extends its model
 * 
 *  Adds a status with the helios_bt ontology (fictional)
 */
class MantisChangeRequest extends ChangeRequest
{
	/**
	 * Adds helios_bt:status as mandatory
	 * 
	 * @var array
	 */
	protected $_mandatory = array('title','identifier','helios_bt:status');

	protected $_optional = array('type','description','subject','creator','modified','mantisbt:project');
	
	// may then add a status ?
	//private $_status;
	
	/**
	 * Create from XML (RDF) OSLC-CM document
	 *
	 * @param string $xmlstr
	 * @return ChangeRequest
	 */
	public static function CreateMantisArrayFromXml($xmlstr)
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
		
		$dc_attr = array("title", "identifier", "type", "description","subject","creator","modified","name","created");
		$mantisbt_attr = array("severity","status","priority","branch","version", "target_version","version_number","notes");

		$xml = simplexml_load_string($xmlstr);
		//print_r($xml);
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
					$x = 0;
					foreach ($changerequest->children($namespace['mantisbt']) as $child) {
						$field = $child->getName();
						if (in_array($field,$mantisbt_attr))
						{
							$value = (string)$child;
							if(strcasecmp($field,"notes")==0) //creating array for storing mantisbt notes
							{
								if($x==0)
								{
									$resource[$field] = array();
								}
								$resource[$field][$x++] = $value;
							}
							else
							{
								$resource[$field] = $value;	
							}
						}
					}
				}
			}
		}

		$changerequest = new MantisChangeRequest();

		// initialize the ChangeRequest attributes
		foreach ($resource as $field => $value) {
			$changerequest->container[$field] = $value;
		}
		//print_r($xml);
		//print_r($resource);
		//print_r($changerequest);

		return $changerequest;
	}
	
	public static function CreateMantisArrayFromJson($jsonstr)
	{
		//print_r($jsonstr);
		$resource = Zend_Json::decode($jsonstr);

		$changerequest = new MantisChangeRequest();

		// the dublin core elements prefix is removed
		
		foreach ($resource as $field => $value) {
			if($field=="mantisbt:notes")
			{
				$value = self::CreateMantisNotesArrayFromJson($jsonstr);
			}
			$field = str_replace('dc:', '', $field);
			$field = str_replace('mantisbt:', '', $field);
			
			$changerequest->container[$field] = $value;
		}

		return $changerequest;
	}
	
	public static function CreateMantisNotesArrayFromJson($jsonstr)
	{
		Zend_Json::decode($jsonstr); //to check for well-formed json
		preg_match_all("/\"mantisbt:notes\":\"[^\"]+/", $jsonstr, $matches);
		$notes = array();
		foreach ($matches[0] as $note)
		{
			$notes[] = preg_replace("/\"mantisbt:notes\":\"/", "", $note);
		}
		return $notes;
	}
	
	/**
	 * Create an array of bugnotes from XML (RDF)
	 *
	 * @param string $xmlstr
	 * @return array containing bug note values
	 */
	public static function CreateMantisNotesArrayFromXml($xmlstr)
	{
		// will contain an array of mantisbt:notes
		$resource = null;

		$xml = simplexml_load_string($xmlstr);
		$namespace = $xml->getNamespaces(true);
		
		if ( ($namespace['rdf'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#') &&
			 ($xml->getName() == 'RDF')) 
		{		
			foreach ($xml->children('http://open-services.net/xmlns/cm/1.0/') as $changerequest) {
				if ($changerequest->getName() == 'ChangeRequest') {
					$resource = array();
					
					$x = 0;
					foreach ($changerequest->children($namespace['mantisbt']) as $child) {
						$field = $child->getName();
						//print($field);
						
						if (strcasecmp($field,"notes")==0)
						{
							$value = (string)$child;
							$resource[$x] = $value;
							$x++;
							
						}
					}
				}
			}
		}

		return $resource;
	}
	
}

// Represents a base of changerequests loaded from FusionForge DB
class ChangeRequestsMantisDb extends ChangeRequests
{

	private static $status_arr = array(10=>'new', 20=>'feedback', 30=>'acknowledged', 40=>'confirmed', 50=>'assigned', 80=>'resolved', 90=>'closed');
	
	private static $priority_arr = array(10=>'none', 20=>'low', 30=>'normal', 40=>'high', 50=>'urgent', 60=>'immediate');
	
	private static $severity_arr = array(10=>'feature', 20=>'trivial', 30=>'text', 40=>'tweak', 50=>'minor', 60=>'major', 70=>'crash', 80=>'block');
	
	/**
	 * @param array $rows_arr as returned by filter_get_bug_rows() in Mantis internal API
	 */
	function __construct($rows_arr, $fields = "")
	{
		parent::__construct();

		$changerequestsdata = $this->convert_rows_array($rows_arr, $fields);
		foreach ($changerequestsdata as $identifier => $data) {
			$this->_data[$identifier] = ChangeRequest::Create('mantis');
			$this->_data[$identifier] = $data;
		}

	}

	/**
	 * converts data as returned by filter_get_bug_rows() in Mantis internal API to array
	 * @param array $rows_arr
	 * @return array of arrays
	 * version_full_name($row->version,$row->project_id,$row->project_id),
	 */
	protected static function convert_rows_array($rows_arr, $fieldstring) {

		$return = array();

		if (is_array($rows_arr) && count($rows_arr) > 0) {
			for ($i=0; $i<count($rows_arr); $i++) {

				$row = $rows_arr[$i];
				
				if(count($row) < 1) { continue; }

				//print_r($rows_arr[$i]);

				$identifier = $row->id;
				
				$v_num_id = custom_field_get_id_from_name("version_number");
				$return[$identifier]=array();
				$fields = explode(",", $fieldstring);
				//print_r($fieldstring);
				$custom_field_array = custom_field_get_linked_ids($row->project_id);
				
				if(empty($fieldstring))	{
					//mandatory attributes
					$return[$identifier]=array(
						'identifier'=>$identifier,
						'title'=>$row->summary,
						'description'=>$row->description,
						'creator'=>user_get_name($row->reporter_id),
						'mantisbt:project'=>project_get_name($row->project_id),
						'mantisbt:status'=>self::$status_arr[$row->status],
						'mantisbt:priority'=>self::$priority_arr[$row->priority],
						'mantisbt:severity'=>self::$severity_arr[$row->severity],
						'modified'=>date(DATE_ATOM,$row->last_updated),
						'created'=>date(DATE_ATOM,$row->date_submitted)
						);
						
					$temp_arr = version_get_all_rows($row->project_id);
					if(!empty($temp_arr))	{
						if($row->version!="")	{
							$return[$identifier]['mantisbt:version'] = $row->version;
						}
						if($row->target_version!="")	{
							$return[$identifier]['mantisbt:target_version'] = $row->target_version;
						}
					}						
						
					if ($v_num_id) {
						$v_num = custom_field_get_value( $v_num_id, $identifier );
						if($v_num!="")	{
							$return[$identifier]['mantisbt:version_number'] = $v_num;
						}						
					}
					 
				}else {
					foreach ($fields as $field)	{
						switch($field)	{
							case 'dc:identifier': $return[$identifier]['identifier'] = $identifier;
								break;
							case 'dc:title': $return[$identifier]['title'] = $row->summary;
								break;
							case 'dc:description': $return[$identifier]['description'] = $row->description;
								break;
							case 'dc:creator': $return[$identifier]['creator'] = user_get_name($row->reporter_id);
								break;
							case 'mantisbt:project': $return[$identifier]['mantisbt:project'] = project_get_name($row->project_id);
								break;
							case 'mantisbt:status': $return[$identifier]['mantisbt:status'] = self::$status_arr[$row->status];
								break;
							case 'mantisbt:priority': $return[$identifier]['mantisbt:priority'] = self::$priority_arr[$row->priority];
								break;
							case 'mantisbt:severity': $return[$identifier]['mantisbt:severity'] = self::$severity_arr[$row->severity];
								break;
							case 'mantisbt:version': $return[$identifier]['mantisbt:version'] = $row->version;
								break;
							case 'mantisbt:target_version': $return[$identifier]['mantisbt:target_version'] = $row->target_version;
								break;
							case 'dc:modified': $return[$identifier]['modified'] = date(DATE_ATOM,$row->last_updated);
								break;
							case 'dc:created': $return[$identifier]['created'] = date(DATE_ATOM,$row->date_submitted);
								break;
							case 'mantisbt:version_number': 	if ($v_num_id) {
					  				$v_num = custom_field_get_value( $v_num_id, $identifier );
					  				$return[$identifier]['mantisbt:version_number'] = $v_num;
								}else	{
									throw new ConflictException("Version number attribute doesnt exist!");
								}
								break;
							default: throw new ConflictException("The attribute specified ".$field." cannot be found!");
						}
					}
								
				}
				
			}
		}
		return $return;
	}

}
