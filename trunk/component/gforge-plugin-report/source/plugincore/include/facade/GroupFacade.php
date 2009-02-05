<?php

require_once(dirname(__FILE__).'/../dao/GroupDAO.php');

/**
 * Facade pour les groupes.
 */
class GroupFacade {

    /**
     * Retourne l'identifiant du groupe (projet) du projet dont le nom unix est celui passé.
     * Elle teste également si l'utilisateur fait partie du groupe.
     * 
     * @param userName le login de l'utilisateur.
     * @param userPw le password de l'utilisateur.
     * @param unixGroupName le nom unix du projet.
     * @return l'identifiant du groupe (projet).
     */
    function getGroupId($userName, $userPw, $unixGroupName){
        $dao =& GroupDAO::getInstance();
        return $dao->getGroupId($userName, $userPw, $unixGroupName);
    }
        	
}

?>