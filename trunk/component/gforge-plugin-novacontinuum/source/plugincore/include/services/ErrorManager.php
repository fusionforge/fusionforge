<?php


class ErrorManager {
	
	var $errors;
	function ErrorManager() {
     $this->errors = array();
  }

  function &getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new ErrorManager();
      }
      return $instance;
  }

  function addError($msg){
    $this->errors[]=$msg;
  }
	
	function addErrorKey($msg){
	  global $Language;
    $this->errors[]=dgettext ("gforge-plugin-novacontinuum",$msg);
  }
	
	function addErrorsKey($errs){
    foreach ( $errs as $value) {
    	$this->addErrorKey($value);
    }
  }
	
	function addErrors($errs){
	 $this->errors = array_merge($this->errors,$errs);
	}
	
	function getErrors(){
    return $this->errors;
  }
}
?>