<?php
/**
 * headermenu plugin
 *
 * Copyright 2012, Franck Villaume - TrivialDev
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

global $headermenu;
global $type;
global $group_id;

$idLink = getIntFromRequest('linkid');
$linkStatus = getIntFromRequest('linkstatus');
$redirect_url = 'plugins/'.$headermenu->name.'/?type='.$type;
if (isset($group_id) && $group_id) {
	$redirect_url .= '&group_id='.$group_id;
}

if (!empty($idLink)) {
	if ($headermenu->updateLinkStatus($idLink, $linkStatus)) {
		$feedback = _('Link Status updated');
		session_redirect($redirect_url.'&feedback='.urlencode($feedback));
	}
	$error_msg = _('Task failed');
	session_redirect($redirect_url.'&error_msg='.urlencode($error_msg));
}
$warning_msg = _('Missing Link or status to be updated.');
session_redirect($redirect_url.'&warning_msg='.urlencode($warning_msg));
