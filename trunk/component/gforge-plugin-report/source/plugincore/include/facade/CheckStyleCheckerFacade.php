<?php

require_once(dirname(__FILE__).'/../dao/CheckStyleCheckerDAO.php');

/**
 * Facade pour les regles Checkstyle.
 */
class CheckStyleCheckerFacade {

    /**
     * Retourne les regles Checkstyle avec le groupId sp�cifi�.
     * 
     * @param groupId l'identifiant du project.
     * @return les regles Checkstyle. 
     */
    function getCheckStyleChecker($groupId){
        $dao =& CheckStyleCheckerDAO::getInstance();
        return $dao->getCheckStyleChecker($groupId);
    }
    
    /**
     * Ajoute une regle.
     * 
     * @param checkStyleCheckerDTO l'enregistrement � ajouter.
     * @return vrai si l'insertion a r�ussi.
     */
    function addCheckStyleChecker($checkStyleCheckerDTO){
        $dao =& CheckStyleCheckerDAO::getInstance();
        return $dao->addCheckStyleChecker($checkStyleCheckerDTO);
    }
    
    
    /**
     * Supprime les regles Checkstyle qui font partie du projet 
     * ayant l'id pass� et les param�tres maven donn�s.
     * 
     * @param groupId l'identifiant du projet dans lequel sont les rapports � effacer.
     * @param mavenArtefactId l'identifiant de l'artefact.
     * @param mavenGroupId l'identifiant du groupe.
     * @param mavenVersion l'identifiant de la version.
     * @return vrai si la suppression � r�ussie
     */
    function deleteCheckStyleCheckerByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
        $dao =& CheckStyleCheckerDAO::getInstance();
        return $dao->deleteCheckStyleCheckerByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
}

?>