<?php

class JavancssFunctionDTO {
	
    var $javancssFunctionId;
	var $name;	   
    var $ncss;
    var $ccn;
    var $javadocs;
    var $javancssId;
    
    function getJavancssFunctionId(){
        return $this->javancssFunctionId;
    }
    
    function setJavancssFunctionId($javancssFunctionId){
        $this->javancssFunctionId = $javancssFunctionId;
    }
    
    function getName(){
    	return $this->name;
    }
    
    function setName($name){
        $this->name = $name;
    }
    
    function getNcss(){
    	return $this->ncss;
    }
    
    function setNcss($ncss){
        $this->ncss = $ncss;
    }
    
    function getCcn(){
    	return $this->ccn;
    }
    
    function setCcn($ccn){
        $this->ccn = $ccn;
    }
    
    function getJavadocs(){
    	return $this->javadocs;
    }
    
    function setJavadocs($javadocs){
        $this->javadocs = $javadocs;
    }
    
    function getJavancssId(){
        return $this->javancssId;
    }
    
    function setJavancssId($javancssId){
        $this->javancssId = $javancssId;
    }
	
}

?>