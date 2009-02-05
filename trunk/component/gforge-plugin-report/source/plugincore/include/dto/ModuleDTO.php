<?php

class ModuleDTO {
	
    var $mavenGroupId;
    var $mavenArtefactId;
    
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
}

?>