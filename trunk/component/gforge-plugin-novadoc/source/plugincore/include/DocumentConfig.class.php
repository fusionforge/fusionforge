<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
 * Class de configuration
 * Singleton!
 */
class DocumentConfig{

    var $sys_novadoc_path;
    var $mustUpdateWithSameName;

    
    var $g_plugin_config_path;  // Chemin vers le répertoire des projets ou sont 
                                // stockés les configurations par projets
    var $g_plugin_config_file;  // Chemin à partir du répetoire du projet qui conduit
                                // au fichier de configuration
    
    var $tailleStatut;          // taille de la colonne statut en pixel
    var $tailleStatutModif;     // taille de la colonne  "Statut modifié par" en pixel
    var $tailleStatutDate;      // taille de la colonne date du statut en pixel
    var $decalage;              // décalage horizontal en pixel de chaque sous répertoire
    var $imgDoc;                // icone d'un document
    var $imgRepO;               // icone dossier ouvert
    var $imgRepF;               // icone dossier fermé
    var $idHtmlRep;             // préfixe pour créer les id html des branches (pour manipulation par du JS)
    var $idImgRep;              // préfixe pour créer les id html des icone des répertoire
    var $displayEmptyGroup;     // afficher ou non les réperoire vide dans l'arbre
    var $statusText;            // tableau des libellés des statuts
    var $statusDefault;         // statut par defaut
    var $statusTable;           // tableau des libellés de la table statut de la fiche document
    var $level0inv;             // true si le niveau 0 de l'arborence doit être affiché par ordre alphabétique inverse
    var $defaultArbo;           // Arborescence créée par défault pour les nouveaux projets
    var $useState;              // Affiche ou non les états des documents
    var $chronoTable;           // La table chrono a afficher

    var $defaultAuthorizationRole;  // Les autorisations par defaut par rôle
    var $defaultAuthorization;      // Authorisation par défaut si le le rôle n'est pas trouvé dans defaultAuthorizationRole


    var $authColor1;            // Couleurs pour les autorisation dans la page "Gérer les autorisations "
    var $authColor2;
    var $authColor3;
    var $authColor4;

    /**
     * Constructeur
     */ 
    function DocumentConfig(){
        $this->chargeConfig( 'plugins/novadoc/config.php', true ); // config globale
        
        global $group_id;
        $g =& group_get_object ($group_id);        
        
        // reconstitution du nom complet du fichier de configutation spécifique au projet
        $fileConf = $this->g_plugin_config_path . $g->getUnixName() . $this->g_plugin_config_file;
        
        if( file_exists( $fileConf ) ){
                $this->chargeConfig( $fileConf ); // propre au projet - écrase la config globale
        }

    }


    /**
     * Retourne une l'instance de la classe
     */
	function &getInstance(){
		static $singleton=null;

		if (!$singleton)
			$singleton = new DocumentConfig();

		return $singleton;
	}

    /**
     * Chargement de la configuration
     */ 
    function chargeConfig($pathConf,$loadPluginPath=false){
        include $pathConf;

	if( isset( $sys_novadoc_path ) ) $this->sys_novadoc_path = $sys_novadoc_path;
        if( isset( $mustUpdateWithSameName ) ) $this->mustUpdateWithSameName = $mustUpdateWithSameName;
        
        if( isset( $tailleStatut ) ) $this->tailleStatut = $tailleStatut;
        if( isset( $tailleStatutModif ) ) $this->tailleStatutModif = $tailleStatutModif;
        if( isset( $tailleStatutDate ) ) $this->tailleStatutDate = $tailleStatutDate;
        if( isset( $decalage ) ) $this->decalage = $decalage;
        if( isset( $imgDoc ) ) $this->imgDoc = $imgDoc;
        if( isset( $imgRepO ) ) $this->imgRepO = $imgRepO;
        if( isset( $imgRepF ) ) $this->imgRepF = $imgRepF;
        
        if( isset( $idHtmlRep ) ) $this->idHtmlRep = $idHtmlRep;
        if( isset( $idImgRep  ) ) $this->idImgRep  = $idImgRep;
        
        if( isset( $displayEmptyGroup ) ) $this->displayEmptyGroup = $displayEmptyGroup;
        
        if( isset( $statusText ) ) $this->statusText = $statusText;
        if( isset( $statusDefault ) ) $this->statusDefault = $statusDefault;
        if( isset( $statusTable ) ) $this->statusTable = $statusTable;
        
        if( isset( $level0inv ) ) $this->level0inv = $level0inv;
        if( isset( $defaultArbo ) ) $this->defaultArbo = $defaultArbo;
        
        if( isset( $useState ) ) $this->useState = $useState;
        if( isset( $chronoTable ) ) $this->chronoTable = $chronoTable;
        
        if( isset( $defaultAuthorizationRole ) ) $this->defaultAuthorizationRole = $defaultAuthorizationRole;
        if( isset( $defaultAuthorization ) ) $this->defaultAuthorization = $defaultAuthorization;

        if( isset( $authColor1 ) ) $this->authColor1 = $authColor1;
        if( isset( $authColor2 ) ) $this->authColor2 = $authColor2;
        if( isset( $authColor3 ) ) $this->authColor3 = $authColor3;
        if( isset( $authColor4 ) ) $this->authColor4 = $authColor4;


        if( $loadPluginPath ){
            if( isset( $g_plugin_config_path ) ) $this->g_plugin_config_path = $g_plugin_config_path;
            if( isset( $g_plugin_config_file ) ) $this->g_plugin_config_file = $g_plugin_config_file;
        }
    }


}
?>
