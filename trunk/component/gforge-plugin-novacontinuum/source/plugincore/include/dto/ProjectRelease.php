<?php

require_once(dirname(__FILE__).'/../services/ErrorManager.php');

class ProjectRelease {
		var $dir;
		var $groupId;
		var $artifactId;
		var $version;
		var $modules= array();
		var $isModule;
		var $connection;
		var $developerConnection;
		var $url;
	function ProjectRelease($dir, $isModule=false)
	{
	    $this->isModule=$isModule;
			$this->dir=$dir;
	}
	
	function setVersion($newVersion ){
    $this->version=$newVersion;
    $this->updatefile();
    foreach ($this->modules as $module) {
      $module->setVersion($newVersion );
    }
  }
  
  function switchToTags(){
    $pattern = '/trunk\/(.*)\/?/i';
    $replacement = 'tags/$1/'.$this->version.'/';
    $this->connection = preg_replace($pattern, $replacement, $this->connection);
    $this->developerConnection = preg_replace($pattern, $replacement, $this->developerConnection);
    $this->url = preg_replace($pattern, $replacement, $this->url);
    $this->updatefile();
  }
  
  function switchToTrunk(){
    $pattern = '/tags\/([^\/]*)\/.*/i';
    $replacement = 'trunk/$1/';
    $this->connection = preg_replace($pattern, $replacement, $this->connection);
    $this->developerConnection = preg_replace($pattern, $replacement, $this->developerConnection);
    $this->url = preg_replace($pattern, $replacement, $this->url);
    $this->updatefile();
  }
  
  function updatefile( ){
     global $Language;
		$errorManager =& ErrorManager::getInstance();
		
		if (!$dom = domxml_open_file($this->dir."/pom.xml")) {
		    $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "prepare_read_pom_error"));
    }
    $root = $dom->document_element();
    $nodes = $root->child_nodes();

    foreach ($nodes as $node) {
      if($node->node_name() == "version"){
        foreach ($node->child_nodes() as $nodev) {
          $node->remove_child($nodev);
        }
        $node->append_child($dom->create_text_node($this->version));
      }else if($this->isModule==true && $node->node_name() == "parent"){
        foreach ($node->child_nodes() as $nodev) {
          if($nodev->node_name() == "version"){
            foreach ($nodev->child_nodes() as $nodevd) {
              $nodev->remove_child($nodevd);
            }
            $nodev->append_child($dom->create_text_node($this->version));
          }
        }
      }else if($node->node_name() == "scm"){
        foreach ($node->child_nodes() as $nodev) {
          if($nodev->node_name() == "connection"){
            foreach ($nodev->child_nodes() as $nodevd) {
              $nodev->remove_child($nodevd);
            }
            $nodev->append_child($dom->create_text_node($this->connection));
          }else if($nodev->node_name() == "developerConnection"){
            foreach ($nodev->child_nodes() as $nodevd) {
              $nodev->remove_child($nodevd);
            }
            $nodev->append_child($dom->create_text_node($this->developerConnection));
          }else if($nodev->node_name() == "url"){
            foreach ($nodev->child_nodes() as $nodevd) {
              $nodev->remove_child($nodevd);
            }
            $nodev->append_child($dom->create_text_node($this->url));
          }
        }
      }
    }
    
    $dom->dump_file($this->dir."/pom.xml", false, true);
  }
  
	function readData($username,$password){
	   global $Language;
		$errorManager =& ErrorManager::getInstance();
		
		if (!$dom = domxml_open_file($this->dir."/pom.xml")) {
		    $errorManager->addError(dgettext ("gforge-plugin-novacontinuum", "prepare_read_pom_error"));
        return false;
    }
    $root = $dom->document_element();
    $nodes = $root->child_nodes();

    $tmpModules = array();
    foreach ($nodes as $node) {
      if($node->node_name() == "groupId"){
        $this->groupId= "";
        foreach ($node->child_nodes() as $nodev) {
          $this->groupId .= $nodev->node_value();
        }
      }else if($node->node_name() == "artifactId"){
        $this->artifactId= "";
        foreach ($node->child_nodes() as $nodev) {
          $this->artifactId .= $nodev->node_value();
        }
      }else if($node->node_name() == "version"){
        $this->version= "";
        foreach ($node->child_nodes() as $nodev) {
          $this->version .= $nodev->node_value();
        }
      }else if($node->node_name() == "modules"){
        foreach ($node->child_nodes() as $nodev) {
          if($nodev->node_name() == "module"){
            $module = "";
            foreach ($nodev->child_nodes() as $nodevd) {
              $module.= $nodevd->node_value();
            }
            $tmpModules[]=$module;
          }
        }
      }else if($node->node_name() == "scm"){
        foreach ($node->child_nodes() as $nodev) {
          if($nodev->node_name() == "connection"){
            $this->connection = "";
            foreach ($nodev->child_nodes() as $nodevd) {
              $this->connection.= $nodevd->node_value();
            }
          }else if($nodev->node_name() == "developerConnection"){
            $this->developerConnection = "";
            foreach ($nodev->child_nodes() as $nodevd) {
              $this->developerConnection.= $nodevd->node_value();
            }
          }else if($nodev->node_name() == "url"){
            $this->url = "";
            foreach ($nodev->child_nodes() as $nodevd) {
              $this->url.= $nodevd->node_value();
            }
          }
        }
      }
      
      
    }
    
    foreach ($tmpModules as $module) {
      $moduleDir = $this->dir."/".$module;
      
      if(!file_exists($moduleDir."/pom.xml")){
        $output = `svn update -N --username $username --password $password --no-auth-cache $moduleDir`;
        if(!preg_match("/vision/i", $output)){
          $errorManager->addError( sprintf ( dgettext ("gforge-plugin-novacontinuum", "prepare_module_update_error") ,$module));
          return false;
        }
      }
      
      $newModule = new ProjectRelease($moduleDir, true);
      if($newModule->readData($username,$password)){
        $this->modules[]=$newModule;
      }else{
        
        $errorManager->addError( sprintf ( dgettext ("gforge-plugin-novacontinuum", "prepare_module_read_pom_error") ,$module));
        return false;
      }
    }
    
    return true;
	}
}
?>