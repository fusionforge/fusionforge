<?php
/**
 * MoinMoinWiki plugin
 *
 * Copyright 2006, Daniel Perez
 * Copyright 2009-2011, Roland Mas
 * Copyright 2016, Franck Villaume - TrivialDev
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
$pluginname = 'moinmoin';

$group = group_get_object($group_id);
if (!$group) {
	exit_error(_('Invalid Project'), _('Invalid Project'));
}

if (!$group->usesPlugin ($pluginname)) {
	exit_error(_('Error'), sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'), $pluginname);
}

$params = array () ;
$params['toptab']      = $pluginname;
$params['group']       = $group_id;
$params['title']       = 'MoinMoinWiki';
$params['pagename']    = $pluginname;
$params['sectionvals'] = array($group->getPublicName());

site_project_header($params);

if (file_exists(forge_get_config('data_path').'/plugins/moinmoin/wikidata/'.$group->getUnixName().'.py')) {
	htmlIframe('/plugins/moinmoin/'.$group->getUnixName().'/FrontPage');
} else {
	echo $HTML->information(_('Wiki not created yet, please wait for a few minutes.'));
}

site_project_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
