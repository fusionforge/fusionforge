<?php
/**
 * SCM Frontend
 *
 * Copyright 2004 (c) Roland Mas, Tim Perdue -GForge LLC
 * Copyright 2013,2018, Franck Villaume - TrivialDev
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

global $HTML;

$group_id = getIntFromRequest("group_id");
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}

session_require_perm('scm', $group_id, 'read');

// Check if there is an associated scm plugin and issue a warning if none.
$scm_plugins = array();
foreach (PluginManager::instance()->getPlugins() as $p) {
	$plugin = PluginManager::instance()->GetPluginObject($p);
	if (isset($plugin->provides['scm']) && $plugin->provides['scm'] && $group->usesPlugin($p)) {
		$scm_plugins[] = $plugin;
	}
}
if (count($scm_plugins) == 0) {
	$warning_msg = _("This project has no associated Source Code Management tool defined, please configure one using the Administration submenu.");
}

scm_header(array('title'=> sprintf(_('Source Code Repository for %s'), $group->getPublicName()), 'group' => $group_id, 'inframe' => 0));

plugin_hook("blocks", "scm index");

if (forge_get_config('allow_multiple_scm') && (count($scm_plugins) > 1)) {
	$elementsLi = array();
	foreach ($scm_plugins as $scm_plugin) {
		$elementsLi[] = array('content' => util_make_link('#tabber-'.$scm_plugin->name, $scm_plugin->text, false, true));
	}
	echo html_ao('div', array('id' => 'tabberid'));
	echo $HTML->html_list($elementsLi);
}

$hook_params = array();
$hook_params['group_id'] = $group_id;
$hook_params['allow_multiple_scm'] = count($scm_plugins);
plugin_hook("scm_page", $hook_params);

if (forge_get_config('allow_multiple_scm') && (count($scm_plugins) > 1)) {
	echo html_ac(html_ap() - 1);
}

echo html_e('script', array('type'=>'text/javascript'), '//<![CDATA['."\n".'jQuery("[id^=tabber]").tabs();'."\n".'//]]>');

scm_footer();
