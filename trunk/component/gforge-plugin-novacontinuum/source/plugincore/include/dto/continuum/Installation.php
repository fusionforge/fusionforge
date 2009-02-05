<?php


class Installation {
	var $installationId;
	var $__class = 'org.apache.maven.continuum.xmlrpc.system.Installation';
	var $varName;
	var $name;
	var $varValue;
	var $type;
	
	function _populate($array){
		if(isset($array)){
			$this->installationId = $array['installationId'];
			$this->varName = $array['varName'];
			$this->name = $array['name'];
			$this->varValue = $array['varValue'];
			$this->type = $array['type'];
		}
	}
	
	function _getRpcValue(){
		$ret = array(
			'installationId' 									=> new xmlrpcval($this->installationId, "int"),
			'__class' 						=> new xmlrpcval($this->__class, "string"),
			'varName' 				=> new xmlrpcval($this->varName, "string"),
			'name' 								=> new xmlrpcval($this->name, "string"),
			'varValue' 							=> new xmlrpcval($this->varValue, "string"),
			'type' 							=> new xmlrpcval($this->type, "string")
		);
		
		return new xmlrpcval($ret,"struct");
	}
}

?>