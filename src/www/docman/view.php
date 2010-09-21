<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$no_gz_buffer=true;

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Document.class.php';

session_require_perm ('project_read', $group_id) ;

$arr=explode('/',getStringFromServer('REQUEST_URI'));
$group_id=$arr[3];
$docid=$arr[4];

if ($docid != 'backup' ) {
	$docname=urldecode($arr[5]);

	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error($g->getErrorMessage(),'docman');
	}

	$d = new Document($g,$docid);
	if (!$d || !is_object($d)) {
		exit_error(_('Document is not available.'),'docman');
	} elseif ($d->isError()) {
		exit_error($d->getErrorMessage(),'docman');
	}

	/** 
	 * If the served document has wrong relative links, then
	 * theses links may redirect to the same document with another
	 * name, this way a search engine may loop and stress the
	 * server.
	 *
	 * A workaround is to serve only the document if the given
	 * name is correct.
	 */
	if ($d->getFileName() != $docname) {
		exit_error(_('No document to display - invalid or inactive document number'),'docman');
	}

	Header ('Content-disposition: filename="'.str_replace('"', '', $d->getFileName()).'"');

	if (strstr($d->getFileType(),'app')) {
		Header ("Content-type: application/binary");
	} else {
		Header ("Content-type: ".$d->getFileType());
	}

	echo $d->getFileData();

} else if ( $docid == 'backup' ) {
    exit_error(_('Not implemented yet'),'docman');
} else {
	exit_error(_('No document to display - invalid or inactive document number.'),'docman');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
