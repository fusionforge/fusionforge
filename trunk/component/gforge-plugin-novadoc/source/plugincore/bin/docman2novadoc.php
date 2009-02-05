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
******************************************************************
* Conversion du gestionnaire de documents de Gforge vers NovaForge
* Date de lancement : <?php echo date("d/m/y H:i"); ?>

******************************************************************

<?php



function usage( ){
?>
Utilisation : 
    ./docman2novadoc.sh noProjet

avec noProjet l'identifiant d'un projet à convertir.
Si noProjet est égal à 0, tous les projets seront convertis.

<?php
}


require_once ("squal_pre.php");
require_once ("plugins/novadoc/include/DocumentGroupDocs.class");
require_once ("plugins/novadoc/include/Document.class");


/*
$idProj = 7;
$videProj = array(
    "delete from plugin_docs_doc_data where group_id = $idProj;",
    "delete from plugin_docs_doc_groups where group_id = $idProj;",
    "delete from plugin_docs_doc_data where group_id = 6;",
    "delete from plugin_docs_doc_groups where group_id = 6;",

);


foreach( $videProj as $sql ){
    $res = db_query( $sql );
    if( !$res ){
        echo "Erreur requete videProj :", db_error(), "\n";
        exit;
    }
}
*/


/*
 * Simumlation du login de l'administrateur
 */
function simuleLoginAdmin(){
    global $G_SESSION;

    $user_id = 102; // id de l'admin dans gForge....
    $res = db_query("SELECT * FROM users WHERE user_id='$user_id'");
    $u = new User($user_id,$res);

    $G_SESSION = $u;
    $G_SESSION->setLoggedIn(true);
}



function creerRepositoryDirectory( $Group ){
    
    $config = DocumentConfig::getInstance();
    if (is_dir ($config->sys_novadoc_path . "/" . $Group->getUnixName ()) == false){
        if (mkdir ($config->sys_novadoc_path . "/" . $Group->getUnixName ()) == false){
            exit_error ("Can't create repository directory.");
            $retour = false;
        }
    }
}




function creerRepertoire( $projet, $group, $idPere ){
 
    $dg = new DocumentGroupDocs( $projet );
    
    if( $dg->isError() ){
        echo "\n** Erreur création objet DocumentGroupDocs : ", $dg->getErrorMessage(), "\n";
        exit;
    }
    
    // Si il y a deux répertoire du même nom, on le renomme en suffixant avec _n avec n un nombre    
    $nom = $group['groupname'];
    $i=2;
    while( !$dg->checkUnique( $nom, $idPere, $projet->getID() ) ){
        $nom = $group['groupname'] . "_$i";
        $i++;
    }
    
    
    // create($name,$parent_doc_group=0) {
    $dg->create( $nom, $idPere );
    if( $dg->isError() ){
        echo "\n** Erreur création du répertoire : ", $dg->getErrorMessage(), "\n";
        exit;
    }
    return $dg;
}



function creerDocument( &$projet, $doc, &$groupDestination, $space ){
    $config = DocumentConfig::getInstance();
    echo "$space  creation du document \"", $doc['title'], "\"\n";
    
    
    $d = new Document( $projet );


    // Verifie que le fichier est unique, sinon on le renomme en ajoutant _n à son nom
    $filename = $doc['filename'];
    $file_ext  = array_pop(explode('.', $filename));
    $file_only_name = basename($filename, '.'.$file_ext);
    
    $i=2;
    while( !$d->checkUnique( $filename, $groupDestination->getID() ) ){
        $filename = $file_only_name . "_$i." . $file_ext;
        $i++;
    }


//	function create($filename,$filetype,$data,$doc_group,$title,$language_id,$description,
//                        $status,$author,$writingDate,$docType,$reference,$version, $doc_observation, 
//                          $doc_chrono, $docid_replace=null ) {

    $d->create( $filename, $doc['filetype'], '', $groupDestination->getID(), $doc['title'], $doc['language_id'], $doc['description'],
                $doc['stateid'], '', date( 'd/m/y', $doc['createdate'] ), '', '', '', '', null );


    if( $d->isURL() ){
        return;
    }
    
    
    //base64_decode( $doc['data'] )
	
    $dest = $config->sys_novadoc_path . $projet->getUnixName() . '/' . $groupDestination->getPath() . '/' . novadoc_unixString( $filename );	


    $res=db_query("SELECT data FROM doc_data WHERE docid='".$doc['docid']."'");
    if( ! $res ){
        echo "\n** Erreur sql récupération data du fichier : " . db_error() . "\n";
        exit;
    }
	$data = base64_decode(db_result($res,0,'data'));

    $resFile = fopen( $dest, 'w' );
    if( !$resFile ){
        echo "\n** Erreur impossible de creer le fichier \"$dest\"\n";
        exit;
    }
    
    if( !fwrite( $resFile, $data, strlen($data) ) ){
        echo "\n** Erreur impossible d'ecrire le fichier \"$dest\"\n";
    }
    
}



function createRepRecur( &$projet, $idPereOrigine, &$tabGroups, &$tabDocs, $idPereDesti=0, $space='' ){
    if( !isset( $tabGroups[ $idPereOrigine ] ) ){
        return;
    }
    
    foreach( $tabGroups[ $idPereOrigine ] as $g ){
        $idGroup = $g['doc_group'];
        echo "$space creation du répertoire \"", $g['groupname'], "\"\n";
        $newGroup = creerRepertoire( $projet, $g, $idPereDesti  );
        if( isset( $tabDocs[ $idGroup ] ) ){
            foreach( $tabDocs[ $idGroup ] as $doc ){
                creerDocument( $projet, $doc, $newGroup, $space );
            }
        }
        createRepRecur( $projet, $idGroup, $tabGroups,  $tabDocs, $newGroup->getID(), $space.'  ' );
    }
}







if( !isset( $_SERVER['argv'][1] ) ){
    usage();
    exit;
}

simuleLoginAdmin();

$noProjet = $_SERVER['argv'][1];

$tabProjet = array();

if( $noProjet ){
    // un projet
    $g = group_get_object( $noProjet );
    if( $g == null ){
        echo "Numero de projet invalide\n";
        exit;
    }
    $tabProjet[] = $g;
}else{
    // tous les projets 
    $sql = " SELECT group_id FROM groups "; //WHERE group_id > 5 ";
    $res=db_query($sql);
    if (!$res ) {
    	echo "\n** Erreur SQL de recuperation des projets : ", db_error() ,"\n";
    	exit;
    }
    while( $r = db_fetch_array($res) ){
        $tabProjet[ ] = group_get_object( $r['group_id'] );
    }    
    
}





foreach( $tabProjet as $g ){
    echo "\n##\nTraitement du projet \"", $g->getPublicName(), "\"\n##\n";
    
    creerRepositoryDirectory( $g );
    
    // Récupération des répertoires du projet
    $sql = " SELECT * FROM doc_groups WHERE group_id = " . $g->getID() . " ORDER BY groupname DESC ";
    $res=db_query($sql);
    if (!$res ) {
    	echo "\n** Erreur SQL de recuperation des répertoire du projet : ", db_error() ,"\n";
    	exit;
    }
    $tabGroups = array();
    while( $r = db_fetch_array($res) ){
        if( !isset( $tabGroups[ $r['parent_doc_group'] ] ) ){
            $tabGroups[ $r['parent_doc_group'] ] = array();
        }
        $tabGroups[ $r['parent_doc_group'] ][] = $r;
    }
    
    
    // Récupération des documents du projet
    $sql = " SELECT docid, stateid, title, updatedate, createdate, created_by, doc_group, description, language_id, filename, filetype, group_id, filesize FROM doc_data WHERE group_id = " . $g->getID();
    $res=db_query($sql);
    if (!$res ) {
    	echo "\n** Erreur SQL de recuperation des fichier du projet : ", db_error() ,"\n";
    	exit;
    }
    $tabDocs = array();
    while( $r = db_fetch_array($res) ){
        if( !isset( $tabDocs[ $r['doc_group'] ] ) ){
            $tabDocs[ $r['doc_group'] ] = array();
        }
        $tabDocs[ $r['doc_group'] ][] = $r;
    }

    
    createRepRecur( $g, 0, $tabGroups, $tabDocs );

}




?>
