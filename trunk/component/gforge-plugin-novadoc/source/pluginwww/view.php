<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: view.php,v 1.5 2006/11/22 10:17:24 pascal Exp $
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novadoc/include/Document.class.php");
require_once ("plugins/novadoc/include/DocumentGroupDocs.class.php");
require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/DocumentGroupAuth.class.php");
require_once ("plugins/novadoc/include/utils.php");

$arr=explode('/',$REQUEST_URI);
$group_id=$arr[4];
$docid=$arr[5];

if ($docid) {

	$g =& group_get_object ($group_id);
	$d = new Document($g,$docid);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error('Error',$g->getErrorMessage());
	}
	if(!$g->isPublic()) {
		session_require(array('group' => $group_id));

        $auth = new DocumentGroupAuth( $group_id, $LUSER );
        
       // if( !$auth->canRead( $group_id ) ){
        if( !$auth->canRead( $d->getDocGroupID() ) ){
	     
		 exit_permission_denied();
	    }

	}

	if (!$d || !is_object($d)) {
		exit_error('Document unavailable','Document is not available.');
	} elseif ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	Header ('Content-disposition: filename="'.str_replace('"', '', $d->getFileName()).'"');

	if (strstr($d->getFileType(),'app')) {
		Header ("Content-type: application/binary");
	} else {
		Header ("Content-type: ".$d->getFileType());
	}

    $config =& DocumentConfig::getInstance();
    $dg = new DocumentGroupDocs( $g, $d->getDocGroupID() );
    $filepath = $config->sys_novadoc_path . $g->getUnixName() . '/' . $dg->getPath() . '/';
    if( isset( $_GET['history'] ) or $d->isDeleted() ){
        $filepath .= $d->getUnixFileNameHistory();
    }else{
        $filepath .= novadoc_unixString ($d->getFileName ());
    }

   

	$length = filesize($filepath);
	Header("Content-length: $length");


    readfile( $filepath );

} else {
	exit_error(dgettext('gforge-plugin-novadoc','no_document_data_title'),dgettext('gforge-plugin-novadoc','no_document_data_text'));
}

?>
