<?php

require_once(dirname(__FILE__).'/../dao/MavenInfoDAO.php');

/**
 * Facade pour les infos maven.
 */
class MavenInfoFacade {

	/**
	 * Ajoute un enregistrement aux maven.
	 *
	 * @param groupId l'identifiant du projet dans lequel sont les rapports � effacer.
	 * @param mavenArtefactId l'identifiant de l'artefact.
	 * @param mavenGroupId l'identifiant du groupe.
	 * @param mavenVersion l'identifiant de la version.
	 * @return vrai si l'insertion a r�ussi.
	 */
	function addMavenInfo($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
        $dao =& MavenInfoDAO::getInstance();
        return $dao->addMavenInfo($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
    
    
    /**
	 * Supprime les infos maven qui font partie du projet
	 * ayant l'id pass� et les param�tres maven donn�s.
	 *
	 * @param groupId l'identifiant du projet dans lequel sont les rapports � effacer.
	 * @param mavenArtefactId l'identifiant de l'artefact.
	 * @param mavenGroupId l'identifiant du groupe.
	 * @param mavenVersion l'identifiant de la version.
	 * @return vrai si la suppression � r�ussie
	 */
	function deleteMavenInfoByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
        $dao =& MavenInfoDAO::getInstance();
        return $dao->deleteMavenInfoByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
}

?>