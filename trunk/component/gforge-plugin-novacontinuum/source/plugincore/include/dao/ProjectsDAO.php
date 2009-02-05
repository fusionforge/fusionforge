<?php

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

require_once(dirname(__FILE__).'/../dto/Project.php');

class ProjectsDAO {
	
	function ProjectsDAO() {
     
  }

  function &getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new ProjectsDAO();
      }
      return $instance;
  }

	function insertProject($name,$url,$user,$password,$group_id){
		$ok = -1;
		
		$query = "INSERT INTO plugin_novacontinuum_projects (name, url, userName, pwd, groupid) VALUES ('".$name."','".$url ."','" .$user ."','" .$password ."','" .$group_id."')";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_novacontinuum_projects", "id");
			if ($id == 0)
			{
				log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
			}
			else
			{
				$ok = $id;
			}
		}
		return $ok;
	}
	
	function addContinuumProject($projectid,$continuumProjectId){
		$ok = -1;
		
		$query = "INSERT INTO plugin_novacontinuum_continuum_projects (projectId, continuumProjectId) VALUES ('".$projectid ."','" .$continuumProjectId ."')";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
		return $ok;
	}
	
	function removeContinuumProject($projectid,$continuumProjectId){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_continuum_projects WHERE projectId='".$projectid."' AND continuumProjectId='".$continuumProjectId."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
		return $ok;
	}
	
	function removeAllContinuumProject($projectid){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_continuum_projects WHERE projectId='".$projectid."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
		return $ok;
	}
	
	function deleteProject($projectid){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_projects WHERE id='".$projectid."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = $this->removeAllContinuumProject($projectid);
		}
		return $ok;
	}
	
	function updateProject($project){
		$ok = false;
		
		$query = "UPDATE plugin_novacontinuum_projects SET name='". $project->name ."' ,url='". $project->url ."' , userName='". $project->userName ."' , pwd='". $project->pwd ."' WHERE id='".$project->id."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = true;
		}
		return $ok;
	}
	
	function getProjects($group_id){
		$array_projects = array ();
		$query = "SELECT id,name,url, userName, pwd, groupId FROM plugin_novacontinuum_projects WHERE groupId=" . $group_id;
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			for ($i = 0; $i < $numrows; $i++)
			{
				$project = $this->__mapproject($result, $i);
				$array_projects [] =$project;
			}
		}
		return $array_projects;
	}
	
	function getProject($projectid){
		$project = null;
		$query = "SELECT id,name,url, userName, pwd, groupId FROM plugin_novacontinuum_projects WHERE id=" . $projectid;
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if ($numrows == 1)
			{
				$project = $this->__mapproject($result, 0);
			}
			else
			{
				log_error ("Function db_query() returned " . $numrows . " results with query '" . $query . "'", __FILE__, __FUNCTION__);
			}
		}
		return $project;
	
	}

	function getAllContinuumProjects($projectid){
		$array_projects = array ();
		$query = "SELECT continuumProjectId FROM plugin_novacontinuum_continuum_projects WHERE projectId=" . $projectid;
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			for ($i = 0; $i < $numrows; $i++)
			{
				$continuumProjectId = db_result ($result, $i, "continuumProjectId");
				$array_projects [] =$continuumProjectId;
			}
		}
		return $array_projects;
	}
	
	function __mapproject($result,$index){
		$id = db_result ($result, $index, "id");
		$name = db_result ($result, $index, "name");
		$url = db_result ($result, $index, "url");
		$userName = db_result ($result, $index, "userName");
		$pwd = db_result ($result, $index, "pwd");
		$groupId = db_result ($result, $index, "groupId");
		
		$project =  new Project();
		$project->id=$id;
		$project->name=$name;
		$project->url=$url;
		$project->userName=$userName;
		$project->pwd=$pwd;
		$project->groupId=$groupId;
		
		$project->continuumProjects = $this->getAllContinuumProjects($id);
		return $project;	
	}
}

?>