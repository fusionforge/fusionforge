<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012,2014 Franck Villaume - TrivialDev
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
global $dirid; //id of doc_group
global $group_id; // id of group
global $HTML;

$sysdebug_enable = false;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect('/docman/?group_id='.$group_id.'&dirid='.$dirid);
}

$itemid = getIntFromRequest('itemid');
// hack to use this action as a getLocked action.
// if the param lock is missing, then we return the status of the lock.
$lock = getIntFromRequest('lock', 2);
$type = getStringfromRequest('type');
$childgroup_id = getIntFromRequest('childgroup_id');
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
}
switch ($type) {
	case 'file': {
		$objectType = new Document($g, $itemid);
		break;
	}
	case 'dir': {
		$objectType = new DocumentGroup($g, $itemid);
		break;
	}
	default: {
		$error_msg = _('Lock failed')._(': ')._('Missing Type');
		session_redirect('/docman/?group_id='.$group_id.'&dirid='.$dirid);
	}
}

if ($objectType->isError()) {
	$error_msg  = $objectType->getErrorMessage();
	session_redirect('/docman/?group_id='.$group_id.'&dirid='.$dirid);
}

if ($lock === 0) {
	//release the lock
	echo $objectType->setLock($lock);
} elseif ($lock === 1) {
	//set the lock
	echo $objectType->setLock($lock, user_getid(), time());
} elseif ($lock === 2) {
	//get the current status of the lock
	if (getIntFromRequest('json')) {
		$result = array();
		if ($objectType->getLocked() && $objectType->getLockedBy() != user_getid()) {
			$result['html'] = $HTML->warning_msg(_('Action currently locked by another user.'));
		}
		echo json_encode($result);
	} else {
		echo $objectType->getLocked();
	}
}
exit;
