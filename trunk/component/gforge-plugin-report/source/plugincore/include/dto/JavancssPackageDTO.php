<?php

class JavancssPackageDTO {
	
    var $javancssPackageId;
	var $name;
    var $classes;
    var $functions;
    var $ncss;
    var $javadocs;
    var $javadocLines;
    var $singleCommentLines;
    var $multiCommentLines;    
    var $javancssId;
    
    function getJavancssPackageId(){
        return $this->javancssPackageId;
    }
    
    function setJavancssPackageId($javancssPackageId){
        $this->javancssPackageId = $javancssPackageId;
    }
    
    function getName(){
        return $this->name;
    }
    
    function setName($name){
        $this->name = $name;
    }
    
    function getClasses(){
        return $this->classes;
    }
    
    function setClasses($classes){
        $this->classes = $classes;
    }
    
    function getFunctions(){
        return $this->functions;
    }
    
    function setFunctions($functions){
        $this->functions = $functions;
    }
    
    function getNcss(){
        return $this->ncss;
    }
    
    function setNcss($ncss){
        $this->ncss = $ncss;
    }
    
    function getJavadocs(){
        return $this->javadocs;
    }
    
    function setJavadocs($javadocs){
        $this->javadocs = $javadocs;
    }
    
    function getJavadocLines(){
        return $this->javadocLines;
    }
    
    function setJavadocLines($javadocLines){
        $this->javadocLines = $javadocLines;
    }
    
    function getSingleCommentLines(){
        return $this->singleCommentLines;
    }
    
    function setSingleCommentLines($singleCommentLines){
        $this->singleCommentLines = $singleCommentLines;
    }
    
    function getMultiCommentLines(){
        return $this->multiCommentLines;
    }
    
    function setMultiCommentLines($multiCommentLines){
        $this->multiCommentLines = $multiCommentLines;
    }
    
    function getJavancssId(){
        return $this->javancssId;
    }
    
    function setJavancssId($javancssId){
        $this->javancssId = $javancssId;
    }
	
}

?>