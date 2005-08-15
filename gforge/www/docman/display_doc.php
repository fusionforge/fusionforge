<?php
/**
 * GForge Doc Mgr Facility (compatibility wich older versions)
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 */

require_once('pre.php');
require_once('include/doc_utils.php');
require_once('common/docman/Document.class');

$docid = getIntFromRequest('docid');
if ($docid) {
	$group_id = getIntFromRequest('group_id');

	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error('Error',$g->getErrorMessage());
	}

	$d = new Document($g,$docid);
	if (!$d || !is_object($d)) {
		exit_error('Document unavailable','Document is not available.');
	} elseif ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	docman_header($d->getName(),$d->getName());
	echo $Language->getText('docman_display_doc','docmoved',array($group_id,$docid));
	docman_footer(array());
} else {
	exit_error($Language->getText('docman_display_doc','no_document_data_title'),$Language->getText('docman_display_doc','no_document_data_text'));
}

?>
