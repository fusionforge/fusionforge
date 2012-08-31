<?php
/**
 * webanalytics plugin
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

global $webanalytics;

session_require_global_perm('forge_admin');
$idLink = getIntFromRequest('linkid');
$linkStatus = getIntFromRequest('linkstatus');

if (!empty($idLink)) {
	if ($webanalytics->updateLinkStatus($idLink, $linkStatus)) {
		$feedback = _('Link Status updated');
		session_redirect('plugins/'.$webanalytics->name.'/?type=globaladmin&feedback='.urlencode($feedback));
	}
	$error_msg = _('Task failed');
	session_redirect('plugins/'.$webanalytics->name.'/?type=globaladmin&error_msg='.urlencode($error_msg));
}
$warning_msg = _('Missing Link or status to be updated.');
session_redirect('plugins/'.$webanalytics->name.'/?type=globaladmin&warning_msg='.urlencode($warning_msg));

?>
