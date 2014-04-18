<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2013-2014 Franck Villaume - TrivialDev
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
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($redirecturl.'&dirid='.$dirid);
}

// plugin projects-hierarchy handler
$childgroup_id = getIntFromRequest('childgroup_id');
if ($childgroup_id) {
	if (!forge_check_perm('docman', $childgroup_id, 'approve')) {
		$warning_msg = _('Document Manager Action Denied.');
		session_redirect($redirecturl.'&dirid='.$dirid);
	}
	$redirecturl .= '&childgroup_id='.$childgroup_id;
	$g = group_get_object($childgroup_id);
}

/* set this dirid to trash */
$dg = new DocumentGroup($g, $dirid);
$currentParent = $dg->getParentID();

if (!$dg->trash()) {
	$error_msg = $dg->getErrorMessage();
	session_redirect($redirecturl.'&dirid='.$currentParent);
}

$feedback = sprintf(_('Documents folder %s moved to trash successfully.'), $dg->getName());
session_redirect($redirecturl.'&dirid='.$currentParent);
