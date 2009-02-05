<?php


class ProjectGroupSummary {
	var $id;
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.ProjectGroupSummary';
	var $groupId;
	var $description;
	var $name;
	
	function _populate($array){
		if(isset($array)){
			$this->id = $array['id'];
			$this->groupId = $array['groupId'];
			$this->description = $array['description'];
			$this->name = $array['name'];
		}
	}
	
	function _getRpcValue(){
		$ret = array(
			'id' 						=> new xmlrpcval($this->id, "int"),
			'__class' 			=> new xmlrpcval($this->__class, "string"),
			'description' 	=> new xmlrpcval($this->description, "string"),
			'name' 					=> new xmlrpcval($this->name, "string"),
			'groupId' 			=> new xmlrpcval($this->cronExpression, "groupId")
		);
		
		return new xmlrpcval($ret,"struct");
	}
  
}

?>