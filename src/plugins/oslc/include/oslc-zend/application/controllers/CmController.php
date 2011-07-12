<?php

/**
 * This file is (c) Copyright 2009 by Madhumita DHAR, Olivier
 * BERGER, Sabri LABBENE, Institut TELECOM
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

require_once('HTTP.php');

$exceptions_dir = dirname( dirname( __FILE__ )) . DIRECTORY_SEPARATOR. 'exceptions'. DIRECTORY_SEPARATOR;
require_once($exceptions_dir . 'oslcException.php');

/**
 * Zend controller managing REST invocations
 *
 * This is the main entry point. It dispatches REST invocations to other applications-specific REST controllers.
 *
 * @package ZendControler
 */
class CmController extends Zend_Rest_Controller {


	/**
	 * Defines by default accepted mime-types for queries on actions, and corresponding
	 * format of output. Applications can define more actions and their respective accepted
	 * mime-types.
	 *
	 * ATTENTION : order is important for the XML variants : the first one is the default returned when only basic XML is required
	 *
	 * @var array
	 */
	private static $supportedAcceptMimeTypes = array(
							 // All potential supported accept for GETs must be listed here (including all other actions')
							'get' => array(
								'application/x-oslc-cm-change-request+xml' => 'xml',
								'application/xml' => 'xml',
								'text/xml' => 'xml',
								'application/atom+xml' => 'xml',
								'application/rdf+xml' => 'xml',
								'application/x-oslc-disc-service-provider-catalog+xml' => 'xml',
								'application/x-oslc-disc-service-provider-catalog+json' => 'json',
								'application/x-oslc-cm-service-description+xml' => 'xml',
								'application/x-oslc-cm-service-description+json' => 'json',
							 	'application/json' => 'json',
							 	'application/x-oslc-cm-change-request+json' => 'json'
							 	//'text/html' => '?',
							 	//'application/xhtml+xml' => '?'
							 	),

							 'post' => array(
								'application/x-oslc-cm-change-request+xml' => 'xml',
								'application/xml' => 'xml',
								'text/xml' => 'xml',
							 	'application/x-oslc-cm-change-request+json' => 'json',
							 	'application/json' => 'json'
							 	//'text/html' => '?',
							 	//'application/xhtml+xml' => '?'
							 	),

							 'put' => array(
								'application/x-oslc-cm-change-request+xml' => 'xml',
								'application/xml' => 'xml',
								'text/xml' => 'xml',
							 	'application/json' => 'json',
							 	'application/x-oslc-cm-change-request+json' => 'json'
							 	//'text/html' => '?',
							 	//'application/xhtml+xml' => '?'
								),

							'readResource'=> array(
								'application/x-oslc-cm-change-request+xml' => 'xml',
								'application/xml' => 'xml',
								'text/xml' => 'xml',
				 				'application/json' => 'json',
				 				'application/x-oslc-cm-change-request+json' => 'json'
								),

							'readResourceCollection' => array(
								'application/atom+xml' => 'xml',
								'application/xml' => 'xml',
								'application/json' => 'json'
								),

							/* Service Provider Catalog : http://open-services.net/bin/view/Main/OslcServiceProviderCatalogV1*/
							'oslcServiceCatalog' => array(
								'application/x-oslc-disc-service-provider-catalog+xml' => 'xml',
							 	'application/xml' => 'xml',
								'application/x-oslc-disc-service-provider-catalog+json' => 'json',
								'application/json' => 'json',
								'application/rdf+xml' => 'xml'
								),

							'oslcCmServiceDocument' => array(
								'application/x-oslc-cm-service-description+xml' => 'xml',
								'application/xml' => 'xml',
								'application/x-oslc-cm-service-description+json' => 'json',
								'application/json' => 'json'
								)
	);

	private $rest_controller;

	/**
	 * Initilizes the Zend REST controler
	 */
	public function init() {
		//print_r("ACTION : ". $this->getRequest()->getActionName());
		// load some initializations needed once we're in the controller, really running the application
		$controller_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
		require_once($controller_dir . 'OSLCConnector.php');

		switch (TRACKER_TYPE) {
			case 'mantis':
				// load Mantis specific classes
				require_once($controller_dir . 'MantisCmController.php');
				$modelDir = $this->getFrontController()->getModuleDirectory(). DIRECTORY_SEPARATOR . 'models';
				require_once($modelDir . '/mantis.inc.php');
				break;

			case 'fusionforge':
				require_once($controller_dir . 'FusionForgeCmController.php');
				break;
			case 'Codendi':
				require_once($controller_dir . 'CodendiCmController.php');
				break;
			case 'demo':
				break;

			default:
				throw new BadRequestException('Unsupported TRACKER_TYPE : '. TRACKER_TYPE .' !');
				break;
		}
	}

	public function getSupportedAcceptMimeTypes(){
		return self::$supportedAcceptMimeTypes;
	}

	/**
	 * Checks if the request's Accept mime-type is correct for that action
	 *
	 * Upon success, returns the prefered content-type for the same format.
	 * @param array $mime_types supported accepted mime types for application action
	 * @param string $action request action
	 *
	 * @return string
	 */
	public function checkSupportedActionMimeType($mime_types, $action) {
	  $req = $this->getRequest();
	  //		$action = $req->getActionName();
	  //	  print_r("Action : ".$action);

	  // check Accept header's mime type
	  $accept = $req->getHeader('Accept');
	  //print_r("\nAccept : ".$accept);

	  // prepare an array of accepted types
	  $accepted_types = array();
	  if(isset($mime_types[$action])) {
	    $accepted_types = array_keys($mime_types[$action]);
	  }
	  // make sure text/html is always an option (in last option)
	  $accepted_types[]='text/html';
	  //print_r("\nAccepted types:");
	  //print_r($accepted_types);
	  // If we can't directly find the accept header, then, have to negociate maybe among alternatives

	  if(!isset($mime_types[$action][$accept])) {
	    // use PEAR's HTTP::negotiateMimeType to identify the preferred content-type
	    //$accept = HTTP::negotiateMimeType($accepted_types,'');
	    $http=new HTTP();
	    $content_type = $http->negotiateMimeType($accepted_types,'');
	    //print_r("Accept2 : ".$content_type);
	  } else {
	    // perfect, just found it directly (note that the 'get' action needs all of them)
	    $content_type = $accept;
	  }

	  if (!$content_type) {
	    // unsupported accept type
	    throw new NotAcceptableForCRCollectionException("Accept header '".$req->getHeader('Accept')."' not supported for action .'".$action."' !");
	  }
	  /*
	  // we have selected the requested type and check the corresponding output format
	  $accept = $content_type;

	  // if found, then check for default type for equivalent formats (the first one with same format)
	  // should make application/xml more specific for instance
	  if(isset($mime_types[$action][$accept])) {
	    $format = $mime_types[$action][$accept];
	    foreach ($mime_types[$action] as $key => $value) {
	      if ($value == $format) {
		$content_type = $key;
		break;
	      }
	    }
	  }*/
	  return $content_type;
	}

	/**
	 * Utility to load the PHP classes for the model
	 *
	 * @param string $class
	 * @param string $module
	 * @return class
	 */
	public function loadModelClasses($class, $module = null)
	{
		$modelDir = $this->getFrontController()->getModuleDirectory($module). DIRECTORY_SEPARATOR . 'models';
		Zend_Loader::loadClass($class, $modelDir);
		return $class;
	}

	public function getAction(){
		switch (TRACKER_TYPE) {
				case 'mantis':
					$this->_forward('get','mantiscm');
					break;
				case 'fusionforge':
					$this->_forward('get', 'fusionforgecm');
					break;
				case 'Codendi':
					$this->_forward('get', 'codendicm');
					break;
				default:
					break;
			}
	}

	public function postAction(){
		switch (TRACKER_TYPE) {
				case 'mantis':
					$this->_forward('post','mantiscm');
					break;
				case 'fusionforge':
					$this->_forward('post','fusionforgecm');
					break;
				case 'Codendi':
					$this->_forward('post', 'codendicm');
					break;
				default:
					break;
			}
	}

	public function indexAction(){
		switch (TRACKER_TYPE) {
			case 'mantis':
				$this->_forward('index','mantiscm');
				break;
			case 'fusionforge':
				$this->_forward('index','fusionforgecm');
				break;
			case 'Codendi':
				$this->_forward('index', 'codendicm');
				break;
			default:
				break;
		}
	}

	public function putAction(){
		switch (TRACKER_TYPE) {
				case 'mantis':
					$this->_forward('put','mantiscm');
					break;
				case 'fusionforge':
					$this->_forward('put','fusionforgecm');
					break;
				case 'Codendi':
					$this->_forward('put', 'codendicm');
					break;
				default:
					break;
			}
	}

	public function deleteAction(){
		throw new BadRequestException('Method delete not yet supported !');
	}
}
?>
