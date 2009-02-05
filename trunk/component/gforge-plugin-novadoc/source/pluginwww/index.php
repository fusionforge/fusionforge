<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: index.php,v 1.10 2006/11/22 10:17:24 pascal Exp $
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novadoc/include/DocumentFactory.class.php");
require_once ("plugins/novadoc/include/DocumentGroupFactory.class.php");
require_once ("plugins/novadoc/include/DocumentTreeView.class.php");
require_once ("plugins/novadoc/include/utils.php");

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
$df = new DocumentFactory($g);
if ($df->isError()) {
	exit_error(dgettext('general','error'),$df->getErrorMessage());
}

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError()) {
	exit_error(dgettext('general','error'),$dgf->getErrorMessage());
}


$auth = new DocumentGroupAuth( $group_id, $LUSER );

// Mise à jour d'un statut d'un document (directement sur l'arborescence)
if( isset( $docId ) and isset( $statusId ) ){
    $d= new Document($g,$docId);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	if( !$auth->canWrite( $d->getDocGroupID() ) ){
	    exit_permission_denied(); 
	}

	if( !$d->updateStatus( $statusId ) ){
	    exit_error('Error',$d->getErrorMessage());
	}
}


// the "selected language" variable will be used in the links to navigate the
// document groups tree

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
novadoc_header (dgettext ('gforge-plugin-novadoc', 'title_display'));
$d_arr =& $df->getDocuments ();
if (!$d_arr || (count ($d_arr) < 1))
{
	$df->setLanguageId (0);
	$d_arr = &$df->getDocuments ();
}
novadoc_droplist_count ($group_id, $language_id, $g);
// Get the document groups info
$nested_groups =& $dgf->getNested ();
$nested_docs = array ();
//put the doc objects into an array keyed off the docgroup
if (is_array ($d_arr) == true)
{
	foreach ($d_arr as $doc)
	{
		$nested_docs [$doc->getDocGroupID ()] [] = $doc;
	}
}
$docView = new DocumentTreeView (true, $auth);
$docView->print_tree ($nested_groups, $nested_docs, $df);
// Si un statut a changé, on doit retrouver l'arborecence telle qu'elle était
if ((isset ($docId) == true) && (isset ($statusId) == true))
{
	$tabDossiersOuverts = explode (';', $dossiersOuverts);
	$docView->print_js_redraw ($tabDossiersOuverts, $scrollLeft, $scrollTop);
}
novadoc_footer ();
?>
