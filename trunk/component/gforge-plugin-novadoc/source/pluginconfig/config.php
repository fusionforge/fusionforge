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
?>
<?php
/** 
 * @version   $Id: conf.php,v 1.5 2006/11/22 10:17:24 pascal Exp $
 *
 * Attention : UTF8 !
 */


// Répertoire où seront stockés les documents
$sys_novadoc_path = "/var/lib/gforge/novadoc/";


// Chemin vers le répertoire des projets ou sont stockés les configurations par projets
$g_plugin_config_path  = '/var/lib/gforge/home/groups/';
// Chemin à partir du répetoire du projet qui conduit au fichier de configuration
$g_plugin_config_file  = '/conf/doc_conf.php';

/// Le chemin complet d'un fichier de configuration du projet est donc :
//       $g_plugin_config_path . UNPROJET . $g_plugin_config_file


// True si un document fichier mis à jour doit avoir le même nom que précédemment
// False : le nom du fichier peut changer entre deux update
$mustUpdateWithSameName = false;


// Liste des statuts des documents 
$statusText = array( 
   1 => "Création",
   2 => "Relecture",
   3 => "Vérifié",
   4 => "Approuvé",
   5 => "Applicable",
   6 => "Archivé",
);

// Statut par défaut 
$statusDefault = 1;

// Libellés du tableau statut de la fiche document
// Le positionné à null pour ne pas afficher de tableau

/*
$statusTable  = array(
    1 => "Rédaction",
    2 => "Vérification interne",
    3 => "Validation",
);   
*/
$statusTable = null;


$useState = false; // affiche ou non les états des documents


$displayEmptyGroup = true;  // afficher ou non les groupes (répertoires) vides
$level0inv = true; // true si le niveau 0 de l'arborence doit être affiché par ordre inverse de création


// Arborescence créée par défaut pour les nouveaux projets (null pour pas d'arborescence par défaut)
$defaultArbo =  array( 
                    "V0" => array(
                        "Architecture" => null,
                        "Spécifications" => array(
                            "Spécifications générales" => null,
                            "Spécifications détaillées" => null,
                        ),
                        "Documents divers" => array(
                            "Jeux de tests" => null,
                            "Cas d'utilisation" => null,
                        ),
                    ),

                );
                    

/*
 * Les champs que la table chrono fera apparaître : 
 *   chrono, title, author, description, createdate, updatedate, type, reference, version, status
 */
$chronoTable = array(
    "chrono"        =>  35,
    "title"         => 275,
    "author"        =>  75,
    "description"   => 175,
    "writingDate"    => 100,
    "type"          =>  75,
    "reference"     => 100,
    "version"       =>  75,
    "updatedate"    => 130,
    "status"        =>  75,
);


/*
 * Default authorization by role :
 *   1 none
 *   2 read
 *   3 write
 *   4 write + delete  
 */
$defaultAuthorizationRole = array(
    "Manager" =>            3,
    "Chef de projet" =>     4,
    "Architecte" =>         3,
    "Développeur" =>        3,
    "Testeur support" =>    3,
    "Client" =>             1,
);


/*
 * Default authorization if a role is not found in $defaultAuthorizationRole
 *   1 none
 *   2 read
 *   3 write
 *   4 write + delete  
 */
$defaultAuthorization = 2;


/*
 * Couleurs pour les autorisation dans la page "Gérer les autorisations "
 */
$authColor1 = '#d4dae9';
$authColor2 = '#ddecdd';
$authColor3 = '#fcfcf1';
$authColor4 = '#faeee9';



$tailleStatut       = 230;  // taille de la colonne statut en pixel
$tailleStatutModif  = 190;  // taille de la colonne  "Statut modifié par" en pixel
$tailleStatutDate   = 90;   // taille de la colonne date du statut en pixel
$decalage           = 40;   // décalage horizontal en pixel de chaque sous répertoire

$imgDoc  = '/plugins/novadoc/images/docman16b.png';  // icone d'un document
$imgRepO = '/plugins/novadoc/images/ofolder15.png';  // icone dossier ouvert
$imgRepF = '/plugins/novadoc/images/cfolder15.png';  // icone dossier fermé

$idHtmlRep = 'idBranch';     // préfixe pour créer les id html des branches (pour manipulation par du JS)
$idImgRep  = 'idRepIco';     // préfixe pour créer les id html des icone des répertoire



?>
