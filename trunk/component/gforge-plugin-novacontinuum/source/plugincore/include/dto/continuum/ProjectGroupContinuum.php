<?php

require_once (dirname(__FILE__).'/ProjectNotifier.php');
require_once (dirname(__FILE__).'/BuildDefinition.php');

class ProjectGroupContinuum {
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.ProjectGroup';
	var $id;
	var $groupId;
	var $buildDefinitions;
	var $description;
	var $name;
	var $notifiers;
	
	function _populate($array){
		if(isset($array)){
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
  		$this->id=$array['id'];
			$this->groupId=$array['groupId'];
			$this->description=$array['description'];
			$this->name=$array['name'];
		}
	}
  
	function _getRpcValue(){
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
			'id' 								=> new xmlrpcval($this->id, "int"),
			'description' 			=> new xmlrpcval($this->description, "string"),
			'name' 							=> new xmlrpcval($this->name, "string"),
			'groupId' 					=> new xmlrpcval($this->groupId, "string"),
			'notifiers' 				=> new xmlrpcval($newNot, "array"),
			'buildDefinitions'	=> new xmlrpcval($newDef, "array")
		);
		
		return new xmlrpcval($ret,"struct");
	}
}

?>