<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; //group object
global $dirid; //id of doc_group
global $group_id; // id of group

$redirecturl = '/docman/?group_id='.$group_id.'&view=listfile';
if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg = _('Document Manager Action Denied.');
	session_redirect($redirecturl.'&dirid='.$dirid.'&warning_msg='.urlencode($return_msg));
}

$childgroup_id = getIntFromRequest('childgroup_id');
if ($childgroup_id) {
	if (!forge_check_perm('docman', $childgroup_id, 'approve')) {
		$return_msg = _('Document Manager Action Denied.');
		session_redirect($redirecturl.'&dirid='.$dirid.'&warning_msg='.urlencode($return_msg));
	}
	$redirecturl .= '&childgroup_id='.$childgroup_id;
	$g = group_get_object($childgroup_id);
}

/* when moving a document group to trash, it's recursive and it's applied to documents that belong to these document groups */
/* Get the document groups info */
$df = new DocumentFactory($g);
if ($df->isError())
	exit_error($df->getErrorMessage(), 'docman');

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError())
	exit_error($dgf->getErrorMessage(), 'docman');

$trashnested_groups =& $dgf->getNested();

$df->setDocGroupID($dirid);
$d_arr =& $df->getDocuments();

$trashnested_docs = array();
/* put the doc objects into an array keyed of the docgroup */
if (is_array($d_arr)) {
	foreach ($d_arr as $doc) {
		$trashnested_docs[$doc->getDocGroupID()][] = $doc;
	}
}

/* set to trash content of this dirid */
docman_recursive_stateid($dirid, $trashnested_groups, $trashnested_docs, 2);

/* set this dirid to trash */
$dg = new DocumentGroup($g, $dirid);
$currentParent = $dg->getParentID();
if (!$dg->setStateID('2'))
	session_redirect($redirecturl.'&error_msg='.urlencode($dg->getErrorMessage()));

$dm = new DocumentManager($g);
if (!$dg->setParentDocGroupId($dm->getTrashID()))
	session_redirect($redirecturl.'&dirid='.$currentParent.'&error_msg='.urlencode($dg->getErrorMessage()));

$return_msg = sprintf(_('Documents folder %s moved to trash successfully.'),$dg->getName());
session_redirect($redirecturl.'&dirid='.$currentParent.'&feedback='.urlencode($return_msg));
?>
