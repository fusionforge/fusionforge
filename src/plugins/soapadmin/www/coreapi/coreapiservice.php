<?php

require_once '../../../env.inc.php';
require_once $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/gettext.php';
require_once $gfcommon.'include/FusionForge.class.php';
require_once './coreapiobjects.php';

// Log4php initialisation
require_once dirname(__FILE__).'/../log4php/Logger.php';
Logger::configure(dirname(__FILE__).'/../log4php.properties');


/**
 * CoreApiService (SOAP Server)
 */
Class CoreApiServer extends SoapServer {

	private $logger;

	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"getSCMData" => "getSCMData_soap",
		"getSCMDataResponse" => "getSCMDataResponse_soap",
		"scmData" => "scmData_soap",
		"getVersion" => "getVersion_soap",
		"getVersionResponse" => "getVersionResponse_soap",
		"getGroups" => "getGroups_soap",
		"getGroupsResponse" => "getGroupsResponse_soap",
		"group" => "group_soap",
		"getPublicProjectNames" => "getPublicProjectNames_soap",
		"getPublicProjectNamesResponse" => "getPublicProjectNamesResponse_soap",
		"userGetGroups" => "userGetGroups_soap",
		"userGetGroupsResponse" => "userGetGroupsResponse_soap",
		"getUsers" => "getUsers_soap",
		"getUsersResponse" => "getUsersResponse_soap",
		"user" => "user_soap",
		"getGroupsByName" => "getGroupsByName_soap",
		"getGroupsByNameResponse" => "getGroupsByNameResponse_soap",
		"getUsersByName" => "getUsersByName_soap",
		"getUsersByNameResponse" => "getUsersByNameResponse_soap",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl="FusionforgeCoreApi.wsdl", $options=array()) {

		$this->logger = Logger::getLogger('api.soap.core.CoreApi');
		$this->logger->debug("FusionForgeCoreApi Soap Server created ...");
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
		parent::__construct($wsdl, $options);
	}
}


class CoreApiService {

	private $logger;

	public function __construct() {
		// log4php logger initialization for the class
		$this->logger = Logger::getLogger('fusionforge.api.soap.CoreApi');
	}

		/**
	 * Checks if an argument list matches against a valid argument type list
	 * @param array $arguments The argument list to check
	 * @param array $validParameters A list of valid argument types
	 * @return boolean true if arguments match against validParameters
	 * @throws Exception invalid function signature message
	 */
	public function _checkArguments($arguments, $validParameters) {
		$variables = "";
		foreach ($arguments as $arg) {
		    $type = gettype($arg);
		    if ($type == "object") {
		        $type = get_class($arg);
		    }
		    $variables .= "(".$type.")";
		}
		if (!in_array($variables, $validParameters)) {
		    throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
		}
		return true;
	}

	/**
	 * Service Call: getVersion
	 *
	 * @param mixed getVersion_soap (Soap request object)
	 * @return getVersionResponse_soap (Soap response object) or SoapFault if parameter are invalid
	 */
	public function getVersion($mixed = null) {
		$this->logger->info("CoreApiService Soap call : getVersion");
		$validParameters = array(
			"(getVersion_soap)",
		);

		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}

		$fusionforge = new FusionForge();
		$response = new getVersionResponse_soap();
		$response->version = $fusionforge->software_version;
		return $response;
	}


	/**
	 * Service Call: getGroups
	 *
	 * @param mixed  getGroups_soap (Soap request object)
	 * @return getGroupsResponse_soap (Soap response object) or SoapFault if parameters are invalid
	 */
	public function getGroups($mixed = null) {
		$group_ids = $mixed->group_id;
		$this->logger->info("CoreApiService Soap call : getGroups for group_id ".var_export($group_ids, true));
		$validParameters = array(
			"(getGroups_soap)",
		);

		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}

		$grps =& group_get_objects($group_ids);
		if (!$grps) {
			$this->logger->debug("Could Not Get Groups by Id");
			return new soap_fault ('2001','group','Could Not Get Groups by Id'.$inputArgs,$feedback);
		}

		$response = new getGroupsResponse_soap();
		$this->logger->debug((count($grps)+1)." Groups objects found");

		for ($i=0; $i<count($grps); $i++) {
			$group = new group_soap();
			$group->group_id = $grps[$i]->data_array['group_id'];
			$group->group_name=$grps[$i]->data_array['group_name'];
			$group->homepage=$grps[$i]->data_array['homepage'];
			$group->is_public=$grps[$i]->data_array['is_public'];
			$group->status=$grps[$i]->data_array['status'];
			$group->unix_group_name=$grps[$i]->data_array['unix_group_name'];
			$group->short_description=$grps[$i]->data_array['short_description'];
			$group->scm_box=$grps[$i]->data_array['scm_box'];
			$group->register_time=$grps[$i]->data_array['register_time'];
			$response->group[$i]=$group;
			$this->logger->debug("Adding Group objects : ".var_export($group, true));
		}

		return $response;
	}


	/**
	 * Service Call: getUsers
	 *
	 * @param mixed  getUsers_soap (Soap request object)
	 * @return getUsersResponse_soap (Soap response object) or SoapFault if parameters are invalid
	 */
	public function getUsers($mixed = null) {
		$this->logger->debug("CoreApiService Soap call getUsers ".var_export($mixed, true));

		// The $mixed->user_id is an array only if several user_id in the SOAP request
		if (is_array($mixed->user_id)) {
			$user_id = $mixed->user_id;
		}
		else {
			$user_id = array(0=>$mixed->user_id);
		}

		$validParameters = array(
			"(getUsers_soap)",
		);

		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}

		$users =& user_get_objects($user_id);
		$this->logger->debug("users found : ".var_export($users, true));
		if (!$users) {
			return new SoapFault('3001','Could Not Get Users By Id');
		}

		$response = new getUsersResponse_soap();
		for ($i=0; $i<count($users); $i++) {
			if ($users[$i]->isError()){
				//skip it if it had an error
			} else {
				//build each user_soap response
				$user = new user_soap();
				$user->user_id=$users[$i]->data_array['user_id'];
				$user->user_name=$users[$i]->data_array['user_name'];
				$user->title=$users[$i]->data_array['title'];
				$user->firstname=$users[$i]->data_array['firstname'];
				$user->lastname=$users[$i]->data_array['lastname'];
				$user->address=$users[$i]->data_array['address'];
				$user->address2=$users[$i]->data_array['address2'];
				$user->phone=$users[$i]->data_array['phone'];
				$user->fax=$users[$i]->data_array['fax'];
				$user->status=$users[$i]->data_array['status'];
				$user->timezone=$users[$i]->data_array['timezone'];
				$user->country_code=$users[$i]->data_array['country_code'];
				$user->add_date=$users[$i]->data_array['add_date'];
				$user->language_id=$users[$i]->data_array['language_id'];
				$response->user[$i]=$user;
			}
		}
		return $response;
	}


	/**
	 * Service Call: getGroupsByName
	 *
	 * @param mixed	getGroupsByName_soap (Soap request object)
	 * @return getGroupsByNameResponse_soap (Soap response object) or SoapFault if parameters are invalid
	 */
	public function getGroupsByName($mixed = null) {
		$this->logger->debug("Soap call getGroupsByName ".var_export($mixed, true));
		// The $mixed->group_name is an array only if several group_name in the SOAP request
		if (is_array($mixed->group_name)) {
			$group_names = $mixed->group_name;
		}
		else {
			$group_names = array(0=>$mixed->group_name);
		}

		$validParameters = array(
			"(getGroupsByName_soap)",
		);

		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}
		$grps =& group_get_objects_by_name($group_names);
		if (!$grps) {
			return new SoapFault('2002','Could Not Get Groups by Name');
		}

		$response = new getGroupsByNameResponse_soap();
		// $grps contains an array of Group object
		for ($i=0; $i<count($grps); $i++) {
			if ($grps[$i]->isError()) {
				//skip it if it had an error
			} else {
				//build each group_soap response
				$group = new group_soap();
				$group->group_id=$grps[$i]->data_array['group_id'];
				$group->group_name=$grps[$i]->data_array['group_name'];
				$group->homepage=$grps[$i]->data_array['homepage'];
				$group->is_public=$grps[$i]->data_array['is_public'];
				$group->status=$grps[$i]->data_array['status'];
				$group->unix_group_name=$grps[$i]->data_array['unix_group_name'];
				$group->short_description=$grps[$i]->data_array['short_description'];
				$group->scm_box=$grps[$i]->data_array['scm_box'];
				$group->register_time=$grps[$i]->data_array['register_time'];
				$response->group[$i] = $group;
			}
		}

		$this->logger->debug("getGroupsByNameResponse_soap : ".var_export($response, true));
		return $response;
	}


	/**
	 * Service Call: getPublicProjectNames
	 *
	 * @param mixed	getPublicProjectNames_soap (Soap request object)
	 * @return getPublicProjectNamesResponse_soap (Soap response object) or SoapFault if parameters are invalid
	 */
	public function getPublicProjectNames($mixed = null) {
		$this->logger->debug("Soap call getPublicProjectNames");
		$validParameters = array(
			"(getPublicProjectNames_soap)",
		);

		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}
		// SOAP Response
		$response = new getPublicProjectNamesResponse_soap();
		$forge = new FusionForge();
		$response->project_name = $forge->getPublicProjectNames();

		if ($forge->isError()) {
			$errMsg = 'Could Not Get Public Group Names: '.$forge->getErrorMessage();
			return new SoapFault('2003',$errMsg);
		}
		$this->logger->debug("Public Projects number found : ".var_export($response->project_name, true));
		return $response;
	}


	/**
	 * Service Call: getUsersByName
	 *
	 * @param mixed	getUsersByName_soap (Soap request object)
	 * @return getUsersByNameResponse_soap (Soap response object) or SoapFault if parameters are invalid
	 */
	public function getUsersByName($mixed = null) {
		$this->logger->debug("CoreApiService Soap call getUsersByName ".var_export($mixed, true));
		// The $mixed->group_name is an array only if several group_name in the SOAP request
		if (is_array($mixed->user_name)) {
			$user_names = $mixed->user_name;
		}
		else {
			$user_names = array(0=>$mixed->user_name);
		}

		$validParameters = array(
			"(getUsersByName_soap)",
		);
		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}

		$users =& user_get_objects_by_name($user_names);
		if (!$users) {
			return new SoapFault('3002','Could Not Get Users By Name');
		}

		$response = new getUsersByNameResponse_soap();
		for ($i=0; $i<count($users); $i++) {
			if ($users[$i]->isError()){
				//skip it if it had an error
			} else {
				//build each user_soap response
				$user = new user_soap();
				$user->user_id=$users[$i]->data_array['user_id'];
				$user->user_name=$users[$i]->data_array['user_name'];
				$user->title=$users[$i]->data_array['title'];
				$user->firstname=$users[$i]->data_array['firstname'];
				$user->lastname=$users[$i]->data_array['lastname'];
				$user->address=$users[$i]->data_array['address'];
				$user->address2=$users[$i]->data_array['address2'];
				$user->phone=$users[$i]->data_array['phone'];
				$user->fax=$users[$i]->data_array['fax'];
				$user->status=$users[$i]->data_array['status'];
				$user->timezone=$users[$i]->data_array['timezone'];
				$user->country_code=$users[$i]->data_array['country_code'];
				$user->add_date=$users[$i]->data_array['add_date'];
				$user->language_id=$users[$i]->data_array['language_id'];
				$response->user[$i]=$user;
			}
		}
		return $response;
	}


	/**
	 * Service Call: userGetGroups
	 *
	 * @param mixed	userGetGroups_soap (Soap request object)
	 * @return userGetGroupsResponse_soap (Soap response object) or SoapFault if parameters are invalid
	 */
	public function userGetGroups($mixed = null) {

		$this->logger->debug("CoreApiService Soap call : userGetGroups for user_id ".var_export($mixed, true));
		$user_id = $mixed->user_id;

		$validParameters = array(
			"(userGetGroups_soap)",
		);
		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}

		$user =& user_get_object($user_id);
		if (!$user) {
			return new SoapFault('3003','Could Not Get Users Groups');
		}

		// $grps contains an array of Group object
		$grps = $user->getGroups();
		$this->logger->debug(count($grps). " groups found");
		$response = new userGetGroupsResponse_soap();

		for ($i=0; $i<count($grps); $i++) {
			if ($grps[$i]->isError()) {
				//skip it if it had an error
			} else {
				//build each group_soap response
				$group = new group_soap();
				$group->group_id=$grps[$i]->data_array['group_id'];
				$group->group_name=$grps[$i]->data_array['group_name'];
				$group->homepage=$grps[$i]->data_array['homepage'];
				$group->is_public=$grps[$i]->data_array['is_public'];
				$group->status=$grps[$i]->data_array['status'];
				$group->unix_group_name=$grps[$i]->data_array['unix_group_name'];
				$group->short_description=$grps[$i]->data_array['short_description'];
				$group->scm_box=$grps[$i]->data_array['scm_box'];
				$group->register_time=$grps[$i]->data_array['register_time'];
				$response->group[$i] = $group;
				$this->logger->debug("adding group : ".var_export($group, true));
			}
		}

		$this->logger->debug("userGetGroupsResponse_soap : ".var_export($response, true));

		return $response;

	}


	/**
	 * Service Call: getSCMData
	 *
	 * @param mixed	getSCMData_soap (Soap request object)
	 * @return getSCMDataResponse_soap (Soap response object) or SoapFault if parameters are invalid
	 */
	public function getSCMData($mixed = null) {
		$group_id = $mixed->group_id;
		$this->logger->debug("Soap call : getSCMData for group_id ".$group_id);
		$validParameters = array(
			"(getSCMData_soap)",
		);
		try {
			$args = func_get_args();
			$this->_checkArguments($args, $validParameters);
		}
		catch (Exception $e) {
			// Invalid parameters => return a soap fault
			return new SoapFault($e->getCode(),$e->getMessage());
		}
		// Search the group object in the database
		$grp =& group_get_object($group_id);
		$this->logger->debug("group_get_object : ".var_export($grp, true));

		// Error handle
		if (!$grp || !is_object($grp)) {
			$this->logger->error("Returning SOAP Fault - Could Not Get Group : ".$group_id);
			// TODO : Error code to be determined
			return new SoapFault('-1', 'Could Not Get Group');
		} elseif ($grp->isError()) {
			$this->logger->error($grp->getErrorMessage()." for group_id : ".$group_id);
			// TODO : Error code to be determined
			return new SoapFault ('-1',$grp->getErrorMessage());
		}
		if (!$grp->usesSCM()) {
			$this->logger->error('SCM is not enabled in this project ; group_id : '.$group_id);
			// TODO : Error code to be determined
			return new SoapFault ('-1','SCM is not enabled in this project');
		}

		// Create the SOAP response
		$response = new getSCMDataResponse_soap();
		$scm_data = new scmData_soap();

		if ($grp->usesPlugin("scmcvs")) {
			$scm_data->type = "CVS";
			$scm_data->allow_anonymous = $grp->enableAnonSCM();
			$scm_data->public = $grp->enablePserver();
			$scm_data->box = $grp->getSCMBox();
			$scm_data->module = $grp->getUnixName();
			$scm_data->connection_string = "";	// this doesn't apply to CVS
			// Note: This was taken from CVS plugin. Maybe we shouldn't hardcode this?
			$scm_data->root = "/cvsroot/".$grp->getUnixName();
		} else if ($grp->usesPlugin("scmsvn")) {
			$scm_data->type = "SVN";
			$scm_data->allow_anonymous = $grp->enableAnonSCM();
			$scm_data->public = $grp->enablePserver();
			$scm_data->box = $grp->getSCMBox();
			$scm_data->root = $GLOBALS["svn_root"]."/".$grp->getUnixName();
			$scm_data->module = "";		// doesn't apply to SVN

			// Note: This is an ugly hack. We can't access SVN plugin object for this project
			// directly. Currently this is being rewritten, but for now we must make this.

			//TODO How to have access to $gfconfig variable ??

			include $gfconfig.'plugins/scmsvn/config.php';
			$scm_data->connection_string = "http".(($use_ssl) ? "s" : "")."://".$grp->getSCMBox()."/".$svn_root."/".$grp->getUnixName();
		}

		$response->scm_data= $scm_data;
		return $response;
	}

}

?>
