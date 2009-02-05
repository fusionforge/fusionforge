<?php

require_once(dirname(__FILE__) . '/../dto/CheckStyleCheckerDTO.php');
require_once(dirname(__FILE__) . '/../dto/ModuleDTO.php');
require_once(dirname(__FILE__) . '/../dto/ObjectiveDTO.php');

require_once(dirname(__FILE__) . '/MavenInfoDAO.php');

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/auth.php");

/**
 * Cette classe gère les requêtes sur les rapports Checkstyle.
 */
class CheckStyleCheckerDAO {

	function &getInstance() {
		static $instance = null;
		if (null === $instance) {
			$instance = new CheckStyleCheckerDAO();
		}
		return $instance;
	}

	function getLastVersion($groupId){

		$ret = null;
		$query = "SELECT max(maven_version) ".
				 "FROM plugin_report_checker_checkstyle AS checker, plugin_report_maven_info AS info ".
				 "WHERE info.group_id ='" . pg_escape_string($groupId) . "' ".
				 "AND info.maven_info_id = checker.maven_info_id";

		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if($numrows > 0){
				$ret = db_result ($result, 0, "max");
			}
		}
		return $ret;
	}

	function getLastVersionForModule($groupId,$mavenGroupId,$mavenArtefactId){

		$ret = null;
		$query = "SELECT max(maven_version) ".
					"FROM plugin_report_checker_checkstyle AS checker, plugin_report_maven_info AS info ".
					"WHERE info.group_id ='" . pg_escape_string($groupId) . "' ".
		 			"AND info.maven_group_id = '" . pg_escape_string($mavenGroupId)."' ".
				 	"AND info.maven_artefact_id = '" . pg_escape_string($mavenArtefactId)."' ".
				 	"AND info.maven_info_id = checker.maven_info_id";

		$result = db_query ($query);
		if ($result === false)
		{
			log_error ("Function db_query() failed with query '" . $query . "': " . db_error (), __FILE__, __FUNCTION__);
		}
		else
		{
			$numrows = db_numrows ($result);
			if($numrows > 0){
				$ret = db_result ($result, 0, "max");
			}
		}
		return $ret;
	}

	function getObjectivesForVersion($groupId,$mavenVersion){
		$array_obj = array ();
		$query = "SELECT DISTINCT objective ".
				 "FROM plugin_report_checker_checkstyle AS checker, plugin_report_maven_info AS info ".
				 "WHERE info.group_id ='" . pg_escape_string($groupId) . "' ".
				 "AND info.maven_version = '" . pg_escape_string($mavenVersion) . "'".
				 "AND info.maven_info_id = checker.maven_info_id";

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
				$obj = new ObjectiveDTO();
				$obj->setName(db_result ($result, $i, "objective"));
				$obj->setRules($this->getRulesForObjectiveForVersion($groupId,$mavenVersion,$obj->getName()));
				$array_obj [] =$obj;
			}
		}
		return $array_obj;
	}

	function getObjectivesForModule($groupId,$mavenGroupId,$mavenArtefactId,$mavenVersion){
		$array_obj = array ();
		$query = "SELECT DISTINCT objective ".
				 "FROM plugin_report_checker_checkstyle AS checker, plugin_report_maven_info AS info ".
				 "WHERE info.group_id ='" . pg_escape_string($groupId) . "' ".
				 "AND info.maven_group_id = '" . pg_escape_string($mavenGroupId)."' ".
				 "AND info.maven_artefact_id = '" . pg_escape_string($mavenArtefactId)."' ".
				 "AND info.maven_version = '" . pg_escape_string($mavenVersion) . "'".
				 "AND info.maven_info_id = checker.maven_info_id";

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
				$obj = new ObjectiveDTO();
				$obj->setName(db_result ($result, $i, "objective"));
				$obj->setRules($this->getRulesForObjective($groupId,$mavenGroupId,$mavenArtefactId,$mavenVersion,$obj->getName()));
				$array_obj [] =$obj;
			}
		}
		return $array_obj;
	}

	function getRulesForObjective($groupId,$mavenGroupId,$mavenArtefactId,$mavenVersion,$objective){
		$array_rule = array ();

		$query = "SELECT criteria_name, criteria_coef, criteria_context, criteria_method, rule_id, ".
					"(SELECT count(DISTINCT file_name) ".
						"FROM plugin_report_checkstyle ".
						"WHERE severity='info' ".
						"AND checker.rule_id = module_id ".
						"AND checker.maven_info_id = maven_info_id ".
						"GROUP BY module_id,maven_info_id) AS NB_INFO, ".
					"(SELECT count(DISTINCT file_name) ".
						"FROM plugin_report_checkstyle ".
						"WHERE severity='warning' ".
						"AND checker.rule_id = module_id ".
						"AND checker.maven_info_id = maven_info_id ".
						"GROUP BY module_id,maven_info_id) AS NB_WARNING, ".
					"(SELECT count(DISTINCT file_name) ".
						"FROM plugin_report_checkstyle ".
						"WHERE severity='error' ".
						"AND checker.rule_id = module_id ".
						"AND checker.maven_info_id = maven_info_id ".
						"GROUP BY module_id,maven_info_id) AS NB_ERROR ".
					"FROM plugin_report_checker_checkstyle AS checker, plugin_report_maven_info AS info ".
					"WHERE rule_id IS NOT NULL ".
		 					"AND rule_id <> '' ".
							"AND info.group_id ='" . pg_escape_string($groupId) . "' ".
	    	        		"AND info.maven_artefact_id = '" . pg_escape_string($mavenArtefactId) . "' ".
	    	                "AND info.maven_group_id = '" . pg_escape_string($mavenGroupId) . "' ".
	    	                "AND info.maven_version = '" . pg_escape_string($mavenVersion) . "' ".
							"AND objective = '" . pg_escape_string($objective) . "'".
				 			"AND info.maven_info_id = checker.maven_info_id";
			
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
				$rule = new RuleDTO();
				$rule->setName(db_result ($result, $i, "criteria_name"));
				$rule->setCoef(db_result ($result, $i, "criteria_coef"));
				$rule->setContext(db_result ($result, $i, "criteria_context"));
				$rule->setMethod(db_result ($result, $i, "criteria_method"));
				$rule->setNbError(db_result ($result, $i, "NB_ERROR"));
				$rule->setNbWarning(db_result ($result, $i, "NB_WARNING"));
				$rule->setNbInfo(db_result ($result, $i, "NB_INFO"));
				$array_rule [] =$rule;
			}
		}
		return $array_rule;
	}

	function getRulesForObjectiveForVersion($groupId,$mavenVersion,$objective){
		$array_rule = array ();
		$query = "SELECT criteria_name, criteria_coef, criteria_context, criteria_method, rule_id, ".
					"(SELECT count(DISTINCT file_name) ".
						"FROM plugin_report_checkstyle ".
						"WHERE severity='info' ".
						"AND checker.rule_id = module_id ".
						"AND checker.maven_info_id = maven_info_id ".
						"GROUP BY module_id,group_id,maven_version,maven_group_id,maven_artefact_id) AS NB_INFO, ".
					"(SELECT count(DISTINCT file_name) ".
						"FROM plugin_report_checkstyle ".
						"WHERE severity='warning' ".
						"AND checker.rule_id = module_id ".
						"AND checker.maven_info_id = maven_info_id ".
						"GROUP BY module_id,group_id,maven_version,maven_group_id,maven_artefact_id) AS NB_WARNING, ".
					"(SELECT count(DISTINCT file_name) ".
						"FROM plugin_report_checkstyle ".
						"WHERE severity='error' ".
						"AND checker.rule_id = module_id ".
						"AND checker.maven_info_id = maven_info_id ".
						"GROUP BY module_id,group_id,maven_version,maven_group_id,maven_artefact_id) AS NB_ERROR ".
					"FROM plugin_report_checker_checkstyle AS checker, plugin_report_maven_info AS info ".
					"WHERE rule_id IS NOT NULL ".
		 					"AND rule_id <> '' ".
	    	        	    "AND info.group_id ='" . pg_escape_string($groupId) . "' ".
				 			"AND info.maven_version = '" . pg_escape_string($mavenVersion) . "' ".
							"AND objective = '" . pg_escape_string($objective) . "'".
				 			"AND info.maven_info_id = checker.maven_info_id";

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
				$rule = new RuleDTO();
				$rule->setName(db_result ($result, $i, "criteria_name"));
				$rule->setCoef(db_result ($result, $i, "criteria_coef"));
				$rule->setContext(db_result ($result, $i, "criteria_context"));
				$rule->setMethod(db_result ($result, $i, "criteria_method"));
				$rule->setNbError(db_result ($result, $i, "NB_ERROR"));
				$rule->setNbWarning(db_result ($result, $i, "NB_WARNING"));
				$rule->setNbInfo(db_result ($result, $i, "NB_INFO"));
				$array_rule [] =$rule;
			}
		}
		return $array_rule;
	}

	/**
	 * Retourne les regles Checkstyle avec le groupId spécifié.
	 *
	 * @param groupid l'identifiant du project.
	 * @return les regles Checkstyle.
	 */
	function getCheckStyleChecker($groupId){
		$array_csc = array ();
		$query = "SELECT checker.* ".
    				 "FROM plugin_report_checker_checkstyle AS checker, plugin_report_maven_info AS info ".
    				 "WHERE info.group_id ='" . pg_escape_string($groupId) . "' ".
				 	 "AND info.maven_info_id = checker.maven_info_id";

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
				$csc = $this->__mapCheckStyleChecker($result, $i);
				$array_csc [] =$csc;
			}
		}
		return $array_csc;
	}

	/**
	 * Lit un enregistrement du résultat d'une requête sur les regles Checkstyle,
	 * construit l'object associé et le retourne.
	 *
	 * @param  fetch une ligne de résultat d'une requête.
	 * @return l'objet correspondant à cette ligne de résultat.
	 */
	function __mapCheckStyleChecker($result,$index){
		$objective = db_result ($result, $index, "objective");
		$criteria_name = db_result ($result, $index, "criteria_name");
		$criteria_coef = db_result ($result, $index, "criteria_coef");
		$criteria_context = db_result ($result, $index, "criteria_context");
		$criteria_method = db_result ($result, $index, "criteria_method");
		$rule_id = db_result ($result, $index, "rule_id");
		$maven_artefact_id = db_result ($result, $index, "maven_artefact_id");
		$maven_group_id = db_result ($result, $index, "maven_group_id");
		$maven_version = db_result ($result, $index, "maven_version");
		$group_id = db_result ($result, $index, "group_id");

		$csc =  new CheckStyleCheckerDTO();
		$csc->setObjective($objective);
		$csc->setCriteriaName($criteria_name);
		$csc->setCriteriaCoef($criteria_coef);
		$csc->setCriteriaContext($criteria_context);
		$csc->setCriteriaMethod($criteria_method);
		$csc->setRuleId($rule_id);
		$csc->setMavenArtefactId($maven_artefact_id);
		$csc->setMavenGroupId($maven_group_id);
		$csc->setMavenVersion($maven_version);
		$csc->setGroupId($group_id);
		return $csc;
	}

	/**
	 * Ajoute une regle Checkstyle.
	 *
	 * @param checkStyleCheckerDTO l'enregistrement à ajouter.
	 * @return vrai si l'insertion a réussi.
	 */
	function addCheckStyleChecker($checkStyleCheckerDTO){
		$ok = -1;

		$dao =& MavenInfoDAO::getInstance();
		
		$mavenInfoId = $dao->getMavenInfoId($checkStyleCheckerDTO->getGroupId(), 
								$checkStyleCheckerDTO->getMavenArtefactId(), 
								$checkStyleCheckerDTO->getMavenGroupId(), 
								$checkStyleCheckerDTO->getMavenVersion());
								
		$query = "INSERT INTO plugin_report_checker_checkstyle (objective, criteria_name, criteria_coef, criteria_context, criteria_method, rule_id, maven_info_id) ".
    	          "VALUES ('" . pg_escape_string($checkStyleCheckerDTO->getObjective()) . "', ".
    	                  "'" . pg_escape_string($checkStyleCheckerDTO->getCriteriaName())   . "', ".
    	                  "'" . pg_escape_string($checkStyleCheckerDTO->getCriteriaCoef()) . "', ".
    	                  "'" . pg_escape_string($checkStyleCheckerDTO->getCriteriaContext()) . "', ".
    	                  "'" . pg_escape_string($checkStyleCheckerDTO->getCriteriaMethod())  . "', ".
    	                  "'" . pg_escape_string($checkStyleCheckerDTO->getRuleId()) . "', ".
                          "'" . $mavenInfoId  . "')";

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

	/**
	 * Supprime les regles Checkstyle qui font partie du projet
	 * ayant l'id passé et les paramètres maven donnés.
	 *
	 * @param groupId l'identifiant du projet dans lequel sont les rapports à effacer.
	 * @param mavenArtefactId l'identifiant de l'artefact.
	 * @param mavenGroupId l'identifiant du groupe.
	 * @param mavenVersion l'identifiant de la version.
	 * @return vrai si la suppression à réussie
	 */
	function deleteCheckStyleCheckerByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
		$ok = false;

		$dao =& MavenInfoDAO::getInstance();
		
		$mavenInfoId = $dao->getMavenInfoId($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
								
		$query = "DELETE FROM plugin_report_checker_checkstyle ".
    	          "WHERE maven_info_id = '" . $mavenInfoId . "'";

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