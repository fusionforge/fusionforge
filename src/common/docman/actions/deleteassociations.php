<?php
/**
 * FusionForge Docman: Delete association Action
 *
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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
global $group_id; // id of group
global $childgroup_id; // id of child group if any
global $HTML;
global $feedback;
global $error_msg;
global $warning_msg;

$dirid = getIntFromRequest('dirid');
$baseurl = '/docman/?group_id='.$group_id.'&dirid='.$dirid;
// plugin projects-hierarchy handler
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$baseurl .= '&childgroup_id='.$childgroup_id;
}

if (!forge_check_perm('docman', $g->getID(), 'submit')) {
	$error_msg = _('Document Manager Action Denied.');
	session_redirect($baseurl);
}

$docid = getIntFromRequest('docid');
if ($docid) {
	$documentObject = document_get_object($docid, $g->getID());
	if ($documentObject && !$documentObject->isError()) {
		$objectRefId = getStringFromRequest('objectrefid');
		$objectId = getStringFromRequest('objectid');
		$objectType = getStringFromRequest('objecttype');
		$link = getStringFromRequest('link');
		$was_error = false;
		if ($link == 'to') {
			if (!$documentObject->removeAssociationTo($objectId, $objectRefId, $objectType)) {
				$error_msg = $documentObject->getErrorMessage();
				$was_error = true;
			}
		} elseif ($link == 'from') {
			if (!$documentObject->removeAssociationFrom($objectId, $objectRefId, $objectType)) {
				$error_msg = $documentObject->getErrorMessage();
				$was_error = true;
			}
		} elseif ($link == 'any') {
			if (!$documentObject->removeAllAssociations()) {
				$error_msg = $documentObject->getErrorMessage();
				$was_error = true;
			}
		}
		if (!$was_error) {
			$feedback = _('Associations removed successfully');
		}
	} else {
		$warning_msg = _('Cannot retrieve document')._(': ').$docid;
	}
} else {
	$warning_msg = _('No document ID. Cannot retrieve versions.');
}

session_redirect($baseurl.'&filedetailid='.$docid);
