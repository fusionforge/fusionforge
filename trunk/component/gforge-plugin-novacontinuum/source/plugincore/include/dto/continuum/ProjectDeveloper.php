<?php


class ProjectDeveloper {
	var $continuumId;
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.ProjectDeveloper';
	var $scmId;
	var $email;
	var $name;
	
	function _populate($array){
		if(isset($array)){
			$this->continuumId = $array['continuumId'];
			$this->scmId = $array['scmId'];
			$this->email = $array['email'];
			$this->name = $array['name'];
		}
	}
	
	function _getRpcValue(){
		$ret = array(
			'continuumId' 		=> new xmlrpcval($this->continuumId, "int"),
			'__class' 				=> new xmlrpcval($this->__class, "string"),
			'scmId' 					=> new xmlrpcval($this->scmId, "string"),
			'email' 					=> new xmlrpcval($this->email, "string"),
			'name' 						=> new xmlrpcval($this->name, "string")
		);
		
		return new xmlrpcval($ret,"struct");
	}
  
}


?>