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

/**
 * OSLC-CM V1 ChangeRequest model
 *
 * @package model
 * @subpackage OslcCmChangeRequest
 */

// Define the backend tracker type : by defaut : mantis
defined('TRACKER_TYPE')
    || define('TRACKER_TYPE', (getenv('TRACKER_TYPE') ? getenv('TRACKER_TYPE') : 'mantis'));

switch (TRACKER_TYPE) {
	case 'mantis':
		break;
	case 'fusionforge':
		break;
	case 'Codendi':
		break;
	case 'demo':
		// Use the PEAR File_CSV module which handles various kinds of CSV encodings
		require_once 'File/CSV.php';
		break;
	default:
		throw new Exception('Unsupported TRACKER_TYPE : '. TRACKER_TYPE .' !');
		break;
}

/**
 * Models OSLC-CM ChangeRequest (see http://open-services.net/bin/view/Main/CmResourceDefinitionsV1)
 *
 * Implements ArrayAccess and Iterator so that it can be accessed as
 * an array of its attributes.
 *
 * @author Olivier Berger <olivier.berger@it-sudparis.eu>
 *
 */

class ChangeRequest implements ArrayAccess, Iterator
{

	/**
	 * Mandatory attributes for a changerequest
	 * @var array
	 */
	protected $_mandatory = array('title','identifier');

	/**
	 * So far, unused, but may need a check
	 * @var array
	 */
	protected $_optional = array('type','description','subject','creator','modified');

	/**
	 * Holds the container for the real attributes array
	 *
	 * The dublin core OSLC-CM V1 attributes ($_mandatory and $_optional) are handled without any prefix.
	 * Additional attributes from other ontologies can be stored but with their prefixes
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * @param string $identifier unique ChangeRequest id
	 * @param string $title
	 * @param string $type Type of request that is represented, such as: defect, enhancement, etc.
	 * @param string $description
	 * @param string $subject a collection of keywords and tags
	 * @param string $creator
	 * @param string $modified modified date time which must conform to RFC3339 format
	 */
	function __construct($identifier = null, $title = null, $type = null, $description = null, $subject = null, $creator = null, $modified = null)
	{
		// $identifier may be provided as null first, for practical reasons although ultimately non null
		$this->container = array(
			'identifier' => $identifier,
			'title' => $title);

		if(isset($type))
		$this->container['type'] = $type;
		if(isset($description))
		$this->container['description'] = $description;
		if(isset($subject))
		$this->container['subject'] = $subject;
		if(isset($creator))
		$this->container['creator'] = $creator;
		if(isset($modified))
		$this->container['modified'] = $modified;
	}

	/**
	 * Factory for the creation of instances of ChangeRequest subclasses
	 *
	 * @param string $bugtrackertype
	 * @return ChangeRequest
	 */
	public static function Create($bugtrackertype = 'default')
	{
		// use a utility function outside the class since otherwise we would
		//have cycle in dependencies between ChangeRequest and MantisChangeRequest
		return __createChangeRequest($bugtrackertype);
	}

	/**
	 * Create from XML (RDF) OSLC-CM document
	 *
	 * @param string $xmlstr
	 * @return ChangeRequest
	 */
	public static function CreateFromXml($xmlstr)
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

		$xml = simplexml_load_string($xmlstr);
		$namespace = $xml->getNamespaces();
		if ( ($namespace['rdf'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#') &&
			 ($xml->getName() == 'RDF'))
		{
			foreach ($xml->children('http://open-services.net/xmlns/cm/1.0/') as $changerequest) {
				if ($changerequest->getName() == 'ChangeRequest') {
					$resource = array();
					foreach ($changerequest->children('http://purl.org/dc/terms/') as $child) {
						$field = $child->getName();
						$value = (string)$child;
						$resource[$field] = $value;
					}
				}
			}
		}

		$changerequest = new ChangeRequest();

		// initialize the ChangeRequest attributes
		foreach ($resource as $field => $value) {
			$changerequest->container[$field] = $value;
		}

		return $changerequest;
	}

	/**
	 * Create a ChangeRequest from a JSON OSLC-CM representation
	 *
	 * @param string $jsonstr
	 * @return ChangeRequest
	 */

	public static function CreateFromJson($jsonstr) {

		$resource = Zend_Json::decode($jsonstr);

		$changerequest = new ChangeRequest();

		// the dublin core elements prefix is removed

		foreach ($resource as $field => $value) {
			$field = str_replace('dc:', '', $field);
			$field = str_replace('mantisbt:', '', $field);
			$changerequest->container[$field] = $value;
		}

		return $changerequest;

	}

	// ArrayAccess methods

	public function offsetSet($offset, $value) {
		$this->container[$offset] = $value;
	}

	public function offsetExists($offset) {
		return isset($this->container[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->container[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->container[$offset]) ? $this->container[$offset] : null;
	}

	// Iterator methods

	public function rewind() {
		reset($this->container);
	}

	public function current() {
		return current($this->container);
	}

	public function key() {
		return key($this->container);
	}

	public function next() {
		return next($this->container);
	}

	public function valid() {
		return $this->current() !== false;
	}

	/**
	 * Check that all mandatory ChangeRequest fields are defined
	 *
	 * Throws an exception otherwise
	 */
	public function checkMandatoryFields() {
		// check that all mandatory fields are there
		foreach ($this->_mandatory as $mandatoryfield) {
			if (! isset($this[$mandatoryfield])) {
				throw new Exception('Mandatory field "'.$mandatoryfield.'" not present !');
			}
		}
	}

}


/**
 * Abstract class implementing a ChangeRequest DB
 *
 * Holds all ChangeRequest in memory.
 * They will be loaded all at once when first access is made
 * unless this behaviour is redefined un subclasses by
 * overloading loadChangeRequest()
 *
 * @abstract
 */
class ChangeRequests implements ArrayAccess, Iterator, Countable
{
	/**
	 * Attributes known for the ChangeRequest instances
	 *
	 * @var array
	 */
	protected $_fields;

	/**
	 * An array containing the ChangeRequest instances accessible by their identifiers
	 *
	 * @var array
	 */
	protected $_data;

	function __construct() {
		$this->_fields = null;
		$this->_data = null;

	}

	/**
	 * Loads all ChangeRequest at once
	 *
	 * Method to be implemented / overloaded in subclasses
	 *
	 * @abstract
	 */
	protected function loadAllChangeRequests()
	{
		//print_r('abstract ChangeRequests::loadAllChangeRequests()');
	}

	/**
	 * Load a single ChangeRequest provided its indentifier
	 *
	 * May be overloaded if need to do individual queries in a DB
	 * instead of loading everything at once
	 *
	 * @param string $identifier
	 */
	protected function loadChangeRequest($identifier) {

		// by default, try and load all changerequests on the first query
		$this->loadAllChangeRequests();
	}

	public function setFilter($params) {

	}

	// Countable methods
	public function count() {
		return count($this->_data);
	}

	// ArrayAccess methods

	public function offsetSet($offset, $value) {
		$newid = $value['identifier'];
		if ($offset != $newid) {
			throw new Exception('Sorry, ChangeRequest identifier cannot be modified !');
		}
		$this->loadChangeRequest($offset);
		$this->_data[$offset] = $value;
		//print_r('offsetSet');
		//throw new Exception('Sorry, writing to read-only ChangeRequests object !');
	}

	public function offsetExists($offset) {
		//print_r('offsetExists');
		$this->loadChangeRequest($offset);
		return isset($this->_data[$offset]);
	}

	public function offsetUnset($offset) {
		//print_r('offsetUnset');
		throw new Exception('Sorry, writing to read-only ChangeRequests object !');
	}

	public function offsetGet($offset) {
		//print_r('offsetGet');
		$this->loadChangeRequest($offset);
		return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
	}

	// Iterator methods

	public function rewind() {
		//print_r('rewind');
		$this->loadAllChangeRequests();
		if(isset($this->_data)) {
			reset($this->_data);
		}
	}

	public function current() {
		//print_r('current');
		if(isset($this->_data))	{
			return current($this->_data);
		}
		else {
		     return false;
		}
	}

	public function key() {
		//print_r('key');
		return key($this->_data);
	}

	public function next() {
		//print_r('next');
		return next($this->_data);
	}

	public function valid() {
		//print_r('valid');
		return $this->current() !== false;
	}

}


/**
 * Utility function for ChangeRequest::Create
 */
$model_dir = APPLICATION_PATH.'/models/';
require_once($model_dir . 'mantis.inc.php');
require_once($model_dir . 'fusionforge.inc.php');

function __createChangeRequest($bugtrackertype)
{
	$changerequest = null;
	switch ($bugtrackertype) {
		case 'default' :
			$changerequest = new ChangeRequest();
			break;
		case 'fusionforge' :
			$changerequest = new FusionForgeChangeRequest();
			break;
		case 'mantis' :
			$changerequest = new MantisChangeRequest();
			break;
		default :
			throw new Exception('Unknown bugtracker type '.$bugtrackertype.' !');
			break;
	}
	return $changerequest;
}


/**
 * For demo DB using a CSV file
 *
 * @package CsvModel
 */

/**
 * Subclass implementing a ChangeRequests DB loaded from a CSV file
 *
 * Can be used to implement standalone read-only ChangeRequests DB
 */

class ChangeRequestsCsv extends ChangeRequests
{
	/**
	 * @var filename
	 */
	private $_csvfilename;

	private $_filter = null;

	/**
	 * @param string $filename
	 */
	function __construct($filename) {
		parent::__construct();

		$this->_csvfilename = $filename;

	}

	public function setFilter($params) {
		//print_r('set filter :');
		//print_r($params);
		if(array_key_exists('project',$params)) {
			$this->_filter = array('mantisbt:project' => $params['project']);
		}
	}

	/**
	 * Load all ChangeRequest from the CSV file
	 *
	 */
	protected function loadAllChangeRequests()
	{
		//print_r('ChangeRequestsCsv::loadAllChangeRequests()');

		// only load once (if not already loaded before)
		if (isset($this->_data))
		return;

		$this->_data = array();

		$filename = $this->_csvfilename;

		// load the formet of the CSV file
		//print_r('load from : '.$filename);
		$conf = File_CSV::discoverFormat($filename);
		//print_r($conf);
		// columns number of the identifier
		$idcolnum = null;
		$bugtrackertype = 'default';

		// process all lines
		while ($res = File_CSV::read($filename, $conf)) {
			//print_r($res);
			// first line defines columns names
			if (! $this->_fields) {

				$this->_fields = array();

				// search for identifier's column number
				for ($i=0; $i < $conf['fields']; $i++ ) {

					if ( trim($res[$i]) == 'identifier') {
						$idcolnum = $i;
					}

					// handle special case of the additional definitions at the end of the columns headers
					$definitions = explode('=', $res[$i]);
					if ( (count($definitions) > 1) && ($definitions[0] == 'bugtracker')) {
						switch ($definitions[1]) {
							case 'default' :
							case 'fusionforge' :
							case 'mantis' :
								$bugtrackertype = $definitions[1];
								break;
							default :
								throw new Exception('Unknown bugtracker type '.$definitions[1].' in CSV file headers !');
								break;
						}
					}
					else {

						$this->_fields[] = $res[$i];
					}
				}

			}
			// other data lines processing
			else {
				// identifier
				$id = $res[$idcolnum];

				// maybe check if not duplicate identifier ?

				// add an entry for the identifier

				$cr = ChangeRequest::Create($bugtrackertype);

				for ($i=0; $i < count($this->_fields); $i++ ) {
					$fieldname = $this->_fields[$i];
					if (isset($res[$i])) {
						$cr[$fieldname] = $res[$i];
					}
					else {
						throw new Exception('No value for bug '.$id.' in column '.$fieldname.' !');
					}
				}

				$cr->checkMandatoryFields();

				//print_r($cr);
				//print_r('filter:');
				//print_r($this->_filter);

				// filter only for specific fields/values
				if (isset($this->_filter) && is_array($this->_filter)) {
					// for each filter field
					foreach ($this->_filter as $field => $value) {
						//print_r('filter on :'.$field.' == '.$value);
						if(isset($cr[$field])) {
							//print_r('found field '.$field);
							//print_r($cr[$field]);
							if ($cr[$field] != $value) {
								$cr = null;
								break;
							}
						}
						else {
							$cr = null;
							break;
						}
					}
				}
				if($cr) {
					//print_r('OK : kept');
					$this->_data[$id] = $cr;
				}/*
				else {
					print_r('NOK : dismissed');
				} 	*/
			}
		}

		// reset the CSV file reading to be able to call it twice on same file
		$file = File_CSV::getPointer($filename,$conf);
		if($file) {
			//print_r('file :');
			//print_r($file);
			$reset = File_CSV::resetPointer($filename, $conf, FILE_MODE_READ);
			if(!$reset) {
				throw new Exception('Could not File_CSV::resetPointer("'.$filename.'") !');
			}
		}
		//print_r($this);
	}

	// load only a specific changerequest from its id
	/*
	protected function loadChangeRequest($identifier) {

		// $this->_data[$identifier] = getArtifact($identifier); -> select * from artifact where id = $identifier

		// We can only parse the whole CSV file at once until further optimization
		$this->loadAllChangeRequests();
	}
	*/
}
