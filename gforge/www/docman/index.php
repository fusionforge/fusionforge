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

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$df = new DocumentFactory($g);
if ($df->isError()) {
	exit_error('Error',$df->getErrorMessage());
}

if (!$language_id) {
	if (session_loggedin()) {
		$language_id = $LUSER->getLanguage();
	} else {
		$language_id = 1;
	}
}

$df->setLanguageID($language_id);
$d_arr =& $df->getDocuments();
if (!$_arr || count($d_arr) <1){
	$df->setLanguageId(0);	
	$d_arr = &$df->getDocuments();
}

docman_header('Project Documentation','Project Documentation','docman','',$g->getPublicName());

if (!$d_arr || count($d_arr) < 1) {
	print "<strong>This project has no visible documents.</strong><p>";
} else { 
	doc_droplist_count($group_id, $language_id);

	print "\n<ul>";
	for ($i=0; $i<count($d_arr); $i++) {

		//
		//	If we're starting a new "group" of docs, put in the 
		//	docGroupName and start a new <ul>
		//
		if ($d_arr[$i]->getDocGroupID() != $last_group) {
			print (($i==0) ? '' : '</ul>');
			print "\n\n<li><strong>". $d_arr[$i]->getDocGroupName() ."</strong></li><ul>";
			$last_group=$d_arr[$i]->getDocGroupID();
		}
		print "\n<li><a href=\"view.php/$group_id/".$d_arr[$i]->getID()."/".$d_arr[$i]->getFileName()."\">". 
			$d_arr[$i]->getName()." [ ".$d_arr[$i]->getFileName()." ]</a>".
			"\n<br /><em>Description:</em> ".$d_arr[$i]->getDescription();

	}
	print "\n</ul>\n";
}

docman_footer(array());

?>
