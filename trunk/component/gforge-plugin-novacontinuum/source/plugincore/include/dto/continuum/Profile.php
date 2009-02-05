<?php

require_once (dirname(__FILE__).'/Installation.php');

class Profile {
	var $id;
	var $__class = 'org.apache.maven.continuum.xmlrpc.system.Profile';
	var $scmMode;
	var $name;
	var $description;
	var $active;
	var $builder;
	var $jdk;
	var $buildWithoutChanges;
	var $environmentVariables = array();
	
	function _populate($array){
		if(isset($array) && !empty($array)){
			$this->id = $array['id'];
			$this->scmMode = $array['scmMode'];
			$this->name = $array['name'];
			$this->description = $array['description'];
			$this->active = $array['active'];
			if(isset($array['builder'])&& $array['builder']!=''){
				$this->builder = new Installation();
   			$this->builder->_populate($array['builder']);
			}	
			if(isset($array['jdk'])&& $array['jdk']!=''){
				$this->jdk = new Installation();
   			$this->jdk->_populate($array['jdk']);
			}	
			$this->buildWithoutChanges = $array['buildWithoutChanges'];
			if(isset($array['environmentVariables'])){
				foreach ($array['environmentVariables'] as $key=>$value) {
	   			 $newEnv = new Installation();
	   			 $newEnv->_populate($value);
					 $this->environmentVariables[] = $newEnv;	
	   		}
   		}
		}
	}
	
	function _getRpcValue(){
		$newEnv = array();
		foreach ($this->environmentVariables as $key=>$value) {
  		$newEnv[]=$value->_getRpcValue();
  	}
  	
  	if($this->builder !=null){
			$builder = $this->builder;
		}else{
			$builder = new Installation();
			$builder->__class = 'null';
		} 
		
		if($this->jdk !=null){
			$jdk = $this->jdk;
		}else{
			$jdk = new Installation();
			$jdk->__class = 'null';
		} 
		
		$ret = array(
			'id' 										=> new xmlrpcval($this->id, "int"),
			'__class' 							=> new xmlrpcval($this->__class, "string"),
			'scmMode' 							=> new xmlrpcval($this->scmMode, "int"),
			'name' 									=> new xmlrpcval($this->name, "string"),
			'description' 					=> new xmlrpcval($this->description, "string"),
			'active' 								=> new xmlrpcval($this->active, "boolean"),
			'buildWithoutChanges' 	=> new xmlrpcval($this->buildWithoutChanges, "boolean"),
			'builder' 							=> $builder->_getRpcValue(),
			'jdk' 									=> $jdk->_getRpcValue(),
			'environmentVariables' 	=> new xmlrpcval($newEnv, "array")
		);
		
		return new xmlrpcval($ret,"struct");
	}
}

?>