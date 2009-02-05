<?php

require_once (dirname(__FILE__).'/Schedule.php');
require_once (dirname(__FILE__).'/Profile.php');

class BuildDefinition {
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.BuildDefinition';
	var $id;
	var $goals;
	var $schedule;
	var $arguments;
	var $defaultForProject;
	var $buildFile = 'pom.xml';
	var $buildFresh;
	var $alwaysBuild;
	var $profile;
	var $type = 'maven2';

	function _populate($array){
		if(isset($array)){
			$this->id=$array['id'];
			$this->goals=$array['goals'];
			$this->arguments=$array['arguments'];
			$this->defaultForProject=$array['defaultForProject'];
			
			$this->schedule = new Schedule();
   		$this->schedule->_populate($array['schedule']);	
   		
   		$this->buildFile=$array['buildFile'];
   		$this->buildFresh=$array['buildFresh'];
			$this->alwaysBuild=$array['alwaysBuild'];
			
			if(isset($array['profile'])){
				$this->profile = new Profile();
   			$this->profile->_populate($array['profile']);
			}	
		}
	}
	
	function _getRpcValue(){
		if($this->profile !=null){
			$profile = $this->profile;
		}else{
			$profile = new Profile();
			$profile->__class = 'null';
		} 
		
  	$ret = array(
			'__class' 					=> new xmlrpcval($this->__class, "string"),
			'id' 								=> new xmlrpcval($this->id, "int"),
			'goals' 						=> new xmlrpcval($this->goals, "string"),
			'type' 							=> new xmlrpcval($this->type, "string"),
			'arguments' 				=> new xmlrpcval($this->arguments, "string"),
			'defaultForProject' => new xmlrpcval($this->defaultForProject, "boolean"),
			'schedule' 					=> $this->schedule->_getRpcValue(),
			'buildFile' 				=> new xmlrpcval($this->buildFile, "string"),
			'buildFresh' 				=> new xmlrpcval($this->buildFresh, "boolean"),
			'alwaysBuild' 			=> new xmlrpcval($this->alwaysBuild, "boolean"),
			'profile' 					=> $profile->_getRpcValue()
		);
		
		return new xmlrpcval($ret,"struct");
	}
  
}

?>