<?php

class JavancssDTO {

	var $javancssId;
    var $reportDate;
    var $reportTime;
    var $mavenArtefactId;
    var $mavenGroupId;
    var $mavenVersion;
    var $groupId;
    
    function getJavancssId(){
        return $this->javancssId;
    }
    
    function setJavancssId($javancssId){
        $this->javancssId = $javancssId;
    }
    
    function getReportDate(){
        return $this->reportDate;
    }
    
    function setReportDate($reportDate){
        $this->reportDate = $reportDate;
    }
    
    function getReportTime(){
        return $this->reportTime;
    }
    
    function setReportTime($reportTime){
        $this->reportTime = $reportTime;
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