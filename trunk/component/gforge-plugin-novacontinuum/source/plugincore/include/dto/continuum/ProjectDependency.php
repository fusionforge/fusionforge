<?php

class ProjectDependency {
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.ProjectDependency';
	var $groupId;
	var $artifactId;
	var $version;
	
	function _populate($array){
		if(isset($array)){
			$this->groupId = $array['groupId'];
			$this->artifactId = $array['artifactId'];
			$this->version = $array['version'];
		}
	}
	
	function _getRpcValue(){
		$ret = array(
			'__class' 				=> new xmlrpcval($this->__class, "string"),
			'groupId' 				=> new xmlrpcval($this->groupId, "string"),
			'artifactId' 			=> new xmlrpcval($this->artifactId, "string"),
			'version' 				=> new xmlrpcval($this->version, "string")
		);
		
		return new xmlrpcval($ret,"struct");
	}
  
}

?>