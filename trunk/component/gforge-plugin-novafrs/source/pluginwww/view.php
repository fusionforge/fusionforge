<?php
/**
 * GForge Fr Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: view.php,v 1.5 2006/11/22 10:17:24 pascal Exp $
 */


/*
	File Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novafrs/include/File.class.php");
require_once ("plugins/novafrs/include/FileGroupFrs.class.php");
require_once ("plugins/novafrs/include/FileConfig.class.php");
require_once ("plugins/novafrs/include/FileGroupAuth.class.php");
require_once ("plugins/novafrs/include/utils.php.php");

$arr=explode('/',$REQUEST_URI);
$group_id=$arr[4];
$frid=$arr[5];

if ($frid) {

	$g =& group_get_object ($group_id);
	$d = new File($g,$frid);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error('Error',$g->getErrorMessage());
	}
	if(!$g->isPublic()) {
		session_require(array('group' => $group_id));

        $auth = new FileGroupAuth( $group_id, $LUSER );
        
        if( !$auth->canRead( $d->getFrGroupID() ) ){
	        exit_permission_denied();
	    }

	}

	if (!$d || !is_object($d)) {
		exit_error('File unavailable','File is not available.');
	} elseif ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	Header ('Content-disposition: filename="'.str_replace('"', '', $d->getFileName()).'"');

	if (strstr($d->getFileType(),'app')) {
		Header ("Content-type: application/binary");
	} else {
		Header ("Content-type: ".$d->getFileType());
	}

    $config =& FileConfig::getInstance();
    $dg = new FileGroupFrs( $g, $d->getFrGroupID() );
    $filepath = $config->sys_novafrs_path . $g->getUnixName() . '/' . $dg->getPath() . '/';
    if( isset( $_GET['history'] ) or $d->isDeleted() ){
        $filepath .= $d->getUnixFileNameHistory();
    }else{
        $filepath .= novafrs_unixString($d->getFileName());
    }

   

	$length = filesize($filepath);
	Header("Content-length: $length");


    readfile( $filepath );

} else {
	exit_error(dgettext('gforge-plugin-novafrs','no_file_data_title'),dgettext('gforge-plugin-novafrs','no_file_data_text'));
}

?>
