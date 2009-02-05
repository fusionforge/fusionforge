<?php

require_once(dirname(__FILE__).'/../dto/HttpProxy.php');

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

class GForgeDAO {
	
	function GForgeDAO() {
     
  }

  function &getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new GForgeDAO();
      }
      return $instance;
  }

 	function getUserProject(){
	 $array_project = array();
	 $query = "SELECT groups.group_name,"
		. "groups.group_id,"
		. "groups.unix_group_name,"
		. "groups.status,"
		. "groups.type_id,"
		. "user_group.admin_flags "
		. "FROM groups,user_group "
		. "WHERE groups.group_id=user_group.group_id "
		. "AND user_group.user_id='". user_getid() ."' "
		. "AND groups.status='A' "
		. "ORDER BY group_name";
	 $result = db_query($query);
		if ($result !== false)
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0)
			{
				for ($i = 0; $i < $numrows; $i++)
				{
					$array_project [db_result ($result, $i, "group_id")] = db_result ($result, $i, "group_name");
				}
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $array_project;
	 
	}
	
	function hasUserProject($userId,$groupName){
	 $group_id = null;
	 $query = "SELECT groups.group_id "
		. "FROM groups,user_group "
		. "WHERE groups.group_id=user_group.group_id "
		. "AND user_group.user_id='". $userId ."' "
		. "AND groups.unix_group_name='". $groupName ."' "
		. "AND groups.status='A'";
	 $result = db_query($query);
		if ($result !== false)
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0)
			{
				$group_id = db_result ($result, 0, "group_id");
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $group_id;
	 
	}
	
	function getGForgeRoles ($groupId)
	{
		$array_roles = array ();
		$query = "SELECT role_id,role_name FROM role WHERE group_id=" . $groupId . " ORDER BY role_name";
		$result = db_query ($query);
		if ($result !== false)
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0)
			{
				for ($i = 0; $i < $numrows; $i++)
				{
					$array_roles [db_result ($result, $i, "role_id")] = db_result ($result, $i, "role_name");
				}
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $array_roles;
	}
	
	function getGForgeUserID ($userName, $userPwd)
	{
		$userId = null;
		$query = "SELECT user_id FROM users WHERE user_name='" . $userName . "' AND user_pw='".md5($userPwd)."'";
		$result = db_query ($query);
		if ($result !== false)
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0)
			{
				$userId = db_result ($result, 0, "user_id");
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $userId;
	}
	
	function isGForgeSuperUser ($userId)
	{
		$ret = false;
		$query = "SELECT * FROM user_group WHERE user_id='". $userId ."' AND group_id='1' AND admin_flags = 'A'";
		$result = db_query ($query);
		if ($result !== false)
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0)
			{
				$ret = true;
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $ret;
	}
	
	function isGForgeUserAdminForGroup ($userId, $groupId)
	{
		$ret = false;
		$query = "SELECT * FROM user_group WHERE user_id='". $userId ."' AND group_id='". $groupId ."' AND admin_flags = 'A'";
		$result = db_query ($query);
		if ($result !== false)
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0)
			{
				$ret = true;
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $ret;
	}
	
	function getGForgeUserRoleForGroup ($userId, $groupId)
	{
		$role_id = null;
		$query = "SELECT role_id,admin_flags FROM user_group 
			WHERE user_id='". $userId ."' 
			AND group_id='". $groupId ."'";
		$result = db_query ($query);
		if ($result !== false)
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0)
			{
				$role_id = db_result ($result, 0, "role_id");
			}
		}
		else
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		return $role_id;
	}
	
	
	function addRoleForGroup($groupId,$roleId,$rolename){
		$ok = false;
		
		$query = "INSERT INTO plugin_novacontinuum_group_roles (groupid, roleid, rolename) VALUES ('". $groupId ."','" .$roleId ."','".$rolename."')";
		
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

	function deleteRoles($groupId){
		$ok = false;
		
		$query = "DELETE FROM plugin_novacontinuum_group_roles WHERE groupid='".$groupId."'";
		
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

	function getRoles($groupId){
	
		$array_roles = array ();
		$query = "SELECT rolename, roleid FROM plugin_novacontinuum_group_roles WHERE groupid='".$groupId."'";
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
				$roleId=db_result ($result, $i, "roleid");
				$rolename=db_result ($result, $i, "rolename");
				if(!isset($array_roles[$rolename])){
					$array_roles[$rolename] = array();
				}
				$array_roles[$rolename][$roleId] = true ;
			}
		}
		return $array_roles;
	}
	
	function hasRoleForGroup($groupId,$roleId,$rolename){
	
		$retValue = false;
		$query = "SELECT rolename, roleid FROM plugin_novacontinuum_group_roles WHERE groupid='".$groupId."' AND rolename='".$rolename."' AND roleid='".$roleId."'";
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if($numrows>0){
				$retValue = true;
			}
		}
		return $retValue;
	}
	
}
?>