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
require_once ("plugins/novadoc/include/DocumentFactory.class.php");
require_once ("plugins/novadoc/include/DocumentGroupFactory.class.php");
require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/DocumentCardView.class.php");
require_once ("plugins/novadoc/include/utils.php");

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object ($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}
$df = new DocumentFactory($g);
if ($df->isError()) {
	exit_error(dgettext('general','error'),$df->getErrorMessage());
}

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError()) {
	exit_error(dgettext('general','error'),$dgf->getErrorMessage());
}

$config = DocumentConfig::getInstance();

// the "selected language" variable will be used in the links to navigate the
// document groups tree

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

// check if the user is docman's admin
$perm =& $g->getPermission( session_get_user() );
if (!$perm || $perm->isError() || !$perm->isDocEditor()) {
	$is_editor = false;
} else {
	$is_editor = true;
}


$auth = new DocumentGroupAuth( $group_id, $LUSER );

$df->setLanguageID($language_id);


function fetchPostStatusArray($config){
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


// Modification d'un document
if( isset($submit) and isset($docid) and $docid ){ 
	$d= new Document($g,$docid);
	if ($d->isError()) {
		exit_error(dgettext('general','error'),$d->getErrorMessage());
	}

	$moveFile = false;
	if( !$d->isURL() && ($d->getDocGroupID() != $doc_group ) ){
	    $moveFile = true;
	}
	
	
	if( !$auth->canWrite( $d->getDocGroupID() ) ){
	    exit_permission_denied(); 
	}
	
	if( $moveFile and !$auth->canWrite( $doc_group ) ){
	    exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','noWriteAuth'));
	}
	
	$data = ''; 
	$changeName = false;
	if ($uploaded_data) {
		if (!is_uploaded_file($uploaded_data)) {
			exit_error(dgettext('general','error'),sprintf( dgettext ( 'gforge-plugin-novadoc' , 'error_invalid_file_attack' ) , $uploaded_data));
		}
		//$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
		$filename=$uploaded_data_name;
		$changeName = ( addslashes($d->getFileName() ) != $filename );
		if ( $config->mustUpdateWithSameName and $changeName ){
			exit_error('Error',dgettext('gforge-plugin-novadoc','bad_filemane'));
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


    if( $changeName or $d->getDocGroupId()!=$doc_group) {
        if( !$d->checkUnique( $filename, $doc_group ) ){
            exit_error('Error',$d->getErrorMessage());        
        }
    }

    if( $uploaded_data ){
        $dg = new DocumentGroupDocs( $g, $d->getDocGroupID() );
        $actual_path_file = $config->sys_novadoc_path . $g->getUnixName() . '/' . $dg->getPath() . '/';
        $actual_location_file = $actual_path_file . novadoc_unixString( $d->getFileName() );

        // Archive previous version
        if (  $archive == 1 ) {
            // Sav the old version

            $new_location_file = $actual_path_file . $d->getUnixFileNameHistory();
            
            $newDoc = $d->newVersion();
            if( $newDoc === false ){
                exit_error( 'Error', "Can't create new version : " . $d->getErrorMessage() );
            }
            
            $d = $newDoc;
            
            if( !rename( $actual_location_file, $new_location_file ) ){
                exit_error( 'File copy failed : ' . $actual_location_file . '==>'. $new_location_file   );
            }
    	}

        // update file
	    $dest = $config->sys_novadoc_path . $g->getUnixName() . '/' . $dg->getPath() . '/' . novadoc_unixString($uploaded_data_name);

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
    $oldDocGroupID = $d->getDocGroupID();
    
    // update database
	if (!$d->update($filename,$filetype,$data,$doc_group,$title,$language_id,$description,$stateid,
	                $status,$author,$writingDate,$doctype,$reference,$version, $observation)) {
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

        
        $dgDest = new DocumentGroupDocs( $g, $doc_group  );
        $dgSrc  = new DocumentGroupDocs( $g, $oldDocGroupID );
        
        
        $dest_path = $config->sys_novadoc_path . $g->getUnixName() . '/' . $dgDest->getPath() . '/';
        $src_path  = $config->sys_novadoc_path . $g->getUnixName() . '/' . $dgSrc->getPath() . '/';
        
        
        foreach( $history as $docVersion ){
            $src  = $src_path  . $docVersion->getUnixFileNameHistory();
            $dest = $dest_path . $docVersion->getUnixFileNameHistory();


            if( !rename( $src, $dest ) ){
                exit_error( 'Error', "Can't move histoty file : " . $docVersion->getUnixFileNameHistory() );
            }
            
        } 
        
        $src = $src_path .  novadoc_unixString( $d->getFilename() );
        $dest = $dest_path .  novadoc_unixString( $d->getFilename() );
        
        if( !rename( $src, $dest ) ){
            exit_error( 'Error', "Can't move $src => $dest" );
        }
        
        if( ! $d->updateHistoryDocGroup( $doc_group ) ){
            exit_error('Error : ', "can't update the histoty file."  );    
        }
        
    }

	$feedback = urlencode( dgettext('general','update_successful') );
	Header("Location: /plugins/novadoc/?group_id=$group_id&feedback=".$feedback);
	exit;


// Ajout d'un document
}else if( isset($submit) ){
	if (!$doc_group) {
		//cannot add a doc unless an appropriate group is provided
		exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','no_valid_group'));
	}

	if( !$auth->canWrite( $doc_group ) ){
	    exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','noWriteAuth'));
	}

	if (!$title ||  (!$uploaded_data && !$file_url && !$ftp_filename )) {
		exit_missing_param();
	}

    if( !$description ){
        $description = '';
    }

    
	$d = new Document($g);
	if (!$d || !is_object($d)) {
		exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','error_blank_document'));
	} elseif ($d->isError()) {
		exit_error(dgettext('general','error'),$d->getErrorMessage());
	}

    $data = '';
	if ($uploaded_data) {
		if (!is_uploaded_file($uploaded_data)) {
			exit_error(dgettext('general','error'),dgettext('general','invalid_filename'));
		}
		//$data = addslashes(fread(fopen($uploaded_data, 'r'), filesize($uploaded_data)));
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
	    $dg = new DocumentGroupDocs( $g, $doc_group );
	    $dest = $config->sys_novadoc_path . $g->getUnixName() . '/' . $dg->getPath() . '/' . novadoc_unixString($uploaded_data_name);
	    if( !move_uploaded_file( $uploaded_data, $dest ) ){
	        exit_error('Error : ', "can't move the uploaded file."  );   
	    }

	}
	
	
	if (!$d->create($uploaded_data_name,$uploaded_data_type,$data,$doc_group,$title,$language_id,$description, 
	                $status,$author,$writingDate,$doctype,$reference,$version,$observation,null)) {
		exit_error(dgettext('general','error'),$d->getErrorMessage());
	} else {
		if (!$d->updateStatusTable( fetchPostStatusArray($config) ) ){
    		exit_error('Error',$d->getErrorMessage());
	    }
		Header("Location: /plugins/novadoc/?group_id=$group_id&feedback=".urlencode(dgettext('gforge-plugin-novadoc','submitted_successfully')));
		exit;
	}
    
}else if( isset($delete_doc) and isset( $docid )  ){
    // delete demand
    novadoc_header (dgettext ('gforge-plugin-novadoc','title_display'));
    
    $docCard = new DocumentCardView( $is_editor );
    $docCard->printConfirm( $group_id, $docid, $Language );

    novadoc_footer ();
    
}else if( isset( $confirm_delete ) and isset( $docid ) ){
    // confirm delete

	$d= new Document($g,$docid);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	if( !$auth->canDelete( $d->getDocGroupID() ) ){
	    exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','noDeleteAuth'));
	}


	if (!$d->delete()) {
		exit_error('Error',$d->getErrorMessage());
	}


    $dg = new DocumentGroupDocs( $g, $d->getDocGroupID()  );
        
    $path = $config->sys_novadoc_path . $g->getUnixName() . '/' . $dg->getPath() . '/';
        
    $src   = $path  . novadoc_unixString( $d->getFilename() );
    $dest  = $path  . $d->getUnixFileNameHistory();
        
    if( !rename( $src, $dest ) ){
        exit_error( 'Error', "Can't rename deleted file"  );
    }

	$feedback = dgettext('general','deleted');
	header('Location: index.php?group_id='.$d->Group->getID().'&feedback='.urlencode($feedback));
	die();	// End parsing file and redirect
    
    
}else if( isset( $docid ) and $docid  ){
    novadoc_header (dgettext ('gforge-plugin-novadoc','title_display'));
    
    $doc = new Document($g, $docid );
    $doc->fetchDataStatus();

	if( !$auth->canRead( $doc->getDocGroupID() ) ){
	    exit_permission_denied();
	}

    $histories = $doc->getHistory();

    $canEdit = session_loggedin() or $is_editor;
    if( !$doc->isCurrent() ){
        $canEdit = false; /* can't edit an history version */
    }

	if( !$auth->canWrite( $doc->getDocGroupID() ) ){
	    $canEdit = false;
	}

    $docCard = new DocumentCardView( $canEdit, $group_id );
    $docCard->printCard( $group_id, $Language, $doc, $g, $dgf, $histories );

    novadoc_footer ();
}else{ 
    novadoc_header (dgettext ('gforge-plugin-novadoc','title_display'));

    $doc = new Document( $g );

   	if (session_loggedin()) {
   		$language_id = $LUSER->getLanguage();
   		$author = $LUSER->getRealName();
   	} else {
   		$language_id = 1;
   		$author = '';
   	}

    // Valeurs par défaut
    $doc->data_array['language_id'] = $language_id;
    $doc->data_array['status'] = $config->statusDefault;
    $doc->data_array['writing_date'] = date('d/m/Y');
    $doc->data_array['author'] = $author;

    $docCard = new DocumentCardView( /*$is_editor*/ true, $group_id );
    $docCard->printCard( $group_id, $Language, $doc, $g, $dgf, false, null );


    novadoc_footer ();
}




?>
