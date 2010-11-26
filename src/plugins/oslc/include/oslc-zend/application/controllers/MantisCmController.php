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
require_once($controller_dir . 'MantisOSLCConnector.php');

class MantisCmController extends CmController{
	
	/**
	 * @var oslc
	 * 
	 * This will be the OSLC-CM controller managing the business logic of the application
	 */
	private $oslc;
	private static $mantisSupportedAcceptMimeTypes = array(
				'readBugnote'=> array(
					'application/x-oslc-cm-change-request+xml' => 'xml',
					'application/xml' => 'xml',
					'text/xml' => 'xml',
				 	'application/json' => 'json',
				 	'application/x-oslc-cm-change-request+json' => 'json'
				),			 	

				'readBugnoteCollection' => array(
					'application/atom+xml' => 'xml',
					'application/json' => 'json'
				)
	);
	
	private static $supportedAcceptMimeTypes = array();
	
	public function getSupportedAcceptMimeTypes(){
		return self::$supportedAcceptMimeTypes;
	}
	
	/*public function __construct(){
		// just merge Mantis specifc action mime types with the default set
		// of supported actions mime types in OSLC-CM
		$this->getSupportedAcceptMimeTypes = array_merge(CmController::getSupportedAcceptMimeTypes(), $this->mantisSupportedAcceptMimeTypes);
	}*/
	
	/**
	 * Init Mantis REST controller.
	 */
	public function init(){
		// TODO : render this path configurable
		//		$writer = new Zend_Log_Writer_Stream('/tmp/zend-log.txt');
		//		$this->logger = new Zend_Log($writer);
		self::$supportedAcceptMimeTypes = array_merge(parent::getSupportedAcceptMimeTypes(), self::$mantisSupportedAcceptMimeTypes);
		parent::loadModelClasses('ChangeRequests');

		// now do things that relate to the REST framework
		$req = $this->getRequest();
		//print_r($req);
		//print_r("in init\n");
		if(($req->getActionName()=='post')||($req->getActionName()=='put'))
		{
			$accept = $req->getHeader('Content-Type');
		}
		elseif($req->getActionName()=='get')
		{
			$accept = $req->getHeader('Accept');
		}
		
		$action = $req->getActionName();
		//print_r("Action : ".$action);
		
		$mime = $this->checkSupportedActionMimeType($this->getSupportedAcceptMimeTypes(), $action);
		
		if($mime) {
			$accept = $mime;
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
		
		foreach (self::$supportedAcceptMimeTypes as $action => $typesarr) {
			if(($action!="post")&&($action!="put")){
				$types = array_unique(array_values($typesarr));
				$contextSwitch->addActionContext($action, $types)->initContext();
				//print_r($action);
				//print_r($types);
			}
		}
		
		// Create an OSLC Connector for Mantis.
		$this->oslc = new MantisOSLCConnector();
		
	}
	

	
		/**
	 * Performs authentication according to the configured AUTH_TYPE configured
	 *
	 * @param string $login
	 * @return True if auth is valid, in which case $login is modified.
	 * 		   If there was actually no auth requested, then return False, but $login will be set to null.
	 */

	private function retrieveAuthentication(&$login) {
		switch (AUTH_TYPE) {
			case 'basic':
				return $this->retrieveRequestAuthHttpBasic($login);
				break;
			case 'oauth':
				return $this->checkOauthAuthorization($login);
				break;
			default:
				throw new BadRequestException('Unsupported AUTH_TYPE : '. AUTH_TYPE .' !');
				break;
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

	/**
	 * Checks that OAuth authorization is correct, or fallbacks to Basic Auth
	 * 
	 * Will log-in the user corresponding to the OAuth authorization delegation
	 * 
	 * @param string $login (write)
	 */
	private function checkOauthAuthorization(&$login) {
		$request = $this->getRequest();
		$auth = $request->getHeader('Authorization');
		if ($auth) {
			$auth_type = explode(' ',$auth);
			$auth_type = $auth_type[0];
			if ($auth_type == 'OAuth') {
				$returned = $this->oslc->checkOauthAuthorization($auth);
				return $returned;
			}
			else {
				return $this->retrieveRequestAuthHttpBasic($login);
			}
		}
		else {
			return False;
		}
	}

	// Actions for the different REST methods

	/**
	 * Displays an HTML page for humans when invoked without additional path
	 */
	public function indexAction(){

	}

	public function listAction(){
		throw new BadRequestException('Method list not yet supported !');
	}
	
	/**
	 * GET REST Action handler : Get one or all changerequests
	 * 
	 * Allow /cm/bug/ or /cm/bugs/ to list all changerequests
	 *  or /cm/bug/bug_id to retrieve one specific changerequest
	 * 
	 */
	public function getAction() {
		//print_r('getAction');

		// Analyse the arguments of the GET request contained in path

		$params = $this->getRequest()->getParams();
		//print_r($params);
		//exit(0);

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
			//return;
		}		
		// handle OSLC-CM service document access
		elseif (isset($params['oslc-cm-service'])) {
			$this->_forward('oslcCmServiceDocument');
			//return;
		}
		elseif(($params['ui']=="selection")&&isset($params['project']))
		{
			$this->_forward('showSelectionUi');
			//echo "here";exit;
		}		
		elseif(preg_match("/^\/cm\/bug\/[1-9]+[0-9]*\/notes[\/]?$/", $this->getRequest()->getPathInfo()))
		{
			$this->_forward('readBugnoteCollection');
		}
		
		elseif(preg_match("/^\/cm\/notes\/[1-9]+[0-9]*[\/]?$/", $this->getRequest()->getPathInfo()))
		{
			$this->_forward('readBugnote');
		}		
		elseif(preg_match("/^\/cm\/([A-Za-z]?\/)|(0\/)|([1-9]+[0-9]*\/)|(\/)stats_activity[\/]?$/", $this->getRequest()->getPathInfo()))
		{
			$proj = $this->getRequest()->getPathInfo();
			$proj = preg_replace("/^\/cm\//","",$proj);
			$proj = preg_replace("/\/stats_activity[\/]?$/","",$proj);
			//print_r($proj);
			$this->oslc->retrieveStatsByActivity($proj);
		}
		
		elseif(preg_match("/^\/cm\/([A-Za-z]?\/)|(0\/)|([1-9]+[0-9]*\/)|(\/)stats_age[\/]?$/", $this->getRequest()->getPathInfo()))
		{
			//print_r("get stats \n");
			$proj = $this->getRequest()->getPathInfo();
			$proj = preg_replace("/^\/cm\//","",$proj);
			$proj = preg_replace("/\/stats_age[\/]?$/","",$proj);
			$this->oslc->retrieveStatsByAge($proj);
		}
		
		elseif(preg_match("/^\/cm\/([A-Za-z]?\/)|(0\/)|([1-9]+[0-9]*\/)|(\/)stats_date[\/]?$/", $this->getRequest()->getPathInfo()))
		{
			//print_r("get stats \n");
			$proj = $this->getRequest()->getPathInfo();
			$proj = preg_replace("/^\/cm\//","",$proj);
			$proj = preg_replace("/\/stats_date[\/]?$/","",$proj);
			$this->oslc->retrieveStatsByDate($proj);
		}		
		else {
			// Now, do the OSLC-CM resources access work
			
			// if no bug was mentioned, then return a resource collection
			if ((array_key_exists('project', $params))||($params['id']=='bug')||($params['id']=='bugs')) {
				// forward to an independant action so that it has its own views
				//print_r("going to readresourcecollectionAction()");
				$this->_forward('readResourceCollection');
			}
			elseif((array_key_exists('bug', $params))||(array_key_exists('bugs', $params)))
			{
				// now we're indeed getting one single resource
				// read the individual resource and pass to the view
				$this->_forward('readResource');

			
			}else
			{
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
		//print_r('putAction');

		$req = $this->getRequest();
		//print_r($req);

		// in case invoked like POST on .../cm/project/whatever we arrive to putAction
		// so we check such case and then redirect to postAction() if needed
		if ($req->isPost()) {
        	//print_r('redirect to post');
            $this->_forward('post');            
		}
		else {
		
			// otherwise it is indeed a PUT and we are trying to modify a change request
			
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
				case 'application/x-oslc-cm-change-request+xml':
				case 'application/x-oslc-cm-change-request+json':
				case 'application/xml':
				case 'application/json':
					break;
				default:
					//print_r('exception');
					throw new UnsupportedMediaTypeException('Unknown Content-Type for method put : '. $contenttype .' !');
					break;
			}

			$identifier = null;

			$params = $req->getParams();
			//print_r($params);

			if (array_key_exists('id', $params)) {
				$identifier = $req->getParam('id');
			}
			else {
				$identifier = $req->getParam('bug');

				if (! isset($identifier)) {
					throw new ConflictException('No change request id provided !');
				}
			}

			// checking if modification

			$modifiedproperties = null;

			$oslc_cm_properties = $req->getParam('oslc_cm_properties');
			if (isset($oslc_cm_properties)) {
				$modifiedproperties = explode(',', $oslc_cm_properties);
				if (array_key_exists('identifier', $modifiedproperties)) {
					throw new ConflictException('Identifier cannot be modified !');
				}
			}
			/*
			 print_r($req->isXmlHttpRequest());
			 print_r($req->getParams());
			 */
			if(APPLICATION_ENV=='testing')
			{
				$body = $_POST['xml'];
				//print_r("in testing");
				//print_r($_POST);
			}
			else
			{
				$body = file_get_contents('php://input');
			}
			//print_r("Body: ".$body."******End of body*******");
			/*
			$newresource = $req->getRawBody();
			print_r($req);
			print_r($newresource);
			*/
			// TODO: This should be done by $this->oslc
			switch($contenttype) {
				case 'application/x-oslc-cm-change-request+xml':
				case 'application/xml':
					$newchangerequest = MantisChangeRequest::CreateMantisArrayFromXml($body);
					break;
				case 'application/x-oslc-cm-change-request+json':
				case 'application/json':
					$newchangerequest = MantisChangeRequest::CreateMantisArrayFromJson($body);
					break;
			}

			if(!$this->oslc->checkChangeRequestExists($identifier))
			{
				throw new ConflictException("Change Request to be updated doesn't exist!");
			}
			else
			{
				$this->oslc->updateChangeRequest($identifier, $newchangerequest, $modifiedproperties);
			}
						
		}
	}
	
	/**
	 * Handles POST action as routed by Zend_Rest_Route
	 * 
	 * Creation of a new changerequest
	 * 
	 * May be invoked from putAction() because of Zend pecularities
	 * 
	 * @return unknown_type
	 */
	public function postAction(){

		$req = $this->getRequest();
		
		// check that we're indeed invoked by a POST request
		if(! $req->isPost()) {
			throw new Exception('postAction invoked without POST !');
		}
		
		$params = $req->getParams();
		//print_r($params);

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
		
		
		$req = $this->getRequest();
		//print_r("Request: ".$req);

		$contenttype = $req->getHeader('Content-Type');
		
		$contenttype = $contenttype ? $contenttype : 'none';

		switch($contenttype) {
			case 'application/x-oslc-cm-change-request+xml':
			case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
			case 'application/x-oslc-cm-change-request+json':
			case 'application/json':
			case 'application/xml':
				break;
			default:
				//print_r('exception');
				throw new UnsupportedMediaTypeException('Unknown Content-Type for method post : '. $contenttype .' !');
				break;
		}

		//print_r(APPLICATION_ENV);
		// used for PhpUnit tests.
		if(APPLICATION_ENV=='testing')
		{
			$body = $_POST['xml'];
			//print_r("in testing");
			//print_r($_POST);
		}
		else
		{
			$body = file_get_contents('php://input');
		}
		
		if(array_key_exists('project',$params)) {
			// create a change request
			switch($contenttype) {
				case 'application/x-oslc-cm-change-request+xml':
				case 'application/xml':
				case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
					$newchangerequest = MantisChangeRequest::CreateMantisArrayFromXml($body);
					break;
				case 'application/x-oslc-cm-change-request+json':
				case 'application/json':
					$newchangerequest = MantisChangeRequest::CreateMantisArrayFromJson($body);
					break;
			}

			$creationparams = array('project' => $params['project'],
									'new' => $newchangerequest);

			// pass the creation work to the OSLC connector
			$identifier = $this->oslc->createChangeRequest($creationparams);
		}
		elseif(array_key_exists('bug',$params))
		{
			switch($contenttype) {
				case 'application/x-oslc-cm-change-request+xml':
				case 'application/xml':
				case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
					$notes_arr = MantisChangeRequest::CreateMantisNotesArrayFromXml($body);
					break;
				case 'application/x-oslc-cm-change-request+json':
				case 'application/json':
					$notes_arr = MantisChangeRequest::CreateMantisNotesArrayFromJson($body);
					break;
			}
			//print_r($req->getPathInfo());
			$path = $req->getPathInfo();
			if (preg_match("/^\/cm\/bug\/[1-9]+[0-9]*\/notes[\/]?$/", $path)) 
			{
				$this->oslc->addBugnotes($params['bug'],$notes_arr);
				$identifier = $params['bug']."/notes";
				//print_r($notes_arr);
			}
			else
			{
				throw new BadRequestException('Incorrect syntax for bugnote URL');
			}
			
		}
		else
		{
			throw new ConflictException('Need a valid project to create change request !');
		}
	
		
		// prepare redirection
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();
		$baseURL = $this->getFrontController()->getBaseUrl();
		$controllerName = $this->getFrontController()->getDefaultControllerName();

		if(APPLICATION_ENV=='testing')
		{
			$newlocation = '/'.$controllerName.'/bug/'.$identifier;
		}
		else
		{
			$newlocation = $httpScheme.'://'.$httpHost.$baseURL.'/'.$controllerName.'/bug/'.$identifier;
		}
		print_r($newlocation);
		
		$this->getResponse()->setRedirect($newlocation,201);
		
	}

	public function deleteAction(){
		print_r('deleteAction');
		//$this->_forward('get');
		throw new BadRequestException('Method delete not yet supported !');
	}
	
	/* returns a collection of OSLC CM ChangeRequests */

	/**
	 * GET REST Action handler : Get all changerequests
	 * 
	 * Allow /cm/bug/ or /cm/bugs/ to list all changerequests
	 * 
	 * Invoked as rerouted from getAction()
	 * 
	 */
	public function readresourcecollectionAction()	{
		
		$content_type = $this->checkSupportedActionMimeType($this->getSupportedAcceptMimeTypes(), $this->getRequest()->getActionName());
		if (! $content_type) {
		  //			print_r("error");
		  throw new NotAcceptableException("Accept header ".$this->getRequest()->getHeader('Accept')." not supported!");
		}
		
		$req = $this->getRequest();
		$params = $req->getParams();
		
		// load the model
		$params = $this->oslc->init($params);
		//print_r($params);

		// construct ATOM feed-like header
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();
		$pos = strpos($requestUri, "?");
		if($pos)	{
			//removing the query string from the uri if it exists
			$requestUri = substr($requestUri, 0, $pos);
		}
		
		$requestUri = str_replace('bugs','bug', $requestUri);
		$requestUri = preg_replace("/project.*/", 'bug/', $requestUri);
		$requestUri = $requestUri.(($requestUri[strlen($requestUri)-1]=='/')?'':'/');
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
		$this->getResponse()->setHeader('Content-Type', $content_type);
		
	}
	
	/**
	 * Retrieve an individual resource and populates the view of an OSLC CM ChangeRequest
	 * 
	 */
	public function readresourceAction()
	{
		if (! $this->checkSupportedActionMimeType($this->getSupportedAcceptMimeTypes(), $this->getRequest()->getActionName())) {
			return;
		}
				
		//(preg_match("/^\/cm\/bug\/[1-9]+[0-9]*[\/]?$/", $this->getRequest()->getPathInfo()))
		$params = $this->getRequest()->getParams();
		$identifier = $params['bug'];
		// prepare resource URI
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();

		$uri = $httpScheme.'://'.$httpHost.$requestUri;
		$content_type = $this->checkSupportedActionMimeType($this->getSupportedAcceptMimeTypes(), $this->getRequest()->getActionName());
		
		if (! $content_type) {
		  //			print_r("error");
		  $this->_forward('UnknownAcceptType','error');
		  return;
		}
		
		$preparedChangeRequest = $this->oslc->getChangeRequest($identifier, $uri);
		//print_r($preparedChangeRequest);

		if (isset($preparedChangeRequest)) {

			// populate the view with the model
			foreach($preparedChangeRequest as $field => $value) {
				$this->view->{$field} = $value;
			}
			
			$this->getResponse()->setHeader('Content-Type', $content_type);
		}

		else
		{
			$this->view->missing_resource = $identifier;
			$this->_forward('ResNotFound','error');

		}
		//print($content_type);
	}
	
	public function readbugnotecollectionAction() {
		if (! $this->checkSupportedActionMimeType($this->getSupportedAcceptMimeTypes(), $this->getRequest()->getActionName())) {
			return;
		}
		
		$req = $this->getRequest();
		$params = $req->getParams();
		
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();
		$requestUri = $requestUri.(($requestUri[strlen($requestUri)-1]=='/')?'':'/');
		$bugnoteUri = preg_replace("/bug\/[1-9]+[0-9]*\//", '', $requestUri);
		$prefix = $httpScheme.'://'.$httpHost.$bugnoteUri;		

		//print_r("get bugnotes \n");
		$collection = $this->oslc->getBugnoteCollection($params['bug'], $prefix);
		
		// construct an intermediate array that will be used to populate the view
		$preparedCollection = array ('id'         => $httpScheme.'://'.$httpHost.$requestUri,
							  'collection'     => $collection);

		// populate the view with the loaded values
		foreach($preparedCollection as $key => $value) {
			$this->view->$key = $value;
		}
		//print_r($this->view);
	}
	
	public function readbugnoteAction()
	{
		$req = $this->getRequest();
		$params = $req->getParams();
		
		$httpScheme = $this->getRequest()->getScheme();
		$httpHost = $this->getRequest()->getHttpHost();
		$requestUri = $this->getRequest()->getRequestUri();
		$prefix = $httpScheme.'://'.$httpHost.$requestUri;		

		//print_r("get a single bugnote \n");
		$note = $this->oslc->getBugnote($params['notes'], $prefix);
		
		if (isset($note)) {

			// populate the view with the model
			foreach($note as $field => $value) {
				$this->view->{$field} = $value;
			}
		}

		else
		{
			$this->view->missing_resource = $params['notes'];
			$this->_forward('ResNotFound','error');

		}
		
	}
	
	/**
	 * Handle OSLC services catalog access (http://open-services.net/bin/view/Main/OslcServiceProviderCatalogV1)
	 */
	public function oslcservicecatalogAction() {
		
		$content_type = $this->checkSupportedActionMimeType($this->getSupportedAcceptMimeTypes(), $this->getRequest()->getActionName());
		if (! $content_type) {
		  //			print_r("error");
		  $this->_forward('UnknownAcceptType','error');
		  return;
		}
		
		// each project will generate its own service description
		$proj_arr = $this->oslc->getProjectList();

		$this->view->projects = $proj_arr;
		
		$this->getResponse()->setHeader('Content-Type', $content_type);
	}
	
	public function oslccmservicedocumentAction() {
		$content_type = $this->checkSupportedActionMimeType($this->getSupportedAcceptMimeTypes(), $this->getRequest()->getActionName());
		if (! $content_type) {
		  //			print_r("error");
		  $this->_forward('UnknownAcceptType','error');
		  return;
		}
		
		$req = $this->getRequest();
		$params = $req->getParams();
		$project = $params['oslc-cm-service'];
		$this->view->project = $project;

		$this->getResponse()->setHeader('Content-Type', $content_type);

	}
	
	public function showselectionuiAction()
	{
		$req = $this->getRequest();
		$params = $req->getParams();
		$project = $params['project'];
		$data = $this->oslc->getDataForSelectionUi($project);
		$this->view->data = $data;
	}
	
}

?>
