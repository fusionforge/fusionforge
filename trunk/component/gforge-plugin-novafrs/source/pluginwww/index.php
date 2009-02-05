<?php
/**
 * GForge Fr Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: index.php,v 1.10 2006/11/22 10:17:24 pascal Exp $
 */


/*
	File Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novafrs/include/FileFactory.class.php");
require_once ("plugins/novafrs/include/FileGroupFactory.class.php");
require_once ("plugins/novafrs/include/FileTreeView.class.php");
require_once ("plugins/novafrs/include/utils.php");

if( !session_loggedin() ){
    exit_permission_denied();
}    


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


$auth = new FileGroupAuth( $group_id, $LUSER );


// Mise à jour d'un statut d'un file (directement sur l'arborescence)
if( isset( $frId ) and isset( $statusId ) ){
    $d= new File($g,$frId);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	if( !$auth->canWrite( $d->getFrGroupID() ) ){
	    exit_permission_denied(); 
	}

	if( !$d->updateStatus( $statusId ) ){
	    exit_error('Error',$d->getErrorMessage());
	}
}


// the "selected language" variable will be used in the links to navigate the
// file groups tree

if ( !isset($language_id) or !$language_id) {
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

$df->setLanguageID ($language_id);
novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_display'));
$d_arr =& $df->getFiles ();
if (!$d_arr || (count($d_arr) < 1))
{
	$df->setLanguageId (0);
	$d_arr = &$df->getFiles ();
}
novafrs_droplist_count ($group_id, $language_id, $g);
// Get the file groups info
$nested_groups =& $dgf->getNested ();
$nested_frs = array ();
//put the fr objects into an array keyed off the frgroup
if (is_array ($d_arr) == true)
{
	foreach ($d_arr as $fr)
	{
		$nested_frs [$fr->getFrGroupID()] [] = $fr;
	}
}
$frView = new FileTreeView (true, $auth);
$frView->print_tree ($nested_groups, $nested_frs, $df);
// Si un statut a changé, on doit retrouver l'arborecence telle qu'elle était
if ((isset($frId) == true) && (isset ($statusId) == true))
{
	$tabDossiersOuverts = explode (';', $dossiersOuverts);
	$frView->print_js_redraw ($tabDossiersOuverts, $scrollLeft, $scrollTop);
}
else
{
	$frView->devToutArborescence ();
}
novafrs_footer ();
?>
