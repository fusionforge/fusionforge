<?php

class JavancssObjectDTO{
	
    var $javancssObjectId;
	var $name;
	var $ncss;
	var $functions;
	var $classes;	
	var $javadocs;
	var $javancssId;
	
    function getJavancssObjectId(){
        return $this->javancssObjectId;
    }
    
    function setJavancssObjectId($javancssObjectId){
        $this->javancssObjectId = $javancssObjectId;
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
    
    function getFunctions(){
        return $this->functions;
    }
    
    function setFunctions($functions){
        $this->functions = $functions;
    }
    
    function getClasses(){
        return $this->classes;
    }
    
    function setClasses($classes){
        $this->classes = $classes;
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