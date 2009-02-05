<?php

class CheckStyleCheckerDTO {
    
    var $objective;
    var $criteriaName;
    var $criteriaCoef;    
    var $criteriaContext;
    var $criteriaMethod;
    var $ruleId;
    var $mavenArtefactId;
    var $mavenGroupId;
    var $mavenVersion;
    var $groupId;
    
    function getObjective(){
        return $this->objective;
    }
    
    function setObjective($objective){
        $this->objective = $objective;
    }
    
    function getCriteriaName(){
        return $this->criteriaName;
    }
    
    function setCriteriaName($criteriaName){
        $this->criteriaName = $criteriaName;
    }
        
    function getCriteriaCoef(){
        return $this->criteriaCoef;
    }
    
    function setCriteriaCoef($criteriaCoef){
        $this->criteriaCoef = $criteriaCoef;
    }
        
    function getCriteriaContext(){
        return $this->criteriaContext;
    }
    
    function setCriteriaContext($criteriaContext){
        $this->criteriaContext = $criteriaContext;
    }
    
    function getCriteriaMethod(){
        return $this->criteriaMethod;
    }
    
    function setCriteriaMethod($criteriaMethod){
        $this->criteriaMethod = $criteriaMethod;
    }
    
    function getRuleId(){
        return $this->ruleId;
    }
    
    function setRuleId($ruleId){
        $this->ruleId = $ruleId;
    }
    
    function getMavenArtefactId(){
        return $this->mavenArtefactId;
    }
    
    function setMavenArtefactId($mavenArtefactId){
    	$this->mavenArtefactId = $mavenArtefactId;
    }
    
    function getMavenGroupId(){
    	return $this->mavenGroupId;
    }
    
    function setMavenGroupId($mavenGroupId){
    	$this->mavenGroupId = $mavenGroupId;
    }
    
    function getMavenVersion(){
    	return $this->mavenVersion;
    }
    
    function setMavenVersion($mavenVersion){
    	$this->mavenVersion = $mavenVersion;
    }
    
    function getGroupId(){
        return $this->groupId;
    }
    
    function setGroupId($groupId){
        $this->groupId = $groupId;
    }
    
}

?>