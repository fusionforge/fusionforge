<?php
/**
 * This file is (c) Copyright 2010 by Sabri LABBENE, Madhumita DHAR,
 * Olivier BERGER, Institut TELECOM
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

$controller_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
require_once($controller_dir . 'CmController.php');
require_once($controller_dir . 'FusionForgeOSLCConnector.php');

class FusionForgeCmController extends CmController {

		/**
	 * @var oslc
	 *
	 * This will be the OSLC-CM controller managing the business logic of the application
	 */
	private $oslc;

		/**
	 * Defines accepted mime-types for queries on actions, and corresponding
	 * format of output
	 *
	 * ATTENTION : order is important for the XML variants : the first one is the default returned when only basic XML is required
	 *
	 * @var array
	 */
	private static $supportedAcceptMimeTypes = array();
	private static $fusionforgeSupportedAcceptMimeTypes = array(
		'oslcServiceCatalogProject' => array(
			'application/x-oslc-disc-service-provider-catalog+xml' => 'xml',
		 	'application/xml' => 'xml',
			'application/x-oslc-disc-service-provider-catalog+json' => 'json',
			'application/json' => 'json',
			'application/rdf+xml' => 'xml'
		),
	);
	private $actionMimeType;

	public function setActionMimeType($action) {
		if(!isset($this->actionMimeType)) {
			$this->actionMimeType = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $action);
		}
	}

	/**
	 * Init FusionForge REST controller.
	 */
	public function init(){
		self::$supportedAcceptMimeTypes = array_merge(parent::getSupportedAcceptMimeTypes(), self::$fusionforgeSupportedAcceptMimeTypes);

		// TODO : render this path configurable
		//		$writer = new Zend_Log_Writer_Stream('/tmp/zend-log.txt');
		//		$this->logger = new Zend_Log($writer);

		parent::loadModelClasses('ChangeRequests');

		// now do things that relate to the REST framework
		$req = $this->getRequest();
		//print_r($req);

		$action = $req->getActionName();

		if(($action == 'post')||($action == 'put'))
		{
			$accept = $req->getHeader('Content-Type');
		}
		elseif($action =='get')
		{
			$accept = $req->getHeader('Accept');
		}

		// Set the mime type for action.
		$this->setActionMimeType($action);

		if(isset($this->actionMimeType)) {
		  $accept = $this->actionMimeType;
		}

		// determine output format
		if (isset(self::$supportedAcceptMimeTypes[$action])) {
			if (isset(self::$supportedAcceptMimeTypes[$action][$accept])) {
				$format = self::$supportedAcceptMimeTypes[$action][$accept];
				//print_r('format :'.$format);
				$req->setParam('format', $format);
			}
		}

		$contextSwitch = $this->_helper->getHelper('contextSwitch');

		// we'll handle JSON ourselves
		$contextSwitch->setAutoJsonSerialization(false);
		$types = array();
		foreach (self::$supportedAcceptMimeTypes as $action => $typesarr) {
			//print_r(array_unique(array_values($typesarr)));
			$types = array_unique(array_values($typesarr));
			//print_r("Typesarr : ".$typesarr);
			$contextSwitch->addActionContext($action, $types)->initContext();
		}

		// Create an OSLC Controller for FusionForge.
		$this->oslc = new FusionForgeOSLCConnector();
	}

	public function getAction(){
		$params = $this->getRequest()->getParams();

		// check authentication although it's not yet really useful
		$login = null;
		$authenticated = $this->retrieveAuthentication($login);
		if(isset($login)) {
			// Basic auth requested
			if (!$authenticated) {
				// not succesfully authd as $login
				// can't go on;
				throw new Exception('Invalid authentication provided !');
			}
		}

		// handle OSLC services catalog access (http://open-services.net/bin/view/Main/OslcServiceProviderCatalogV1)
		if ( isset($params['id']) && ($params['id'] == "oslc-services")) {
				$this->_forward('oslcServiceCatalog');
				return;
		}

		// Handle OSLC-CM services catalog for specific project
		// An OSLC-CM services catalog in FusionForge lists all the trackers
		// of a specific project.
		elseif (isset($params['oslc-cm-services'])){
			$this->_forward('oslcServiceCatalogProject');
			return;
		}

		// handle OSLC-CM service document access
		// An OSLC-CM service document describes capabilities of a FusionForge tracker.
		elseif (isset($params['oslc-cm-service']) && isset($params['tracker'])) {
			$this->_forward('oslcCmServiceDocument');
			return;
		}
		// Handle creation UI access
		elseif (isset($params['ui']) && $params['ui'] == 'creation' && isset($params['project']) && isset($params['tracker'])){
			$this->_forward('showCreationUi');
			return;
		}
		// Handle selection UI access
		elseif (isset($params['ui']) && $params['ui'] == 'selection' && isset($params['project']) && isset($params['tracker'])){
			$this->_forward('showSelectionUi');
			return;
		}

		// Now, do the OSLC-CM resources access work
		// if no bug was mentioned, then return a resource collection
		if (!array_key_exists('bug', $params)) {
			// forward to an independant action so that it has its own views
			// (see readresourcecollectionAction())
			$this->_forward('readResourceCollection');
		}
		elseif(array_key_exists('bug', $params)) {
			// now we're indeed getting one single resource
			$this->_forward('readResource');
		} else {
			throw new NotFoundException("Resource ".$this->getRequest()->getRequestUri()." was not found on the server!");
		}

		// This is not explicitely required in OSLC-CM V1
		// In the case of RDF+XML requested, mention it explicitely : LOD friendly
		$req = $this->getRequest();
		$accept = $req->getHeader('Accept');
		switch (true) {
			case (strstr($accept, 'application/rdf+xml')):
				$resp = $this->getResponse()->setHeader('Content-Type', 'application/rdf+xml');
				break;
		}
	}

	/**
	 * Handles PUT action as routed by Zend_Rest_Route
	 *
	 * Update of an existing changerequest
	 * Will be invoked if PUT or if POST on a path relating to resources (due to Zend REST route behaviour)
	 * So in case of POST, will pass the handling to postAction()
	 *
	 * @return unknown_type
	 */
	public function putAction(){
		$req = $this->getRequest();

		// in case invoked like POST on .../cm/project/whatever we arrive to putAction
		// so we check such case and then redirect to postAction() if needed
		if ($req->isPost()) {
            $this->postAction();
		}
		else {

			// otherwise it is indeed a PUT and we are trying to modify a change request

			// do authentication.
			$login = null;
			$authenticated = $this->retrieveAuthentication($login);
			if(isset($login)) {
				// Basic auth requested
				if (!$authenticated) {
					// not succesfully authd as $login
					// can't go on;
					throw new Exception('Invalid authentication provided !');
				}
			}

			$contenttype = $req->getHeader('Content-Type');
			$contenttype = $contenttype ? $contenttype : 'none';

			switch($contenttype) {
				case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
				case 'application/x-oslc-cm-change-request+json; charset=UTF-8':
				case 'application/xml; charset=UTF-8':
				case 'application/json; charset=UTF-8':
				case 'application/x-oslc-cm-change-request+xml':
				case 'application/x-oslc-cm-change-request+json':
				case 'application/xml':
				case 'application/json':
					break;
				default:
					throw new UnsupportedMediaTypeException('Unknown Content-Type for method put : '. $contenttype .' !');
					break;
			}

			$identifier = null;

			$params = $req->getParams();

			if (array_key_exists('id', $params)) {
				$identifier = $req->getParam('id');
			}
			else {
				$identifier = $req->getParam('bug');

				if (! isset($identifier)) {
					throw new Exception('No change request id provided !');
				}
			}

			// checking if modification

			$modifiedproperties = null;

			$oslc_cm_properties = $req->getParam('oslc_cm_properties');
			if (isset($oslc_cm_properties)) {
				$modifiedproperties = explode(',', $oslc_cm_properties);
				if (array_key_exists('identifier', $modifiedproperties)) {
					throw new Exception('Identifier cannot be modified !');
				}
			}

			$body = file_get_contents('php://input');

			// TODO: This should be done by $this->oslc
			switch($contenttype) {
				case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
				case 'application/xml; charset=UTF-8':
				case 'application/x-oslc-cm-change-request+xml':
				case 'application/xml':
					// extract values from XML
					$newchangerequest = FusionForgeChangeRequest::CreateFusionForgeArrayFromXml($body);
					break;
				case 'application/x-oslc-cm-change-request+json; charset=UTF-8':
				case 'application/json; charset=UTF-8':
				case 'application/x-oslc-cm-change-request+json':
				case 'application/json':
					// extract values from JSON.
					$newchangerequest = FusionForgeChangeRequest::CreateFusionForgeArrayFromJson($body);
					break;
			}

			if(!$this->oslc->checkChangeRequestExists($identifier))	{
				throw new Exception("Change Request to be updated doesn't exist!");
			}
			else {
				// Proceed to change request update
				$this->oslc->updateChangeRequest($identifier, $newchangerequest, $modifiedproperties);

				//logout the user
				session_logout();
			}
		}
	}

	public function postAction(){
		$req = $this->getRequest();

		// check that we're indeed invoked by a POST request
		if(! $req->isPost()) {
			throw new Exception('postAction invoked without POST !');
		}

		$params = $req->getParams();

		if (!isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		// do authentication.
		/*$login = null;
		$authenticated = $this->retrieveAuthentication($login);
		if(isset($login)) {
			// Basic auth requested
			if (!$authenticated) {
				// not succesfully authd as $login
				// can't go on;
				throw new Exception('Invalid authentication provided !');
			}
		}*/

		$contenttype = $req->getHeader('Content-Type');
		$contenttype = $contenttype ? $contenttype : 'none';

		switch($contenttype) {
			case 'application/x-oslc-cm-change-request+xml':
			case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
			case 'application/x-oslc-cm-change-request+json':
			case 'application/x-oslc-cm-change-request+json; charset=UTF-8':
			case 'application/json':
			case 'application/json; charset=UTF-8':
			case 'application/xml; charset=UTF-8':
			case 'application/xml':
				break;
			default:
				throw new UnsupportedMediaTypeException('Unknown Content-Type for method post : '. $contenttype .' !');
				break;
		}

		// used for PhpUnit tests.
		if(APPLICATION_ENV=='testing') {
			$body = $_POST['xml'];
		} else {
			$body = file_get_contents('php://input');
		}

		if(array_key_exists('project',$params)) {
			if (array_key_exists('tracker', $params)) {
				// create a change request
				switch($contenttype) {
					case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
					case 'application/x-oslc-cm-change-request+xml':
					case 'application/xml; charset=UTF-8':
					case 'application/xml':
						$newchangerequest = FusionForgeChangeRequest::CreateFusionForgeArrayFromXml($body);
						break;
					case 'application/x-oslc-cm-change-request+json; charset=UTF-8':
					case 'application/x-oslc-cm-change-request+json':
					case 'application/json; charset=UTF-8':
					case 'application/json':
						$newchangerequest = FusionForgeChangeRequest::CreateFusionForgeArrayFromJson($body);
						break;
				}

				$creationparams = array('project' => $params['project'],
										'tracker' => $params['tracker'],
										'new' => $newchangerequest);

				// pass the creation work to the OSLC connector
				$identifier = $this->oslc->createChangeRequest($creationparams);


			}else{
				throw new ConflictException('Need a valid tracker to create a change request');
			}
		}else{
			throw new ConflictException('Need a valid project and tracker to create change request !');
		}

		// prepare redirection
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();
		$baseURL = $this->getFrontController()->getBaseUrl();
		$controllerName = $this->getFrontController()->getDefaultControllerName();

		if(APPLICATION_ENV=='testing')
		{
			$newlocation = '/'.$controllerName.'/project/'.$params['project'].'/tracker/'.$params['tracker'].'/bug/'.$identifier;
		}
		else
		{
			$newlocation = $httpScheme.'://'.$httpHost.$baseURL.'/'.$controllerName.'/project/'.$params['project'].'/tracker/'.$params['tracker'].'/bug/'.$identifier;
		}

		//Send back as a reponse the uri of the newly created change request
		$this->getResponse()->setHeader('Content-Type', 'text/html');
		$this->getResponse()->setHttpResponseCode(201);
		$this->getResponse()->appendBody($newlocation);
	}
	public function indexAction(){

	}
	public function deleteAction(){

	}

	/**
	 * Retrieve an individual resource and populates the view of an OSLC CM ChangeRequest
	 *
	 * @param string $identifier
	 * @param string $uri
	 */
	public function readresourceAction() {

		$params = $this->getRequest()->getParams();
		//$content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
		if (!isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		$identifier = $params['bug'];

		// prepare resource URI
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();
		$changerequestURI = $httpScheme.'://'.$httpHost.$requestUri;

		// Check if some specific fields have been requested for the ChangeRequest.
		if (isset($params['oslc_properties'])){
			$preparedChangeRequest = $this->oslc->fetchChangeRequest($identifier, $changerequestURI, $params['oslc_properties']);
		} else {
			$preparedChangeRequest = $this->oslc->fetchChangeRequest($identifier, $changerequestURI);
		}

		if(isset($preparedChangeRequest)) {

			// populate the view with the model
			foreach($preparedChangeRequest as $field => $value) {
				$this->view->{$field} = $value;
			}

			$this->getResponse()->setHeader('Content-Type', $this->actionMimeType);
		}
		else{
			$this->view->missing_resource = $identifier;
			$this->_forward('ResNotFound','error');
		}
	}

	public function readresourcecollectionAction()	{
		$req = $this->getRequest();
		$params = $req->getParams();

		//$content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
		// TODO: raise the correct error code according to the specs.
		if (!isset($this->actionMimeType)) {
		  //			print_r("error");
		  throw new NotAcceptableException("Accept header ".$req->getHeader('Accept')." not supported!");
		  return;
		}

		// load the model. Will fetch requested change requests from the db.
		$params = $this->oslc->init($params);

		// construct ATOM feed-like header
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();
		$prefix = $httpScheme.'://'.$httpHost.$requestUri;

		// get all resources
		$collection = $this->oslc->getResourceCollection($prefix);

		// construct an intermediate array that will be used to populate the view
		$preparedCollection = array ('id'         => $httpScheme.'://'.$httpHost.$requestUri,
									'collection'     => $collection
							);
		// Add request params so they ca reach views.
		foreach($params as $key => $value){
			$preparedCollection[$key] = $value;
		}

		// populate the view with the loaded values
		foreach($preparedCollection as $key => $value) {
			$this->view->$key = $value;
		}

		//print_r($this->view);
		$this->getResponse()->setHeader('Content-Type', $this->actionMimeType);

	}

	/**
	 * Handle OSLC Core services provider catalog access (http://open-services.net/bin/view/Main/OslcCoreSpecification)
	 * Will show the list of prjects.
	 *
	 */
	public function oslcservicecatalogAction() {

		//$content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
		if (! isset($this->actionMimeType)) {
		  //			print_r("error");
		  $this->_forward('UnknownAcceptType','error');
		  return;
		}
		// each project is considered as a service Provider.
		$proj_arr = $this->oslc->getProjectsList();

		$this->view->projects = $proj_arr;

		$this->getResponse()->setHeader('Content-Type', $this->actionMimeType);
	}

	/**
	 * Handle OSLC services catalog access per project.
	 * Accessed by uris like ".../cm/oslc-cm-services/x"
	 * where x is a project id.
	 */
	public function oslcservicecatalogprojectAction() {
		//$content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
		if (! isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		$req = $this->getRequest();
		$params = $req->getParams();

		$project = $params['oslc-cm-services'];
		$trackers = $this->oslc->getProjectTrackers($project);

		$this->view->project = $project;
		$this->view->trackers = $trackers;

		$this->getResponse()->setHeader('Content-Type', $this->actionMimeType);
	}

	/**
	 *
	 * Handles OSLC-CM service document access.
	 */
	public function oslccmservicedocumentAction() {
		//$this->actionMimeType = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
		if (! isset($this->actionMimeType)) {
			$this->_forward('UnknownAcceptType','error');
			return;
		}

		$req = $this->getRequest();
		$params = $req->getParams();

		$this->view->project = $params['oslc-cm-service'];
		$this->view->tracker = $params['tracker'];

		$this->getResponse()->setHeader('Content-Type', $this->actionMimeType);

	}

	public function showselectionuiAction()	{
		$req = $this->getRequest();
		$params = $req->getParams();
		$project = $params['project'];
		$tracker = $params['tracker'];
		$data = $this->oslc->getDataForSelectionUi($project, $tracker);
		$this->view->data = $data;
	}

	public function showcreationuiAction() {
		$req = $this->getRequest();
		$params = $req->getParams();
		$project = $params['project'];
		$tracker = $params['tracker'];

		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$prefix = $httpScheme.'://'.$httpHost;
		$this->view->delegUrl = $prefix;

		// do authentication.
		if (isset($params['oauth_signature'])) {
			session_set_for_authplugin('oauthprovider');
		}

		//TODO add flags to propagate to the view if authentication went well ...
		//And timers ....
		if(session_loggedin()) {
			$auth_timestamp = time();
		}


		if(isset($params['build_url'])) {
			$this->view->build_url = $params['build_url'];
		}
		if (isset($params['build_number'])) {
			$this->view->build_number = $params['build_number'];
		}
		if (isset($auth_timestamp)) {
			$this->view->auth_timestamp = $auth_timestamp;
		}

		$data = $this->oslc->getDataForCreationUi($project, $tracker);

		$this->view->data = $data;


	}

	/**
	 * Performs authentication according to the authorization header recieved.
	 *
	 * @param string $login
	 * @return True if auth is valid, FALSE otherwise.
	 */

	private function retrieveAuthentication(&$login) {
		$request = $this->getRequest();
		$auth = $request->getHeader('Authorization');
		if ($auth) {
			$auth_type = explode(' ',$auth);
			$auth_type = $auth_type[0];
			if (strcasecmp($auth_type, 'OAuth')==0) {
				/*$returned = $this->oslc->checkOauthAuthorization($auth);
				return $returned;*/
				session_set_for_authplugin('oauthprovider');
			} elseif (strcasecmp($auth_type, 'basic')==0) {
				return $this->retrieveRequestAuthHttpBasic($login);
			} else {
				throw new BadRequestException('Unsupported Authorization type : '. $auth_type .' !');
			}
		} else {
			return FALSE;
		}

	}

	/**
	 * Helper function that performs HTTP Basic authentication from request parameters/headers
	 *
	 * @param string $login
	 * @return True if auth is valid, in which case $login is modified.
	 * 		   If there was actually no auth requested, then return False, but $login will be set to null.
	 */

	private function retrieveRequestAuthHttpBasic(&$login) {
		// extract login and password from Basic auth
		$login = null;
		$password = null;

		$returned = False;

		$request = $this->getRequest();
		$auth = $request->getHeader('Authorization');
		//		print_r('Auth :'.$auth);
		//		print_r('Auth :'.$auth.'!');
		if (strlen($auth) != 0) {
			$auth = explode(' ',$auth);
			if ($auth[0] == 'Basic') {
				//print_r($auth);
				$basic = base64_decode($auth[1]);
				//print_r($basic);
				$basic = explode(':',$basic);

				$login = $basic[0];
				$password = $basic[1];
				//print_r('request username'.$login);
				//print_r('request password'.$password);
			}
			/*elseif ($auth[0] == 'OAuth') {
				session_set_for_authplugin('oauthprovider');
			}*/
			else {
				throw new BadRequestException('Unsupported auth method : '. $auth[0] .' !');
			}
		}
		if (isset($password)) {

			$config = array(
		    	'accept_schemes' => 'basic',
    			'realm'          => 'Oslc-Demo',
    			'digest_domains' => '/cm',
    			'nonce_timeout'  => 3600,
			);

			// Http authentication adapter
			$adapter = new Zend_Auth_Adapter_Http($config);

			// setup the OslcControler's Auth HTTP Basic resolver
			$basicResolver = $this->oslc->getHttpAuthBasicResolver($login, $password);

			// The authentication check will be performed by Mantis
			$adapter->setBasicResolver($basicResolver);

			$request = $this->getRequest();
			$adapter->setRequest($request);
			$response = $this->getResponse();
			$adapter->setResponse($response);

			// perform authentication check
			//$result = $this->auth->authenticate($adapter);
			$result = $adapter->authenticate();
			if (!$result->isValid()) {
				print_r('Access denied for : '. $login .' !');
			}
			//print_r($result->getCode());
			switch ($result->getCode()) {
				case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
					/** do stuff for nonexistent identity **/
					print_r('Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND');
					break;
				case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
					/** do stuff for invalid credential **/
					print_r('Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID');
					break;
				case Zend_Auth_Result::SUCCESS:
					/** do stuff for successful authentication **/
					//					print_r('Zend_Auth_Result::SUCCESS');
					$returned = True;
					break;
				default:
					/** do stuff for other failure **/
					print_r('other problem');
					break;
			}
		}

		return $returned;
	}
}
?>
