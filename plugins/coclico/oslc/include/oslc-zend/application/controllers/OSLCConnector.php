<?php

/**
 * This file is (c) Copyright 2009 by Olivier BERGER, Institut
 * TELECOM
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

/* $Id:$ */

require_once("cql.php");

/**
 * OSLC-CM connector module
 * 
 * This implements the application's controller. It is distinct from the Zend controller
 * to try and become more independant from Zend (think reuse).
 * 
 * @author Olivier Berger <olivier.berger@it-sudparis.eu
 * @package controler
 *
 */

/**
 * OSLC-CM controler base class
 *
 * @abstract
 */
class OSLCConnector {

	/**
	 * Holds a database of OSLC-CM ChangeRequest elements (the model) 
	 * 
	 * @var ChangeRequests
	 */
	protected $changerequests;

	/**
	 * @param array $params unused in base class
	 */
	public function __construct($params=null) {
		$this->changerequests = null;
	}
	
	public function getChangeRequests() {
		return $this->changerequests;
	}
	
	/**
	 * Initialize the model with appropriate parameters (project, etc.)
	 * 
	 * Performs the checks on the parameters provided by Zend and invokes the model as needed.
	 * 
	 * @param array $params parameters as passed by Zend 
	 * @return unknown_type
	 */
	public function init($params=null) {
		
		$modelparams = $this->filterRequestParams($params);

		// take into account the filtering on certain constraints
		//the oslc_cm query parameters need to be url encoded for the params to be correct
		//print_r($params);
		$modelparams['filter'] = array();
		if(array_key_exists('oslc_where', $params)) {
			//print_r($params['oslc_cm_query']);
			$filter=parse_cql(urldecode($params['oslc_where']));
			if($filter) {
				$modelparams['filter']['where']=$filter;
			}
		}
		
		if(array_key_exists('oslc_orderBy', $params))
		{
			$tok = strtok($params['oslc_orderBy'], ",");
			while ($tok !== false) {
    			//echo "Word=$tok ";
    			if(preg_match("/^[+-][a-zA_Z:_]+$/", $tok))	{
    				if(substr($tok, 0, 1)=="+")	{
    					$dir = "ASC";
    				}elseif(substr($tok, 0, 1)=="-") {
    					$dir = "DESC";
    				} 
    				$attr = substr($tok, 1);
    				$modelparams['filter']['orderBy'][] = array($dir, $attr);
    			}
    			else	{
    				throw new BadRequestException("Error in oslc_orderBy syntax");
    			}
    			$tok = strtok(",");
			}
		}
		
		$temp_limit = 0;
		
		if(array_key_exists('oslc_limit', $params))
		{
			//converting to type int or float depending on the value of the value of the param
			$temp_limit = $params['oslc_limit']+0;
			//checking for a positive integer
			if((is_int($temp_limit))&&($temp_limit>0))	{
				$modelparams['filter']['limit']=$params['oslc_limit'];
			}else	{
				throw new BadRequestException("The value for oslc_limit is not a positive integer!");
			}
		}
		
		if(array_key_exists('oslc_offset', $params))
		{
			//converting to type int or float depending on the value of the value of the param
			$temp_offset = $params['oslc_offset']+0;
			//checking for a positive integer 
			if((is_int($temp_offset))&&($temp_offset>0))	{
				//checking that oslc_limit has also been correctly defined
				if(array_key_exists('limit', $modelparams['filter']))	{					
					//offset should be a multiple of limit
					if($temp_offset%$temp_limit==0)	{
						$modelparams['filter']['offset']= ($temp_offset/$temp_limit)+1;
					}else 	{
						throw new ConflictException("oslc_offset should be a multiple of oslc_limit");
					}
				}else 	{
					throw new ConflictException("oslc_offset cannot work without oslc_limit being defined");
				}
			}else	{
				throw new BadRequestException("The value for oslc_limit is not a positive integer!");
			}
		}
		
		// take into account the restriction on values to be returned
		if(array_key_exists('oslc_properties', $params)) {
				$modelparams['fields'] = $params['oslc_properties']; 
		}
		
		if(array_key_exists('oslc_searchTerms', $params)) {
			$tok = strtok($params['oslc_searchTerms'], ",");
			do 	{
    			$tok = str_replace("\"", "", $tok);
    			$modelparams['filter']['searchTerms'][] = $tok;
    			$tok = strtok(",");
			}while ($tok !== false);
		}

		if(array_key_exists('filter', $modelparams) || array_key_exists('fields', $modelparams))
		{
			$this->changeRequestsQuery($modelparams);
		}
		else
		{
			$this->fetchChangeRequests($modelparams);
		}
		//print_r($this->changerequests);
		return $modelparams;
	}

	/**
	 * Instantiate a Zend Auth adapter for HTTP Basic auth
	 * 
	 * It will be responsible of the validation of username and passwords provided
	 * 
	 * By default, use a file containing usernames, realms and passwords
	 * (see docs of Zend_Auth_Adapter_Http_Resolver_File)
	 * 
	 * $login and $password are only there to allow subclassing
	 * 
	 * @param string $login transmitted in request
	 * @param string $password transmitted in request (clear text)
	 * @return Zend_Auth_Adapter_Http_Resolver_Interface
	 */
	public function getHttpAuthBasicResolver($login, $password) {
		// authenticate to .htpasswd-like file
		$basicResolver = new Zend_Auth_Adapter_Http_Resolver_File();
		$basicResolver->setFile(APPLICATION_PATH.'/basic-pwd.txt');
			
		return $basicResolver;

	}

	/**
	 * Retrieves ChangeRequest resources to be sent to the view
	 * 
	 * The format returned is array( 'id' => identifier,
	 * 								 'resource' => array (
	 * 											'fieldname' => value,
	 * 											...))
	 * This format should suit all needs of every views
	 * 
	 * @param string $identifier of the ChangeRequest to be retrieved
	 * @param string $uri to be defined as its id
	 * @return array
	 */
	public function getResource($identifier, $uri=null) {
		$returned = null;

		$changerequest = $this->changerequests[$identifier];

		if (isset($changerequest)) {
			$returned = $this->prepareChangeRequest($changerequest, $uri);
		}

		return $returned;
	}

	/**
	 * Retrieves a list of ChangeRequest resources to be sent to the view
	 * 
	 * @param string $uri
	 * @return array
	 * @TODO: change function name to something like 'formatRessourceCollection'
	 */
	public function getResourceCollection($uri=null)
	{
		
		$returned = array();
		// construct a list of all entries of the feed
		foreach ($this->changerequests as $identifier => $changerequest) {

			$feedentry = $this->prepareChangeRequest($changerequest);

			$feedentry['title'] = 'changerequest '.$identifier.' : '.$feedentry['resource']['dc:title'];
			$feedentry['id']= $uri.$identifier;

			$returned[] = $feedentry;
		}
		return $returned;
	}

	/**
	 * Prepare a ChangeRequest to the format expected by the views
	 * 
	 * It will do any necessary conversions, such as adding proper 
	 * ontology prefixes.
	 * 
	 * The format returned is array( 'id' => identifier,
	 * 								 'resource' => array (
	 * 											'fieldname' => value,
	 * 											...))
	 * This format should suit all needs of every views
	 * 
	 * @param unknown_type $changerequest
	 * @param unknown_type $uri
	 * @return string
	 */
	protected function prepareChangeRequest($changerequest, $uri=null) {
		$preparedChangeRequest = array('resource' => array());
		$dc_attr = array("title", "identifier", "type", "description","subject","creator","modified","name","created");

		foreach ($changerequest as $fieldname => $value) {

			// here, may do some conversions betw model and view
			switch ($fieldname) {
				/*
					case 'creator':
					$feedentry['author'] = $value;
					break;
					*/
				default :
					// construct values with ontology prefix for the OSLC-CM DC fields
					// TODO : use real RDF triples ?
					$tokens = explode(':', $fieldname);
					if( (count($tokens) == 1) && (in_array($fieldname,$dc_attr))) {
						$fieldname = 'dc:'.$fieldname;
					}
					$preparedChangeRequest['resource'][$fieldname] = $value;
					break;
			}
		}

		if (!isset($uri)) {
			$uri = '#';
		}
		$preparedChangeRequest['id'] = $uri;

		return $preparedChangeRequest;

	}
	
	/**
	 * Create a new bug in the model
	 * @param ChangeRequest $cm_request
	 * @return unknown_type
	 */
	/*public function createChangeRequest($cm_request)
	{
		$identifier = -1;
		$cm_request['identifier'] = $identifier;
		
		$this->changerequests[$identifier] = $cm_request;
		
		return $identifier;
	}*/
	
	public function modifyChangeRequest()
	{
		
	}
}

/**
 * For demo DB using a CSV file
 *  
 * @package CsvControler 
 */

/**
 * Concrete Demo OslcControler controler for CSV file support 
 *
 */
class OslcCsvDemoConnector extends OslcConnector {

	protected $csvFilename;
	
	/**
	 * @param array $params optional 'csvfilename' path to CSV file
	 */
	public function __construct($params = null) {
		parent::__construct($params);
		
		if(is_array($params) && array_key_exists('csvfilename',$params)) {
			$csvFilename = $params['csvfilename'];
			if($csvFilename) {
				$this->csvFilename = $csvFilename;
			}
		}
	}
	/**
	 * Initialize a ChangeRequestsCsv DB
	 * 
	 * It is passed in $params the 'id' => identifier if only one resource requested
	 * 
	 * @param array $params
	 */
	protected function fetchChangeRequests($params=null) {

		// TODO : allow a configurable location for the test file outside of the code ?

		$filename = $this->csvFilename;
		
		if(!$filename) {
			$filename = APPLICATION_PATH."/test.csv";
		}

		$this->changerequests = new ChangeRequestsCsv($filename);

		if(array_key_exists('project', $params)) {
			$this->changerequests->setFilter(array('project' => $params['project']));
		}
	}
	
}
