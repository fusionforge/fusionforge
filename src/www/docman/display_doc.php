<?php
/**
 * FusionForge Doc Mgr Facility (compatibility wich older versions)
 *
 * Copyright 2002 GForge, LLC
 * http://fusionforge.org/
 *
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'docman/include/doc_utils.php';
require_once $gfcommon.'docman/Document.class.php';

$docid = getIntFromRequest('docid');
if ($docid) {
	$group_id = getIntFromRequest('group_id');

	$g = group_get_object($group_id);
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
	printf(_('This document was moved to <a href="%1$s">this new location</a>'),
	       util_make_url ("/docman/view.php/$group_id/$docid"));
	docman_footer(array());
} else {
	exit_error(_('No document data'),_('No document to display - invalid or inactive document number.'));
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
