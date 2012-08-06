<?php

/**
 * ProjectImporter Class
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// Include standard ARC library
include_once("arc/ARC2.php");
// Include the JSON RDF parser ala OSLC developped in COCLICO
include_once('ARC2_OSLCCoreRDFJSONParserPlugin.php');

#require_once $gfcommon.'import/import_users.php';

//require_once $gfcommon.'import/import_trackers.php';


define('FORGEPLUCKER_NS', 'http://planetforge.org/ns/forgeplucker_dump/');
define('PLANETFORGE_NS', 'http://coclico-project.org/ontology/planetforge#');

class ImportedProject {
	protected $res;

	protected $full_name;
	//protected $purpose;
	protected $description;
	protected $unix_name;
	protected $scm;
	protected $is_public;
	protected $built_from_template;

	function ImportedProject($res) {
		$this->res = $res;

		$this->unix_name = $this->res->getPropValue('doap:name');
		$this->description = $this->res->getPropValue('dcterms:description');
		$this->is_public = 0;

		/*
		$this->full_name = $this->res->getPropValue('doap:name');
		$this->purpose = $this->res->getPropValue('doap:name');
		$this->unix_name = $this->res->getPropValue('doap:name');
		$this->scm = $this->res->getPropValue('doap:name');
		$this->is_public = $this->res->getPropValue('doap:name');
		$this->built_from_template = $this->res->getPropValue('doap:name');

		$this->homepage = $res->getPropValue('doap:homepage');
		$this->hosted_by = $res->getPropValue('planetforge:hosted_by');
		*/
		$this->roles = array();
		$roles = $res->getPropValues('sioc:scope_of');
		foreach($roles as $role) {
			$importer = ProjectImporter::getInstance();
			$roleres = ProjectImporter::make_resource($role);
			$role_obj = new ImportedProjectRole($this, $roleres);
			$this->roles[] = $role_obj;
		}
	}

	/**
	 * Returns a project's name
	 */
	function getUnixName() {
		return $this->unix_name;
	}

	/**
	 * Return a project's description
	 */
	function getDescription() {
		return $this->description;
	}

	function getFullName() {
		if ($this->full_name) {
			return $this->full_name;
		}
		else {
			return $this->getUnixName();
		}
	}
	/*
	function getPurpose() {
		return $this->purpose;
	}*/
	function getIsPublic() {
		return $this->is_public;
	}
	/**
	 * Return the spaces used by a project
	 * @param ARC2 resource $projectres
	 * @return array of ARC2 resources
	 */
	function getSpaces() {
		global $feedback;

		$results = array();

		$importer = ProjectImporter::getInstance();

		$spaces = $this->res->getPropValues('sioc:has_space');
		foreach ($spaces as $space) {
			$spaceres = ProjectImporter::make_resource($space);
			$provider = $spaceres->getPropValue('planetforge:provided_by');
			if (! $importer->supportsTool($provider))	{
				if ($feedback) $feedback .= '<br />';
				$feedback .= 'error : no supported provider for '. $space .': '. $provider."!\n";
			}
			else {
				$results[$space] = $spaceres;
			}
		}
		return $results;
	}

	function getRoles() {
		return $this->roles;
	}
}

class ImportedProjectRole {
	protected $name;
	protected $project;
	protected $users;

	function ImportedProjectRole(& $project, $res) {
		$this->project = $project;
		$this->name = $res->getPropValue('sioc:name');
		$this->users = $res->getPropValues('sioc:function_of');
		//print_r('Role: ' .$this->name);
		//print_r('Users: ');
		//print_r($this->users);
	}

	function getName() {
		return $this->name;
	}

	function getUsers() {
		return $this->users;
	}
}

class ImportedUser {
	protected $res;

	protected $unix_name;
	protected $firstname;
	protected $lastname;
	protected $email;

	//protected $initial_role;
	function ImportedUser($res) {
		$this->res = $res;

		$this->unix_name = $this->res->getPropValue('foaf:accountName');
		$this->email = $this->res->getPropValue('sioc:email');

	}
	function init_owner_props($res) {
		$name = $res->getPropValue('foaf:name');
		$first = '';
		$last = '';
		list($first, $last) = explode(' ', str_replace('/\s+/gi',' ',$name), 2);
		$this->firstname = $first;
		$this->lastname = $last;
	}
	function getUnixName() {
		return $this->unix_name;
	}
	function getEmail() {
		return $this->email;
	}
	function getFirstname() {
		return $this->firstname;
	}
	function getLastname() {
		return $this->lastname;
	}

}
/**
 * TODO Enter description here ...
 * @author Olivier Berger
 *
 */
class ProjectImporter {
	private static $_instance ;

	/**
	 * Index of all triples imported
	 * @var ARC2 triples
	 */
	protected $index;

	protected $project_dump_res;

	/**
	 * Users descriptions found in the dump
	 * @var array of ARC2 resources (keys are URIs)
	 */
	protected $users;

	protected $persons;

	protected $roles;

	/**
	 * User names for the users found in the dump
	 * @var array of strings (keys are URIs)
	 */
	protected $user_names;

	protected $user_roles;

	/**
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $providers;

	/**
	 * Enter description here ...
	 * @var unknown_type
	 */
	static $allowedprovidertypes = array('planetforge:TrackersTool',
					      'planetforge:ForumsTool',
					      'planetforge:DocumentsTool',
					      'planetforge:MailingListTool',
					      'planetforge:TaskTool',
					      'planetforge:ScmTool',
					      'planetforge:NewsTool',
					      'planetforge:FilesReleasesTool',
						  'planetforge:SvnScmTool');

	/**
	 * Enter description here ...
	 * @var unknown_type
	 */
	static $ns = array(
			    'rdf'	=> 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
				'foaf' => 'http://xmlns.com/foaf/0.1/',
				'dcterms' => 'http://purl.org/dc/terms/',
				'oslc' => 'http://open-services.net/ns/core#',
				'oslc_cm' => 'http://open-services.net/ns/cm#',
				'forgeplucker' => FORGEPLUCKER_NS,
				'doap' => 'http://usefulinc.com/ns/doap#',
				'sioc' => 'http://rdfs.org/sioc/ns#',
				'planetforge' => PLANETFORGE_NS
	);

	public static function getInstance() {
		if (!isset(self::$_instance)) {
			$c = __CLASS__;
			self::$_instance = new $c;
		}

		return self::$_instance;
	}

	/**
	 * TODO Enter description here ...
	 * @param unknown_type $group_id
	 */
	function ProjectImporter($the_group_id = FALSE) {
		global $group_id;
		if (! $the_group_id) {
			$the_group_id = $group_id;
		}
		self::$_instance = $this;
		$this->group_id = $the_group_id;
		$this->index = False;


		$this->trackers = array();
		$this->users = False;
		$this->persons = False;
		$this->project_dump_res = False;
		$this->user_names = array();
		$this->user_roles = array();

	}

	/**
	 * Converts the JSON RDF ala OSLC to ARC2 triples
	 * @param string $json
	 * @return triples
	 */
	function parse_OSLCCoreRDFJSON($json)
	{
		$conf = array('ns' => ProjectImporter::$ns);

		// "load" the ARC2 plugin to parse RDF ala OSLC in JSON
		$parser = ARC2::getComponent("OSLCCoreRDFJSONParserPlugin", $conf);

		$debug = FALSE;
		//$debug = TRUE;
		if ($debug) {
			$arr = json_decode($json, true);
			if ($arr) {
				$message = "JSON decoded to :";
				$message .= '<pre>'. nl2br(print_r($arr, true)) . '</pre>';
				echo $message;

				/*
				 $prefixes=false;
				 $result=false;
				 foreach($arr as $type => $tabType){
				 //					$message .= 'type: '.$type.'<br />';
				 //					$message .= '<pre>'. nl2br(print_r($tabType, true)) . '</pre>';

				 if ($type=="users"){
				 $users = $tabType;
				 }
				 elseif($type=="roles"){
				 $roles = $tabType;
				 }
				 elseif($type=="trackers"){
				 $trackers = $tabType;
				 }
				 elseif($type=="docman"){
				 $docman = $tabType;
					}
					elseif($type=="frs"){
						$frs = $tabType;
					}
					elseif($type=='forums'){
						$forums = $tabType;
						}
						elseif($type=='forgeplucker:trackers') {
						foreach($tabType as $bar)
						{
						$result = json_encode($bar);
						}
						break;
						}
						elseif($type=='prefixes') {
						$prefixes = $tabType;
						}
						}
						$result['prefixes']=$prefixes;
						$message .= '<pre>'. nl2br(print_r($arr, true)) . '</pre>';
				$arr = $parser->parseData($result);
*/
			}
		}
	  $parser->parseData($json);
	  $triples = $parser->getTriples();
	  if ($debug) {
			echo 'triples :';
			echo '<pre>'. nl2br(print_r($triples, true)) . '</pre>';
	  }
	  $this->index = ARC2::getSimpleIndex($triples, false);
	  return $triples;
	}

	/**
	 * Creates an ARC2 resource
	 * @param ARC2 triples index $index
	 * @param string $uri
	 * @return ARC2 resource
	 */
	static function make_resource($uri) {
		$importer = ProjectImporter::getInstance();
		$index = $importer->index;
		$conf = array('ns' => ProjectImporter::$ns);
		$res = ARC2::getResource($conf);
	  	$res->setIndex($index);
	  	$res->setUri($uri);
	  	return $res;
	}

	/**
	 * Returns a Dump object for the index, whose rdf:type is http://planetforge.org/ns/forgeplucker_dump/project_dump#
	 * @return ARC2 resource
	 */
	protected function project_dump() {
		if (! is_array($this->project_dump_res)) {
			$dumpres = array();

			$dumpresuri = False;
			$debug = FALSE;
			//$debug = TRUE;
			if($debug) {
				echo '<pre>';
				print_r($this->index);
				echo '</pre>';
			}
			foreach ($this->index as $uri => $resource) {
				//echo '<pre>';
				//print_r('URI : '. $uri);
				//print_r($resource);
				//echo '</pre><br />';
				$res = ProjectImporter::make_resource($uri);
				if ($res->hasPropValue('rdf:type', 'http://planetforge.org/ns/forgeplucker_dump/project_dump#')) {
					$dumpresuri = $uri;
					break;
				}
			}
			// found a dump resource
			if ($dumpresuri) {
				//	    $dumpres = $this->index[$dumpresuri];Enter description here ...
				$dumpres = ProjectImporter::make_resource($dumpresuri);
			}
			else {
				// assuming it misses the top-level resource, so adding one
				if (array_key_exists('', $this->index)) {
					$base = $this->index[''];


					$about = array();
					$about[] = array('value' => 'http://coin.example.com/');
					$base['http://www.w3.org/1999/02/22-rdf-syntax-ns#about'] = $about;

					$type = array();
					$type[] = array('value' => 'http://planetforge.org/ns/forgeplucker_dump/project_dump#');
					$base['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = $type;

					$this->index['http://coin.example.com/'] = $base;
					$dumpres = ProjectImporter::make_resource('http://coin.example.com/');
				}
			}
			//print_r($dumpres);
			$this->project_dump_res = $dumpres;
		}
		return $this->project_dump_res;
	}

	function has_project_dump() {
		$result = False;
		$dumpres = $this->project_dump();
		if ($dumpres && count($dumpres)) {
			$result = True;
		}
		return $result;
	}

	function get_user_name($user) {
		return $this->user_names[$user];
	}
	/**
	 * Return a user's email
	 * @param URI $user
	 */
	function get_user_email($user) {
		$res = $this->users[$user];
		return $res->getPropValue('sioc:email');
	}

	function get_user_role($user) {
		return $this->user_roles[$user];
	}

	function display_user($user) {
		$html = '';

		$username = $this->get_user_name($user);
		$email = $this->get_user_email($user);

		$res = $this->users[$user];
		$person = $res->getPropValue('sioc:account_of');
		$res = $this->persons[$person];
		$name = $res->getPropValue('foaf:name');
		$role = $this->get_user_role($user);

		$html .= 'User :<br />';
		$html .= ' account name : '. $username .'<br />';
		$html .= ' email : '. $email .'<br />';
		$html .= ' owner : '. $name .'<br />';
		$html .= ' initial role : '. $role .'<br />';
		$html .= '<br/>';

		return $html;
	}

	function display_role($role) {
		$html = '';

		$username = $this->get_user_name($user);
		$email = $this->get_user_email($user);

		$res = $this->users[$user];
		$person = $res->getPropValue('sioc:account_of');
		$res = $this->persons[$person];
		$name = $res->getPropValue('foaf:name');
		$role = $this->get_user_role($user);

		$html .= 'User :<br />';
		$html .= ' account name : '. $username .'<br />';
		$html .= ' email : '. $email .'<br />';
		$html .= ' owner : '. $name .'<br />';
		$html .= ' role : '. $role .'<br />';
		$html .= '<br/>';

		return $html;
	}

	/**
	 * Extract users / persons from the dump
	 * @param unknown_type $dumpres
 	 * @return array of ARC2 resource
	 */
	function get_users() {
		if (! $this->users) {

			$dumpres = $this->project_dump();

			$this->users = array();
			$this->user_objs = array();

			// parse the users
			$users = $dumpres->getPropValues('forgeplucker:users');
			foreach ($users as $user) {
				//print_r('User : '.$user);
				//	      print_r($this->index[$user]);
				$res = ProjectImporter::make_resource($user);

				$user_obj = new ImportedUser($res);
				$this->user_objs[$user] = $user_obj;

				$accountName = $user_obj->getUnixName();
				$this->user_names[$user] = $accountName;
				$this->users[$user] = $res;
				//			print 'Found user : '. $accountName . "\n";
			}

			$this->persons = array();

			// parse persons and link users to the persons
			$persons = $dumpres->getPropValues('forgeplucker:persons');
			foreach ($persons as $person) {
				$res = ProjectImporter::make_resource($person);

				$this->persons[$person] = $res;

				//			print 'Found person : '. $res->getPropValue('foaf:name') . "\n";
				//	      print_r($this->index[$person]);
				//print_r($res->getProps());
				$accounts = $res->getPropValues('foaf:holdsAccount');
				foreach($accounts as $account) {
					//				print 'account : '.$account;
					$user = $this->users[$account];
					if (! $user->getPropValue('sioc:account_of')) {
						$user->setProp('sioc:account_of', $person);
					}

					$user_obj = & $this->user_objs[$account];
					$user_obj->init_owner_props($res);
				}
			}
			/*		foreach ($this->users as $user) {
			 print 'this->user : ';
			 print_r($user->getProps());
			 }
			 */
		}
		return $this->users;
	}
	function get_user_objs() {
		if (! $this->user_objs) {

			$this->get_users();

		}
		return $this->user_objs;
	}


	function supportsTool($tool)
	{
		return in_array($tool, $this->providers);
	}
	/**
	 * Analyze the tools description found in the dump
	 */
	function get_tools() {
		global $feedback;

		$dumpres = $this->project_dump();

		// TOOLS

		$tools = $dumpres->getPropValues('forgeplucker:tools');
		//	    print_r($tools);
		$providers = array();
		foreach ($tools as $tool) {
			$toolres = ProjectImporter::make_resource($tool);
			//	      print_r($toolres->getProps()); echo "\n";
			$provider = $toolres->getPropValue('planetforge:provided_by');
			if ($provider) {
				$providerres = ProjectImporter::make_resource($provider);
				$types = $providerres->getPropValues('rdf:type');
				foreach ($types as $type) {
					if (!in_array($type, ProjectImporter::$allowedprovidertypes)) {
						$feedback .= 'Need provider type : '. $type . "\n";
					}
					else {
						if (!in_array($provider, $providers)) {
							$providers[] = $provider;
						}
					}
				}
			}
		}
		//	    echo 'PROVIDERS';
		//	    print_r($providers); echo "\n";
		$this->providers = $providers;
	}

	/**
	 * Enter description here ...
	 * @param unknown_type $dumpres
	 * @return Ambigous <multitype:, ARC2>
	 */
	function get_projects() {
		global $feedback;

		$dumpres = $this->project_dump();

		$results = array();


		$this->get_users($dumpres);

		// PROJECT
		//	    $projects = $this->dump_project_uris($dumpres);
		$projects = $dumpres->getPropValues('forgeplucker:project');

		foreach ($projects as $project) {
			//	      print 'Found project : '. $project . "\n";
			//	      print_r($this->index[$project]);
			$res = ProjectImporter::make_resource($project);

			//	      print_r($res->getProps());
			//$name = $res->getPropValue('doap:name');
			//$description = $res->getPropValue('dcterms:description');
			//$homepage = $res->getPropValue('doap:homepage');
			//$hosted_by = $res->getPropValue('planetforge:hosted_by');

			$project_obj = new ImportedProject($res);

			$results[] = $project_obj;

			// handle project's roles
			$this->user_roles=array();

			foreach($project_obj->getRoles() as $role) {

				$name = $role->getName();
				foreach($role->getUsers() as $user) {

					$this->user_roles[$user] = $name;
				}
			}
			//print_r($this->user_roles);
			//	      print_r($user_roles);
//			echo "creating roles of existing users in the project\n";
//			echo "calling user_fill(".'$users'.", $this->group_id)\nwhere ".'$users'." is";
			// check user_fill : True == check mode
//			user_fill($user_roles, $this->group_id, True);
//			print_r($user_roles);
//			echo "\n";

		}

		return $results;
	}

	/**
	 * Enter description here ...
	 * @param URI $space
	 * @param ARC2 resource $spaceres
	 */
	function decode_space($space, $spaceres) {

		$types = $spaceres->getPropValues('rdf:type');
		$supported_type = False;

		foreach($types as $type) {

			// Case of the trackers
			if ($type == PLANETFORGE_NS.'Tracker') {

				$supported_type = True;

				//		      print 'Found tracker :'. $space . "\n";
				$tracker = array('uri' => $space);

				// Decode TRACKER contents
				$artifacts = $spaceres->getPropValues('oslc:results');
				$tracker['artifacts'] = array();
				foreach ($artifacts as $artifact) {
					// Decode ARTIFACTS
					//			print 'Found tracker artifact :'. $artifact . "\n";
					$cmres = ProjectImporter::make_resource($artifact);
					$tracker['artifacts'][] = array('uri' => $artifact,
													'details' => $cmres->getProps());
				}
//				$this->trackers[] = $tracker;
				echo '<pre>'. htmlspecialchars(print_r($tracker, True)) . '</pre>';

				tracker_fill($trackers, $group_id, $users);

				break;
			}
			// other cases
			//  ...
		}
	}


}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
?>
