<?php

/**
 * This file is (c) Copyright 2010 by Olivier BERGER, Institut
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

/**
 * This is the FusionForge OSLC-CM controller, which specializes the generic 
 * OSLC-CM controller (in oslc.inc.php) 
 */

/* $Id:$ */

$controller_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
require_once($controller_dir . 'OSLCConnector.php');

$model_dir = APPLICATION_PATH.'/models/';
require_once($model_dir . 'fusionforge.inc.php');

require(APPLICATION_PATH.'/../../../../../common/include/env.inc.php');
require_once $gfwww.'include/pre.php';

require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'include/Group.class.php';
require_once $gfcommon.'include/FusionForge.class.php';


/**
 * FusionForge OSLC server controller
 *
 * @author olivier
 *
 */
class FusionForgeOSLCConnector extends OslcConnector {


	private static $status_arr = array('open'=>1, 'closed'=>2, 'deleted' => 3);
	private static $query_properties = array(
		'dc:identifier',
		'dc:title',
		'dc:description',
		'dc:creator',
		'oslc_cm:status',
		'helios_bt:priority',
		'helios_bt:assigned_to',
		'dc:modified',
		'dc:created'
	);
	private static $orderBy_properties = array(
		'dc:identifier',
		'dc:title',
		'dc:created',
		'dc:creator',
		'dc:closed',
		'helios_bt:assigned_to',
		'helios_bt:priority'
	);
	
	/**
	 * Filter parameters provided in the REST GET request to check whether mandatory ones are set.
	 * 
	 * @param array $params
	 * @return array 
	 */
	public function filterRequestParams($params) {

		// the args that need to be passed to initialize the model
		$modelparams = array();
		
		// Process the args provided by Zend REST
		if (is_array($params)) {
			if(!isset($params['project'])){
				throw new Exception('Missing project id !');
			}
			elseif(!isset($params['tracker'])) {
				throw new Exception('Missing tracker id resource for project '.$params['project'].' !');
			} else {
				//$modelparams['project'] = $params['project'];
				//$modelparams['tracker'] = $params['tracker'];
			}
			
			/*if(isset($params['bug'])) {
				$modelparams['bug'] = $params['bug'];
			}*/
		}

		//return $modelparams;
		return $params; 		
	}
	
	/**
	 * Checks whether a change request exists inside FusionForge trackers. 
	 * @param int $id change request id.
	 * @return bool
	 */
	public function checkChangeRequestExists($id) {
		$returned = false;
		$art_obj = artifact_get_object($id);
		if (!$art_obj || !is_object($art_obj)) {
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * 
	 *  Constructs the model from fusionforge db by fetching the change requests requested
	 *  through an oslc query.
	 *  @param array $filter array of the query settings.
	 *  @TODO: Implement oslc_searchTerms
	 *  @TODO: Implement oslc_select
	 */
	protected function changeRequestsQuery($params) {
		$query = array();
		$filter = $params['filter'];
		
		if(array_key_exists('limit', $filter))	{
			$query['max_rows'] = $filter['limit'];		
		}else 	{
			$query['max_rows'] = 0;
		}
		
		if(array_key_exists('offset', $filter))	{
			$query['offset'] = $filter['offset'];		
		}else 	{
			$query['offset'] = 0;		
		}

		if(array_key_exists('where', $filter))	{
			foreach($filter['where']['terms'] as $term)	{
				if($term[0]=='=')	{	
					$term[2] = str_replace("\"", "", $term[2]);
					switch($term[1])	{
						case 'oslc_cm:status':
							if (array_key_exists($term[2],self::$status_arr))	{
								$query['status'] = self::$status_arr[$term[2]];
							}else	{
								throw new BadRequestException('Invalid oslc_cm:status value specified!');
							}
							break;
						case 'helios_bt:assigned_to':
							if ($assignee = user_get_object_by_name($term[2])) {
								$query['assigned_to'] = $assignee->getID();
							}else {
								throw new BadRequestException("Invalid helios_bt:assigned_to: " . $term[2]); 
							}
							break;
						default:  throw new BadRequestException("Invalid attribute ".$term[1]." specified in oslc_where! only oslc_cm:status|assigned_to are accepted.");
						break;
					}
				}
			}		
		}
		
		if (!array_key_exists('status', $query)) {
			$query['status'] = '';
		}
		if (!array_key_exists('assigned_to', $query)) {
			$query['assigned_to'] = '';
		}
		
		if (array_key_exists('orderBy', $filter)) {
			if (count(array_keys($filter['orderBy'])) > 1) {
				throw new ConflictException("Sorting over more than one attribute is not supported by FusionForge API!");
			} else {
				switch ($filter['orderBy'][0][1]) {
					case 'dc:identifier': 
						$query['order_col'] = 'artifact_id';
						break;
					case 'dc:title':
						$query['order_col'] = 'summary';
						break;
					case 'dc:created': 
						$query['order_col'] = 'open_date';
						break;
					case 'dc:closed':
						$query['order_col'] = 'close_date';
						break;
					case 'helios_bt:assigned_to': 
						$query['order_col'] = 'assigned_to';
						break;
					case 'dc:creator': 
						$query['order_col'] = 'submitted_by';
						break;
					case 'helios_bt:priority': 
						$query['order_col'] = 'priority';
						break;
					default: 
						throw new ConflictException("Sorting over attribute ".$filter['orderBy'][0][1]." is not supported in FusionForge API !");
						break;
				}
			}
			$query['sort'] = $filter['orderBy'][0][0];
		} else {
			$query['order_col'] = '';
			$query['sort'] = '';
		}
		if (array_key_exists('searchTerms', $filter)) {
			$terms = "";
			foreach ($filter['searchTerms'] as $term) {
				$terms = $terms.$term." " ;
			}
			$query['search'] = substr($terms, 0, -1);
		} else {
			$query['search'] = "";
		}
		$query['set'] = false;
		
		$group_id = $params['project'];
		$atid = $params['tracker'];
		
		$group = group_get_object($group_id);
		if (!$group || !is_object($group)) {
			exit_no_group();
		}
		if ($group->isError()) {
			if($group->isPermissionDeniedError()) {
				throw new Exception('Error : permission denied');
			} else {
				throw new Exception('Error '. $group->getErrorMessage());
			}
		}
		
		$ath = new ArtifactTypeHtml($group,$atid);
		if (!$ath || !is_object($ath)) {
			throw new Exception('Error '. 'ArtifactType could not be created');
		}
		if ($ath->isError()) {
			//print_r($ath->isError());
			if($ath->isPermissionDeniedError()) {
				//print_r($ath->isPermissionDeniedError());
				throw new Exception('Error : permission denied');
			} else {
				throw new Exception('Error '. $ath->getErrorMessage());
			}
		}
		
		$af = new ArtifactFactory($ath);
		if (!$af || !is_object($af)) {
			throw new Exception('Error Could Not Get ArtifactFactory');
		} elseif ($af->isError()) {
			throw new Exception('Error '. $af->getErrorMessage());
		}
		
		// parametrize the tracker query.
		$af->setup($query['offset'],
			$query['order_col'],
			$query['sort'],
			$query['max_rows'],
			$query['set'],
			$query['assigned_to'],
			$query['status'],
			array()
		);
		
		// Force these properties of ArtifactQuery object to take the values we want.
		$af->status = $query['status'];
		$af->assigned_to = $query['assigned_to'];
		$af->offset = $query['offset'];
		$af->max_rows = $query['max_rows'];
		$af->sort = $query['sort'];
		$af->order_col = $query['order_col'];
		
		// Can add here values for 'details' and 'summary'. Values comes from oslc_searchTerms
		//$af->summary = $query['search'];
		//$af->details =	$query['search'];
		
		// query the DB for requested artifacts 
		$art_arr =& $af->getArtifacts();
		
		if ($art_arr === false) {
			throw new Exception('Error '. $af->getErrorMessage());
		}
		// instanciate the model from the returned artifacts
		if(isset($params['fields'])){
			$this->changerequests = new ChangeRequestsFusionForgeDb($art_arr, $params['fields']);
		} else {
			$this->changerequests = new ChangeRequestsFusionForgeDb($art_arr);
		}
	}
	/**
	 * Retrieves needed data for the display of the creation UI.
	 * 
	 * @param int $project project id
	 * @param int $tracker tracker id
	 * 
	 * @return array $data tracker fields and their respective possible values.
	 */
	public function getDataForCreationUi($project, $tracker) {
		$data = array();
		$data['project'] = $project;
		$data['tracker'] = $tracker;

		$group = group_get_object($project);
		if (!$at = new ArtifactType($group, $tracker)){
			throw new Exception('Error : Could not instanciate project Tracker');
		} else {
			// construct data for tracker extra fields and their values.
			$extrafields = $at->getExtraFields();
			$keys = array_keys($extrafields);
			for ($k = 0; $k<count($keys); $k++){
				$i = $keys[$k];
				if ($extrafields[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SELECT){
					$efelements = $at->getExtraFieldElements($extrafields[$i]);
					foreach ($efelements as $key => $value){
						$data[$extrafields[$i]['field_name']][] = $efelements[$key]['element_name'];
					}
				} elseif ($extrafields[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_TEXT) {
					$data[$extrafields[$i]['field_name']] = '';
				}
			}

			// Add assigned to data. We use default FusionForge UI for assigned_to selectBox. 
			$ath = new ArtifactTypeHtml($group,$tracker);
			$data['assigned_to'] = $ath->technicianBox('assigned_to'); 

			// Add priority data. We use default FusionForge UI for priority selectBox.
			$data['priority'] = build_priority_select_box('priority');
						
			// Add summary and detailed description.
			$data['summary'] = '';
			$data['description'] = '';
			
			//return data for creation UI.
			return $data;
		}
	}

	/**
	 * Retrieves needed data for the display of the selection UI.
	 * 
	 * @param int $project project id
	 * @param int $tracker tracker id
	 * 
	 * @return array $data tracker fields and their respective possible values.
	 */
	public function getDataForSelectionUi($project, $tracker) {
		$data = array();
		$data['project'] = $project;
		$data['tracker'] = $tracker;

		$group = group_get_object($project);
		if (!$at = new ArtifactType($group, $tracker)){
			throw new Exception('Error : Could not instanciate project Tracker');
		} else {
			// Construct array for oslc.where with all possible values for each attribute
			// Currently only oslc_cm:status and helios_bt:assigned_to are supported for
			// oslc.where query.
			$engine = RBACEngine::getInstance() ;
			$techs = $engine->getUsersByAllowedAction ('tracker', $tracker, 'tech') ;
			foreach ($techs as $tech) {
				$data['where']['assigned_to'][] = $tech->getRealName();
			}
			$data['where']['status'] = self::$status_arr;
			
			//construct array for oslc.properties with all possible values for each attribute
			$data['properties'] = self::$query_properties;
			
			// Construct array for oslc.orderBy 
			$data['orderBy'] = self::$orderBy_properties;
			
			// Return data needed for selection UI.
			return $data;
		}
	}

	/*
	 * Constructs the model from the FusionForge DB by fetching the requested changeRequests.
	 *
	 * TODO : remove provided project if not found in DB
	 * 
	 * @param array $params contains 'project' + 'tracker' if filtered on particular project's tracker
	 */
	public function fetchChangeRequests($params) {
		$art_arr	= array();

		// what the DB will be queried on
		$group_id = null;
		$atid = null;

		if (is_array($params)) {
			
			if (isset($params['project'])) {
				$group_id = $params['project'];
			}
			if (isset($params['tracker'])) {
				$atid = $params['tracker'];
			}
			if (isset($params['bug'])) {
				$art_id = $params['bug'];
				$art_obj = artifact_get_object($art_id);
				$art = $art_obj->fetchData($art_id);
				$this->changerequests = new ChangeRequestsFusionForgeDb($art);
			}

			$group = group_get_object($group_id);
			if (!$group || !is_object($group)) {
				exit_no_group();
			}
			if ($group->isError()) {
				if($group->isPermissionDeniedError()) {
					throw new Exception('Error : permission denied');
				} else {
					throw new Exception('Error '. $group->getErrorMessage());
				}
			}

			$ath = new ArtifactTypeHtml($group,$atid);
			if (!$ath || !is_object($ath)) {
				throw new Exception('Error '. 'ArtifactType could not be created');
			}
			if ($ath->isError()) {
				//print_r($ath->isError());
				if($ath->isPermissionDeniedError()) {
					//print_r($ath->isPermissionDeniedError());
					throw new Exception('Error : permission denied');
				} else {
					throw new Exception('Error '. $ath->getErrorMessage());
				}
			}


			$af = new ArtifactFactory($ath);

			if (!$af || !is_object($af)) {
				throw new Exception('Error Could Not Get ArtifactFactory');
			} elseif ($af->isError()) {
				throw new Exception('Error '. $af->getErrorMessage());
			}

			$_assigned_to = '';
			$_status = '';
			$set=false;

			// parameters of the query
			$af->setup(0,'','',0,$set,$_assigned_to,$_status);

			// query the DB for requested artifacts 
			$art_arr =& $af->getArtifacts();

			if ($art_arr === false) {
				throw new Exception('Error '. $af->getErrorMessage());
			}
			// instanciate the model from the returned artifacts
			if(isset($params['fields'])){
				$this->changerequests = new ChangeRequestsFusionForgeDb($art_arr, $params['fields']);
			} else {
				$this->changerequests = new ChangeRequestsFusionForgeDb($art_arr);
			}
		}
	}
	
	public function fetchChangeRequest($identifier, $uri, $requested_fields=array()) {
		$art_obj =& artifact_get_object($identifier);
		if(!$art_obj){
			throw new NotFoundException('Change Request not found');
		}
		$art = array($art_obj);
		
		$changerequest = new ChangeRequestsFusionForgeDb($art, $requested_fields);

		return $this->prepareChangeRequest($changerequest[$identifier], $uri);
	}
	
	/**
	 * Updates an existant FusionForge ChangeRequest in the tracker DataBase.
	 * @param int $identifier id of the ChangeRequest within FusionForge tracker
	 * @param ChangeRequest the change request given as input for the PUT request.
	 * @param array $props array of the properties that PUT request is going to change 
	 * @return boolean true if success, false otherwise.
	 */
	public function updateChangeRequest($identifier, $changerequest, $props) {
		// Get the artifact data using its ID. 
		$art_obj =& artifact_get_object($identifier);
		$art = $art_obj->data_array;

		$cm_request = $changerequest->container;

		$terms = array('dcterms:','helios_bt:','oslc_cm');
		foreach($props as &$prop) {
			$prop = str_replace($terms,"",$prop);
			//echo $prop;
		}
		
		// Check all the mandatory fields for an artifact update request.
		
		// dc:title ===> summary 
		if(in_array('title',$props))
		{
			if(isset($cm_request['title']))
			{
				$art['summary'] = $cm_request['title'];			// mandatory
			}
			else
			{
				throw new BadRequestException("dc:title mentioned in the request query not found in request body!");
			}
		}
		
		// dc:description ===> details 
		if(in_array('description',$props))
		{
			if(isset($cm_request['description']))
			{
				$art['details'] = $cm_request['description'];			// mandatory
			}
			else
			{
				throw new BadRequestException("dc:decription mentioned in the request query not found in request body!");
			}
		}
		
		// helios_bt:priority ===> priority 
		if(in_array('priority',$props))
		{
			if(isset($cm_request['priority']))
			{
				$art['priority'] = $cm_request['priority'];			// mandatory
			}
			else
			{
				throw new BadRequestException("helios_bt:priority mentioned in the request query not found in request body!");
			}
		}
		
		// oslc_cm:status ===> status 
		if(in_array('status',$props))
		{
			if(isset($cm_request['status']))
			{
				$art['status_id'] = self::$status_arr[$cm_request['status']];			// mandatory
			}
			else
			{
				throw new BadRequestException("oslc_cm:status mentioned in the request query not found in request body!");
			}
		}
		
		//helios_bt:assigned_to ====> assigned_to
		if(in_array('assigned_to', $props))
		{
			if(isset($cm_request['assigned_to']))
			{
				$art['assigned_to'] = $cm_request['assigned_to'];
			}
			else 
			{
				throw new BadRequestException("helios_bt:assigned_to mentionned in the request query not found in request body!");
			}
		}
		
		
		$canned_response=100;
		
		// We assume that we don't change the artifact type (bug, task, etc)
		// in PUT request. 
		$new_artifact_type_id = $art_obj->ArtifactType->getID();
		
		//TODO: figure out if a follow up is in OSLC specs and if it is the case include it.
		$follow_up_msg = '';
		
		if(!$art_obj->update($art['priority'],$art['status_id'],$art['assigned_to'],$art['summary'],$canned_response,$follow_up_msg,$new_artifact_type_id,array(),$art['details']))
		{
			throw new Exception($art_obj->getErrorMessage());
		}
	}
	
	/**
	 * Creates a new change request in FusionForge tracker 
	 * 
	 * @param array $creation_params array that contain:change request data, tracker id and project id. 
	 * @return int $identifier Id in FusionForge tracker of the newly created change request.
	 */
	public function createChangeRequest($creation_params){
		$cm_request = $creation_params['new']->container;

		$grp = group_get_object($creation_params['project']);

		if(!$grp || !is_object($grp)){
			throw new Exception('Error: Could not get project. Please give a Valid project identifier.');
		}
		
		$at = new ArtifactType($grp, $creation_params['tracker']);
		if (!$at || !is_object($at)) {
			throw new Exception('Error: Could Not Get ArtifactType. Please give a valid tracker identifier.', $code);
		}
		
		$a = new Artifact($at);
		if (!$a || !is_object($a)) {
			throw new Exception('Error: Could Not Get Artifact');
		}
		
		// Check that Mandatory fields are given.
		if (!isset($cm_request['title'])) {
			throw new BadRequestException('Mandatory field "Title" missing!!');
		}
		if (!isset($cm_request['description'])) {
			throw new BadRequestException('Mandatory field "Description" missing!!');
		}
		// Proceed to Change request creation in FusionForge Tracker.
		if (!$a->create($cm_request['title'], $cm_request['description'])){
			throw new Exception($a->getErrorMessage());
		}else {
			return $a->getID();
		}

	}

	/**
	 * Gets the projects list. Needed for a service catalog creation
	 */
	public function getProjectsList()
	{
		return $this->getProjects();
	}

	/**
	 * gets the list of public projects Names
	 */
	private function getProjects()
	{
		$fusionforge = new FusionForge();
		$projects_names = $fusionforge->getPublicProjectNames();
		// manage errors on $projects_names here ...
		$projects = group_get_objects_by_name($projects_names);
		// manage errors on $projects here ...
		return $this->createProjectsArray($projects);
	}  
	
	/**
	 *  Converts projects objects into a single projects array.
	 */
	private function createProjectsArray($projects)
	{
		$return = array();
		foreach($projects as $prj_idx => $project){
			$data = $project->data_array;
			$return[$prj_idx] = array(
				'id'                => $data['group_id'], 
				'name'              => $data['group_name'],
				'homepage'          => $data['homepage'],
				'is_public'         => $data['is_public'],
				'status'            => $data['status'],
				'unix_group_name'   => $data['unix_group_name'],
				'short_description' => $data['short_description'],
				'scm_box'           => $data['scm_box'],
				'register_time'     => $data['register_time']
			);
		}
		return $return;
	
	}
	
	/**
	 * 
	 * Returns a formatted array of trackers of a project
	 * Trackers Array is indexed by tracker ids and for each one we set
	 * the id, name and description.
	 * 
	 * @param int $project project id.
	 * 
	 * @return Array of trackers
	 */
	public function getProjectTrackers($project) {
		$group = group_get_object($project);
		$trackers = array();
		$atf = new ArtifactTypeFactory($group);
		foreach($atf->getArtifactTypes() as $at) {
			$trackers[$at->getID()] = array(
				'id'          => $at->getID(),
				'group_id'    => $project,
				'name'        => $at->getName(),
				'description' => $at->getDescription()
			);
		}
		return $trackers;
	}
	
	public function getHttpAuthBasicResolver($login, $password) {
		$basicResolver = new FusionForge_Http_Auth_Resolver($login, $password);
		return $basicResolver;
	}
}

// HTTP auth adapater's resolver using FusionForge APIs for user + password verification
class FusionForge_Http_Auth_Resolver implements Zend_Auth_Adapter_Http_Resolver_Interface
{
	// the query's elements
	protected $username = null;
	protected $password = null;

	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	
	/**
	 * Check if the password matches with FusionForge database
	 */
	public function resolve($username, $realm) {
		// Include FusionForge Sessions Management API 
		//require_once $gfcommon.'include/session.php';
		
		// Try and login the user into fusionforge.
		$success=session_login_valid(strtolower($this->username), $this->password);
        if ($success) {
			return $this->password; 
        } else {
        	return false;
        } 
	}

}
?>
