<?php

require_once(dirname(__FILE__) . '/../dto/RuleDTO.php');

class ObjectiveDTO {

	var $name;
	var $rules = array();
    
    function getName(){
        return $this->name;
    }
    
    function setName($name){
        $this->name = $name;
    }
    
    function getRules(){
        return $this->rules;
    }
    
    function setRules($rules){
        $this->rules = $rules;
    }
    
    function addRule($rule){
    	$rules[] = $rule;
    }
    
    function getRate($jncssResume){
    	$rate = 0;
    	$nb = 0;
    	foreach ($this->rules as $rule) {
    		$rate = $rate + ($rule->getRate($jncssResume) * $rule->getCoef());
    		$nb=$nb + $rule->getCoef();
    	}
    	
    	if($nb == 0){
    		return 0;
    	}else{
    		return $rate / $nb;
    	}
    }
    
}

?>