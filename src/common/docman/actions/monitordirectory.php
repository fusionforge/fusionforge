<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012,2014, Franck Villaume - TrivialDev
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

if (!forge_check_perm('docman', $group_id, 'read')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
}

$directoryid = getStringFromRequest('directoryid');
$option = getStringFromRequest('option');
$feedback = _('Folder').' ';
switch ($option) {
	case 'add': {
		if (!empty($directoryid)) {
			$dg = new DocumentGroup($g, $directoryid);
			$feedback .= $dg->getName()._(': ');
			if ($dg->isError() || !$dg->addMonitoredBy($LUSER->getID())) {
				$error_msg = $dg->getErrorMessage();
				session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
			}
		} else {
			$warning_msg = _('No action to perform');
			session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
		}
		$feedback .= _('Monitoring Started');
		break;
	}
	case 'remove': {
		if (!empty($directoryid)) {
			$dg = new DocumentGroup($g, $directoryid);
			$feedback .= $dg->getName().' ';
			if ($dg->isError() || !$dg->removeMonitoredBy($LUSER->getID())) {
				$error_msg = $dg->getErrorMessage();
				session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
			}
		} else {
			$warning_msg = _('No action to perform');
			session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
		}
		$feedback .= _('Monitoring Stopped');
		break;
	}
	default: {
		$error_msg = _('Docman: monitoring action unknown.');
		session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
	}
}

session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid);
