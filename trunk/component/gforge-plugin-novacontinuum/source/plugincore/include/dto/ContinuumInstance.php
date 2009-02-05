<?php

require_once(dirname(__FILE__).'/../utils/ServiceLocator.php');

require_once (dirname(__FILE__).'/SelectedInstance.php');
require_once (dirname(__FILE__).'/continuum/ProjectContinuum.php');
require_once (dirname(__FILE__).'/continuum/ProjectNotifier.php');
require_once (dirname(__FILE__).'/continuum/ProjectDependency.php');
require_once (dirname(__FILE__).'/continuum/ProjectDeveloper.php');
require_once (dirname(__FILE__).'/continuum/ProjectSummary.php');
require_once (dirname(__FILE__).'/continuum/ProjectGroupSummary.php');
require_once (dirname(__FILE__).'/continuum/BuildDefinition.php');	
require_once (dirname(__FILE__).'/continuum/Schedule.php');
require_once (dirname(__FILE__).'/continuum/BuildResultSummary.php');
require_once (dirname(__FILE__).'/continuum/ProjectGroupContinuum.php');

class ContinuumInstance extends ServiceLocator {

	var $name;
	var $url;
	var $user;
	var $password;
	var $id = -1;
	var $maxUse = 0;
	var $isEnabled = true;
	var $httpProxy;
	var $groupId=-1;
	
	function ContinuumInstance($name, $url, $user, $password,$maxUse,$isEnabled)
	{
			$this->name=$name;
			$this->url=$url;
			$this->user=$user;
			$this->password=$password;
			$this->maxUse=$maxUse;
			$this->isEnabled=$isEnabled;
	}
	
	function ping(){
		
		$params = array();
			
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.ping",$params, 1);
	
	}
	
	function addMavenTwoProject($url,$user,$password,$projectGroupId){
		if(isset($user)){
			$url = str_replace('://','://'.$user.':'.$password.'@',$url);
		}
		$params = array(
								new xmlrpcval($url,"string"),
								new xmlrpcval($projectGroupId,"int"));
		$addingResult = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addMavenTwoProjectRPC",$params, 10);
		
		if(isset($addingResult['projects'])){
			$narray = array();
			foreach ($addingResult['projects'] as $key=>$project) {
	  		$sh = new ProjectSummary();
				$sh->_populate($project);
	  		$narray[] = $sh;	
	  	}
			return $narray;
		}else{
			return array();
		}
	}
	
	function removeProject($projectid){
		$params = array(new xmlrpcval($projectid, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.removeProject",$params, 10);
	}
		
	function getBuildDefinitionsForProjectGroup($projectGroupid){
		$params = array(
								new xmlrpcval($projectGroupid,"int"));
		$arraySH = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getBuildDefinitionsForProjectGroupRPC",$params, 10);
		
		$narray = array();
		if(is_array($arraySH)){
			foreach ($arraySH as $value) {
				$sh = new BuildDefinition();
				$sh->_populate($value);
	  		$narray[] = $sh;	
	  	}
	  }
		return $narray;
	}
			
	
	function updateBuildDefinitionForProjectGroup($projectGroupid, $buildDef){
		$params = array(
								new xmlrpcval($projectGroupid,"int"),
								$buildDef->_getRpcValue());
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.updateBuildDefinitionForProjectGroupRPC",$params, 10);
		
		if(is_array($retValue)){
			$sh = new BuildDefinition();
			$sh->_populate($retValue);
  	}else{
			$sh = null;
		}
		return $sh;
	}
	
	function addBuildDefinitionToProjectGroup($projectGroupid, $buildDef){
		$params = array(
								new xmlrpcval($projectGroupid,"int"),
								$buildDef->_getRpcValue());
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addBuildDefinitionToProjectGroupRPC",$params, 10);
		
		
		$sh = new BuildDefinition();
		$sh->_populate($retValue);
  	
		return $sh;
	}
	
	function removeBuildDefinitionFromProjectGroup($projectGroupid, $buildDefid){
		$params = array(
								new xmlrpcval($projectGroupid,"int"),
								new xmlrpcval($buildDefid,"int"));
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.removeBuildDefinitionFromProjectGroup",$params, 10);
		
		return $retValue;
	}
	
	function getBuildResultsForProject($projectid){
		$params = array(
								new xmlrpcval($projectid,"int"));
		$arraySH = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getBuildResultsForProjectRPC",$params, 10);
		
		$narray = array();
		foreach ($arraySH as $value) {
			$sh = new BuildResultSummary();
			$sh->_populate($value);
  		$narray[] = $sh;	
  	}
		return $narray;
	}

	function getBuildResult($projectid, $buildId){
		$params = array(
								new xmlrpcval($projectid,"int"),
								new xmlrpcval($buildId,"int"));
		$arraySH = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getBuildResultRPC",$params, 10);
		
		$sh = new BuildResultSummary();
		$sh->_populate($arraySH);
  	
		return $sh;
	}
	
	function getBuildOutput($projectId,$buildId){
		$params = array(
								new xmlrpcval($projectId,"int"),
								new xmlrpcval($buildId,"int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getBuildOutput",$params, 10);;
	}
	
	function getProjectWithAllDetails($projectid){
		$params = array(
								new xmlrpcval($projectid,"int"));
		$project = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getProjectWithAllDetailsRPC",$params, 10);
		
		$retProject = new ProjectContinuum();
		$retProject->_populate($project);
		
		return $retProject;
	}
	
	function addProjectToBuildQueue($projectid){
		$params = array(new xmlrpcval($projectid, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addProjectToBuildQueue",$params, 10);
	}
	
	function addProjectToBuildQueueWithBuildDef($projectid,$builddefid){
		$params = array(new xmlrpcval($projectid, "int"),new xmlrpcval($builddefid, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addProjectToBuildQueue",$params, 10);
	}
	
	function getProfiles(){
		$params = array();
		
		$arraySH = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getProfilesRPC",$params, 10);
		$narray = array();
		foreach ($arraySH as $value) {
			$sh = new Profile();
			$sh->_populate($value);
  		$narray[] = $sh;	
  	}
		return $narray;
	}
	
	function getProfile($profileId){
		$params = array(new xmlrpcval($profileId, "int"));
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getProfileRPC",$params, 10);
		
			$sh = new Profile();
			$sh->_populate($value);
  	return $sh;
	}
	
	function deleteProfile($profileId){
		$params = array(new xmlrpcval($profileId, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.deleteProfile",$params, 10);
	}
	
	function addProfile($profile){
		$params = array($profile->_getRpcValue());
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addProfileRPC",$params, 10);
		
			$sh = new Profile();
			$sh->_populate($value);
  	return $sh;
	}
	
	function updateProfile($profile){
		$params = array($profile->_getRpcValue());
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.updateProfileRPC",$params, 10);
		
			$sh = new Profile();
			$sh->_populate($value);
  	return $sh;
	}
	
	function getInstallations(){
		$params = array();
		
		$arraySH = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getInstallationsRPC",$params, 10);
		$narray = array();
		foreach ($arraySH as $value) {
			$sh = new Installation();
			$sh->_populate($value);
  		$narray[] = $sh;	
  	}
		return $narray;
	}
	
	function getInstallation($installationid){
		$params = array(new xmlrpcval($installationid, "int"));
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getInstallationRPC",$params, 10);
		
			$sh = new Installation();
			$sh->_populate($value);
  	return $sh;
	}
	
	function deleteInstallation($installationid){
		$params = array(new xmlrpcval($installationid, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.deleteInstallation",$params, 10);
	}
	
	function addInstallation($installation){
		$params = array($installation->_getRpcValue());
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addInstallationRPC",$params, 10);
		
			$sh = new Installation();
			$sh->_populate($value);
  	return $sh;
	}
	
	function updateInstallation($installation){
		$params = array($installation->_getRpcValue());
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.updateInstallationRPC",$params, 10);
		
			$sh = new Installation();
			$sh->_populate($value);
  	return $sh;
	}
	
	function getSchedules(){
		$params = array();
		
		$arraySH = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getSchedulesRPC",$params, 10);
		$narray = array();
		foreach ($arraySH as $value) {
			$sh = new Schedule();
			$sh->_populate($value);
  		$narray[] = $sh;	
  	}
		return $narray;
	}
	
	function getSchedule($scheduleId){
		$params = array(new xmlrpcval($scheduleId, "int"));
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getScheduleRPC",$params, 10);
		
			$sh = new Schedule();
			$sh->_populate($value);
  	return $sh;
	}
	
	function updateSchedule($schedule){
		$params = array($schedule->_getRpcValue());
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.updateScheduleRPC",$params, 10);
		
			$sh = new Schedule();
			$sh->_populate($value);
  	return $sh;
	}
	
	function addSchedule($schedule){
		$params = array($schedule->_getRpcValue());
		
		$value = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addScheduleRPC",$params, 10);
		
			$sh = new Schedule();
			$sh->_populate($value);
  	return $sh;
	}
	
	function removeSchedule($scheduleid){
		$params = array(new xmlrpcval($scheduleid, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.removeSchedule",$params, 10);
	}
	
	function addProjectGroup($groupName, $groupId, $description){
		$params = array(
								new xmlrpcval($groupName,"string"),
								new xmlrpcval($groupId,"string"),
								new xmlrpcval($description,"string"));
		
		$struct = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addProjectGroupRPC",$params, 10);
		
		$retValue = new ProjectGroupSummary();
		$retValue->_populate($struct);
		return $retValue;
	}
	
	function removeProjectGroup($projectGroupId){
	
		$params = array(new xmlrpcval($projectGroupId, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.removeProjectGroup",$params, 10);
	}

	function getProjectGroup($projectGroupId){
	
		$params = array(new xmlrpcval($projectGroupId, "int"));
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getProjectGroupRPC",$params, 10);
		$ret = new ProjectGroupContinuum();
		$ret->_populate($retValue);
		return $ret;
	}
	
	function getGroupNotifier($projectGroupId,$notifierid){
	
		$params = array(new xmlrpcval($projectGroupId, "int"),new xmlrpcval($notifierid, "int"));
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getGroupNotifierRPC",$params, 10);
		$ret = new ProjectNotifier();
		$ret->_populate($retValue);
		return $ret;
	}
	
	function getNotifier($projectId,$notifierid){
	
		$params = array(new xmlrpcval($projectId, "int"),new xmlrpcval($notifierid, "int"));
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.getNotifierRPC",$params, 10);
		$ret = new ProjectNotifier();
		$ret->_populate($retValue);
		return $ret;
	}
	
	function updateGroupNotifier($projectGroupId,$notifier){
	
		$params = array(new xmlrpcval($projectGroupId, "int"),$notifier->_getRpcValue());
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.updateGroupNotifierRPC",$params, 10);
		$ret = new ProjectNotifier();
		$ret->_populate($retValue);
		return $ret;
	}
	
	function updateNotifier($projectId,$notifier){
	
		$params = array(new xmlrpcval($projectId, "int"),$notifier->_getRpcValue());
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.updateNotifierRPC",$params, 10);
		$ret = new ProjectNotifier();
		$ret->_populate($retValue);
		return $ret;
	}
	
	function removeGroupNotifier($projectGroupId,$notifierid){
	
		$params = array(new xmlrpcval($projectGroupId, "int"),new xmlrpcval($notifierid, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.removeGroupNotifier",$params, 10);
	}
	
	function removeNotifier($projectId,$notifierid){
	
		$params = array(new xmlrpcval($projectId, "int"),new xmlrpcval($notifierid, "int"));
		
		return $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.removeNotifier",$params, 10);
	}
	
	function addGroupNotifier($projectGroupId,$notifier){
	
		$params = array(new xmlrpcval($projectGroupId, "int"),$notifier->_getRpcValue());
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addGroupNotifierRPC",$params, 10);
		$ret = new ProjectNotifier();
		$ret->_populate($retValue);
		return $ret;
	}
	
	function addNotifier($projectId,$notifier){
	
		$params = array(new xmlrpcval($projectId, "int"),$notifier->_getRpcValue());
		
		$retValue = $this->__callwsfuntction("org.apache.maven.continuum.xmlrpc.ContinuumService.addNotifierRPC",$params, 10);
		$ret = new ProjectNotifier();
		$ret->_populate($retValue);
		return $ret;
	}
}




?>