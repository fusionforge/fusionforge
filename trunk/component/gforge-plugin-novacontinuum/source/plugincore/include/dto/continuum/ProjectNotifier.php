<?php


class ProjectNotifier {
	var $__class = 'org.apache.maven.continuum.xmlrpc.project.ProjectNotifier';
	var $enabled;
	var $from;
	var $type;
	var $id;
	var $sendOnWarning;
	var $sendOnFailure;
	var $sendOnError;
	var $sendOnSuccess;
	var $address;
	var $committers;
	var $recipientType;
	
	function _populate($array){
		if(isset($array)){
			$this->enabled = $array['enabled'];
			$this->from = $array['from'];
			$this->type = $array['type'];
			$this->id = $array['id'];
			$this->sendOnWarning = $array['sendOnWarning'];
			$this->sendOnFailure = $array['sendOnFailure'];
			$this->sendOnError = $array['sendOnError'];
			$this->sendOnSuccess = $array['sendOnSuccess'];
			$configuration = $array['configuration'];
			$this->address = $configuration['address'];
			$this->committers = $configuration['committers'];
			$this->recipientType = $array['recipientType'];
		}
	}
                    
	function _getRpcValue(){
		$configuration = array(
			'address' 				=> new xmlrpcval($this->address, "string"),
			'committers' 			=> new xmlrpcval($this->committers, "string")
			);
		$ret = array(
			'__class' 				=> new xmlrpcval($this->__class, "string"),
			'enabled' 				=> new xmlrpcval($this->enabled, "boolean"),
			'from' 						=> new xmlrpcval($this->from, "int"),
			'type' 						=> new xmlrpcval($this->type, "string"),
			'id' 							=> new xmlrpcval($this->id, "int"),
			'sendOnWarning'		=> new xmlrpcval($this->sendOnWarning, "boolean"),
			'sendOnFailure'		=> new xmlrpcval($this->sendOnFailure, "boolean"),
			'sendOnError' 		=> new xmlrpcval($this->sendOnError, "boolean"),
			'sendOnSuccess' 	=> new xmlrpcval($this->sendOnSuccess, "boolean"),
			'configuration' 	=> new xmlrpcval($configuration, "struct"),
			'recipientType' 	=> new xmlrpcval($this->recipientType, "int")
		);
		
		return new xmlrpcval($ret,"struct");
	}
  
}

?>