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

$group_id = getIntFromRequest('group_id');
$language_id = getStringFromRequest('language_id');

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

docman_header($Language->getText('docman_display_doc','title'),$Language->getText('docman_display_doc','section'));

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

	$nested_docs=array();
	//put the doc objects into an array keyed off the docgroup
	foreach ($d_arr as $doc) {
		$nested_docs[$doc->getDocGroupID()][] = $doc;
	}

/*
	EXPERIMENTAL CODE TO USE JAVASCRIPT TREE
*/
function docman_recursive_display($docgroup) {
	global $nested_groups,$nested_docs,$group_id;
	foreach ($nested_groups[$docgroup] as $dg) {
		$folder = '<span class="JSCookTreeFolderClosed"><i><img src=\"/jscook/ThemeXP/folder1.gif\"></i></span><span class="JSCookTreeFolderOpen"><i><img src=\"/jscook/ThemeXP/folderopen1.gif\"></i></span>';
		echo "\n['$folder', '".$dg->getName()."', '', '', ''";
		echo ",";
		docman_recursive_display($dg->getID());
		foreach ($nested_docs[$dg->getID()] as $d) {
			echo "\n\t,['<img src=\"/jscook/ThemeXP/page.gif\">', '".$d->getName()." (".$d->getFileName().")', '/docman/view.php/".$group_id."/".$d->getID()."/".$d->getFileName()."', '', '".$d->getDescription()."']";
		}
		echo ",\n],";

	}
}

?>
<script language="JavaScript" src="/jscook/JSCookTree.js"></script>
<link rel="stylesheet" href="/jscook/ThemeXP/theme.css" type="text/css" />
<script src="/jscook/ThemeXP/theme.js" type="text/javascript"></script>

<script language="JavaScript"><!--
var myMenu =
[
['<span class="JSCookTreeFolderClosed"><i><img src="/jscook/ThemeXP/folder1.gif"></i></span><span class="JSCookTreeFolderOpen"><i><img src="/jscook/ThemeXP/folderopen1.gif"></i></span>', '/', '', '', '',
<?php
docman_recursive_display(0);
?>
]
]
--></script>
<div id="myMenuID"></div>

<script language="JavaScript"><!--
        ctDraw ('myMenuID', myMenu, ctThemeXP1, 'ThemeXP', 0, 2);
--></script>
<?php

	echo '<noscript>';
	docman_display_documents($nested_groups,$df,$is_editor);
	echo '</noscript>';
}

docman_footer(array());

?>
