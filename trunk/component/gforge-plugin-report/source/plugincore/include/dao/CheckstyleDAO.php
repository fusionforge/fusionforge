<?php

require_once(dirname(__FILE__) . '/../dto/CheckstyleDTO.php');
require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

require_once(dirname(__FILE__) . '/MavenInfoDAO.php');

/**
 * Cette classe gère les requêtes sur les rapports Checkstyle.
 */
class CheckstyleDAO {

	function &getInstance() {
		static $instance = null;
		if (null === $instance) {
			$instance = new CheckstyleDAO();
		}
		return $instance;
	}

	/**
	 * Retourne les lignes des rapports Checkstyle avec le groupId spécifié.
	 *
	 * @param groupid l'identifiant du project.
	 * @return le tableau des lignes des rapports Checkstyle.
	 */
	function getCheckstyleReports($groupId){
		$array_csc = array ();
		$query = "SELECT checkstyle.* ".
    				 "FROM plugin_report_checkstyle AS checkstyle, plugin_report_maven_info AS info ".
    				 "WHERE info.group_id ='" . pg_escape_string($groupId) . "' ".
				 	 "AND info.maven_info_id = checkstyle.maven_info_id";
		
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
				$csc = $this->__mapCheckStyle($result, $i);
				$array_csc [] =$csc;
			}
		}
		return $array_csc;
	}

	/**
	 * Lit un enregistrement du résultat d'une requête sur les rapports Checkstyle,
	 * construit l'object associé et le retourne.
	 *
	 * @param  fetch une ligne de résultat d'une requête.
	 * @return l'objet correspondant à cette ligne de résultat.
	 */
	function __mapCheckStyle($result,$index){
		$insertion_date = db_result ($result, $index, "insertion_date");
		$checkstyle_id = db_result ($result, $index, "checkstyle_id");
		$file_name = db_result ($result, $index, "file_name");
		$nb_line = db_result ($result, $index, "nb_line");
		$nb_column = db_result ($result, $index, "nb_column");
		$severity = db_result ($result, $index, "severity");
		$message = db_result ($result, $index, "message");
		$module_id = db_result ($result, $index, "module_id");
		$source = db_result ($result, $index, "source");
		$maven_artefact_id = db_result ($result, $index, "maven_artefact_id");
		$maven_group_id = db_result ($result, $index, "maven_group_id");
		$maven_version = db_result ($result, $index, "maven_version");
		$group_id = db_result ($result, $index, "group_id");

		$csc =  new CheckstyleDTO();
		$csc->setInsertionDate($insertion_date);
		$csc->setCheckstyleId($checkstyle_id);
		$csc->setFileName($file_name);
		$csc->setNbLine($nb_line);
		$csc->setNbColumn($nb_column);
		$csc->setSeverity($severity);
		$csc->setMessage($message);
		$csc->setModuleId($module_id);
		$csc->setSource($source);
		$csc->setMavenArtefactId($maven_artefact_id);
		$csc->setMavenGroupId($maven_group_id);
		$csc->setMavenVersion($maven_version);
		$csc->setGroupId($group_id);
		return $csc;
	}

	/**
	 * Ajoute un enregistrement aux rapports Checkstyle.
	 *
	 * @param checkstyleDTO l'enregistrement à ajouter.
	 * @return vrai si l'insertion a réussi.
	 */
	function addCheckstyleReport($checkstyleDTO){
		$ok = -1;

		$dao =& MavenInfoDAO::getInstance();
		
		$mavenInfoId = $dao->getMavenInfoId($checkstyleDTO->getGroupId(), $checkstyleDTO->getMavenArtefactId(), $checkstyleDTO->getMavenGroupId(), $checkstyleDTO->getMavenVersion());
			
		$query = "INSERT INTO plugin_report_checkstyle (file_name, nb_line, nb_column, severity, message, module_id, source, maven_info_id)  ".
  	          "VALUES ('" . pg_escape_string($checkstyleDTO->getFileName()) . "', ".
  	                  "'" . pg_escape_string($checkstyleDTO->getNbLine())   . "', ".
  	                  "'" . pg_escape_string($checkstyleDTO->getNbColumn()) . "', ".
  	                  "'" . pg_escape_string($checkstyleDTO->getSeverity()) . "', ".
  	                  "'" . pg_escape_string($checkstyleDTO->getMessage())  . "', ".
  	                  "'" . pg_escape_string($checkstyleDTO->getModuleId()) . "', ".
  	                  "'" . pg_escape_string($checkstyleDTO->getSource())   . "', ".
  	                  "'" . pg_escape_string($mavenInfoId) . "')";
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_report_checkstyle", "checkstyle_id");
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
	 * Supprime les enregistrements des rapports Checkstyle qui font partie du projet
	 * ayant l'id passé et les paramètres maven donnés.
	 *
	 * @param groupId l'identifiant du projet dans lequel sont les rapports à effacer.
	 * @param mavenArtefactId l'identifiant de l'artefact.
	 * @param mavenGroupId l'identifiant du groupe.
	 * @param mavenVersion l'identifiant de la version.
	 * @return vrai si la suppression à réussie
	 */
	function deleteCheckstyleReportByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
		$ok = false;

		$dao =& MavenInfoDAO::getInstance();
		
		$mavenInfoId = $dao->getMavenInfoId($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
								
		$query = "DELETE FROM plugin_report_checkstyle ".
    	          "WHERE maven_info_id = '" . pg_escape_string($mavenInfoId) . "'";

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