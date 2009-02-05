<?php

require_once(dirname(__FILE__).'/../dao/JavancssDAO.php');

/**
 * Facade pour les rapports Javancss.
 */
class JavancssReportFacade {

    /**
     * Supprime les enregistrements des rapports Javancss 
     * ayant l'id passé et les paramètres maven donnés.
     * 
     * @param groupId l'identifiant du projet dans lequel sont les rapports à effacer.
     * @param mavenArtefactId l'identifiant de l'artefact.
     * @param mavenGroupId l'identifiant du groupe.
     * @param mavenVersion l'identifiant de la version.
     * @return vrai si la suppression à réussie
     */
    function deleteJavancssReportByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion){
        $dao =& JavancssDAO::getInstance();
        return $dao->deleteJavancssReportByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
    
    /**
     * Retourne les enregistrements des rapports Javancss
     * 
     * @param groupId l'identifiant du groupe.
     * @return les enregistrements des rapports Javancss.
     */
    function getJavancssReports($groupId){
        $dao =& JavancssDAO::getInstance();
        return $dao->getJavancssReports($groupId, REPORT_INFO);
    }
    
    /**
     * Retourne les enregistrements des rapports Javancss sur les fonctions.
     * 
     * @param javancssId l'identifiant du rapport Javancss.
     * @return les enregistrements des rapports Javancss sur les fonctions.
     */
    function getJavancssFunctionReports($javancssId){
        $dao =& JavancssDAO::getInstance();
        return $dao->getJavancssReports($javancssId, REPORT_BY_FUNCTION);
    }
    
    /**
     * Retourne les enregistrements des rapports Javancss sur les objets.
     * 
     * @param javancssId l'identifiant du rapport Javancss.
     * @return les enregistrements des rapports Javancss sur les objets.
     */
    function getJavancssObjectReports($javancssId){
        $dao =& JavancssDAO::getInstance();
        return $dao->getJavancssReports($javancssId, REPORT_BY_OBJECT);
    }
    
    /**
     * Retourne les enregistrements des rapports Javancss sur les packages.
     * 
     * @param javancssId l'identifiant du rapport Javancss.
     * @return les enregistrements des rapports Javancss sur les packages.
     */
    function getJavancssPackageReports($javancssId){
        $dao =& JavancssDAO::getInstance();
        return $dao->getJavancssReports($javancssId, REPORT_BY_PACKAGE);
    }
    
    /**
     * Ajoute les informations d'un nouveau rapport Javancss et retourne son identifiant dans la base.
     * 
     * @param javancssDTO l'objet qui contient les informations à ajouter dans la base.
     * @return l'identifiant du rapport.
     */
    function addJavancssReport($javancssDTO, $mavenArtefactId, $mavenGroupId, $mavenVersion){
        $dao =& JavancssDAO::getInstance();
        return $dao->addJavancssReport($javancssDTO, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    }
    
    /**
     * Ajoute un rapport Javancss sur les fonctions dans la base.
     * 
     * @param javancssFunctionDTO l'objet qui contient les informations à insérer.
     * @return vrai si l'insertion a réussi.
     */
    function addJavancssFunctionReport($javancssFunctionDTO){
        $dao =& JavancssDAO::getInstance();
        return $dao->addJavancssFunctionReport($javancssFunctionDTO);
    }
    
    /**
     * Ajoute un rapport Javancss sur les objets dans la base.
     * 
     * @param javancssObjectDTO l'objet qui contient les informations à insérer.
     * @return vrai si l'insertion a réussi.
     */
    function addJavancssObjectReport($javancssObjectDTO){
        $dao =& JavancssDAO::getInstance();
        return $dao->addJavancssObjectReport($javancssObjectDTO);
    }
    
    /**
     * Ajoute un rapport Javancss sur les packages dans la base.
     * 
     * @param javancssPackageDTO l'objet qui contient les informations à insérer.
     * @return vrai si l'insertion a réussi.
     */
    function addJavancssPackageReport($javancssPackageDTO){
        $dao =& JavancssDAO::getInstance();
        return $dao->addJavancssPackageReport($javancssPackageDTO);
    }
    
}

?>