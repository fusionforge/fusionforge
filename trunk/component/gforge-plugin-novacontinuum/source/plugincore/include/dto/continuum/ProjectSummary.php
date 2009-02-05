<?php

class ProjectSummary {
	var $id;
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.ProjectSummary';
	
	function _populate($array){
		if(isset($array)){
			$this->id = $array['id'];
		}
	}
	
	function _getRpcValue(){
		$ret = array(
			'id' 									=> new xmlrpcval($this->id, "int"),
			'__class' 						=> new xmlrpcval($this->__class, "string")
		);
		
		return new xmlrpcval($ret,"struct");
	}
  
}

?>