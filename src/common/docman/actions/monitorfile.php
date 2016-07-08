<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012-2015, Franck Villaume - TrivialDev
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
global $LUSER; // User object
global $childgroup_id; // id of child group if any

$redirecturl = '/docman/?group_id='.$group_id.'&dirid='.$dirid;

// plugin projects-hierarchy handler
if ($childgroup_id) {
	$redirecturl .= '&childgroup_id='.$childgroup_id;
	if (!forge_check_perm('docman', $childgroup_id, 'submit')) {
		$warning_msg = _('Document Manager Action Denied.');
		session_redirect($redirecturl);
	}
	$g = group_get_object($childgroup_id);
}

if (!forge_check_perm('docman', $g->getID(), 'read')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($redirecturl);
}

$arr_fileid = explode(',', getStringFromRequest('fileid'));
$option = getStringFromRequest('option');
switch ($option) {
	case 'start': {
		foreach ($arr_fileid as $fileid) {
			if (!empty($fileid)) {
				$d = document_get_object($fileid, $g->getID());
				if (!$d) {
					$error_msg = _('Cannot retrieve document');
					session_redirect($redirecturl);
				}
				if ($d->isError() || !$d->addMonitoredBy(user_getid())) {
					$error_msg = $d->getErrorMessage();
					session_redirect($redirecturl);
				}
			} else {
				$warning_msg = _('No action to perform');
				session_redirect($redirecturl);
			}
		}
		$count = count($arr_fileid);
		$feedback = sprintf(ngettext('Monitoring %s document started.', 'Monitoring %s documents started.', $count), $count);
		break;
	}
	case 'stop': {
		foreach ($arr_fileid as $fileid) {
			if (!empty($fileid)) {
				$d = document_get_object($fileid, $g->getID());
				if (!$d) {
					$error_msg = _('Cannot retrieve document');
					session_redirect($redirecturl);
				}
				if ($d->isError() || !$d->removeMonitoredBy($LUSER->getID())) {
					$error_msg = $d->getErrorMessage();
					session_redirect($redirecturl);
				}
			} else {
				$warning_msg = _('No action to perform');
				session_redirect($redirecturl);
			}
		}
		$count = count($arr_fileid);
		$feedback = sprintf(ngettext('Monitoring %s document stopped.', 'Monitoring %s documents stopped.', $count), $count);
		break;
	}
	default: {
		$error_msg = _('Docman')._(': ')._('monitoring action unknown.');
		session_redirect($redirecturl);
	}
}

session_redirect($redirecturl);
