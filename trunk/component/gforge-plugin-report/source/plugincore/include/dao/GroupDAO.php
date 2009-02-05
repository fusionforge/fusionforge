<?php

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

/**
 * Cette classe gère les requêtes destinées à identifier l'utilisateur.
 */
class GroupDAO {

	function &getInstance() {
		static $instance = null;
		if (null === $instance) {
			$instance = new GroupDAO();
		}
		return $instance;
	}

	/**
	 * Retourne l'identifiant et le password de l'utilisateur.
	 *
	 * @param userName le nom de l'utilisateur dont on veut connaître les informations.
	 * @return l'identifiant et le password sous la forme d'un tableau associatif
	 *         la clé de l'identifiant est user_id et celle du password est user_pw.
	 */
	function getUserInfo($userName){
		$userInfo = array ();
		$query = "SELECT user_id,user_pw ".
				 "FROM users ".
				 "WHERE user_name = '" . pg_escape_string($userName) . "'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if($numrows > 0){
				$userInfo["user_id"] = db_result ($result, 0, "user_id");
				$userInfo["user_pw"] = db_result ($result, 0, "user_pw"); 
			}
		}
		return $userInfo;
	}

	/**
	 * Retourne l'identifiant du groupe (projet) avec le nom passé.
	 *
	 * @param unixGroupName le nom unix du projet.
	 * @return l'identifiant du groupe (projet).
	 */
	function getGroupIdFromGroups($unixGroupName){
		$groupId = null;
		$query = "SELECT group_id ".
				 "FROM groups ".
				 "WHERE unix_group_name = '" . pg_escape_string($unixGroupName) . "'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if($numrows > 0){
				$groupId = db_result ($result, 0, "group_id"); 
			}
		}
		return $groupId;
	}

	/**
	 * Teste si l'utilisateur avec l'identifiant donné appartient au groupe (projet)
	 * dont l'identifiant est passé.
	 *
	 * @param userId l'identifiant de l'utilisateur.
	 * @param groupId l'identifiant du groupe (projet).
	 * @return vrai si l'utilisateur appartient au groupe.
	 */
	function hasPerm($userId, $groupId){
		$retValue = false;
		$query = "SELECT COUNT(*) ".
				 "FROM user_group ".
			   	 "WHERE user_id = '" . pg_escape_string($userId) . "' ".
				 "AND group_id = '" . pg_escape_string($groupId) . "'";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if ($numrows > 0 ){
				$retValue = true;
			}
		}
		return $retValue;
	}

	/**
	 * Retourne l'identifiant du groupe (projet) du projet dont le nom unix est celui passé.
	 * Elle teste également si l'utilisateur fait partie du groupe.
	 *
	 * @param userName le login de l'utilisateur.
	 * @param userPw le password de l'utilisateur.
	 * @param unixGroupName le nom unix du projet.
	 * @return l'identifiant du groupe (projet).
	 */
	function getGroupId($userName, $userPw, $unixGroupName) {

		$userInfo = $this->getUserInfo($userName);
		$password = $userInfo["user_pw"];
		$userId = $userInfo["user_id"];

		if($password != $userPw){
			return false;
		}

		$groupId = $this->getGroupIdFromGroups($unixGroupName);

		if($this->isGForgeSuperUser($userId)){
			return $groupId;
		}

		if($this->hasPerm($userId, $groupId)){
			return $groupId;
		} else {
			return false;
		}
	}

	function isGForgeSuperUser ($userId)
	{
		$ret = false;
		$query = "SELECT * ".
				 "FROM user_group ".
				 "WHERE user_id='". $userId ."' ".
				 "AND group_id='1' ".
				 "AND admin_flags = 'A'";
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

}

?>