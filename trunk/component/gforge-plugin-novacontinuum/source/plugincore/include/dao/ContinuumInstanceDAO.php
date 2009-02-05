<?php

require_once(dirname(__FILE__).'/../dto/ContinuumInstance.php');

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

require_once(dirname(__FILE__).'/HttpProxyDAO.php');

class ContinuumInstanceDAO {
	
	function ContinuumInstanceDAO() {
     
  }

  function &getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new ContinuumInstanceDAO();
      }
      return $instance;
  }


	function addContinuumInstance($instance){
		$ok = false;
		
		$query = "INSERT INTO plugin_novacontinuum_instances (name, url, userName, pwd, maxUse , isEnabled, proxyId,groupId) VALUES ('". $instance->name ."','" .$instance->url ."','" .$instance->user ."','" .$instance->password ."','" .$instance->maxUse ."','" .$instance->isEnabled ."','". (isset($instance->httpProxy)?$instance->httpProxy->id:'-1')."','".$instance->groupId."')";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_novacontinuum_instances", "id");
			if ($id == 0)
			{
				log_error ("Function db_insertid() failed after query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
			}
			else
			{
				$ok = true;
			}
		}
		return $ok;
	}
	
	function updateContinuumInstance($instance){
		$ok = false;
		
		$query = "UPDATE plugin_novacontinuum_instances SET name='". $instance->name ."' , url='". $instance->url ."' , userName='". $instance->user ."' , pwd='". $instance->password ."' , maxUse='". $instance->maxUse ."' , isEnabled='". $instance->isEnabled ."' , groupId='". $instance->groupId ."' , proxyId='". (isset($instance->httpProxy)?$instance->httpProxy->id:'-1')."' WHERE id='".$instance->id."'";
		
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
	
	function deleteContinuumInstance($instanceid){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_instances WHERE id='".$instanceid."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = $this->removeInstanceUsedByProject($instanceid);
		}
		return $ok;
	}
	
	function deletePrivateInstance($group_id){
		$ok = false;
		$instance = $this->getPrivateInstanceForProjects($group_id);
		$query = "DELETE FROM plugin_novacontinuum_instances WHERE groupId='".$group_id."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$ok = $this->removeInstanceUsedByProject($instance->id);
		}
		return $ok;
	}
	
	function getAllContinuumInstances(){
	
		$array_instances = array ();
		$query = "SELECT id,name,url, userName, pwd, maxUse, isEnabled, proxyId, groupId FROM plugin_novacontinuum_instances ORDER BY name";
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
				$instance = $this->__mapinstance($result, $i);
				$array_instances [] =$instance;
			}
		}
		return $array_instances;
	}
	
	function getInstanceForProjects($group_id){
		$instance = null;
		$query = "SELECT instanceid,continuumProjectGroupId FROM plugin_novacontinuum_instances_projects WHERE groupid='".$group_id."'";
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
				$id = db_result ($result, 0, "instanceid");
				
				$instance = new SelectedInstance();
				$instance->instance = $this->getContinuumInstance($id);
				$instance->continuumProjectGroupId = db_result ($result, 0, "continuumProjectGroupId");
			}
			else
			{
				log_error ("Function db_query() returned " . $numrows . " results with query '" . $query . "'", __FILE__, __FUNCTION__);
			}
		}
		return $instance;
	}
	
	function getProjectsForInstance($instanceId){
		$array_instances = array ();
		$query = "SELECT ip.groupid,g.group_name FROM plugin_novacontinuum_instances_projects ip,groups g WHERE ip.groupid=g.group_id AND ip.instanceid='".$instanceId."'";
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
				$id = db_result ($result, $i, "groupid");
				$name = db_result ($result, $i, "group_name");
				$array_instances [$id] = $name;
			}
		}
		return $array_instances;
	}
	
	function defineInstanceForProject($group_id,$instanceId,$continuumProjectGroupId){
		$ok = false;
		
		$query = "UPDATE plugin_novacontinuum_instances_projects SET instanceid='" . $instanceId ."', continuumProjectGroupId='".$continuumProjectGroupId."' WHERE groupid='". $group_id ."'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			if(db_affected_rows($result)==0){
		
				$query = "INSERT INTO plugin_novacontinuum_instances_projects (instanceid, groupid, continuumProjectGroupId) VALUES ('". $instanceId ."','" .$group_id ."','".$continuumProjectGroupId."')";
		
				$result = db_query ($query);
				if ($result === false)
				{
					log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
				}
				else
				{
						$ok = true;
				}
			}else{
				$ok = true;
			}
			
		}
		return $ok;
	}
	
	function removeInstanceUsedByProject($instanceid){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_instances_projects WHERE instanceid='".$instanceid."'";
		
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
	
	function removeInstanceForProject($group_id){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_instances_projects WHERE groupid='".$group_id."'";
		
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
	
	function getAllUsableContinuumInstances(){
	
		$array_instances = array ();
		$query = "SELECT id,name,url, userName, pwd, maxUse, isEnabled, proxyId, groupId FROM plugin_novacontinuum_instances WHERE isEnabled=1 ORDER BY name";
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
				$instance = $this->__mapinstance($result, $i);
				$array_instances [] =$instance;
			}
		}
		return $array_instances;
	}
	
	function getPrivateInstanceForProjects($groupId){
		$instance = null;
		$query = "SELECT id,name,url, userName, pwd,maxUse, isEnabled, proxyId, groupId FROM plugin_novacontinuum_instances WHERE groupId=" . $groupId;
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
				$instance = $this->__mapinstance($result, 0);
			}
			else
			{
				log_error ("Function db_query() returned " . $numrows . " results with query '" . $query . "'", __FILE__, __FUNCTION__);
			}
		}
		return $instance;
	}
	
	function getContinuumInstance($instanceId){
		$instance = null;
		$query = "SELECT id,name,url, userName, pwd,maxUse, isEnabled, proxyId, groupId FROM plugin_novacontinuum_instances WHERE id=" . $instanceId;
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
				$instance = $this->__mapinstance($result, 0);
			}
			else
			{
				log_error ("Function db_query() returned " . $numrows . " results with query '" . $query . "'", __FILE__, __FUNCTION__);
			}
		}
		return $instance;
	}
	
	function __mapinstance($result,$index){
		$id = db_result ($result, $index, "id");
		$name = db_result ($result, $index, "name");
		$url = db_result ($result, $index, "url");
		$userName = db_result ($result, $index, "userName");
		$pwd = db_result ($result, $index, "pwd");
		$maxUse = db_result ($result, $index, "maxUse");
		$isEnabled = db_result ($result, $index, "isEnabled");
		$httpProxyId = db_result ($result, $index, "proxyId");
		$groupId = db_result ($result, $index, "groupId");
		$instance =  new ContinuumInstance($name,$url,$userName,$pwd,$maxUse,$isEnabled);
		$instance->id=$id;
		
		$instance->groupId=$groupId;
		if($httpProxyId > 0){
			$httpProxyDAO =& HttpProxyDAO::getInstance();
			$instance->httpProxy=$httpProxyDAO->getHttpProxy($httpProxyId);
		}
		
		return $instance;	
	}
}
?>