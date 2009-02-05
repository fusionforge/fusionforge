<?php

require_once(dirname(__FILE__).'/xmlrpc/xmlrpc.inc');
require_once(dirname(__FILE__).'/../services/ErrorManager.php');

class ServiceLocator {


	function __callwsfuntction($wsFunction,$params, $timeout){
		$errorManager =& ErrorManager::getInstance();
		
		$client = new xmlrpc_client($this->url);
		$client->return_type = 'phpvals';
		$client->setCredentials($this->user,$this->password);
		
		//$client->setDebug(2);
		
		if(isset($this->httpProxy)){
			$client->setProxy($this->httpProxy->host,$this->httpProxy->port,$this->httpProxy->userName,$this->httpProxy->password);
		}
		$message = new xmlrpcmsg($wsFunction, $params);
		
		$resp = $client->send($message, $timeout);
		if ($resp->faultCode()){ 
		  
		  $errorManager->addErrorKey($resp->faultString());
		  
			return $resp->faultString(); 
		}else {
		  $value = $resp->value(); 
		  if(isset($value['errors'])){
		    $errorManager->addErrorsKey($value['errors']);  
      }	  
			return $value;
		}
	}
}

?>