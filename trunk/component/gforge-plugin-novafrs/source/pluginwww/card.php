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

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novafrs/include/FileFactory.class.php");
require_once ("plugins/novafrs/include/FileGroupFactory.class.php");
require_once ("plugins/novafrs/include/FileConfig.class.php");
require_once ("plugins/novafrs/include/FileCardView.class.php");
require_once ("plugins/novafrs/include/utils.php");

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object ($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}
$df = new FileFactory($g);
if ($df->isError()) {
	exit_error(dgettext('general','error'),$df->getErrorMessage());
}

$dgf = new FileGroupFactory($g);
if ($dgf->isError()) {
	exit_error(dgettext('general','error'),$dgf->getErrorMessage());
}

$config = FileConfig::getInstance();

// the "selected language" variable will be used in the links to navigate the
// file groups tree

if( !isset($language_id) or !$language_id) {
	if (session_loggedin()) {
		$language_id = $LUSER->getLanguage();
	} else {
		$language_id = 1;
	}
	
	$selected_language = $language_id;
} else if ($language_id == "*") {
	$language_id = 0 ;
	$selected_language = "*";
} else {
	$selected_language = $language_id;
}

// check if the user is frman's admin
$perm =& $g->getPermission( session_get_user() );
if (!$perm || $perm->isError() || !$perm->isReleaseTechnician()) {
	$is_editor = false;
} else {
	$is_editor = true;
}


$auth = new FileGroupAuth( $group_id, $LUSER );

$df->setLanguageID($language_id);


function fetchPostStatusArray(&$config){
    $arr_status = array();
    foreach( $config->statusText as $k=>$s ){
        $date = isset( $_POST['statusDate'.$k] ) ? $_POST['statusDate'.$k] : '';
        $name = isset( $_POST['statusName'.$k] ) ? $_POST['statusName'.$k] : '';
        $desc = isset( $_POST['statusDesc'.$k] ) ? $_POST['statusDesc'.$k] : '';
        
        $arr_status[$k] = array();
        $arr_status[$k]['date'] = $date;
        $arr_status[$k]['name'] = $name;
        $arr_status[$k]['description'] = $desc;
    }
    return $arr_status;
}


// Modification d'un file
if( isset($submit) and isset($frid) and $frid ){ 
	$d= new File($g,$frid);
	if ($d->isError()) {
		exit_error(dgettext('general','error'),$d->getErrorMessage());
	}

	$moveFile = false;
	if( !$d->isURL() && ($d->getFrGroupID() != $fr_group ) ){
	    $moveFile = true;
	}
	
	
	if( !$auth->canWrite( $d->getFrGroupID() ) ){
	    exit_permission_denied(); 
	}
	
	if( $moveFile and !$auth->canWrite( $fr_group ) ){
	    exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','noWriteAuth'));
	}
	
	$data = ''; 
	$changeName = false;
	$thefilesize = null;
	if ($uploaded_data) {
		if (!is_uploaded_file($uploaded_data)) {
			exit_error(dgettext('general','error'),sprintf( dgettext ( 'gforge-plugin-novafrs' , 'error_invalid_file_attack' ) , $uploaded_data));
		}
		$thefilesize = filesize($uploaded_data);
		$filename=$uploaded_data_name;
		$changeName = ( addslashes($d->getFileName() ) != $filename );
		if ( $config->mustUpdateWithSameName and $changeName ){
			exit_error('Error',dgettext('gforge-plugin-novafrs','bad_filemane'));
		}
		$filetype=$uploaded_data_type;
	} elseif ($file_url) {
		//$data = '';
		$filename=$file_url;
		$filetype='URL';
	} elseif ($ftp_filename) {
		$filename=$upload_dir.'/'.$ftp_filename;
		//$data = addslashes(fread(fopen($filename, 'r'), filesize($filename)));
		$filetype=$uploaded_data_type;
	} else {
		$filename=addslashes($d->getFileName());
		$filetype=addslashes($d->getFileType());
	}
	
	
	
    if( !$status  ){	
        exit_error('Error',dgettext('general','error_on_update') . 'bad status id' );    
    }


    if( $changeName or $d->getFrGroupId()!=$fr_group) {
        if( !$d->checkUnique( $filename, $fr_group ) ){
            exit_error('Error',$d->getErrorMessage());        
        }
    }

    if( $uploaded_data ){
        $dg = new FileGroupFrs( $g, $d->getFrGroupID() );
        $actual_path_file = $config->sys_novafrs_path . $g->getUnixName() . '/' . $dg->getPath() . '/';

        $actual_location_file = $actual_path_file . novafrs_unixString( $d->getFileName() );

        // Archive previous version
        if (  $archive == 1 ) {
            // Sav the old version
            $new_location_file = $actual_path_file . $d->getUnixFileNameHistory();
            
            $newFr = $d->newVersion();
            if( $newFr === false ){
                exit_error( 'Error', "Can't create new version : " . $d->getErrorMessage() );
            }
            
            $d = $newFr;
            
            if( !rename( $actual_location_file, $new_location_file ) ){
                exit_error( 'File copy failed : ' . $actual_location_file . '==>'. $new_location_file   );
            }
    	}

        // update file
	    $dest = $config->sys_novafrs_path . $g->getUnixName() . '/' . $dg->getPath() . '/' . novafrs_unixString($uploaded_data_name);

	    if( !move_uploaded_file( $uploaded_data, $dest ) ){
	        exit_error('Error : ', "can't move the uploaded file."  );   
	    }
	    
        // delete old file if no archive and the name change
        if( $changeName and !$archive ){
            unlink( $actual_location_file );
        }        
	    
	    
    }

    if( !isset( $stateid ) ) $stateid = null;
    

    // save data needed for move file after the database update
    $oldFrGroupID = $d->getFrGroupID();
    
    // update database
	if (!$d->update($filename,$filetype,$data,$fr_group,$title,$language_id,$description,$stateid,
	                $status,$author,$writingDate,$frtype,$reference,$version, $observation, $thefilesize)) {
		exit_error('Error',$d->getErrorMessage());
	}



	if (!$d->updateStatusTable( fetchPostStatusArray($config) ) ){
		exit_error('Error',$d->getErrorMessage());
	}



    // move file
    if( $moveFile  ){
        
        $history = $d->getHistory();
        if( $history === false ){
            exit_error('Error',$d->getErrorMessage());
        }

        
        $dgDest = new FileGroupFrs( $g, $fr_group  );
        $dgSrc  = new FileGroupFrs( $g, $oldFrGroupID );
        
        
        $dest_path = $config->sys_novafrs_path . $g->getUnixName() . '/' . $dgDest->getPath() . '/';
        $src_path  = $config->sys_novafrs_path . $g->getUnixName() . '/' . $dgSrc->getPath() . '/';
        
        
        foreach( $history as $frVersion ){
            $src  = $src_path  . $frVersion->getUnixFileNameHistory();
            $dest = $dest_path . $frVersion->getUnixFileNameHistory();


            if( !rename( $src, $dest ) ){
                exit_error( 'Error', "Can't move histoty file : " . $frVersion->getUnixFileNameHistory() );
            }
            
        } 
        
        $src = $src_path .  novafrs_unixString( $d->getFilename() );
        $dest = $dest_path .  novafrs_unixString( $d->getFilename() );
        
        if( !rename( $src, $dest ) ){
            exit_error( 'Error', "Can't move $src => $dest" );
        }
        
        if( ! $d->updateHistoryFrGroup( $fr_group ) ){
            exit_error('Error : ', "can't update the histoty file."  );    
        }
        
    }

	$feedback = urlencode( dgettext('general','update_successful') );
	Header("Location: /plugins/novafrs/?group_id=$group_id&feedback=".$feedback);
	exit;


// Ajout d'un file
}else if( isset($submit) ){
	if (!$fr_group) {
		//cannot add a fr unless an appropriate group is provided
		exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','no_valid_group'));
	}

	if( !$auth->canWrite( $fr_group ) ){
	    exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','noWriteAuth'));
	}

	if (!$title ||  (!$uploaded_data && !$file_url && !$ftp_filename )) {
		exit_missing_param();
	}

    if( !$description ){
        $description = '';
    }

    
	$d = new File($g);
	if (!$d || !is_object($d)) {
		exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','error_blank_file'));
	} elseif ($d->isError()) {
		exit_error(dgettext('general','error'),$d->getErrorMessage());
	}

    $data = '';
    $thefilesize = 0;
	if ($uploaded_data) {
		if (!is_uploaded_file($uploaded_data)) {
			exit_error(dgettext('general','error'),dgettext('general','invalid_filename'));
		}
		//$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
		$thefilesize = filesize($uploaded_data);
		$file_url='';
	} elseif ($file_url) {
		//$data = '';
		$uploaded_data_name=$file_url;
		$uploaded_data_type='URL';
	} elseif ($ftp_filename) {
		$uploaded_data_name=$upload_dir.'/'.$ftp_filename;
		//$data = addslashes(fread(fopen($uploaded_data_name, 'r'), filesize($uploaded_data_name)));
	}

    if( !$status  ){	
        exit_error('Error',dgettext('general','error') . 'bad status ' );    
    }



    if( $uploaded_data_type != 'URL' ){
	    $dg = new FileGroupFrs( $g, $fr_group );
	    $dest = $config->sys_novafrs_path . $g->getUnixName() . '/' . $dg->getPath() . '/' . novafrs_unixString($uploaded_data_name);
	    if( !move_uploaded_file( $uploaded_data, $dest ) ){
	        exit_error('Error : ', "can't move the uploaded file"  );   
	    }

	}
	
	
	if (!$d->create($uploaded_data_name,$uploaded_data_type,$data,$fr_group,$title,$language_id,$description, 
	                $status,$author,$writingDate,$frtype,$reference,$version,$observation,$thefilesize,null)) {

		exit_error(dgettext('general','error'),$d->getErrorMessage());
	} else {
		if (!$d->updateStatusTable( fetchPostStatusArray($config) ) ){
    		exit_error('Error',$d->getErrorMessage());
	    }
		Header("Location: /plugins/novafrs/?group_id=$group_id&feedback=".urlencode(dgettext('gforge-plugin-novafrs','submitted_successfully')));
		exit;
	}
    
}else if( isset($delete_fr) and isset( $frid )  ){
    // delete demand
    novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_display'));
    
    $frCard = new FileCardView( $is_editor );
    $frCard->printConfirm( $group_id, $frid, $Language );

    novafrs_footer ();
    
}else if( isset( $confirm_delete ) and isset( $frid ) ){
    // confirm delete

	$d= new File($g,$frid);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	if( !$auth->canDelete( $d->getFrGroupID() ) ){
	    exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','noDeleteAuth'));
	}


	if (!$d->delete()) {
		exit_error('Error',$d->getErrorMessage());
	}


    $dg = new FileGroupFrs( $g, $d->getFrGroupID()  );
        
    $path = $config->sys_novafrs_path . $g->getUnixName() . '/' . $dg->getPath() . '/';
        
    $src   = $path  . novafrs_unixString( $d->getFilename() );
    $dest  = $path  . $d->getUnixFileNameHistory();
        
    if( !rename( $src, $dest ) ){
        exit_error( 'Error', "Can't rename deleted file"  );
    }

	$feedback = dgettext('general','deleted');
	header('Location: index.php?group_id='.$d->Group->getID().'&feedback='.urlencode($feedback));
	die();	// End parsing file and redirect
    
    
}else if( isset( $frid ) and $frid  ){
    novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_display'));
    
    $fr = new File($g, $frid );
    $fr->fetchDataStatus();

	if( !$auth->canRead( $fr->getFrGroupID() ) ){
	    exit_permission_denied();
	}

    $histories = $fr->getHistory();

    $canEdit = session_loggedin() or $is_editor;
    if( !$fr->isCurrent() ){
        $canEdit = false; /* can't edit an history version */
    }

	if( !$auth->canWrite( $fr->getFrGroupID() ) ){
	    $canEdit = false;
	}

    $frCard = new FileCardView( $canEdit, $group_id );
    $frCard->printCard( $group_id, $Language, $fr, $g, $dgf, $histories );

    novafrs_footer ();
}else{ 
    novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_display'));

    $fr = new File( $g );

   	if (session_loggedin()) {
   		$language_id = $LUSER->getLanguage();
   		$author = $LUSER->getRealName();
   	} else {
   		$language_id = 1;
   		$author = '';
   	}

    // Valeurs par défaut
    $fr->data_array['language_id'] = $language_id;
    $fr->data_array['status'] = $config->statusDefault;
    $fr->data_array['writing_date'] = date('d/m/Y');
    $fr->data_array['author'] = $author;

    $frCard = new FileCardView( true, $group_id );
    $frCard->printCard( $group_id, $Language, $fr, $g, $dgf, false, null );

    novafrs_footer ();
}




?>
