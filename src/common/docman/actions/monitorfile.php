<?php
/**
 * FusionForge Documentation Manager
 *
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
global $dirid; //id of doc_group
global $group_id; // id of group
global $LUSER; // User object

if (!forge_check_perm ('docman', $group_id, 'approve')) {
	$return_msg = _('Docman Action Denied');
	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&warning_msg='.urlencode($return_msg));
} else {

	$fileid = getIntFromRequest('fileid');
    $option = getStringFromRequest('option');
	$d= new Document($g,$fileid);

	if ($d->isError())
	    session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&error_msg='.urlencode($d->getErrorMessage()));

	switch ($option) {
	case "add":
		if (!$d->addMonitoredBy($LUSER->getID())) {
	    	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&error_msg='.urlencode($d->getErrorMessage()));
		} else {
			$feedback = _('Monitoring started');
		}
		break;
	case "remove":
		if (!$d->removeMonitoredBy($LUSER->getID())) {
	    	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&error_msg='.urlencode($d->getErrorMessage()));
		} else {
			$feedback = _('Monitoring stopped');
		}
		break;
	default:
		$error_msg = _('Docman : monitoring action unknown');
	   	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&error_msg='.urlencode($error_msg));
	}

	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&feedback='.urlencode($feedback));
}
?>
