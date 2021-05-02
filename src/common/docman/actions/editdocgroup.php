<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2013-2014,2016,2021, Franck Villaume - TrivialDev
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
global $g; // Group object
global $dirid; // id of doc_group
global $group_id; // id of group
global $childgroup_id; // plugin projects hierarchy handler

$urlredirect = '/docman/?group_id='.$group_id.'&dirid='.$dirid;

// plugin projects-hierarchy handler
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$urlredirect .= '&childgroup_id='.$childgroup_id;
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($urlredirect);
}

$groupname = getStringFromRequest('groupname');
$parent_dirid = getIntFromRequest('parent_dirid');
$stateid = getIntFromRequest('stateid');

$dg = documentgroup_get_object($dirid, $g->getID());
if ($dg->isError()) {
	$error_msg = $dg->getErrorMessage();
	session_redirect($urlredirect);
}

$currentParentID = $dg->getParentID();
if (!$dg->update($groupname, $parent_dirid, 1, $stateid)) {
	$error_msg = $dg->getErrorMessage();
	session_redirect($urlredirect);
}

$dm = new DocumentManager($g);
if (($stateid == 1 || $stateid == 5) && ($currentParentID == $dm->getTrashID()) && !$dg->setStateID($stateid, true)) {
	$error_msg = $dg->getErrorMessage();
	session_redirect($urlredirect);
}

$feedback = sprintf(_('Documents folder %s updated successfully'), $dg->getName());
if ($childgroup_id) {
	$feedback .= ' '.sprintf(_('on project %s'), $g->getPublicName());
}
session_redirect($urlredirect);
