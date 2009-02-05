<?php

require_once (dirname(__FILE__).'/ProjectDeveloper.php');
require_once (dirname(__FILE__).'/ProjectDependency.php');
require_once (dirname(__FILE__).'/ProjectNotifier.php');
require_once (dirname(__FILE__).'/BuildDefinition.php');
require_once (dirname(__FILE__).'/ProjectGroupSummary.php');

class ProjectContinuum {
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.Project';
	var $developers;
	var $dependencies;
	var $notifiers;
	var $latestBuildId;
	var $version;
	var $id;
	var $state;
	var $description;
	var $name;
	var $scmUsername;
	var $scmPassword;
	var $workingDirectory;
	var $artifactId;
	var $groupId;
	var $url;
	var $scmUrl;
	var $scmTag;
	var $projectGroup;
	
	function _populate($array){
		if(isset($array)){
		
			
			$this->developers = array();
			foreach ($array['developers'] as $key=>$value) {
   			 $newDev = new ProjectDeveloper();
   			 $newDev->_populate($value);
				 $this->developers[] = $newDev;	
   		}
			$this->dependencies = array();
			foreach ($array['dependencies'] as $key=>$value) {
   			 $newDep = new ProjectDependency();
   			 $newDep->_populate($value);
				 $this->dependencies[] = $newDep;	
   		}
   		
   		$this->notifiers = array();
			foreach ($array['notifiers'] as $key=>$value) {
   			 $newNot = new ProjectNotifier();
   			 $newNot->_populate($value);
				 $this->notifiers[] = $newNot;	
   		}
   		
   		$this->buildDefinitions = array();
			foreach ($array['buildDefinitions'] as $key=>$value) {
   			 $newDef = new BuildDefinition();
   			 $newDef->_populate($value);
				 $this->buildDefinitions[] = $newDef;	
   		}
   		$this->projectGroup=new ProjectGroupSummary();
   		$this->projectGroup->_populate($array['projectGroup']);
   		$this->latestBuildId=$array['latestBuildId'];
			$this->version=$array['version'];
			$this->id=$array['id'];
			$this->state=$array['state'];
			$this->description=$array['description'];
			$this->name=$array['name'];
			$this->scmUsername=$array['scmUsername'];
			$this->scmPassword=$array['scmPassword'];
			$this->workingDirectory=$array['workingDirectory'];
			$this->groupId=$array['groupId'];
			$this->artifactId=$array['artifactId'];
			$this->url=$array['url'];
			$this->scmUrl=$array['scmUrl'];
			$this->scmTag=$array['scmTag'];
		}
	}
  
	function &getStateNew(){	return 1;	}
  function &getStateOk(){	return 2;	}
  function &getStateFailed(){	return 3;	}
  function &getStateError(){	return 4;	}
  function &getStateBuilding(){	return 6;	}
  function &getStateCheckingOut(){	return 7;	}
  function &getStateUpdating(){	return 8;	}
  function &getStateWarning(){	return 9;	}
  function &getStateCheckedOut(){	return 10;	}
		
	function getStateImage(){
		$retValue = NULL;
		switch ($this->state) {
  		case 1:
  		case 2:
  			$retValue = 'icon_success_sml.gif';
  			break;
  		case 3:
  			$retValue = 'icon_warning_sml.gif';
  			break;
  		case 4:
  			$retValue = 'icon_error_sml.gif';
  			break;
  		case 6:
  			$retValue = 'building.gif';
  			break;
  		case 7:
  		case 8:
  			$retValue = 'checkingout.gif';
  			break;
  		case 9:
  			$retValue = 'icon_warning_sml.gif';
  			break;
  		case 10:
  			$retValue = 'icon_success_sml.gif';
  			break;
  	}
  	return $retValue;
	}
	
	function _getRpcValue(){
		$newDev = array();
		foreach ($this->developers as $key=>$value) {
  		$newDev[]=$value->_getRpcValue();
  	}
  	$newDep = array();
		foreach ($this->dependencies as $key=>$value) {
  		$newDep[]=$value->_getRpcValue();
  	}
  	$newNot = array();
		foreach ($this->notifiers as $key=>$value) {
  		$newNot[]=$value->_getRpcValue();
  	}
  	$newDef = array();
		foreach ($this->buildDefinitions as $key=>$value) {
  		$newDef[]=$value->_getRpcValue();
  	}
		$ret = array(
			'__class' 					=> new xmlrpcval($this->__class, "string"),
			'latestBuildId' 		=> new xmlrpcval($this->latestBuildId, "int"),
			'version' 					=> new xmlrpcval($this->version, "string"),
			'state' 						=> new xmlrpcval($this->state, "int"),
			'id' 								=> new xmlrpcval($this->id, "int"),
			'description' 			=> new xmlrpcval($this->description, "string"),
			'name' 							=> new xmlrpcval($this->name, "string"),
			'scmUsername' 			=> new xmlrpcval($this->scmUsername, "string"),
			'scmPassword' 			=> new xmlrpcval($this->scmPassword, "string"),
			'workingDirectory' 	=> new xmlrpcval($this->workingDirectory, "string"),
			'groupId' 					=> new xmlrpcval($this->groupId, "string"),
			'artifactId' 				=> new xmlrpcval($this->artifactId, "string"),
			'url' 							=> new xmlrpcval($this->url, "string"),
			'scmUrl' 						=> new xmlrpcval($this->scmUrl, "string"),
			'dependencies' 			=> new xmlrpcval($newDev, "array"),
			'notifiers' 				=> new xmlrpcval($newNot, "array"),
			'buildDefinitions'	=> new xmlrpcval($newDef, "array"),
			'developers' 				=> new xmlrpcval($newDep, "array")
		);
		
		return new xmlrpcval($ret,"struct");
	}
}

?>