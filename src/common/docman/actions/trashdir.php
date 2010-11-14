<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume - Capgemini
 * http://fusionforge.org
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; //group object
global $dirid; //id of doc_group
global $group_id; // id of group
global $dgf; // document group factory of this group
global $d_arr; // documents array of this group

if (!forge_check_perm ('docman', $group_id, 'approve')) {
	$return_msg = _('Document Action Denied');
	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&warning_msg='.urlencode($return_msg));
} else {

	/* when moving a document group to trash, it's recursive and it's applied to documents that belong to these document groups */
	/* Get the document groups info */
	$trashnested_groups =& $dgf->getNested();
	$trashnested_docs=array();
	/* put the doc objects into an array keyed of the docgroup */
	foreach ($d_arr as $doc) {
		$trashnested_docs[$doc->getDocGroupID()][] = $doc;
	}

	/* set to trash content of this dirid */
	docman_recursive_stateid($dirid,$trashnested_groups,$trashnested_docs,2);

	/* set this dirid to trash */
	$dg = new DocumentGroup($g,$dirid);
	$dg->setStateID('2');

	$return_msg = _('Document Directory moved to trash successfully');
	session_redirect('/docman/?group_id='.$group_id.'&feedback='.urlencode($return_msg));
}
?>
