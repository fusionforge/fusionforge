<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once('pre.php');
require_once('include/doc_utils.php');
require_once('common/docman/DocumentFactory.class');
require_once('common/docman/DocumentGroupFactory.class');

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$df = new DocumentFactory($g);
if ($df->isError()) {
	exit_error($Language->getText('general','error'),$df->getErrorMessage());
}

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError()) {
	exit_error($Language->getText('general','error'),$dgf->getErrorMessage());
}

// the "selected language" variable will be used in the links to navigate the
// document groups tree

if (!$language_id) {
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

$df->setLanguageID($language_id);

docman_header($Language->getText('docman_display_doc','title'),$Language->getText('docman_display_doc','section'),'docman','',$g->getPublicName());

$d_arr =& $df->getDocuments();
if (!$d_arr || count($d_arr) <1){
	$df->setLanguageId(0);
	$d_arr = &$df->getDocuments();
}

if (!$d_arr || count($d_arr) < 1) {
	print "<strong>".$Language->getText('docman','error_no_docs')."</strong>";
} else {
	doc_droplist_count($group_id, $language_id, $g);

	// Get the document groups info
	$nested_groups =& $dgf->getNested();
	docman_display_documents($nested_groups,$df,$is_editor);
}

docman_footer(array());

/*
$d_arr =& $df->getDocuments();
if (!$d_arr || count($d_arr) <1){
	$df->setLanguageId(0);
	$d_arr = &$df->getDocuments();
}

docman_header($Language->getText('docman_display_doc','title'),$Language->getText('docman_display_doc','section'),'docman','',$g->getPublicName());

if (!$d_arr || count($d_arr) < 1) {
	print "<strong>".$Language->getText('docman','error_no_docs')."</strong>";
} else {
	doc_droplist_count($group_id, $language_id, $g);

	print "\n<ul>";
	$last_group = "";
	for ($i=0; $i<count($d_arr); $i++) {
		//
		//	If we're starting a new "group" of docs, put in the
		//	docGroupName and start a new <ul>
		//
		if ($d_arr[$i]->getDocGroupID() != $last_group) {
			print (($i==0) ? '' : '</ul></li><br />');
			print "\n\n<li><strong>". $d_arr[$i]->getDocGroupName() ."</strong></li><li style=\"list-style: none\"><ul>";
			$last_group=$d_arr[$i]->getDocGroupID();
		}
		print "\n<li><a href=\"".(( $d_arr[$i]->isURL() ) ? $d_arr[$i]->getFileName() : "view.php/$group_id/".$d_arr[$i]->getID()."/".$d_arr[$i]->getFileName() )."\">".
			$d_arr[$i]->getName()." [ ".$d_arr[$i]->getFileName()." ]</a>".
			"\n<br /><em>".$Language->getText('docman','description').":</em> ".$d_arr[$i]->getDescription()."</li>\n";
	}
	print "\n</ul></li></ul>\n";
}

docman_footer(array());
*/
?>
