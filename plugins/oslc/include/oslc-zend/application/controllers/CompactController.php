<?php
/**
 * This file is (c) Copyright 2010 by Sabri LABBENE, Institut TELECOM
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
 */
require_once('HTTP.php');
$exceptions_dir = dirname( dirname( __FILE__ )) . DIRECTORY_SEPARATOR. 'exceptions'. DIRECTORY_SEPARATOR;
require_once($exceptions_dir . 'oslcException.php');

/**
 * TODO : document me
 *
 */
class CompactController extends Zend_Rest_Controller {

	private static $supportedAcceptMimeTypes = array(
		'get' => array(
			'application/x-oslc-compact+xml' => 'xml'
		),
		'oslccompactuser' => array(
			'application/x-oslc-compact+xml' => 'xml'
		),
		'oslccompactproject' => array(
			'application/x-oslc-compact+xml' => 'xml'
		)
	);

	private $rest_controller;

	/**
	 * Initilizes the Zend REST controler
	 */
	public function init() {
		$controller_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
		require_once($controller_dir . 'FusionForgeCompactController.php');
	}

	public function getSupportedAcceptMimeTypes(){
		return self::$supportedAcceptMimeTypes;
	}

	public function checkSupportedActionMimeType($mime_types, $action) {
		$req = $this->getRequest();

		// check Accept header's mime type
		$accept = $req->getHeader('Accept');

		// prepare an array of accepted types
		$accepted_types = array();
		if(isset($mime_types[$action])) {
			$accepted_types = array_keys($mime_types[$action]);
		}
		// make sure text/html is always an option (in last option)
		$accepted_types[]='text/html';

		// If we can't directly find the accept header, then, have to negociate maybe among alternatives
		if(!isset($mime_types[$action][$accept])) {
			// use PEAR's HTTP::negotiateMimeType to identify the preferred content-type
			//$accept = HTTP::negotiateMimeType($accepted_types,'');
			$http = new HTTP();
			$content_type = $http->negotiateMimeType($accepted_types,'');
		} else {
			// perfect, just found it directly (note that the 'get' action needs all of them)
			$content_type = $accept;
		}
		//print($content_type); die();
		if (!isset($content_type)) {
			// unsupported accept type
			throw new NotAcceptableForCRCollectionException("Accept header '".$req->getHeader('Accept')."' not supported for action .'".$action."' !");
		}

		return $content_type;
	}

	public function getAction(){
		switch (TRACKER_TYPE) {
				case 'fusionforge':
					$this->_forward('get', 'fusionforgecompact');
					break;
				default:
					break;
			}
	}

	public function putAction() {

	}

	public function postAction() {

	}

	public function deleteAction() {

	}

	public function indexAction() {

	}
}
?>
