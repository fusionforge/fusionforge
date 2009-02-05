<?php

class RuleDTO {

	var $name;
	var $coef;
	var $context;
	var $method;
	var $nbError;
	var $nbWarning;
	var $nbInfo;
    
    function getName(){
        return $this->name;
    }
    
    function setName($name){
        $this->name = $name;
    }
    
    function getCoef(){
        return $this->coef;
    }
    
    function setCoef($coef){
        $this->coef = $coef;
    }
    
    function getContext(){
        return $this->context;
    }
    
    function setContext($context){
        $this->context = $context;
    }
    
    function getMethod(){
        return $this->method;
    }
    
    function setMethod($method){
        $this->method = $method;
    }
    
    function getNbError(){
        return $this->nbError;
    }
    
    function setNbError($nbError){
        $this->nbError = $nbError;
    }
    
    function getNbWarning(){
        return $this->nbWarning;
    }
    
    function setNbWarning($nbWarning){
        $this->nbWarning = $nbWarning;
    }
    
    function getNbInfo(){
        return $this->nbInfo;
    }
    
    function setNbInfo($nbInfo){
        $this->nbInfo = $nbInfo;
    }
    
    function getRate1($jncssResume){
    	if($this->getNbError() == null || $this->getNbError()==0){
    		return "-";
    	}else{
	    	$rate = ($this->getNbError() * 100)/$this->getTotal($jncssResume);
	    	$rate = round($rate,0); 
	    	return $rate."% - ".$this->getNbError();
    	}
    }
    
    function getRate2($jncssResume){
    	if($this->getNbWarning() == null || $this->getNbWarning()==0){
    		return "-";
    	}else{
	    	$rate = ($this->getNbWarning() * 100)/$this->getTotal($jncssResume);
	    	$rate = round($rate,0);
	    	return $rate."% - ".$this->getNbWarning();
    	}
    }
    
    function getRate3($jncssResume){
    	if($this->getNbInfo() == null || $this->getNbInfo()==0){
    		return "-";
    	}else{
	    	$rate = ($this->getNbInfo() * 100)/$this->getTotal($jncssResume);
	    	$rate = round($rate,0);
	    	return $rate."% - ".$this->getNbInfo();
    	}
    }
    
	function getRate4($jncssResume){
    	if($this->getNbGood($jncssResume) == null || $this->getNbGood($jncssResume)==0){
    		return "-";
    	}else{
	    	$rate = ($this->getNbGood($jncssResume) * 100)/$this->getTotal($jncssResume);
	    	$rate = round($rate,0);
	    	return $rate."% - ".$this->getNbGood($jncssResume);
    	}
    }
    
    function getTotal($jncssResume){
   		if($jncssResume == null){
   			return 0;
   		}
    	$total = 0;
    	switch ($this->getContext()) {
    		case 'class':{
    			$total = $jncssResume->getNbClass();
    			break;
    		}
    		case 'method':{
    			$total = $jncssResume->getNbFunction();
    			break;
    		}
    		case 'package':{
    			$total = $jncssResume->getNbPackage();
    			break;
    		}
    	}
    	return $total;
    }
    
	function getNbGood($jncssResume){
		$total = $this->getTotal($jncssResume);
		return $total - ($this->getNbError()+$this->getNbWarning()+$this->getNbInfo());
    }
    
    function getRate($jncssResume){
    	$rate = 0;
    	switch ($this->getMethod()) {
    		case 'avg':{
    			$total = $this->getTotal($jncssResume);
    			$nbGood = $this->getNbGood($jncssResume);
    			$rate = (1*$this->getNbError())+(3*$this->getNbWarning())+(3*$this->getNbInfo())+(4*$nbGood);
    			$rate = $rate / $total;
    			break;
    		}
    		case 'exclusive':{
    			if($this->getNbError() > 0){
    				$rate = 1;
    			}else if($this->getNbWarning() > 0){
    				$rate = 2;
    			}else if($this->getNbInfo() > 0){
    				$rate = 3;
    			}else if($this->getNbGood($jncssResume) > 0){
    				$rate = 4;
    			}else {
    				$rate = 0;
    			}
    			break;
    		}
    	}
    	
    	return $rate;
    }
}

?>