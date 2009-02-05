<?php

require_once(dirname(__FILE__).'/../dao/CheckStyleCheckerDAO.php');

/**
 * Facade pour les regles Checkstyle.
 */
class CheckStyleCheckerFacade {

    /**
     * Retourne les regles Checkstyle avec le groupId spécifié.
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
     * @param checkStyleCheckerDTO l'enregistrement à ajouter.
     * @return vrai si l'insertion a réussi.
     */
    function addCheckStyleChecker($checkStyleCheckerDTO){
        $dao =& CheckStyleCheckerDAO::getInstance();
        return $dao->addCheckStyleChecker($checkStyleCheckerDTO);
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
        $dao =& CheckStyleCheckerDAO::getInstance();
        return $dao->deleteCheckStyleCheckerByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
}

?>