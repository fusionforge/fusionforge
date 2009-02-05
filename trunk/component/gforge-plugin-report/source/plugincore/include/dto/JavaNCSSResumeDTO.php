<?php

class JavaNCSSResumeDTO {
	var $groupId;
    var $mavenGroupId;
    var $mavenArtefactId;
    var $mavenVersion;
    var $nbFunction;
    var $nbClass;
    var $nbPackage;

	function getGroupId(){
        return $this->groupId;
    }
    
    function setGroupId($groupId){
        $this->groupId = $groupId;
    }
    
	function getNbFunction(){
        return $this->nbFunction;
    }
    
    function setNbFunction($nbFunction){
        $this->nbFunction = $nbFunction;
    }
    
	function getNbClass(){
        return $this->nbClass;
    }
    
    function setNbClass($nbClass){
        $this->nbClass = $nbClass;
    }
    
	function getNbPackage(){
        return $this->nbPackage;
    }
    
    function setNbPackage($nbPackage){
        $this->nbPackage = $nbPackage;
    }
    
    function getMavenGroupId(){
        return $this->mavenGroupId;
    }
    
    function setMavenGroupId($mavenGroupId){
        $this->mavenGroupId = $mavenGroupId;
    }
    
    function getMavenArtefactId(){
        return $this->mavenArtefactId;
    }
    
    function setMavenArtefactId($mavenArtefactId){
        $this->mavenArtefactId = $mavenArtefactId;
    }

    function getMavenVersion(){
        return $this->mavenVersion;
    }
    
    function setMavenVersion($mavenVersion){
        $this->mavenVersion = $mavenVersion;
    }
}

?>