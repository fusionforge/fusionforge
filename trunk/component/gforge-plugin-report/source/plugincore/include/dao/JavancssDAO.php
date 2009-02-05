<?php

require_once(dirname(__FILE__) . '/../dto/JavancssDTO.php');
require_once(dirname(__FILE__) . '/../dto/JavancssFunctionDTO.php');
require_once(dirname(__FILE__) . '/../dto/JavancssObjectDTO.php');
require_once(dirname(__FILE__) . '/../dto/JavancssPackageDTO.php');
require_once(dirname(__FILE__) . '/../dto/JavaNCSSResumeDTO.php');

require_once(dirname(__FILE__) . '/MavenInfoDAO.php');

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

define("REPORT_INFO"       , "info"    );
define("REPORT_BY_FUNCTION", "function");
define("REPORT_BY_OBJECT"  , "object"  );
define("REPORT_BY_PACKAGE" , "package" );

/**
 * Cette classe gère les requêtes sur les rapports Javancss.
 */
class JavancssDAO {

	function &getInstance() {
		static $instance = null;
		if (null === $instance) {
			$instance = new JavancssDAO();
		}
		return $instance;
	}

	function getResume($groupId,$mavenGroupId,$mavenArtefactId,$mavenVersion){

		$ret = null;
		$query = "SELECT info.group_id,info.maven_group_id,info.maven_artefact_id,info.maven_version, ".
					"(SELECT count(DISTINCT name) FROM plugin_report_javancss_function WHERE javancss_id=jncss.javancss_id GROUP BY javancss_id) AS NB_FUNCTION, ".
					"(SELECT count(DISTINCT name) FROM plugin_report_javancss_object   WHERE javancss_id=jncss.javancss_id GROUP BY javancss_id) AS NB_CLASS, ".
					"(SELECT count(DISTINCT name) FROM plugin_report_javancss_package  WHERE javancss_id=jncss.javancss_id GROUP BY javancss_id) AS NB_PACKAGE ".
				 "FROM plugin_report_javancss as jncss, plugin_report_maven_info AS info ".
				 "WHERE info.group_id ='" . pg_escape_string($groupId) . "' ".
				 "AND info.maven_group_id = '" . pg_escape_string($mavenGroupId)."' ".
				 "AND info.maven_artefact_id = '" . pg_escape_string($mavenArtefactId)."' ".
				 "AND info.maven_version = '" . pg_escape_string($mavenVersion) . "' ".
				 "AND info.maven_info_id = jncss.maven_info_id";
		
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if($numrows > 0){
				$ret = new JavaNCSSResumeDTO();
				$ret->setGroupId($groupId);
				$ret->setMavenGroupId($mavenGroupId);
				$ret->setMavenArtefactId($mavenArtefactId);
				$ret->setMavenVersion($mavenVersion);
				$ret->setNbFunction(db_result ($result, 0, "NB_FUNCTION"));
				$ret->setNbClass(db_result ($result, 0, "NB_CLASS"));
				$ret->setNbPackage(db_result ($result, 0, "NB_PACKAGE"));
			}
		}
		return $ret;
	}
	
	/**
	 * Ajoute les informations d'un nouveau rapport Javancss et retourne son identifiant dans la base.
	 *
	 * @param javancssDTO l'objet qui contient les informations à ajouter dans la base.
	 * @return l'identifiant du rapport.
	 */
	function addJavancssReport($javancssDTO, $mavenArtefactId, $mavenGroupId, $mavenVersion){
		$ok = -1;

		$dao =& MavenInfoDAO::getInstance();
		
		$mavenInfoId = $dao->getMavenInfoId($javancssDTO->getGroupId(), $mavenArtefactId, $mavenGroupId, $mavenVersion);
		
		$query = "INSERT INTO plugin_report_javancss (report_date, report_time, maven_info_id) ".
                      " VALUES ('" . pg_escape_string($javancssDTO->getReportDate()) . "',".
                              " '" . pg_escape_string($javancssDTO->getReportTime()) . "',".
                              " '" . pg_escape_string($mavenInfoId) . "')";
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_report_javancss", "javancss_id");
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
	 * Ajoute un rapport Javancss sur les packages dans la base.
	 *
	 * @param javancssPackageDTO l'objet qui contient les informations à insérer.
	 * @return vrai si l'insertion a réussi.
	 */
	function addJavancssPackageReport($javancssPackageDTO){
		$ok = -1;

		$query = "INSERT INTO plugin_report_javancss_package (name, classes, functions, ncss, javadocs, javadoc_lines, single_comment_lines, multi_comment_lines, javancss_id)".
                 " VALUES ('" . pg_escape_string($javancssPackageDTO->getName())               . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getClasses())            . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getFunctions())          . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getNcss())               . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getJavadocs())           . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getJavadocLines())       . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getSingleCommentLines()) . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getMultiCommentLines())  . "',".
                          " '" . pg_escape_string($javancssPackageDTO->getJavancssId())           . "')";
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_report_javancss_package", "javancss_package_id");
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
	 * Ajoute un rapport Javancss sur les objets dans la base.
	 *
	 * @param javancssObjectDTO l'objet qui contient les informations à insérer.
	 * @return vrai si l'insertion a réussi.
	 */
	function addJavancssObjectReport($javancssObjectDTO){
		$ok = -1;

		$query = "INSERT INTO plugin_report_javancss_object (name, ncss, functions, classes, javadocs, javancss_id) ".
                  " VALUES ('" . pg_escape_string($javancssObjectDTO->getName())      . "',".
                          " '" . pg_escape_string($javancssObjectDTO->getNcss())      . "',".
                          " '" . pg_escape_string($javancssObjectDTO->getFunctions()) . "',".
                          " '" . pg_escape_string($javancssObjectDTO->getClasses())   . "',".
                          " '" . pg_escape_string($javancssObjectDTO->getJavadocs())  . "',".
                          " '" . pg_escape_string($javancssObjectDTO->getJavancssId())  . "')";
		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_report_javancss_object", "javancss_object_id");
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
	 * Ajoute un rapport Javancss sur les fonctions dans la base.
	 *
	 * @param javancssFunctionDTO l'objet qui contient les informations à insérer.
	 * @return vrai si l'insertion a réussi.
	 */
	function addJavancssFunctionReport($javancssFunctionDTO){
		$ok = -1;

		$query = "INSERT INTO plugin_report_javancss_function (name, ncss, ccn, javadocs, javancss_id) ".
                  " VALUES ('" . pg_escape_string($javancssFunctionDTO->getName())     . "',".
                          " '" . pg_escape_string($javancssFunctionDTO->getNcss() )    . "',".
                          " '" . pg_escape_string($javancssFunctionDTO->getCcn())      . "',".
                          " '" . pg_escape_string($javancssFunctionDTO->getJavadocs()) . "',".
                          " '" . pg_escape_string($javancssFunctionDTO->getJavancssId()) . "')";

		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$id = db_insertid ($result, "plugin_report_javancss_function", "javancss_function_id");
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
	 * Supprime les enregistrements des rapports Javancss qui font partie du projet
	 * ayant l'id passé et les paramètres maven donnés.
	 *
	 * @param groupId l'identifiant du projet dans lequel sont les rapports à effacer.
	 * @param mavenArtefactId l'identifiant de l'artefact.
	 * @param mavenGroupId l'identifiant du groupe.
	 * @param mavenVersion l'identifiant de la version.
	 * @return vrai si la suppression à réussie
	 */
	function deleteJavancssReportByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
		$ok = false;

		$dao =& MavenInfoDAO::getInstance();
		
		$mavenInfoId = $dao->getMavenInfoId($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
		
		$query = "DELETE FROM plugin_report_javancss ".
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