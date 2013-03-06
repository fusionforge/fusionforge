<?php
/**
 * SCM Frontend
 *
 * Copyright 2004 (c) Roland Mas, Tim Perdue -GForge LLC
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';

$group_id = getIntFromRequest("group_id");
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}

session_require_perm('scm', $group_id, 'read');

// Check if there is an associated scm plugin and issue a warning if none.
$scm_plugin = '';
foreach (PluginManager::instance()->GetPlugins() as $p) {
	$plugin = PluginManager::instance()->GetPluginObject($p);
	if (isset($plugin->provides['scm']) && $plugin->provides['scm'] && $group->usesPlugin($p)) {
		$scm_plugin = $p;
	}
}
if (!$scm_plugin) {
	$warning_msg = _("This project has no associated Source Code Management tool defined, please configure one using the Administration submenu.");
}

scm_header(array('title'=> sprintf(_('Source Code Repository for %s'), $group->getPublicName()),'group'=>$group_id));

plugin_hook("blocks", "scm index");

$hook_params = array();
$hook_params['group_id'] = $group_id;
plugin_hook("scm_page", $hook_params);

scm_footer();
