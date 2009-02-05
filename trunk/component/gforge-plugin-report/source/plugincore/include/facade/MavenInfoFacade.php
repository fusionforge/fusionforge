<?php

require_once(dirname(__FILE__).'/../dao/MavenInfoDAO.php');

/**
 * Facade pour les infos maven.
 */
class MavenInfoFacade {

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
        $dao =& MavenInfoDAO::getInstance();
        return $dao->addMavenInfo($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
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
        $dao =& MavenInfoDAO::getInstance();
        return $dao->deleteMavenInfoByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
}

?>