<?php

/**
 * This file is (c) Copyright 2009 by Olivier BERGER, Madhumita DHAR, Institut
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
 * This is the Mantis OSLC-CM connector, which specializes the generic 
 * OSLC-CM controller (in oslc.inc.php) 
 */

/* $Id:$*/

// load base class OslcConnector
$controller_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

// load Mantis specific classes
require_once($controller_dir . 'OSLCConnector.php');

// load Mantis specific model classes
$model_dir = APPLICATION_PATH.'/models/';
require_once($model_dir . 'mantis.inc.php');

// load MantisBt internal APIs
// MANTIS_DIR is set in config.inc.php
$t_mantis_dir = MANTIS_DIR;

// TODO : explain the following line :
$g_bypass_headers = true;
require_once( $t_mantis_dir . 'core.php' );

// we then need to make sure all globals are properly initialized (important when in phpunit)
// see tests/mantis-config_inc.php(.example) for the setting of 'MANTIS_GLOBALS_SETAGAIN'
defined('MANTIS_GLOBALS_SETAGAIN') ||
		define('MANTIS_GLOBALS_SETAGAIN', '0');
// by default nothing like this is done, so loading core.php is enough to have all globals set as needs be
// only when in phpunit is this set and this hack is done
if(MANTIS_GLOBALS_SETAGAIN == 1) {
	foreach(get_defined_vars() as $key=>$value)
	{
	       if((substr($key,0,2)=="g_")&&(!array_key_exists($key, $GLOBALS)))
	       {
	       		if(!isset($GLOBALS[$key])) {
	       			//print('set : $GLOBALS['.$key.']='.$value);
	               $GLOBALS[$key] = $value;
	       		}
	       }
	}
}

require_once( $t_mantis_dir . 'core/summary_api.php' );


foreach(get_defined_vars() as $key=>$value)
{
       if((substr($key,0,2)=="g_")&&(!array_key_exists($key, $GLOBALS)))
       {
       		if(!isset($GLOBALS[$key])) {
       			//print('set : $GLOBALS['.$key.']='.$value);
               $GLOBALS[$key] = $value;
       		}
       }
}
// Mantis specific classes used by the controller

// HTTP auth adapater's resolver using Mantis APIs for user + password verification
class Mantis_Http_Auth_Resolver implements Zend_Auth_Adapter_Http_Resolver_Interface
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
	 * Check if the password matches with Mantis database
	 */
	public function resolve($username, $realm)
	{

		//print_r('username :'. $username);
		//print_r('realm :'. $realm);
		$returned = false;

		//		print_r('login :'.$this->username);
		//		print_r('password :'.$this->password);
		//		exit(0);

		// get user id
		$t_user_id = user_get_id_by_name( $username );
		// retrieve the user's encoded pasword
		$password = user_get_field( $t_user_id, 'password' );

		// check if the encoding of the provided password matches the one retrieved from DB
		$encoded_pass = auth_process_plain_password($this->password , null, MD5 );

		$user_id = null;
		// if passwords match, we can return the clear text password to the HTTP adapter
		if ( $encoded_pass == $password ) {

			$return_val = auth_attempt_script_login( $username, $this->password );
			//print_r('login'.$return_val.'!');
			//	echo $return_val;

			if ($return_val) {
				$user_id = auth_get_current_user_id();
				//print_r('user :');
				//print_r($user_id);
			}
		}
		if (!isset($user_id)) {
			throw new ForbiddenException('Mantis user/password provided were incorrect!');
		}
		else {
			$returned = $this->password;
		}

		return $returned;
	}
}

/**
 * Mantis OSLC server connector
 *
 * @author olivier
 *
 */
class MantisOSLCConnector extends OSLCConnector {


	private static $status_arr = array('new'=>10, 'feedback'=>20, 'acknowledged'=>30, 'confirmed'=>40, 'assigned'=>50, 'resolved'=>80, 'closed'=>90);
	
	private static $priority_arr = array('none'=>10, 'low'=>20, 'normal'=>30, 'high'=>40, 'urgent'=>50, 'immediate'=>60);
	
	private static $severity_arr = array('feature'=>10, 'trivial'=>20, 'text'=>30, 'tweak'=>40, 'minor'=>50, 'major'=>60, 'crash'=>70, 'block'=>80);
	
	private static $reproducibility_arr = array('always'=>10, 'sometimes'=>30, 'random'=>50, 'have not tried'=>70, 'unable to duplicate'=>90, 'not applicable'=>100);
	
	private static $where_params = array(
		'dc:creator',
		'dc:type',
		'mantisbt:severity',
		'mantisbt:status',
		'mantisbt:priority',
		'mantisbt:target_version',
		'mantisbt:version_number',
		'mantisbt:project'
	);
	
	private static $orderBy_params = array(
		'mantisbt:priority',
		'dc:identifier',
		'dc:type',
		'mantisbt:severity',
		'mantisbt:status',
		'dc:modified',
		'dc:title'
	);
	
	private static $properties_params = array( 	//TODO synchronize with models/mantis.inc.php 
												//where the properties are implemented
		'dc:identifier',
		'dc:title',
		'dc:description',
		'dc:creator',
		'mantisbt:project',
		'mantisbt:status',
		'mantisbt:priority',
		'mantisbt:severity',
		'mantisbt:version',
		'mantisbt:target_version',
		'dc:modified',
		'dc:created',
		'mantisbt:version_number' 
	);
	
	/**
	 * Filter only meaningful parameters provided in the REST GET request
	 * 
 	 * Initially $params may contain 'id' => 'bug' or 'id' => 'bugs' for 
	 * all resources to be loaded or 'bug' => identifier for one 
	 * resource, so a filtering and normalization is needed
	 *
	 * @param array $params
	 * @return array
	 */
	protected function filterRequestParams($params) {
	
		// if only one bug requested
		$identifier = null;

		// if a project  is filtered on
		$project = null; //by default for all projects

		//print_r($params);
		// Process the args provided by Zend REST
		// potential args that matter :
		//  'id' => 'bug[s]' : all bugs to be returned
		//  'bug' => bugid : only one bug
		//  'project' => projid : only one project's bugs
		//  none of these : all bugs

		// if ../cm/anything_but_slash
		if (is_array($params)) {
			if (array_key_exists('id', $params)) {
				// anything but ../cm/bug or ../cm/bugs
				if ( ($params['id'] != "bug") && ($params['id'] != "bugs")) {
					throw new NotFoundException('Unknown resource '.$params['id'].' !');
				}
			}
			else {
				// case of ../cm/project/projid
				if (array_key_exists('project', $params)) {
					$project = $params['project'];
				}
				// case of ../cm/bug/bugid
				if (array_key_exists('bug', $params)) {
					$identifier = $params['bug'];
				}
				// forbid ../cm/bugs/whatever
				if (array_key_exists('bugs', $params)) {
					throw new BadRequestException('Incorrect resource bugs['.$params['bugs'].'] !');
				}
			}
		}
		
		// the args that need to be passed to initialize the model
		$modelparams = array();
		
		if(isset($project)) {
			$modelparams['project'] = $project;
		}
		if ($identifier) {
			$modelparams['bug'] = $identifier;
		}
		
		return $modelparams;		
	}
	
	protected function createQueryFromFilter($filter)
	{
		//print_r($filter);
		$query = array();
		$query['type'] = 1;
		$query['view_type'] = "advanced";
		$query['user_monitor'][]= 0;
		$query['handler_id'][] = 0;
		$query['show_resolution'][] = 0;
		$query['show_profile'][] = 0;
		$query['view_state'] = 0;
		$query['sticky_issues'] = "on";
		$query['highlight_changed'] = 6;
		$query['relationship_type'] = -1;
		$query['relationship_bug'] = 0;
		$query['platform'][] = 0;
		$query['os'][] = 0;
		$query['os_build'][] = 0;
		$query['tag_string'] = "";
		$query['note_user_id'][] = 0;
		
		if(array_key_exists('limit', $filter))	{
			$query['per_page'] = $filter['limit'];		
		}else 	{
			$query['per_page'] = 0;
		}
		
		if(array_key_exists('offset', $filter))	{
			$query['page_number'] = $filter['offset'];		
		}else 	{
			$query['page_number'] = 1;		
		}
		
		if(array_key_exists('where', $filter))	{
			foreach($filter['where']['terms'] as $term)	{
				if($term[0]=='=')	{	
					$term[2] = str_replace("\"", "", $term[2]);
					//print_r($term[2]);		
					switch($term[1])	{
						case self::$where_params[0]:
							if($creator_id = user_get_id_by_name($term[2]))	{
								$query['reporter_id'][] = $creator_id;
							}else	{
								throw new BadRequestException("Invalid dc:creator: ".$term[2]);
							}
							break;
						case self::$where_params[1]:
							$category_array = category_get_all_rows(0);
							$flag_type = 0;
							foreach( $category_array as $category_row )
							{
								if(strcasecmp($category_row['name'],$term[2])==0 ) {
									$query['show_category'][] = $category_row['name'];
									$flag_type = 1;
									break;
								}
							}
							if($flag_type==0) {
								throw new BadRequestException("Invalid dc:type: ".$term[2]);
							}
							break;
						case self::$where_params[2]:
							if (array_key_exists($term[2],self::$severity_arr))	{
								$query['show_severity'][] = self::$severity_arr[$term[2]];
							}else	{
								throw new BadRequestException('Invalid mantisbt:severity value specified!');
							}
							break;
						case self::$where_params[3]:
							if (array_key_exists($term[2],self::$status_arr))	{
								$query['show_status'][] = self::$status_arr[$term[2]];
							}else	{
								throw new BadRequestException('Invalid mantisbt:status value specified!');
							}
							break;
						case self::$where_params[4]:
							if (array_key_exists($term[2],self::$priority_arr))	{
								$query['show_priority'][] = self::$priority_arr[$term[2]];
							}else	{
								throw new BadRequestException('Invalid mantisbt:priority value specified!');
							}
							break;
						case self::$where_params[5]:
							if(version_get_id($term[2], 0))	{
								$query['target_version'][] = $term[2];
							}else	{
								throw new NotFoundException('Version '.$term[2].' does not exist!');
							}
							break;
						case self::$where_params[6]:
							$v_num_id = custom_field_get_id_from_name("version_number");
							if(($v_num_id!=false)&&(custom_field_validate( $v_num_id, $term[2] )))	{
								$query['custom_field_'.$v_num_id][] = $term[2];
							}else {
								throw new BadRequestException("Invalid value ".$term[2]." specified for mantisbt:version_number!");
							}
							break;
						case self::$where_params[7]:
							if(project_exists(project_get_id_by_name($term[2])))	{
								$query['project_id'][] = project_get_id_by_name($term[2]);
							}else {
								throw new BadRequestException("Invalid value ".$term[2]." specified for mantisbt:project!");
							}
							break;
						default:  throw new BadRequestException("Invalid attribute ".$term[1]." specified in oslc_where!");
						break;
					}
				}
			}		
		}
		
		if(!array_key_exists('reporter_id', $query))	{
			$query['reporter_id'][] = 0;
		}
		
		if(!array_key_exists('show_category', $query))	{
			$query['show_category'][] = 0;
		}
		
		if(!array_key_exists('show_severity', $query))	{
			$query['show_severity'][] = 0;
		}
		
		if(!array_key_exists('show_status', $query))	{
			$query['show_status'][] = 0;
		}
		
		if(!array_key_exists('show_priority', $query))	{
			$query['show_priority'][] = 0;
		}
		
		if(!array_key_exists('target_version', $query))	{
			$query['target_version'][] = 0;
		}
		
		$custom_field_ids = custom_field_get_linked_ids();
		foreach($custom_field_ids as $id)	{
			if(!array_key_exists('custom_field_'.$id, $query))	{
				$query['custom_field_'.$id][] = 0;
			}
		}
		
		if(!array_key_exists('project_id', $query))	{
			$query['project_id'][] = 0;
		}

		if(array_key_exists('orderBy', $filter))	{
			if(count($filter['orderBy'])>2)	{
				throw new ConflictException("Sorting over more than two attributes is not supported for mantis!");
			}else {
				for($i=0;$i<2;$i++)	{
					if(array_key_exists($i, $filter['orderBy']))	{
						$query['dir_'.$i] = $filter['orderBy'][$i][0];
						switch($filter['orderBy'][$i][1])	{
							case self::$orderBy_params[0]: $query['sort_'.$i] = "priority";
								break;
							case self::$orderBy_params[1]: $query['sort_'.$i] = "id";
								break;
							case self::$orderBy_params[2]: $query['sort_'.$i] = "category_id";
								break;
							case self::$orderBy_params[3]: $query['sort_'.$i] = "severity";
								break;
							case self::$orderBy_params[4]: $query['sort_'.$i] = "status";
								break;
							case self::$orderBy_params[5]: $query['sort_'.$i] = "last_updated";
								break;
							case self::$orderBy_params[6]: $query['sort_'.$i] = "summary";
								break;
							default: throw new ConflictException("Sorting over attribute ".$filter['orderBy'][$i][1]." is not supported for mantis!");
								break; 
						}
						
					}else	{
						$query['dir_'.$i] = "";
						$query['sort_'.$i] = "";
					}
				}
			}
			
		}else	{
			$query['dir_0'] = "";
			$query['dir_1'] = "";
			$query['sort_0'] = "";
			$query['sort_1'] = "";
		}

		if (array_key_exists('searchTerms', $filter))	{
			$terms = "";
			foreach ($filter['searchTerms'] as $term) {
				$terms = $terms.$term." " ;
			}
			//print_r($terms);
			$query['search'] = substr($terms, 0, -1);
		}else 	{
			$query['search'] = "";
		}		
		
		return $query;
	}
	
	/*
	 * Retrieves the data to be displayed in the creation ui
	 */
	public function getDataForCreationUi($project)
	{
		$data = array();
		
		//check project validity
		if(!is_numeric($project)) {
			$project = project_get_id_by_name($project);
		}
		if(project_exists($project))	{
			$data['project'] = project_get_name($project);
		}else {
			throw new BadRequestException("Invalid project specified!");
		}
		
		$fields = config_get( 'bug_report_page_fields');
		//print_r($fields);
		//exit;
		
		foreach($fields as $field)	{
			switch($field)	{
				
				case 'category_id': foreach(category_get_all_rows($project) as $row){
										$data[$field][] = $row['name'];
									}
									break;
				case 'view_state' : $data[$field][] = "public";
									$data[$field][] = "private";
									break;
				case 'handler' : 	break;
				case 'priority' : 	foreach(self::$priority_arr as $key=>$value)	{
										$data[$field][] = $key;
									}
									break;
				case 'severity' :	foreach(self::$severity_arr as $key=>$value)	{
										$data[$field][] = $key;
									}
									break;
				case 'reproducibility':	foreach(self::$reproducibility_arr as $key=>$value)	{
											$data[$field][] = $key;
										}
										break;
				case 'target_version':
				case 'product_version':	$temp_arr = version_get_all_rows($project);
										if(!empty($temp_arr))	{
											foreach ($temp_arr as $version) {
												$data[$field][] = $version['version'];
											}
										}
										break;
				case 'summary':
				case 'description':
				case 'additional_info':
				case 'steps_to_reproduce':	$data[$field] = "";
											break;
			}
		}
		
		//print_r($data);
		//exit;
		return $data;
		
	}
	
	/*
	 * Retrieves the data to be displayed in the selection ui
	 */
	public function getDataForSelectionUi($project)
	{
		$data = array();
		
		//check project validity
		if(!is_numeric($project)) {
			$project = project_get_id_by_name($project);
		}
		if(project_exists($project))	{
			$data['project'] = project_get_name($project);
		}else {
			throw new BadRequestException("Invalid project specified!");
		}
		
		
		//construct array for oslc.where with all possible values for each attribute
		foreach (self::$where_params as $param) {
			if($param=='dc:creator')
			{
				foreach(project_get_all_user_rows($project) as $user)	{
					$data['where'][$param][] = $user['username'];
				}
			}
			if($param=='dc:type')
			{
				foreach(category_get_all_rows($project) as $row){
					$data['where'][$param][] = $row['name'];
				}
			}
			if($param=='mantisbt:severity')
			{
				foreach (self::$severity_arr as $key => $value) {
					$data['where'][$param][] = $key;
				}
			}
			if($param=='mantisbt:status')
			{
				foreach (self::$status_arr as $key => $value) {
					$data['where'][$param][] = $key;
				}
			}
			if($param=='mantisbt:priority')
			{
				foreach (self::$priority_arr as $key => $value) {
					$data['where'][$param][] = $key;
				}
			}
			if($param=='mantisbt:target_version')
			{
				foreach (version_get_all_rows($project) as $version) {
					$data['where'][$param][] = $version['version'];
				}
			}
			if($param=='mantisbt:version_number')
			{
				$v_id = custom_field_get_id_from_name("version_number");
				if(($v_id!=false)&&(custom_field_is_linked($v_id, $project)))	{
					$def = custom_field_get_definition($v_id);
					foreach (custom_field_distinct_values($def, $project) as $v_num) {
						$data['where'][$param][] = $v_num;
					}
				}
				
			}
						
		}
		
		//construct array for oslc.properties
		foreach(self::$properties_params as $prop)	{
			switch($prop)	{
				case 'mantisbt:target_version':
				case 'mantisbt:version':
					$temp_arr = version_get_all_rows($project);
					if(!empty($temp_arr))	{
						$data['properties'][] = $prop;
					}
					break;
					
				case 'mantisbt:version_number':
					if(custom_field_get_id_from_name("version_number")!=false)	{
						$data['properties'][] = $prop;
					}
					break;
					
				default:
					$data['properties'][] = $prop;
					
			}
		}
		
		//construct array for oslc.orderBy
		$data['orderBy'] = self::$orderBy_params;
		
		return $data;
		
	}
	
	protected function postQuery($filter, $fields)
	{
		/*$post = array();
		$data = "type=1&page_number=1&view_type=advanced&reporter_id%5B%5D=2&user_monitor%5B%5D=0&handler_id%5B%5D=0&show_category%5B%5D=General&show_severity%5B%5D=10&show_resolution%5B%5D=0&show_profile%5B%5D=0&show_status%5B%5D=10&show_priority%5B%5D=20&target_version%5B%5D=Branch+2.1&per_page=0&view_state=10&sticky_issues=on&highlight_changed=6&relationship_type=-1&relationship_bug=0&platform%5B%5D=0&os%5B%5D=0&os_build%5B%5D=0&tag_string=&custom_field_1%5B%5D=1.1&note_user_id%5B%5D=2&sort_0=last_updated&dir_0=DESC&sort_1=status&dir_1=DESC&project_id%5B%5D=0&search=random+post";
		$data = "type=1&page_number=1&view_type=advanced&reporter_id%5B%5D=0&user_monitor%5B%5D=0&handler_id%5B%5D=0&show_category%5B%5D=General&show_severity%5B%5D=0&show_resolution%5B%5D=0&show_profile%5B%5D=0&show_status%5B%5D=10&show_priority%5B%5D=0&target_version%5B%5D=0&per_page=100&view_state=0&sticky_issues=on&highlight_changed=6&relationship_type=-1&relationship_bug=0&platform%5B%5D=0&os%5B%5D=0&os_build%5B%5D=0&tag_string=&custom_field_1%5B%5D=0&note_user_id%5B%5D=0&sort_0=summary&dir_0=ASC&sort_1=last_updated&dir_1=DESC&project_id%5B%5D=0&search=random+post";
		parse_str($data, $post);
		print_r($post);*/
		
		$post = array();
		$post = $this->createQueryFromFilter($filter);
		
		//print_r($post); exit;
		foreach($post as $key => $value) //to mimic POST action on view_all_set.php
		{
			$_POST[$key] = $value;
		}
		$_POST['temporary']=true;
		
		//another option, to copy all the code from view_all_set.php to a local file mantisquery.php
		//include APPLICATION_PATH.'/models/'.'mantisquery.php';
		
		//to disable the redirection code in view_all_set.php
		$temp_stop_on_errors = config_get_global( 'stop_on_errors');
		config_set_global( 'stop_on_errors' , ON);
		global $g_error_handled;
		$temp_g_error_handled = $g_error_handled;
		$g_error_handled = true;
		
		include MANTIS_DIR.'view_all_set.php';
		$_POST['filter']=$t_token_id;
		//print_r("BACK TO POST QUERY");
		
		//putting everything back to previous state
		config_set_global( 'stop_on_errors' , $temp_stop_on_errors);
		$g_error_handled = $temp_g_error_handled;
		
		$f_page_number		= $post['page_number'];

		$t_per_page = null;
		$t_bug_count = null;
		$t_page_count = null;
	
		$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, null, null, null, true );
		
		/*foreach($rows as $bug)
		{
			print_r($bug->id."-");
		}
		*/
		
		$this->changerequests = new ChangeRequestsMantisDb($rows, $fields);
		//print_r($this->changerequests);
		
	}
			
	/*
	 * Constructs the model from the Mantis DB
	 *
	 * TODO : remove provided project if not found in DB
	 * 
	 * @param array $params contains 'project' if filtered on particular project
	 */
	protected function fetchChangeRequests($params=null)
	{
		$rows	= null;
		//print_r($params);
		if(empty($params))	{
			$arr['where']['terms'][] = array();
			$this->postQuery($arr, null);
		}
		elseif(isset($params['project']))	{
			if(is_numeric($params['project']))	{
				if(( $params['project'] == 0 ) || !project_exists( $params['project'])) {
					throw new NotFoundException("Project does not exist!!!");
				}
				else {
					$params['project'] = project_get_name($params['project']);
				}
			}
			$arr['where']['terms'][] = array("=","mantisbt:project",'"'.$params['project'].'"');
			$this->postQuery($arr, null);
		}
		

	}
	
	public function getChangeRequest($id, $uri)
	{
		$row[0] = bug_get($id, true);
		$changerequest = new ChangeRequestsMantisDb($row);
		//print_r($this->prepareChangeRequest($changerequest[$id], $uri));
		return $this->prepareChangeRequest($changerequest[$id], $uri);
	}
	
	public function getBugnoteCollection($bugid, $url)
	{
		$tmp_arr = bugnote_get_all_visible_bugnotes($bugid, 'DESC', -1);
		//print_r($tmp_arr);
		$notes_arr = array();
		foreach($tmp_arr as $note)
		{
			$notes['resource']['dc:title'] = "Bugnote-".$note->id;
			$notes['resource']['dc:identifier'] = $url.$note->id;
			$notes['resource']['dc:creator'] = user_get_name($note->reporter_id);
			$notes['resource']['dc:description'] = $note->note;
			$notes['resource']['dc:created'] = date(DATE_ATOM, $note->date_submitted);
			$notes['resource']['dc:modified'] = date(DATE_ATOM, $note->last_modified);
			$notes['id'] = $notes['resource']['dc:identifier'];
			$notes['title'] = $notes['resource']['dc:title'];
			//print_r($note->id);
			$notes_arr[] = $notes;
			
		}
		return $notes_arr;
	}
	
	public function getBugnote($bugnote_id, $url)
	{
		if(!bugnote_exists($bugnote_id))
		{
			throw new Exception("Bugnote ".$bugnote_id." does not exist!");
		}
		
		$note['id'] = $url;
		
		$note['resource']['dc:title'] = "Bugnote-".$bugnote_id;
		$note['resource']['dc:identifier'] = $url;
		$note['resource']['dc:description'] = bugnote_get_text($bugnote_id);
		
		$creator = bugnote_get_field($bugnote_id, 'reporter_id');
		$note['resource']['dc:creator'] = user_get_name($creator);
				
		$created = bugnote_get_field($bugnote_id, 'date_submitted');
		$note['resource']['dc:created'] = date(DATE_ATOM, $created);
		
		$modified = bugnote_get_field($bugnote_id, 'last_modified');
		$note['resource']['dc:modified'] = date(DATE_ATOM, $modified);

		return $note;
	}
	
	public function getProjectList()
	{
		return project_get_all_rows();
	}
	
	public function setProject($proj_id)
	{
		if(!is_numeric($proj_id))
		{
			$proj_id = project_get_id_by_name($proj_id);
		}
		if(($proj_id!=0)&&!(project_exists($proj_id)))
		{
			$proj_id = 0;
		}
		return $proj_id;
	}
	
	public function retrieveStatsByDate($id)
	{
		$pid = $this->setProject($id);
		$this->statsByDate( config_get( 'date_partitions' ), $pid);
	}
	
	public function retrieveStatsByActivity($id)
	{
		$pid = $this->setProject($id);
		$this->statsByActivity($pid);
	}
	
	public function retrieveStatsByAge($id)
	{
		$pid = $this->setProject($id);
		$this->statsByAge($pid);
	}
	
	# Print list of bugs opened from the longest time
	# taken from function summary_print_by_age()
	function statsByAge($pid)
	{
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	
		$t_project_id = $pid;
		$t_user_id = auth_get_current_user_id();
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
	
		$specific_where = helper_project_specific_where( $t_project_id );
		if( ' 1<>1' == $specific_where ) {
			return;
		}
		$query = "SELECT * FROM $t_mantis_bug_table
					WHERE status < $t_resolved
					AND $specific_where
					ORDER BY date_submitted ASC, priority DESC";
		$result = db_query( $query );
	
		$t_count = 0;
		$t_private_bug_threshold = config_get( 'private_bug_threshold' );
		while( $row = db_fetch_array( $result ) ) {
	
			// as we select all from bug_table, inject into the cache.
			bug_cache_database_result( $row );
	
			// Skip private bugs unless user has proper permissions
			if(( VS_PRIVATE == bug_get_field( $row['id'], 'view_state' ) ) && ( false == access_has_bug_level( $t_private_bug_threshold, $row['id'] ) ) ) {
				continue;
			}
	
			if( $t_count++ == 10 ) {
				break;
			}
	
			$t_bugid = $row['id'];
			$t_summary = $row['summary'];
			$t_days_open = intval(( time() - $row['date_submitted'] ) / SECONDS_PER_DAY );
	
			print $t_bugid.",".$t_summary.",".$t_days_open."\n";
			
		}
	}
	
	# Print list of open bugs with the highest activity score
	# the score is calculated assigning one "point" for each history event
	# associated with the bug
	# taken from function summary_print_by_activity()
	function statsByActivity($pid)
	{
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
		$t_mantis_history_table = db_get_table( 'mantis_bug_history_table' );
	
		$t_project_id = $pid;
		$t_user_id = auth_get_current_user_id();
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
	
		$specific_where = helper_project_specific_where( $t_project_id );
		if( ' 1<>1' == $specific_where ) {
			return;
		}
		$query = "SELECT COUNT(h.id) as count, b.id, b.summary, b.view_state
					FROM $t_mantis_bug_table AS b, $t_mantis_history_table AS h
					WHERE h.bug_id = b.id
					AND b.status < " . db_param() . "
					AND $specific_where
					GROUP BY h.bug_id, b.id, b.summary, b.last_updated, b.view_state
					ORDER BY count DESC, b.last_updated DESC";
		$result = db_query_bound( $query, Array( $t_resolved ) );
	
		$t_count = 0;
		$t_private_bug_threshold = config_get( 'private_bug_threshold' );
		$t_summarydata = Array();
		$t_summarybugs = Array();
		while( $row = db_fetch_array( $result ) ) {
	
			// Skip private bugs unless user has proper permissions
			if(( VS_PRIVATE == $row['view_state'] ) && ( false == access_has_bug_level( $t_private_bug_threshold, $row['id'] ) ) ) {
				continue;
			}
	
			if( $t_count++ == 10 ) {
				break;
			}
	
			$t_summarydata[] = array(
				'id' => $row['id'],
				'summary' => $row['summary'],
				'count' => $row['count'],
			);
			$t_summarybugs[] = $row['id'];
		}
	
		bug_cache_array_rows( $t_summarybugs );
	
		foreach( $t_summarydata as $row ) {
			//$t_bugid = string_get_bug_view_link( $row['id'] );
			$t_bugid = $row['id'];
			$t_summary = string_html_specialchars( $row['summary'] );
			$t_notescount = $row['count'];
	
			print $t_bugid.",".$t_summary.",".$t_notescount."\n";
			
		}
	}
	
	# This function shows the number of bugs submitted in the last X days
	# An array of integers representing days is passed in
	# taken from summary_print_by_date( $p_date_array )
	function statsByDate( $p_date_array, $pid) {
		helper_set_current_project($pid);
		$arr_count = count( $p_date_array );
		$x = 0;
		$stats[$x][] = 'By date (days)';
		$stats[$x][] = 'Opened';
		$stats[$x][] = 'Resolved';
		$stats[$x][] = 'Balance';
		print implode(",",$stats[$x]);
		print "\n";
		
		foreach( $p_date_array as $t_days ) {
			$x++;
			$t_new_count = $this->summaryNewBugCountByDate( $t_days, $pid);
			$t_resolved_count = $this->summaryResolvedBugCountByDate( $t_days, $pid);
	
			$t_start_date = mktime( 0, 0, 0, date( 'm' ), ( date( 'd' ) - $t_days ), date( 'Y' ) );
			$t_new_bugs_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;' . FILTER_PROPERTY_FILTER_BY_DATE . '=on&amp;' . FILTER_PROPERTY_START_YEAR . '=' . date( 'Y', $t_start_date ) . '&amp;' . FILTER_PROPERTY_START_MONTH . '=' . date( 'm', $t_start_date ) . '&amp;' . FILTER_PROPERTY_START_DAY . '=' . date( 'd', $t_start_date ) . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=">';
	
			$stats[$x][] = $t_days;
			$stats[$x][] = $t_new_count;
			$stats[$x][] = $t_resolved_count;
			
				
			$t_balance = $t_new_count - $t_resolved_count;
			if( $t_balance > 0 ) {
	
				# we are talking about bugs: a balance > 0 is "negative" for the project...
				$t_style = " negative";
				$t_balance = sprintf( '%+d', $t_balance );
	
				# "+" modifier added in PHP >= 4.3.0
			}
			else if( $t_balance < 0 ) {
				$t_style = ' positive';
				$t_balance = sprintf( '%+d', $t_balance );
			}
	
			$stats[$x][] = $t_balance;
			print implode(",",$stats[$x]);
			print "\n";
						
		}
	
		# end foreach
		//print_r($stats);
		//return $stats;
		
	}
	
	# prints the bugs submitted in the last X days (default is 1 day) for the
	# current project
	function summaryNewBugCountByDate( $p_time_length = 1, $id) {
		$t_mantis_bug_table = db_get_table( 'mantis_bug_table' );
	
		$c_time_length = (int) $p_time_length * SECONDS_PER_DAY;
	
		$t_project_id = $id;
		$t_user_id = auth_get_current_user_id();
	
		$specific_where = helper_project_specific_where( $t_project_id );
		if( ' 1<>1' == $specific_where ) {
			return;
		}
	
		$query = "SELECT COUNT(*)
					FROM $t_mantis_bug_table
					WHERE " . db_helper_compare_days( "" . db_now() . "", "date_submitted", "<= $c_time_length" ) . " AND $specific_where";
		$result = db_query_bound( $query );
		return db_result( $result, 0 );
	}
	
	# returns the number of bugs resolved in the last X days (default is 1 day) for the
	# current project
	function summaryResolvedBugCountByDate( $p_time_length = 1, $id) {
		$t_bug_table = db_get_table( 'mantis_bug_table' );
		$t_bug_history_table = db_get_table( 'mantis_bug_history_table' );
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
	
		$c_time_length = (int) $p_time_length * SECONDS_PER_DAY;
	
		$t_project_id = $id;
		$t_user_id = auth_get_current_user_id();
	
		$specific_where = helper_project_specific_where( $t_project_id );
		if( ' 1<>1' == $specific_where ) {
			return;
		}
	
		$query = "SELECT COUNT(DISTINCT(b.id))
					FROM $t_bug_table b
					LEFT JOIN $t_bug_history_table h
					ON b.id = h.bug_id
					AND h.type = " . NORMAL_TYPE . "
					AND h.field_name = 'status'
					WHERE b.status >= " . db_param() . "
					AND h.old_value < " . db_param() . "
					AND h.new_value >= " . db_param() . "
					AND " . db_helper_compare_days( "" . db_now() . "", "date_modified", "<= $c_time_length" ) . "
					AND $specific_where";
		$result = db_query_bound( $query, Array( $t_resolved, $t_resolved, $t_resolved ) );
		return db_result( $result, 0 );
	}
	
	public function addBugnotes($bugid, $bugnotes)
	{
		if(!bug_exists($bugid))
		{
			throw new NotFoundException( "Bug ".$bugid." does not exist!" );
		}
	
		foreach($bugnotes as $note)
		{
			if((!isset($note))||(is_blank($note)))
			{
				throw new BadRequestException( "Bug note must not be blank!" );
			}
		}
				
		$project_id = bug_get_field( $bugid, 'project_id' );
		$user_id = auth_get_current_user_id();
				
		if(!access_has_bug_level(config_get('add_bugnote_threshold'), $bugid, $user_id))
		{
			throw new Exception( "You do not have access rights to add notes to this bug!" );
		}
	
		if(bug_is_readonly($bugid))
		{
			throw new Exception("Bug ".$bugid." is readonly!" );
		}
	
		foreach($bugnotes as $note)
		{
			bugnote_add($bugid, $note);
		}
			
	}
	
	private function checkCreator($reporter, &$cm_data)
	{
		$user = auth_get_current_user_id();
		if($reporter_id = user_get_id_by_name($reporter))
		{
			if($reporter_id==$user)
			{
				if(!(user_get_access_level( $user, $cm_data->project_id )>=REPORTER))
				{
					throw new Exception("Access denied!!");
				}
				else
				{
					$cm_data->reporter_id = $user;
				}
			}
			else
			{
				if( !access_has_project_level(DEVELOPER, $cm_data->project_id, $user ) ) {
					throw new Exception( "User does not have access level required to specify a different bug reporter!" );
				}
				else
				{
					$cm_data->reporter_id = $reporter_id;
				}
			}
		}
		else
		{
			throw new BadRequestException("Invalid creator: ".$reporter);
		}
		
	}
	
	private function checkType($category, &$cm_data)
	{
		$category_id = null;
		$category_array = category_get_all_rows( $cm_data->project_id );
		foreach( $category_array as $category_row )
		{
			if( $category_row['name'] == $category ) {
				$category_id = $category_row['id'];
				break;
			}
		}
		if ( !isset($category_id) && !config_get( 'allow_no_category' ) )
		{
			throw new BadRequestException("Invalid subject/category field specified");
		}
		else
		{
			$cm_data->category_id = $category_id;
		}
	}
	
	private function checkStatus($status, &$cm_data)
	{
		if (array_key_exists($status,self::$status_arr))
		{
			$cm_data->status = self::$status_arr[$status];
			//print_r($cm_data->status);
		}
		else
		{
			throw new BadRequestException('Unknown mantisbt:status value specified!');
		}
	}
	
	private function checkPriority($priority, &$cm_data)
	{
		if (array_key_exists($priority,self::$priority_arr))
		{
			$cm_data->priority = self::$priority_arr[$priority];
			//print_r($cm_data->priority);
		}
		else
		{
			throw new BadRequestException('Unknown mantisbt:priority value specified!');
		}
	}
	
	private function checkSeverity($severity, &$cm_data)
	{
		if (array_key_exists($severity,self::$severity_arr))
		{
			$cm_data->severity = self::$severity_arr[$severity];
		}
		else
		{
			throw new BadRequestException('Unknown mantisbt:severity value specified!');
		}
	}
	
	private function checkReproducibility($reproducibility, &$cm_data)
	{
		if (array_key_exists($reproducibility,self::$reproducibility_arr))
		{
			$cm_data->reproducibility = self::$reproducibility_arr[$reproducibility];
		}
		else
		{
			throw new BadRequestException('Unknown mantisbt:reproducibility value specified!');
		}
	}
	
	private function checkVersion($version, &$cm_data)
	{
		if(version_get_id($version, $cm_data->project_id ))
		{
			$cm_data->version = $version;
		}
		else
		{
			$proj_name = project_get_name( $cm_data->project_id );
			throw new NotFoundException('Version '.$version.' does not exist in Project '.$proj_name.' !!!');
		}
	}
	
	private function checkTargetVersion($target_version, &$cm_data)
	{
		if(version_get_id($target_version, $cm_data->project_id ))
		{
			$cm_data->target_version = $target_version;
		}
		else
		{
			$proj_name = project_get_name( $cm_data->project_id );
			throw new NotFoundException('Version '.$target_version.' does not exist in Project '.$proj_name.' !!!');
		}
	}
	
	private function checkVersionNumber($version_number, &$cm_data, $id)
	{
		//version_number => custom_field
		$v_num_id = custom_field_get_id_from_name("version_number");
		//$v_def = custom_field_get_definition($v_num_id);
		//$v_values = custom_field_distinct_values($v_def);
		//print_r($v_values);
		if( !$v_num_id) {
				throw new NotFoundException('mantisbt:version_number not found (it has possibly not been created in the custom fields)' );
		}
		else if(!custom_field_is_linked($v_num_id,$cm_data->project_id))
		{
			$proj_name = project_get_name( $cm_data->project_id );
			throw new Exception('Custom_field mantisbt:version_number has not been linked to Project '.$proj_name.' yet!!!');
		}
		else if(!custom_field_has_write_access($v_num_id, $id, $cm_data->reporter_id))
		{
			throw new Exception('User '.$cm_data->reporter_id.' does not have access to set attribute mantisbt:version_number');
		}
		else if( !custom_field_validate( $v_num_id, $version_number ))
		{
			throw new BadRequestException( 'Invalid custom_field value for attribute mantisbt:version_number!!!');
		}
		else if( !custom_field_set_value( $v_num_id, $id, $version_number ))
		{
			throw new Exception( 'Unable to set custom_field value for attribute mantisbt:version_number to ChangeRequest:'.$cm_id.' !!!');
		}
	}

	public function createChangeRequest($params)
	{
		$cm_request = $params['new']->container;
		//print_r($cm_request);
		  
		$cm_data = new BugData;
		
		//dc:title ===> mantis:summary
		if(isset($cm_request['title'])) {
			$cm_data->summary = $cm_request['title'];			//mandatory
		}else{
			throw new BadRequestException('Mandatory field "Title" missing!!');
		}

		//dc:description ===> mantis:description
		if(isset($cm_request['description'])) {
			$cm_data->description = $cm_request['description'];			//mandatory
		}else{
			throw new BadRequestException('Mandatory field "Description" missing!!');
		}

		///project/xxxxx ===> mantis:project
		//print_r($params['project']);
		$project = $params['project'];
		if(!preg_match ("/[^0-9]/", $project))
		{
			$cm_data->project_id = $project;
		}
		else
		{
			$cm_data->project_id = project_get_id_by_name($project);
			//print_r($cm_data->project_id);
		}
		
		if(( $cm_data->project_id == 0 ) || !project_exists( $cm_data->project_id ) ) {
			throw new NotFoundException("Project does not exist!!!");
		}
		
		$user = auth_get_current_user_id();

		if(isset($cm_request['creator']))
		{
			$reporter = $cm_request['creator'];
			$this->checkCreator($reporter, $cm_data);			
		}
		else
		{
			if(!(user_get_access_level( $user, $cm_data->project_id )>=REPORTER))
			{
				throw new Exception("Access denied!!");
			}
			else
			{
				$cm_data->reporter_id = $user;
			}
		}
			
		//dc:type ===> mantis:category
		if(isset($cm_request['type']))
		{
			$category= $cm_request['type'];                 //mandatory
			$this->checkType($category, $cm_data);			
		}
		else
		{
			throw new BadRequestException('Mandatory field "Category" missing!!');
		}
		
		//mantisbt:priority
		//print_r($cm_request['priority']);
		
		if(isset($cm_request['priority']))
		{
			$cm_request['priority'] = strtolower($cm_request['priority']);
			$this->checkPriority($cm_request['priority'], $cm_data);			
		}
		else
		{
			$cm_data->priority = config_get( 'default_bug_priority' );
		}
		
		//mantisbt:severity
		if(isset($cm_request['severity']))
		{
			$cm_request['severity'] = strtolower($cm_request['severity']);
			$this->checkSeverity($cm_request['severity'], $cm_data);
		}
		else
		{
			$cm_data->severity = config_get( 'default_bug_severity' );
		}
		
		//mantisbt:reproducibility
		if(isset($cm_request['reproducibility']))
		{
			$cm_request['reproducibility'] = strtolower($cm_request['reproducibility']);
			$this->checkReproducibility($cm_request['reproducibility'], $cm_data);
		}
		else
		{
			$cm_data->reproducibility = config_get( 'default_bug_reproducibility' );
		}
		
		//cases to be chked when versions not specified
		if ( isset( $cm_request['version'] ) && !is_blank( $cm_request['version'] ) )
		{
			//$v_id = version_get_id( $cm_request['version'], $cm_data->project_id );
			$this->checkVersion($cm_request['version'], $cm_data);						
		}
		
		if ( isset( $cm_request['target_version'] ) && !is_blank( $cm_request['target_version'] ) )
		{
			//$v_id = version_get_id( $cm_request['version'], $cm_data->project_id );
			$this->checkTargetVersion($cm_request['target_version'], $cm_data);						
		}
		
		//mantisbt:steps_to_reproduce
		if(isset($cm_request['steps_to_reproduce'])) {
			$cm_data->steps_to_reproduce = $cm_request['steps_to_reproduce'];
		}else{
			$cm_data->steps_to_reproduce = config_get( 'default_bug_steps_to_reproduce' );
		}
		
		//mantisbt:additional_information
		if(isset($cm_request['additional_information'])) {
			$cm_data->additional_information = $cm_request['additional_information'];
		}else{
			$cm_data->additional_information = config_get( 'default_bug_additional_info' );
		}
		
		//mantisbt:view_state
		if(isset($cm_request['view_state'])) {
			if($cm_request['view_state']=="public")	{
				$cm_data->view_state = VS_PUBLIC;
			}elseif($cm_request['view_state']=="private")	{
				$cm_data->view_state = VS_PRIVATE;
			}else	{
				throw new BadRequestException('Unknown mantisbt:view_state value specified!');
			}
		}else{
			$cm_data->view_state = config_get( 'default_bug_view_status' );
		}	
		
		$cm_data->handler_id = 0;
		$cm_data->profile_id = 0;
				
		$cm_data->status = config_get( 'bug_submit_status' );		
		$cm_data->projection = config_get( 'default_bug_projection' );
		$cm_data->eta = config_get( 'default_bug_eta' );
		$cm_data->resolution = config_get( 'default_bug_resolution' );
		
		$cm_data->due_date = date_get_null();
		$cm_data->summary = trim( $cm_data->summary );

		# still have to add code to Validate the custom fields (for a particular project) before adding the bug
		
		# Create the bug
		$cm_id = $cm_data->create();
		// bugnotes still to be added
		email_new_bug( $cm_id );
		//print_r("after creation");

		if ( isset( $cm_request['version_number'] ) && !is_blank( $cm_request['version_number'] ) )
		{
			$this->checkVersionNumber($cm_request['version_number'], $cm_data, $cm_id);
		}
				
		if(isset( $cm_request['notes']))
		{
			$notes = array();
			$x = 0;
			foreach($cm_request['notes'] as $request)
			{
				if (!is_blank( $request))
				{
					$notes[$x++] = (string)$request;
					//echo "note " . $x;
				}
			}
			$this->addBugnotes($cm_id, $notes);
		}
			
		return $cm_id;
	}
	
	public function checkChangeRequestExists($id)
	{
		return bug_exists($id);
	}
	
	public function updateChangeRequest($id, $changerequest, $props)
	{
		$cm_data = bug_get($id, true);
		$cm_request = $changerequest->container;
		//print_r($cm_data);
		$terms = array('dc:','mantisbt:');
		foreach($props as &$prop)
		{
			$prop = str_replace($terms,"",$prop);
			//echo $prop;
		}
		//print_r($props);
		
		$user = auth_get_current_user_id();
		if(!(user_get_access_level($user, $cm_data->project_id )>=REPORTER))
		{
			throw new Exception("User doesnt have access to update bug!!");
		}
				
		if(in_array('creator',$props))
		{
			if(isset($cm_request['creator']))
			{
				$reporter = $cm_request['creator'];
				$this->checkCreator($reporter, $cm_data);			
			}
			else
			{
				throw new BadRequestException("dc:creator mentioned in the request query not found in request body!");
			}
		}
		
		//dc:title ===> mantis:summary
		if(in_array('title',$props))
		{
			if(isset($cm_request['title']))
			{
				$cm_data->summary = $cm_request['title'];			//mandatory
			}
			else
			{
				throw new BadRequestException("dc:title mentioned in the request query not found in request body!");
			}
		}

		//dc:description ===> mantis:description
		if(in_array('description',$props))
		{
			if(isset($cm_request['description']))
			{
				$cm_data->description = $cm_request['description'];			//mandatory
				//print_r($cm_data);
			}
			else
			{
				throw new BadRequestException("dc:description mentioned in the request query not found in request body!");
			}
		}		
						
		//dc:type ===> mantis:category
		if(in_array('type',$props))
		{
			if(isset($cm_request['type']))
			{
				$category= $cm_request['type'];                 //mandatory
				$this->checkType($category, $cm_data);			
			}
			else
			{
				throw new BadRequestException("dc:type mentioned in the request query not found in request body!");
			}
		}
		
		//mantisbt:status
		if(in_array("status",$props))
		{
			if(isset($cm_request['status']))
			{
				$cm_request['status'] = strtolower($cm_request['status']);
				$this->checkStatus($cm_request['status'], $cm_data);		
			}
			else
			{
				throw new BadRequestException("mantisbt:status mentioned in the request query not found in request body!");
			}
		}
		
		//mantisbt:priority
		if(in_array("priority",$props))
		{
			if(isset($cm_request['priority']))
			{
				$cm_request['priority'] = strtolower($cm_request['priority']);
				$this->checkPriority($cm_request['priority'], $cm_data);			
			}
			else
			{
				throw new BadRequestException("mantisbt:priority mentioned in the request query not found in request body!");
			}
		}
		
		//mantisbt:severity
		if(in_array('severity',$props))
		{
			$cm_request['severity'] = strtolower($cm_request['severity']);
			if(isset($cm_request['severity']))
			{
				$this->checkSeverity($cm_request['severity'], $cm_data);
			}
			else
			{
				throw new BadRequestException("mantisbt:severity mentioned in the request query not found in request body!");
			}
		}
		
		if(in_array('version',$props))
		{
			if ( isset( $cm_request['version'] ) && !is_blank( $cm_request['version'] ) )
			{
				//$v_id = version_get_id( $cm_request['version'], $cm_data->project_id );
				$this->checkVersion($cm_request['version'], $cm_data);						
			}
			else
			{
				throw new BadRequestException("mantisbt:version mentioned in the request query not found in request body!");
			}
		}
		
		if(in_array('target_version',$props))
		{
			if ( isset( $cm_request['target_version'] ) && !is_blank( $cm_request['target_version'] ) )
			{
				//$v_id = version_get_id( $cm_request['version'], $cm_data->project_id );
				$this->checkTargetVersion($cm_request['target_version'], $cm_data);						
			}
			else
			{
				throw new BadRequestException("mantisbt:target_version mentioned in the request query not found in request body!");
			}
		}
		
		
		$cm_data->summary = trim( $cm_data->summary );

		# still have to add code to Validate the custom fields (for a particular project) before adding the bug
		
		# Update the bug
		if(!$cm_data->update(TRUE, TRUE))
		{
			throw new Exception("Change Request ".$id." was not able to be updated for some reason!");
		}
		// bugnotes still to be added
		//print_r("after creation");

		if(in_array('version_number',$props))
		{
			if ( isset( $cm_request['version_number'] ) && !is_blank( $cm_request['version_number'] ) )
			{
				$this->checkVersionNumber($cm_request['version_number'], $cm_data, $id);
			}
			else
			{
				throw new BadRequestException("mantisbt:version_number mentioned in the request query not found in request body!");
			}
		}
				
		if(in_array('notes',$props))
		{
			if(isset( $cm_request['notes']))
			{
				$notes = array();
				$x = 0;
				foreach($cm_request['notes'] as $request)
				{
					if (!is_blank( $request))
					{
						$notes[$x++] = (string)$request;
						//echo "note " . $x;
					}
				}
				$this->addBugnotes($id, $notes);
			}
			else
			{
				throw new BadRequestException("mantisbt:notes mentioned in the request query not found in request body!");
			}
		}
		
	}

	public function getHttpAuthBasicResolver($login, $password) {

		$basicResolver = new Mantis_Http_Auth_Resolver($login, $password);

		return $basicResolver;

	}
	
	/**
	 * Reproduced from OAuth PHP lib : splits oauth headers
	 * 
	 * @param string $header
	 */
	private static function splitHeader($header) {
		// remove 'OAuth ' at the start of a header
		$header = substr($header, 6);

		// error cases: commas in parameter values?
		$parts = explode(",", $header);
		$out = array();
		foreach ($parts as $param) {
			$param = ltrim($param);
			// skip the "realm" param, nobody ever uses it anyway
			if (substr($param, 0, 5) != "oauth") continue;

			$param_parts = explode("=", $param);

			// rawurldecode() used because urldecode() will turn a "+" in the
			// value into a space
			$out[$param_parts[0]] = rawurldecode(substr($param_parts[1], 1, -1));
		}
		return $out;
	}

	/**
	 * Uses OAuth PHP lib's and OauthAuthz plugin's data store to check OAuth headers
	 * validating an access token, and logging-in the corresponding user
	 * 
	 * @param string $auth_header
	 * @return boolean
	 */
	public function checkOauthAuthorization($auth_header) {
		//try {
			$oauth_server = new OAuthServer(MantisBtDbOAuthDataStore::singleton());

			$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
			$oauth_server->add_signature_method($hmac_method);
			
			// HACK : zend framework relies on PHP parse_str() which replaces dots by underscores in the parameters
			// so we need to restore the original "oslc_cm.xxx" query parameters before checking the signature
			$parameters=$_GET;
			$new_params=array();
			foreach($parameters as $key => $value) {
				$prefix = substr($key, 0,8);
				if($prefix == 'oslc_cm_') {
					$suffix=substr($key, 8);
					$new_params['oslc_cm.'.$suffix] = $value;
				}
				else {
					$new_params[$key] = $value;
				}
			}
			$header_parameters = $this->splitHeader($auth_header);
			$parameters = array_merge($header_parameters, $new_params);
			$req = OAuthRequest::from_request(null,null,$parameters);
			list($consumer, $token) = $oauth_server->verify_request( $req);

			// Now, the request is valid.

			// We know which consumer is connected
			/*
			echo "Authenticated as consumer : \n";
			//print_r($consumer);
			echo "  name: ". $consumer->getName() ."\n";
			echo "  key: $consumer->key\n";
			echo "\n";

			// And on behalf of which user it connects
			echo "Authenticated with access token whose key is :  $token->key \n";
			echo "\n";*/
			$t_token = OauthAuthzAccessToken::load_by_key($token->key);
			$user = user_get_name($t_token->getUserId());
			/*
			echo "Acting on behalf of user : $user\n";
			echo "\n"; */

			// if user is succesfully retrieved, then simulate its login
			if( isset($user) && auth_attempt_script_login($user) ) { 
				return True;
			}
			else {
				return False;
			} 
		/*	
		} catch (OAuthException $e) {
			//print($e->getMessage() . "\n<hr />\n");
			//print_r($req);
			return false;
		}
		*/
	}
}
