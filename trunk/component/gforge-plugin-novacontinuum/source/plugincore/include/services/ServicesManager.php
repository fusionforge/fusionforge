<?php

require_once(dirname(__FILE__).'/../dto/ContinuumInstance.php');
require_once(dirname(__FILE__).'/../dao/ContinuumInstanceDAO.php');

require_once(dirname(__FILE__).'/../dto/HttpProxy.php');
require_once(dirname(__FILE__).'/../dao/HttpProxyDAO.php');

require_once(dirname(__FILE__).'/../dto/GlobalConfiguration.php');
require_once(dirname(__FILE__).'/../dao/GlobalConfigurationDAO.php');

require_once(dirname(__FILE__).'/../dao/GForgeDAO.php');

require_once(dirname(__FILE__).'/../dto/Project.php');
require_once(dirname(__FILE__).'/../dao/ProjectsDAO.php');

require_once(dirname(__FILE__).'/../dto/ProjectRelease.php');

class ServicesManager {
	
	function ServicesManager() {
     
  }

  function &getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new ServicesManager();
      }
      return $instance;
  }

	function getGForgeRoles($groupId){
		$dao =& GForgeDAO::getInstance();

		return $dao->getGForgeRoles($groupId);
	}

	function addRoleForGroup($groupId,$roleId,$rolename){
		$dao =& GForgeDAO::getInstance();

		return $dao->addRoleForGroup($groupId,$roleId,$rolename);	
	}
	
	function getUserProject(){
		$dao =& GForgeDAO::getInstance();
		return $dao->getUserProject();
	}
	
	function hasRoleForGroup($groupId,$rolename){
		global $group;
		$sessionUser = session_get_user ();
		if(isset($sessionUser)&& $sessionUser!=null){
			$perm = &$group->getPermission ($sessionUser);
			
			if(isset($perm)&& $perm->error_state!=1){
				$isAdmin = $perm->isAdmin ();
				$permData = $perm->getPermData ();
				$roleId = $permData['role_id'];
			}else{
				$roleId = -1;
				$isAdmin = false;
			}
		}else{
			$roleId = -1;
			$isAdmin = false;
		}
		$dao =& GForgeDAO::getInstance();

		return $isAdmin || $dao->hasRoleForGroup($groupId,$roleId,$rolename);	
	}
	
	function deleteRoles($groupId){
		$dao =& GForgeDAO::getInstance();

		return $dao->deleteRoles($groupId);	
	}
	
	function getRoles($groupId){
		$dao =& GForgeDAO::getInstance();

		return $dao->getRoles($groupId);	
	}
		
	function getConfiguration(){
		$dao =& GlobalConfigurationDAO::getInstance();

		return $dao->getConfiguration();
	}
	
	function updateConfiguration($configuration){
		$dao =& GlobalConfigurationDAO::getInstance();

		return $dao->updateConfiguration($configuration);
	}
	
	function addContinuumInstance($instance){
		$dao =& ContinuumInstanceDAO::getInstance();

		return $dao->addContinuumInstance($instance);
	}
	
	function updateContinuumInstance($instance){
		$dao =& ContinuumInstanceDAO::getInstance();

		return $dao->updateContinuumInstance($instance);
	}
	
	function deleteContinuumInstance($instanceid){
		$dao =& ContinuumInstanceDAO::getInstance();

		return $dao->deleteContinuumInstance($instanceid);
	}
	
	function getProjectsForInstance($instanceId){
		$dao =& ContinuumInstanceDAO::getInstance();
		
		return $dao->getProjectsForInstance($instanceId);
	}
	
	function getInstanceForProjects($group_id){
		$dao =& ContinuumInstanceDAO::getInstance();
		
		return $dao->getInstanceForProjects($group_id);
	}
	
	function defineInstanceForProject($group_id,$instanceId){
		$dao =& ContinuumInstanceDAO::getInstance();
		
		$selectedInstance = $dao->getInstanceForProjects($group_id);
		if(isset($selectedInstance)){
			$pingRetPrivate = $selectedInstance->instance->ping();
			if($pingRetPrivate===true) {
				$selectedInstance->instance->removeProjectGroup($selectedInstance->continuumProjectGroupId);
			}
		}
		$newInstance = $dao->getContinuumInstance($instanceId);
		if(isset($newInstance)){
			$group = &group_get_object ($group_id);
			$projectGroup  = $newInstance->addProjectGroup($group->getPublicName(),$group->getUnixName(),'Project group for '.$group->getPublicName());
			
			if($projectGroup->id=='F'){
				return $dao->removeInstanceForProject($group_id);
			}else{
				$dao->defineInstanceForProject($group_id,$instanceId,$projectGroup->id);
				$dao =& ProjectsDAO::getInstance();
				$projects = $dao->getProjects($group_id);
				foreach ($projects as $keyProject=>$project) {
					$dao->removeAllContinuumProject($project->id);
					$addedProjects = $newInstance->addMavenTwoProject($project->url,$project->userName,$project->pwd,$projectGroup->id);
					foreach ($addedProjects as $key=>$value) {
	   				$dao->addContinuumProject($project->id,$value->id);
	   			}
				}
				return true;
			}
		}else{
			return false;
		}
	}
	
	function addMavenTwoProject($name,$url,$user,$password,$selectedInstance,$group_id){
	
		if(isset($selectedInstance) && isset($url)){
			$addedProjects = $selectedInstance->instance->addMavenTwoProject($url,$user,$password,$selectedInstance->continuumProjectGroupId);
			if(count($addedProjects)>0){
				$dao =& ProjectsDAO::getInstance();
				$projectid = $dao->insertProject($name,$url,$user,$password,$group_id);
				if($projectid!=-1){
					foreach ($addedProjects as $key=>$value) {
	   				$dao->addContinuumProject($projectid,$value->id);
	   			}
	   		}
   		}
		}
	}

	function getContinuumProject($projectid,$selectedInstance){
		return $selectedInstance->instance->getProjectWithAllDetails($projectid);
	}
	
	function getBuildDefinitionsForProjectGroup($selectedInstance){
		return $selectedInstance->instance->getBuildDefinitionsForProjectGroup($selectedInstance->continuumProjectGroupId);
	}
	
	function getProjects($group_id){
		$dao =& ProjectsDAO::getInstance();
		return $dao->getProjects($group_id);
	}
	
	function getProject($projectid){
		$dao =& ProjectsDAO::getInstance();
		return $dao->getProject($projectid);
	}
	
	function updateProject($project){
		$dao =& ProjectsDAO::getInstance();
		return $dao->updateProject($project);
	}
	
	function deleteProject($projectid){
		$dao =& ProjectsDAO::getInstance();
		return $dao->deleteProject($projectid);
	}
	
	function getAllContinuumProjects($projectid){
		$dao =& ProjectsDAO::getInstance();
		return $dao->getAllContinuumProjects($projectid);
	}
	
	function removeInstanceForProject($group_id){
		$dao =& ContinuumInstanceDAO::getInstance();
		
		$selectedInstance = $dao->getInstanceForProjects($group_id);
		if(isset($selectedInstance)){
			$pingRetPrivate = $selectedInstance->instance->ping();
			if($pingRetPrivate===true) {
				$selectedInstance->instance->removeProjectGroup($selectedInstance->continuumProjectGroupId);
			}
		}
		return $dao->removeInstanceForProject($group_id);
	}
	
	function deletePrivateInstance($group_id){
		$dao =& ContinuumInstanceDAO::getInstance();
		return $dao->deletePrivateInstance($group_id);
	}
	
	function getPrivateInstanceForProjects($group_id){
		$dao =& ContinuumInstanceDAO::getInstance();
		
		return $dao->getPrivateInstanceForProjects($group_id);
	}
	function getAllUsableContinuumInstances($group_id){
		$dao =& ContinuumInstanceDAO::getInstance();


		$array_instances = array ();
		$usableInstances = $dao->getAllUsableContinuumInstances();
	
		$projectDefine = $dao->getInstanceForProjects($group_id);
		
		foreach ($usableInstances as $instance ) {
  		
  		if($instance->groupId==-1){
  			if(isset($projectDefine)&&$projectDefine->instance->id==$instance->id){
	  			$array_instances [] =$instance;
				}else{
		  		$projects = $dao->getProjectsForInstance($instance->id);
		  		if((count($projects)<$instance->maxUse) || ($instance->maxUse == 0)){
			  		$pingRet = $instance->ping();
			  		if($pingRet===true){
							$array_instances [] =$instance;
						}
					}
				}
			}
  	}
		return $array_instances;
	}
	
	function getAllContinuumInstances(){
		$dao =& ContinuumInstanceDAO::getInstance();

		return $dao->getAllContinuumInstances();
	}
	
	function getContinuumInstance($instanceId){
		$dao =& ContinuumInstanceDAO::getInstance();

		return $dao->getContinuumInstance($instanceId);
	}
	
	function addHttpProxy($instance){
		$dao =& HttpProxyDAO::getInstance();

		return $dao->addHttpProxy($instance);
	}
	
	function updateHttpProxy($instance){
		$dao =& HttpProxyDAO::getInstance();

		return $dao->updateHttpProxy($instance);
	}
	
	function deleteHttpProxy($instanceid){
		$dao =& HttpProxyDAO::getInstance();

		return $dao->deleteHttpProxy($instanceid);
	}
	
	function getAllHttpProxies(){
		$dao =& HttpProxyDAO::getInstance();

		return $dao->getAllHttpProxies();
	}
	
	function getHttpProxy($instanceId){
		$dao =& HttpProxyDAO::getInstance();

		return $dao->getHttpProxy($instanceId);
	}
	
	function setdefaultbuilddef($builddefid,$group_id){
		return $this->definedefaultbuilddef($builddefid,$group_id,true);
	}
	
	function unsetdefaultbuilddef($builddefid,$group_id){
		return $this->definedefaultbuilddef($builddefid,$group_id,false);
	}
	
	function definedefaultbuilddef($builddefid,$group_id,$value){
		$selectedInstance = $this->getInstanceForProjects($group_id);
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				$buildDefs = $this->getBuildDefinitionsForProjectGroup($selectedInstance);
				foreach ($buildDefs as $key=>$buildDef) {
					if($buildDef->id==$builddefid){
						$buildDef->defaultForProject =$value;
						return $selectedInstance->instance->updateBuildDefinitionForProjectGroup($selectedInstance->continuumProjectGroupId,$buildDef);
					}
				}
				
			}
		}
		return null;
	}
	
	function getbuilddef($builddefid,$group_id){
		$selectedInstance = $this->getInstanceForProjects($group_id);
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				$buildDefs = $this->getBuildDefinitionsForProjectGroup($selectedInstance);
				foreach ($buildDefs as $key=>$buildDef) {
					if($buildDef->id==$builddefid){
						return $buildDef;
					}
				}
			}
		}
		return null;
	}
	
	function deletebuilddef($builddefid,$group_id){
		$selectedInstance = $this->getInstanceForProjects($group_id);
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				return $selectedInstance->instance->removeBuildDefinitionFromProjectGroup($selectedInstance->continuumProjectGroupId,$builddefid);
			}
		}
		return null;
	}
	
	function addbuilddef($group_id,$goals,$arguments,$scheduleid,$profileid,$buildFresh,$alwaysBuild,$buildFile){
		$selectedInstance = $this->getInstanceForProjects($group_id);
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				$buildDef = new BuildDefinition();
				$buildDef->defaultForProject =false;
				$buildDef->goals=$goals;
				$buildDef->arguments=$arguments;
				$buildDef->schedule=$selectedInstance->instance->getSchedule($scheduleid);
				if($profileid==-1){
					$buildDef->profile=null;
				}else{
					$buildDef->profile=$selectedInstance->instance->getProfile($profileid);
				}
				$buildDef->buildFile=$buildFile;
				$buildDef->buildFresh=$buildFresh;
				$buildDef->alwaysBuild=$alwaysBuild;
				return $selectedInstance->instance->addBuildDefinitionToProjectGroup($selectedInstance->continuumProjectGroupId,$buildDef);
			}
		}
		return null;
	}
	
	function editbuilddef($group_id,$builddefid,$goals,$arguments,$scheduleid,$profileid,$buildFresh,$alwaysBuild,$buildFile){
		$selectedInstance = $this->getInstanceForProjects($group_id);
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				$buildDefs = $this->getBuildDefinitionsForProjectGroup($selectedInstance);
				foreach ($buildDefs as $key=>$buildDef) {
					if($buildDef->id==$builddefid){
						$buildDef->goals = $goals;
						$buildDef->arguments = $arguments;
						$buildDef->schedule=$selectedInstance->instance->getSchedule($scheduleid);
						if($profileid==-1){
							$buildDef->profile=null;
						}else{
							$buildDef->profile=$selectedInstance->instance->getProfile($profileid);
						}
						
						$buildDef->buildFile=$buildFile;
						$buildDef->buildFresh=$buildFresh;
						$buildDef->alwaysBuild=$alwaysBuild;
						return $selectedInstance->instance->updateBuildDefinitionForProjectGroup($selectedInstance->continuumProjectGroupId,$buildDef);
					}
				}
				
			}
		}
		return null;
	}
	
	function getSchedules( $groupId ){
		$selectedInstance = $this->getInstanceForProjects($groupId);
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				return $selectedInstance->instance->getSchedules();
			}
		}
		return null;
	}
	
	function getProfiles($groupId){
		$selectedInstance = $this->getInstanceForProjects($groupId);
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				return $selectedInstance->instance->getProfiles();
			}
		}
		return null;
	}
	
	function getProfilesForInstance($instance){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getProfiles();
			}
		}
		return null;
	}
	
	function getProfileForInstance($instance,$profileid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getProfile($profileid);
			}
		}
		return null;
	}
	
	function deleteProfile($instance,$profileid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->deleteProfile($profileid);
			}
		}
		return null;
	}
	
	function addProfile($instance,$profile){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->addProfile($profile);
			}
		}
		return null;
	}
	
	function updateProfile($instance,$profile){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->updateProfile($profile);
			}
		}
		return null;
	}
	
	function getInstallationsForInstance($instance){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getInstallations();
			}
		}
		return null;
	}
	
	function getInstallationForInstance($instance,$installationid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getInstallation($installationid);
			}
		}
		return null;
	}
	
	function deleteInstallation($instance,$installationid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->deleteInstallation($installationid);
			}
		}
		return null;
	}
	
	function addInstallation($instance,$installation){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->addInstallation($installation);
			}
		}
		return null;
	}
	
	function updateInstallation($instance,$installation){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->updateInstallation($installation);
			}
		}
		return null;
	}
	
	function getSchedulesForInstance( $instance ){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getSchedules();
			}
		}
		return null;
	}
	
	function getScheduleForInstance( $instance, $scheduleid ){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getSchedule($scheduleid);
			}
		}
		return null;
	}
	
	function deleteSchedule($instance,$scheduleid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->removeSchedule($scheduleid);
			}
		}
		return null;
	}
	
	function addSchedule($instance,$schedule){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->addSchedule($schedule);
			}
		}
		return null;
	}
	
	function updateSchedule($instance,$schedule){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->updateSchedule($schedule);
			}
		}
		return null;
	}
	
	function enableSchedule($instance, $scheduleid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				$schedule = $instance->getSchedule($scheduleid);
				$schedule->active = true;
				return $instance->updateSchedule($schedule);
			}
		}
		return null;
	}
	
	function disableSchedule($instance, $scheduleid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				$schedule = $instance->getSchedule($scheduleid);
				$schedule->active = false;
				return $instance->updateSchedule($schedule);
			}
		}
		return null;
	}
	
	function getProjectGroupDetails($selectedInstance){
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				return $selectedInstance->instance->getProjectGroup($selectedInstance->continuumProjectGroupId);
			}
		}
		return null;
	}
	
	function getBuildResultsForProject($projectId, $selectedInstance){
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				return $selectedInstance->instance->getBuildResultsForProject($projectId);
			}
		}
		return null;
	}
	
	function getBuildResult($projectId,$buildId, $selectedInstance){
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				return $selectedInstance->instance->getBuildResult($projectId,$buildId);
			}
		}
		return null;
	}
	
	function getBuildOutput($projectId,$buildId,$selectedInstance){
		if(isset($selectedInstance)){
			$pingRet = $selectedInstance->instance->ping();
			if($pingRet===true){
				return $selectedInstance->instance->getBuildOutput($projectId,$buildId);
			}
		}
		return null;
	}
	
	function getGroupNotifier($instance,$projectGroupId,$notifierid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getGroupNotifier($projectGroupId,$notifierid);
			}
		}
		return null;
	}
	
	function getNotifier($instance,$projectid,$notifierid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->getNotifier($projectid,$notifierid);
			}
		}
		return null;
	}
	
	function updateGroupNotifier($instance,$projectGroupId,$notifier){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->updateGroupNotifier($projectGroupId,$notifier);
			}
		}
		return null;
	}
	
	function updateNotifier($instance,$projectid,$notifier){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->updateNotifier($projectid,$notifier);
			}
		}
		return null;
	}
	
	function addGroupNotifier($instance,$projectGroupId,$notifier){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->addGroupNotifier($projectGroupId,$notifier);
			}
		}
		return null;
	}
	
	function addNotifier($instance,$projectid,$notifier){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->addNotifier($projectid,$notifier);
			}
		}
		return null;
	}
	
	function deleteGroupNotifier($instance,$projectGroupId,$notifierid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->removeGroupNotifier($projectGroupId,$notifierid);
			}
		}
		return null;
	}
	
	function deleteNotifier($instance,$projectid,$notifierid){
		if(isset($instance)){
			$pingRet = $instance->ping();
			if($pingRet===true){
				return $instance->removeNotifier($projectid,$notifierid);
			}
		}
		return null;
	}
	
	function getContinuumDataDir(){
		return '/var/lib/gforge/novacontinuum/';
	}
	
	function authDavUserWrite($user,$pwd, $path) {
		if (empty($user)) {
			return false;
		}
		
		$dao =& GForgeDAO::getInstance();

		$userId = $dao->getGForgeUserID($user, $pwd);	
		
		if($userId == null){
			return false;
		}
		
		return $this->authDavUser($userId,$path,'write_maven_site');
	}
	
	function authDavUserRead($path) {
		$sessionUser = session_get_user ();
		if($sessionUser==null){
			return false;
		}else{
			return $this->authDavUser($sessionUser->getID(),$path,'read_maven_site');
		}
	}
	
	function checkGroupDirExists($path) {
		$pluginRoot = $this->getContinuumDataDir();
		$groupName = $this->extractGroupName($path);
		if(!is_dir($pluginRoot)){
			mkdir($pluginRoot);
		}
		if(!is_dir($pluginRoot.'/'.$groupName)){
			mkdir($pluginRoot.'/'.$groupName);
		}
	}
	
	function authDavUser($userId,$path, $role) {
		$dao =& GForgeDAO::getInstance();
		
		if($dao->isGForgeSuperUser($userId)){
			return true;
		}
		
		$groupName = $this->extractGroupName($path);
		if($groupName == null){
			return false;
		}
		$groupId = $dao->hasUserProject($userId,$groupName);
		
		if($groupId == null){
			return false;
		}
		
		if($dao->isGForgeUserAdminForGroup($userId,$groupId)){
			return true;
		}
		
		$roleId = $dao->getGForgeUserRoleForGroup($userId,$groupId);
		if($roleId == null){
			return false;
		}
		
  	if($dao->hasRoleForGroup($groupId,$roleId,$role)){
  			return true;
  	}
  	
		return false;
	}
	
	function extractGroupName($path){
		$arr = explode ("/", $path, 3);
		if($arr===false ||count($arr)<2){
			return null;
		}else{
			if(empty($arr[1])){
				return null;
			}else{
				return $arr[1];
			}
		}
		 
	}
	
	function extractFileName($path){
		$arr = explode ("/", $path);
		$ret = "";
		for ($i=2;$i<count($arr) ;$i++ ) {
  		$ret=$ret.'/'.$arr[$i];
  	}
		return $ret;
	}
	
	function unauthDavUser() {
		$realm = 'Restricted area novacontinuum';
		header('WWW-Authenticate: Basic realm="'.$realm.'"');
		header('HTTP/1.0 401 Unauthorized');
		die('401 Unauthorized');
	}
	
	function getAvailableHisto($projectRoot){
		$availableHisto = array();
		$dh = @opendir($projectRoot);
		if($dh){
			while (false !== ($file = readdir($dh))) {
				if($file != '.' && $file != '..'){
					if (is_dir("$projectRoot/$file")) {
						$availableHisto[] = $file;
					}
				}
			}
		}

		sort($availableHisto,SORT_STRING);
		$availableHisto = array_reverse($availableHisto);
		return $availableHisto;
	}
	
	function formatSiteDate($value){
		global $Language;
		$year = $value[0].$value[1].$value[2].$value[3];
		$month = $value[4].$value[5];
		$day = $value[6].$value[7];
				
		return  sprintf ( dgettext ("gforge-plugin-novacontinuum", "consult_maven_site_date_format") ,array($year,$month,$day));
	}
	
	function deleteSite($siteid,$group_id){
		$group = &group_get_object ($group_id);
		if (!$group || !is_object($group) || $group->isError()) {
			return false;	
		}else{
			$dir = $this->getContinuumDataDir().$group->getUnixName().'/'.$siteid;
			if(is_dir($dir)){
				$this->rmDir($dir);
			}
		}
		
	}
	
	function rmDir($dir){
		if(!$dh = @opendir($dir)) return;
    while (false !== ($obj = readdir($dh))) {
        if($obj!='.' && $obj!='..'){
        	if (!@unlink($dir.'/'.$obj)){
						$this->rmDir($dir.'/'.$obj);
					}
				}
    }

    closedir($dh);
    
    @rmdir($dir);
    
	}
	
	function getTempDir(){
    return '/tmp/novacontinuum/';
  }
  
  function getTagURL($projectid){
    $projectToRelease = $this->getProject($projectid);
    $url = $projectToRelease->url;
    $url = substr($projectToRelease->url,0, -strlen('pom.xml'));
  
    return $this->replaceTagURL($url);
  }
  
  function replaceTagURL($url){
    
    $pattern = '/trunk/i';
    $replacement = 'tags';
    return preg_replace($pattern, $replacement, $url);
  }
  
  function getReleaseVersion($version){
    return substr($version,0, -strlen('-SNAPSHOT'));
  }
  function getNewVersion($version){
    $releaseVersion = $this->getReleaseVersion($version);
    
    $versions = preg_split("/[\.]+/", $releaseVersion);
    $newVersion="";
    for ($i = 0; $i < count($versions)-1;$i++ ) {
    	$newVersion.=$versions[$i].".";
    }
    $newVersion.=($versions[count($versions)-1]+1);
    return $newVersion."-SNAPSHOT";
  }
  
  function releaseProject($projectid,$newVersion,$addtag,$group_id){
    global $Language;
	   
    $errorManager =& ErrorManager::getInstance();
    if(isset($projectid)){
    	$projectToRelease = $this->getProject($projectid);
    	
    	$tmpdir = $this->getTempDir().$projectToRelease->name;
    	if(is_file($tmpdir."/pom.xml")){
        $projectRelease = new ProjectRelease($tmpdir);
        session_start();
        $username = $_SESSION['release_scm_username'];
	      $password = $_SESSION['release_scm_password'];
	   
        if($projectRelease->readData($username,$password)){
          $trunkUrl = $projectToRelease->url;
    	    $trunkUrl = substr($projectToRelease->url,0, -strlen('pom.xml'));
    	    
    	    $tagsUrl = $this->replaceTagURL($trunkUrl);
    	    $testUrl = preg_replace("/(http[s]?:\/\/)/i", "$1".$username.":".$password."@", $tagsUrl);
    	    
    	    if(fopen($testUrl, 'r') == false){
    	      $message = dgettext ("gforge-plugin-novacontinuum", "release_tag_prepare"); 
            $output = `svn mkdir --username $username --password $password --no-auth-cache -m $message $tagsUrl`;
            if(!preg_match("/vision \d+ propag/i", $output)){
                $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "release_can_t_create_dir"));
                return;
            }
          }
          
          if(fopen($testUrl, 'r') == false){
            $errorManager->addError( sprintf ( dgettext ("gforge-plugin-novacontinuum", "release_url_access_error") ,$tagsUrl));
          }else{
            $releaseVersion = $this->getReleaseVersion($projectRelease->version);
            $tagsUrl.=$releaseVersion;
            
            $projectRelease->setVersion($this->getReleaseVersion($projectRelease->version));
            $projectRelease->switchToTags();
            $message =  sprintf ( dgettext ("gforge-plugin-novacontinuum", "release_tag_update") ,$projectRelease->version); 
            $output = `svn commit -m $message --username $username --password $password --no-auth-cache $tmpdir`;
            
            if(!preg_match("/Committed revision/i", $output)){
              $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "release_can_t_commit"));
              return;
            }
            
            $message = sprintf (dgettext ("gforge-plugin-novacontinuum", "release_tag_creation") ,$projectRelease->version); 
      	    $output = `svn copy --username $username --password $password --no-auth-cache $trunkUrl $tagsUrl -m $message`;
      	    if(!preg_match("/Committed revision/i", $output)){
              $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "release_can_t_copy"));
              return;
            }
            
            $projectRelease->setVersion($newVersion);
            $projectRelease->switchToTrunk();
            $message = sprintf ( dgettext ("gforge-plugin-novacontinuum", "release_new_version_prepare") ,$projectRelease->version);
            $output = `svn commit -m $message --username $username --password $password --no-auth-cache $tmpdir`;
            if(!preg_match("/Committed revision/i", $output)){
              $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "release_can_t_commit_new_version"));
              return;
            }
            
            $output = `rm -rf $tmpdir`;
            
            if($addtag){
              $selectedInstance = $this->getInstanceForProjects($group_id);
    					if(isset($selectedInstance)){
    						$pingRet = $selectedInstance->instance->ping();
    						if($pingRet===true){
    							$this->addMavenTwoProject($projectToRelease->name." ".$releaseVersion,$tagsUrl."/pom.xml",$username,$password,$selectedInstance,$group_id);
    						}
    					}
            }
          }
        }
      }else{
        $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "project_not_prepared"));
      }
    	
    }
  }
  
	function prepareRelease($username, $password, $projectid) {
	   global $Language;
	   
	   session_start();
	   
	   $_SESSION['release_scm_username']=$username;
	   $_SESSION['release_scm_password']=$password;
	   
     $errorManager =& ErrorManager::getInstance();
    if(isset($projectid)){
    	$projectToRelease = $this->getProject($projectid);
    	
    	$url = $projectToRelease->url;
    	$url = substr($projectToRelease->url,0, -strlen('pom.xml'));
      
    	$tmpdir = $this->getTempDir().$projectToRelease->name;
    
    	$output = `svn checkout --username $username --password $password --no-auth-cache -N $url $tmpdir`;
    	if(preg_match("/Checked out revision/i", $output)){
        
        $projectRelease = new ProjectRelease($tmpdir);
        
        if($projectRelease->readData($username,$password)){
          return $projectRelease;
        }else{
          return null;
        }
      }else{
        $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "prepare_error_checkout"));
        $errorManager->addError($output);
        return null;
      }
    	
    }
  }
  
  
}
?>