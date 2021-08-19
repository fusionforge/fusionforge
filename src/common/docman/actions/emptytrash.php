<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2013-2015, Franck Villaume - TrivialDev
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
global $group_id; // id of group
global $childgroup_id; // id of child group if any
global $feedback;
global $error_msg;
global $warning_msg;

$redirecturl = DOCMAN_BASEURL.$group_id;

// plugin projects-hierarchy handler
if ($childgroup_id) {
	$redirecturl .= '&childgroup_id='.$childgroup_id;
	$g = group_get_object($childgroup_id);
}

if (!forge_check_perm('docman', $g->getID(), 'submit')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($redirecturl);
}

$dm = new DocumentManager($g);
if (!$dm->cleanTrash()) {
	$error_msg = _('Unable to clean trash');
	session_redirect($redirecturl);
}

$feedback = _('Emptied Trash successfully.');
session_redirect($redirecturl);
