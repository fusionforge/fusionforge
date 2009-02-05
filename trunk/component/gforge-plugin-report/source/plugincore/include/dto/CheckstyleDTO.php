<?php

class CheckstyleDTO {
    
    var $checkstyleId;
    var $insertionDate;
    var $fileName;    
    var $nbLine;
    var $nbColumn;
    var $severity;
    var $message;
    var $moduleId;
    var $source;
    var $mavenArtefactId;
    var $mavenGroupId;
    var $mavenVersion;
    var $groupId;
    
    function getCheckstyleId(){
        return $this->checkstyleId;
    }
    
    function setCheckstyleId($checkstyleId){
        $this->checkstyleId = $checkstyleId;
    }
    
    function getInsertionDate(){
        return $this->insertionDate;
    }
    
    function setInsertionDate($insertionDate){
        $this->insertionDate = $insertionDate;
    }
    
    function getFileName(){
        return $this->fileName;
    }
    
    function setFileName($fileName){
        $this->fileName = $fileName;
    }
    
    function getNbLine(){
        return $this->nbLine;
    }
    
    function setNbLine($nbLine){
        $this->nbLine = $nbLine;
    }
    
    function getNbColumn(){
        return $this->nbColumn;
    }
    
    function setNbColumn($nbColumn){
        $this->nbColumn = $nbColumn;
    }
    
    function getSeverity(){
        return $this->severity;
    }
    
    function setSeverity($severity){
        $this->severity = $severity;
    }
    
    function getMessage(){
        return $this->message;
    }
    
    function setMessage($message){
        $this->message = $message;
    }
    
    function getModuleId(){
        return $this->moduleId;
    }
    
    function setModuleId($moduleId){
        $this->moduleId = $moduleId;
    }
    
    function getSource(){
        return $this->source;
    }    
    
    function setSource($source){
        $this->source = $source;
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