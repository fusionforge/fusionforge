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
global $group_id; // id of the group
global $g; // the project object
global $warning_msg;
global $childgroup_id;

// plugin hierarchy support
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
}

if (!forge_check_perm('docman', $g->getID(), 'read')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect(DOCMAN_BASEURL.$group_id);
}

echo html_ao('div', array('class' => 'docmanDivIncluded'));
plugin_hook('blocks', 'doc help');
if (forge_get_config('use_webdav') && $g->useWebdav()) {
	echo html_e('p', array(), _('Documents parsing is also available through webdav. Only for registered users.'), false);
	echo html_e('p', array(), util_make_link('/docman/view.php/'.$g->getID().'/webdav',_('Direct Webdav URL')), false);
}
echo html_ac(html_ap() - 1);
