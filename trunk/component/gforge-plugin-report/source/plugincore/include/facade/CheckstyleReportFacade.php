<?php

require_once(dirname(__FILE__).'/../dao/CheckstyleDAO.php');

/**
 * Facade pour les rapports Checkstyle.
 */
class CheckstyleReportFacade {

    /**
     * Retourne les lignes des rapports Checkstyle avec le groupId spécifié.
     * 
     * @param groupId l'identifiant du project.
     * @return le tableau des lignes des rapports Checkstyle. 
     */
    function getCheckstyleReports($groupId){
        $dao =& CheckstyleDAO::getInstance();
        return $dao->getCheckstyleReports($groupId);
    }
    
    /**
     * Ajoute un enregistrement aux rapports Checkstyle.
     * 
     * @param checkstyleDTO l'enregistrement à ajouter.
     * @return vrai si l'insertion a réussi.
     */
    function addCheckstyleReport($checkstyleDTO){
        $dao =& CheckstyleDAO::getInstance();
        return $dao->addCheckstyleReport($checkstyleDTO);
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
        $dao =& CheckstyleDAO::getInstance();
        return $dao->deleteCheckstyleReportByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
}

?>