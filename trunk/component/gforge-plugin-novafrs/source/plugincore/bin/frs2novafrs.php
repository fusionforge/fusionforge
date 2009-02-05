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
* Conversion du gestionnaire fichiers de Gforge vers NovaForge
* Date de lancement : <?php echo date("d/m/y H:i"); ?>

******************************************************************

<?php



function usage( ){
?>
Utilisation : 
    ./frs2novafrs.sh noProjet

avec noProjet l'identifiant d'un projet à convertir.
Si noProjet est égal à 0, tous les projets seront convertis.

<?php
}


require_once ("squal_pre.php");
require_once ("plugins/novafrs/include/FileGroupFrs.class"(;
require_once ("plugins/novafrs/include/File.class"(;
require_once ("www/plugins/novafrs/include/fr_utils.php"(;


/*
$idProj = 7;
$videProj = array(
    "delete from plugin_frs_fr_data where group_id = $idProj;",
    "delete from plugin_frs_fr_groups where group_id = $idProj;",
    "delete from plugin_frs_fr_data where group_id = 6;",
    "delete from plugin_frs_fr_groups where group_id = 6;",
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
    $config = FileConfig::getInstance();
    if (is_dir ($config->sys_novafrs_path . "/" . $Group->getUnixName ()) == false){
        if (mkdir ($config->sys_novafrs_path . "/" . $Group->getUnixName ()) == false){
            echo "Can't create repository directory.";
            exit;
        }
    }
}




/**
 * Créer la branche correspondant à un package du gestionnaire de fichier de Gforge
 * Retourne l'objet FileGroupFrs créé.
 */
function creerBranche( &$group, &$package ){
    
    echo "Creation de la branche \"", $package->getName(), "\" ..."; 
    $fg = new FileGroupFrs( $group );
    
    $nom = $package->getName();
    $i=2;
    while( !$fg->checkUnique( $nom, 0, $group->getID() ) ){
        $nom = $package->getName() . "_$i";
        $i++;
    }
    
    $fg->create( $nom, 0 );
    if( $fg->isError() ){
        echo "\n** Erreur creation de group lié au package. \n\n", $fg->getErrorMessage();
        exit;
    }
    echo " ok\n";
    return $fg;
}


/**
 * Créer la branche correspondant à un package du gestionnaire de fichier de Gforge
 * Retourne l'objet FileGroupFrs créé.
 */
function creerBrancheRelease( &$group, $nomRelease, $branchePackageId ){
    echo "  Creation de la release \"", $nomRelease, "\" ..."; 
    $fg = new FileGroupFrs( $group );
    $fg->create( $nomRelease, $branchePackageId );
    if( $fg->isError() ){
        echo "\n** Erreur creation de group lié a la realease. \n\n", $fg->getErrorMessage();
        exit;
    }
    echo " ok\n";
    return $fg;
}







function getReleasesOfPackage( $package_id ){
    $sql="SELECT * FROM frs_release
    	WHERE package_id='". $package_id ."'";
    $res=db_query($sql);
    if (!$res ) {
    	echo "\n** Erreur SQL de recuperation des releases du packages : ", db_error() ,"\n";
    	exit;
    }
    
    $tabRes = array();
    while( $r = db_fetch_array($res) ){
        $tabRes[ ] = $r;
    }
    return $tabRes;
}


function convertitFileType( $type_id ){
    switch( $type_id ){
        case 1000 : return 1;   // deb
        case 2000 : return 2;   // rpm
        case 3000 : return 4;   // zip
        case 3110 : return 6;   // gz
        default: return 10;     // default : other
    }
}

/*
		

*/
function creerFichierRelease( $file, $release, $package, $group, $frsGroup ){
    $objRelease = new FRSRelease( $package, $release['release_id'], $release );
	
	$config = FileConfig::getInstance();

    echo "    Recopie fichier \"",  $file['filename'], "\" ...";
	
	$f = new File( $group );
	
	// Le titre doit avoir au moins 5 caractères
	$titre = $file['filename'];
	while( strlen( $titre ) < 5 ){
	    $titre .= '_';
	}
	
	$language = 7; // french


	//function create($filename,$filetype,$data,$fr_group,$title,$language_id,$description,
    //                    $status,$author,$writingDate,$frType,$reference,$version, $fr_observation, $filesize, $fr_chrono=null, $frid_replace=null ) {
	
	$res = $f->create( $file['filename'], convertitFileType( $file['type_id'] ), null, 	$frsGroup->getID(), $titre, $language, $objRelease->getNotes(),
	                        1, '', date( 'd/m/y', $file['release_time'] ), 1, '', '', $objRelease->getChanges(), $file['file_size'] );
	
	if( $res == false or $f->isError() ){
        echo "\n** Erreur creation fichier release en bdd. \n\n", $f->getErrorMessage();
        exit;
    }


    $filelocation = $GLOBALS['sys_upload_dir'].'/'.
			$group->getUnixName().'/'.
			$package->getFileName().'/'.
			$objRelease->getFileName().'/'.
			$file['filename'];
    	
    $dest = $config->sys_novafrs_path . $group->getUnixName() . '/' . $frsGroup->getPath() . '/' . novafrs_unixString($file['filename'] );	
    
    if( !copy( $filelocation, $dest ) ){
        echo "\n** Erreur copie du fichier.\n\n";
        exit;
    }
    
    echo "ok \n";
}


function getFilesOfRelease( $release_id ){
    $sql="SELECT * FROM frs_file
    	WHERE release_id='". $release_id ."'";
    $res=db_query($sql);
    if (!$res ) {
    	echo "\n** Erreur SQL de recuperation des fichiers de la release : ", db_error() ,"\n";
    	exit;
    }
    
    $tabRes = array();
    while( $r = db_fetch_array($res) ){
        $tabRes[ ] = $r;
    }
    return $tabRes;    
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

    $packages = get_frs_packages( $g );
    if( $packages ){
        echo "Le projet a ", count( $packages ), " packages.\n";
    }else{
        echo "Le projet n'a pas de package\n";
        continue;
    }
    

    foreach( $packages as $p ){
        $fg = creerBranche( $g, $p );


        $releases = getReleasesOfPackage( $p->getID() );

        if( $releases ){
            echo "Le packages a ", count( $releases ), " releases.\n";
        }else{
            echo "Le package n'a pas de release\n";
        }


        foreach( $releases as $r ){
            $fg_r = creerBrancheRelease( $g, $r['name'], $fg->getID() );
            
            $fichiers = getFilesOfRelease( $r['release_id'] );
            if( $fichiers ){
                echo "  La release a ", count( $fichiers ), " fichiers.\n";
            }else{
                echo "  La release n'a pas de fichier\n";
            }
            
            
            foreach( $fichiers as $f ){
                creerFichierRelease( $f, $r, $p, $g, $fg_r );
            }
            
            
        }
    }
  
}




?>
