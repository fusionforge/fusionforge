<?php

require_once(dirname(__FILE__) . '/../dto/CheckStyleCheckerDTO.php');
require_once(dirname(__FILE__) . '/../dto/ModuleDTO.php');
require_once(dirname(__FILE__) . '/../dto/ObjectiveDTO.php');

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

/**
 * Cette classe gère les requêtes sur les infos maven.
 */
class MavenInfoDAO {

	function &getInstance() {
		static $instance = null;
		if (null === $instance) {
			$instance = new MavenInfoDAO();
		}
		return $instance;
	}
	
	function getModules($groupId){
		$array_mods = array ();
		$query = "SELECT maven_group_id,maven_artefact_id ".
    			 "FROM plugin_report_maven_info ".
    			 "WHERE group_id ='" . $groupId . "' ".
    			 "GROUP BY maven_group_id,maven_artefact_id ".
				 "ORDER BY maven_group_id,maven_artefact_id";

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
				$mod = new ModuleDTO();
				$mod->setMavenGroupId(db_result ($result, $i, "maven_group_id"));
				$mod->setMavenArtefactId(db_result ($result, $i, "maven_artefact_id"));
				$array_mods [] =$mod;
			}
		}
		return $array_mods;
	}

	function getVersions($groupId){
		$array_versions = array ();
		$query = "SELECT maven_version ".
    			 "FROM plugin_report_maven_info ".
    			 "WHERE group_id ='" . $groupId . "' ".
    			 "GROUP BY maven_version ".
				 "ORDER BY maven_version DESC";

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
				$array_versions [] =db_result ($result, $i, "maven_version");
			}
		}
		return $array_versions;
	}

	function getVersionsForModule($groupId,$mavenGroupId,$mavenArtefactId){
		$array_versions = array ();
		$query = "SELECT maven_version ".
    			 "FROM plugin_report_maven_info ".
    			 "WHERE group_id ='" . $groupId ."' ".
    		     "AND maven_group_id = '" . pg_escape_string($mavenGroupId)."' ".
				 "AND maven_artefact_id = '" . pg_escape_string($mavenArtefactId)."' ".
    			 "GROUP BY maven_version ".
				 "ORDER BY maven_version DESC";

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
				$array_versions [] =db_result ($result, $i, "maven_version");
			}
		}
		return $array_versions;
	}
	
	function getMavenInfoId($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){

		$ret = null;
		$query = "SELECT maven_info_id ".
					"FROM plugin_report_maven_info ".
					"WHERE group_id = '" . pg_escape_string($groupId) . "' ".
					"AND maven_artefact_id = '" . pg_escape_string($mavenArtefactId) . "' ".
    	            "AND maven_group_id = '" . pg_escape_string($mavenGroupId) . "' ".
    	            "AND maven_version = '" . pg_escape_string($mavenVersion) . "'";
			
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if($numrows > 0){
				$ret = db_result ($result, 0, "maven_info_id");
			}
		}
		return $ret;
	}

	/**
	 * Ajoute un enregistrement aux maven.
	 *
	 * @param groupId l'identifiant du projet dans lequel sont les rapports à effacer.
	 * @param mavenArtefactId l'identifiant de l'artefact.
	 * @param mavenGroupId l'identifiant du groupe.
	 * @param mavenVersion l'identifiant de la version.
	 * @return vrai si l'insertion a réussi.
	 */
	function addMavenInfo($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
		$ok = -1;

		$query = "INSERT INTO plugin_report_maven_info (maven_artefact_id, maven_group_id, maven_version, group_id)  ".
	  	          "VALUES ('" . pg_escape_string($mavenArtefactId) . "', ".
	  	                  "'" . pg_escape_string($mavenGroupId)    . "', ".
	  	                  "'" . pg_escape_string($mavenVersion)  . "', ".
                          "'" . pg_escape_string($groupId)  . "')";
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_report_checkstyle", "maven_info_id");
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

	/**
	 * Supprime les infos maven qui font partie du projet
	 * ayant l'id passé et les paramètres maven donnés.
	 *
	 * @param groupId l'identifiant du projet dans lequel sont les rapports à effacer.
	 * @param mavenArtefactId l'identifiant de l'artefact.
	 * @param mavenGroupId l'identifiant du groupe.
	 * @param mavenVersion l'identifiant de la version.
	 * @return vrai si la suppression à réussie
	 */
	function deleteMavenInfoByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
		$ok = false;

		$query = "DELETE FROM plugin_report_maven_info  ".
    	          "WHERE group_id = '" . pg_escape_string($groupId) . "' ".
    	                "AND maven_artefact_id = '" . pg_escape_string($mavenArtefactId) . "' ".
    	                "AND maven_group_id = '" . pg_escape_string($mavenGroupId) . "' ".
    	                "AND maven_version = '" . pg_escape_string($mavenVersion) . "'";

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
}

?>