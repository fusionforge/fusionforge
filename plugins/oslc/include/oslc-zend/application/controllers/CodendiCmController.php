<?php
/**
 * Copyright (c) Institut TELECOM, 2010. All Rights Reserved.
 *
 * Originally written by Sabri LABBENE, 201O
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once ('CmController.php');
require_once ('CodendiOSLCConnector.php');

class CodendiCmController extends CmController {
    /**
     * @var oslc
     *
     * This will be the OSLC-CM controller managing the business logic of the application
     */
    private $oslc;

    /**
     * Defines accepted mime-types for queries, and corresponding
     * format of output
     *
     * Order is important for the XML variants :
     * the first one is the default returned when only basic XML is required
     *
     * @var array
     */
    private static $supportedAcceptMimeTypes = array();

    /**
     * Init Codendi REST controller.
     */
    public function init() {
    self::$supportedAcceptMimeTypes = parent::getSupportedAcceptMimeTypes();

    parent::loadModelClasses('ChangeRequests');

    // Now do things that relate to the REST framework

    $req = $this->getRequest();

    if(($req->getActionName()=='post')||($req->getActionName()=='put'))	{
        $accept = $req->getHeader('Content-Type');
    }
    elseif($req->getActionName()=='get') {
        $accept = $req->getHeader('Accept');
    }

    $action = $req->getActionName();

    $mime = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $action);
    if($mime) {
        $accept = $mime;
    }

    // determine output format
    if (isset(self::$supportedAcceptMimeTypes[$action])) {
        if (isset(self::$supportedAcceptMimeTypes[$action][$accept])) {
            $format = self::$supportedAcceptMimeTypes[$action][$accept];
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

    // Create an OSLC Controller for Codendi.
        $this->oslc = new CodendiOSLCConnector();
    }

    public function getAction(){
        $params = $this->getRequest()->getParams();

        // Check if Authorization header was set in the request.
        $auth = $this->getRequest()->getHeader('Authorization');
        if (strlen($auth) != 0) {
            // check authentication
            if(!$this->retrieveAuthentication($login)){
                throw new Exception('Invalid authentication provided !');
            }
        }

        // handle OSLC services catalog access (http://open-services.net/bin/view/Main/OslcServiceProviderCatalogV1)
        if ( isset($params['id']) && ($params['id'] == "oslc-services")) {
            $this->_forward('oslcServiceCatalog');
            return;
        }

        // handle OSLC-CM service document access
        elseif (isset($params['oslc-cm-service'])) {
            $this->_forward('oslcCmServiceDocument');
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
        } else {
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
                case 'application/x-oslc-cm-change-request+xml':
                case 'application/x-oslc-cm-change-request+json':
                case 'application/xml':
                    break;
                default:
                    throw new Exception('Unknown Content-Type for method put : '. $contenttype .' !');
                    break;
            }

            $identifier = null;

            $params = $req->getParams();

            if (array_key_exists('id', $params)) {
                $identifier = $req->getParam('id');
            } else {
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
                case 'application/x-oslc-cm-change-request+xml':
                case 'application/xml':
                    // extract values from XML
                    $newchangerequest = CodendiChangeRequest::CreateCodendiArrayFromXml($body);
                    break;
                case 'application/x-oslc-cm-change-request+json':
                    // extract values from JSON.
                    $newchangerequest = CodendiChangeRequest::CreateCodendiArrayFromJson($body);
                    break;
                default:
                	break;
            }

            if(!$this->oslc->checkChangeRequestExists($identifier))	{
                throw new Exception("Change Request to be updated doesn't exist!");
            } else {
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
            case 'application/x-oslc-cm-change-request+xml':
            case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
            case 'application/x-oslc-cm-change-request+json':
            case 'application/json':
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
        //print $body;

        if(array_key_exists('project',$params)) {
            if (array_key_exists('tracker', $params)) {
                // create a change request
                switch($contenttype) {
                    case 'application/x-oslc-cm-change-request+xml':
                    case 'application/xml':
                    case 'application/x-oslc-cm-change-request+xml; charset=UTF-8':
                        $newchangerequest = CodendiChangeRequest::CreateCodendiArrayFromXml($body);
                        break;
                    case 'application/x-oslc-cm-change-request+json':
                        $newchangerequest = CodendiChangeRequest::CreateCodendiArrayFromJson($body);
                        break;
                }

                $creationparams = array('project' => $params['project'],
                                        'tracker' => $params['tracker'],
                                        'new' => $newchangerequest);

                // pass the creation work to the OSLC connector
                $identifier = $this->oslc->createChangeRequest($creationparams);
            } else {
                throw new ConflictException('Need a valid tracker to create a change request');
            }
        } else {
            throw new ConflictException('Need a valid project and tracker to create change request !');
        }

        // prepare redirection
        $httpScheme = $this->getRequest()->getScheme();
        $httpHost = $this->getRequest()->getHttpHost();
        $requestUri = $this->getRequest()->getRequestUri();
        $baseURL = $this->getFrontController()->getBaseUrl();
        $controllerName = $this->getFrontController()->getDefaultControllerName();

        if(APPLICATION_ENV=='testing') {
            $newlocation = '/'.$controllerName.'/project/'.$params['project'].'/tracker/'.$params['tracker'].'/bug/'.$identifier;
        } else {
            $newlocation = $httpScheme.'://'.$httpHost.$baseURL.'/'.$controllerName.'/project/'.$params['project'].'/tracker/'.$params['tracker'].'/bug/'.$identifier;
        }

        //logout the user
        session_logout();

        //redirect to new change request
        $this->getResponse()->setRedirect($newlocation,201);
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
        $content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
        if (! $content_type) {
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

            $this->getResponse()->setHeader('Content-Type', $content_type);
        } else {
            $this->view->missing_resource = $identifier;
            $this->_forward('ResNotFound','error');
        }
    }

    public function readresourcecollectionAction()	{

        $content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
        if (!$content_type) {
            throw new NotAcceptableException("Accept header ".$this->getRequest()->getHeader('Accept')." not supported!");
            return;
        }

        $req = $this->getRequest();
        $params = $req->getParams();

        // load the model. Will fetch requested change requests from the db.
        $params = $this->oslc->init($params);

        // construct ATOM feed-like header
        $httpScheme = $this->getRequest()->getScheme();
        $httpHost = $this->getRequest()->getHttpHost();
        $requestUri = $this->getRequest()->getRequestUri();
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
     * Handle OSLC services catalog access.
     */
    public function oslcservicecatalogAction() {

        $content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
        if (!$content_type) {
            throw new NotAcceptableException("Accept header ".$this->getRequest()->getHeader('Accept')." not supported!");
            return;
        }

        // each project will generate its own service description
        $proj_arr = $this->oslc->getProjectsList();

        $this->view->projects = $proj_arr;

        $this->getResponse()->setHeader('Content-Type', $content_type);
    }

    /**
     *
     * Handles OSLC service document (service document) access.
     * TODO: Implement service document details.
     */
    public function oslccmservicedocumentAction() {
        $content_type = parent::checkSupportedActionMimeType(self::$supportedAcceptMimeTypes, $this->getRequest()->getActionName());
        if(!$content_type) {
            throw new NotAcceptableException("Accept header ".$this->getRequest()->getHeader('Accept')." not supported!");
            return;
        }

        $req = $this->getRequest();
        $params = $req->getParams();
        $project = $params['oslc-cm-service'];
        $this->view->project = $project;

        $this->getResponse()->setHeader('Content-Type', $content_type);
    }

    /**
     * Performs authentication according to the configured AUTH_TYPE configured
     *
     * @param string $login
     * @return True if auth is valid, in which case $login is modified.
     * If there was actually no auth requested, then return False, but $login will be set to null.
     */
    private function retrieveAuthentication(&$login) {
        switch (AUTH_TYPE) {
            case 'basic':
                return $this->retrieveRequestAuthHttpBasic();
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
     * @return True if auth is valid, in which case $login is modified.
     * If there was actually no auth requested, then return False, but $login will be set to null.
     */
    private function retrieveRequestAuthHttpBasic() {
        // extract login and password from Basic auth
        $login = null;
        $password = null;

        $return = False;

        $request = $this->getRequest();
        $auth = $request->getHeader('Authorization');

        if (strlen($auth) != 0) {
            $auth = explode(' ',$auth);
            if($auth[0] == 'Basic') {
                $basic = base64_decode($auth[1]);
                $basic = explode(':',$basic);

                $login = $basic[0];
                $password = $basic[1];
            } else {
                throw new BadRequestException('Unsupported auth method : '. $auth[0] .' !');
            }
        }
        // Do authentication in Codendi
        if(isset($password)) {
            $user = UserManager::instance()->login($login, $password);
            if($user->isLoggedIn()) {
            	$return = true;
            } else {
            	$return =  false;
            }
        }

        return $return;
    }
}

?>
