<?php

require_once (dirname(__FILE__).'/BuildDefinition.php');

class BuildResultSummary {
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.BuildResultSummary';
	var $id;
	var $startTime;
	var $endTime;
	var $exitCode;
	
	var $success;
	var $buildDefinition;
	var $state;
	var $error;
	var $trigger;
	var $buildNumber;
	
	function _populate($array){
		if(isset($array)){
		
			$this->id=$array['id'];
			$this->startTime=$array['startTime'];
			$this->endTime=$array['endTime'];
			$this->exitCode=$array['exitCode'];
			$this->success=$array['success'];
			$this->state=$array['state'];
			$this->error=$array['error'];
			$this->trigger=$array['trigger'];
			$this->buildNumber=$array['buildNumber'];
			
			$this->buildDefinition = new BuildDefinition();
   		$this->buildDefinition->_populate($array['buildDefinition']);	
   		
			
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
		
  	$ret = array(
			'__class' 					=> new xmlrpcval($this->__class, "string"),
			'id' 								=> new xmlrpcval($this->id, "int"),
			'startTime' 								=> new xmlrpcval($this->startTime, "int"),
			'endTime' 								=> new xmlrpcval($this->endTime, "int"),
			'exitCode' 								=> new xmlrpcval($this->exitCode, "int"),
			'success' 				=> new xmlrpcval($this->success, "string"),
			'state' 								=> new xmlrpcval($this->state, "int"),
			'error' 				=> new xmlrpcval($this->error, "string"),
			'trigger' => new xmlrpcval($this->trigger, "int"),
			'buildNumber' => new xmlrpcval($this->buildNumber, "int"),
			'buildDefinition' 					=> $this->buildDefinition->_getRpcValue()
		);
		
		return new xmlrpcval($ret,"struct");
	}
  
}

?>