<?php

/**
 * extratab -> headermenu migration script
 *
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';

$pm = plugin_manager_get_object();

if ($pm->PluginIsInstalled('extratabs')) {
	$used = false;
	$groupnames = db_query_params('SELECT g.group_name FROM plugins p, group_plugin gp, groups g WHERE plugin_name = $1 and gp.group_id = g.group_id and p.plugin_id = gp.plugin_id',
		array('extratabs'));
	if ($groupnames) {
		if (db_numrows($groupnames) > 0) {
			$used = true;
		}
	}

	if ($used) {
		// is headermenu activated ?
		// no ... -> activate the plugin
		if (!$pm->PluginIsInstalled('headermenu')) {
			$pm->activate('headermenu');
			$pm->LoadPlugin('headermenu');
			$pluginHeaderMenu = $pm->GetPluginObject('headermenu');
			$pluginHeaderMenu->install();
		} else {
			$pm->LoadPlugin('headermenu');
			$pluginHeaderMenu = $pm->GetPluginObject('headermenu');
		}

		// loop on the list of groups using extratab
		while ($arrGroupNames = db_fetch_array($groupnames)) {
			// -> register headermenu for these groups
			$projectObject = group_get_object_by_publicname($arrGroupNames['group_name']);
			$projectObject->setPluginUse('headermenu');

			$extratabsDesc = db_query_params('SELECT * FROM plugin_extratabs_main WHERE group_id=$1', array($projectObject->getID()));
			if (db_numrows($extratabsDesc) > 0) {
				while($arrExtraTabsDesc = db_fetch_array($extratabsDesc)) {
					$url = $arrExtraTabsDesc['tab_url'];
					$name = $arrExtraTabsDesc['tab_name'];
					$description = $arrExtraTabsDesc['tab_name'];
					$linkmenu = 'groupmenu';
					$linktype = ($arrExtraTabsDesc['type']? 'iframe' : 'url');
					$project = $projectObject->getID();
					$ordering = $arrExtraTabsDesc['index'];
					$pluginHeaderMenu->addLink($url, $name, $description, $linkmenu, $linktype, $project, '', $ordering);
				}
			}
			$projectObject->setPluginUse('extratabs', false);
		}
	}
	$pm->deactivate('extratabs');
	// Remove the symbolic link made if plugin has a www.
	if (is_dir(forge_get_config('plugins_path') . '/extratabs/www')) { // if the plugin has a www dir delete the link to it
		if (file_exists('../www/plugins/extratabs')) {
			$result = unlink('../www/plugins/extratabs');
			if (!$result) {
				$feedback .= "\n"._('Soft link wasn\'t removed in www/plugins folder, please do so manually.');
			}
		}
	}

	// Remove the symbolic link made if plugin has a config.
	if (file_exists(forge_get_config('config_path'). '/plugins/headermenu')) {
		$result = unlink(forge_get_config('config_path'). '/plugins/headermenu'); // the apache group or user should have write perms in forge_get_config('config_path')/plugins folder...
		if (!$result) {
			$feedback .= _('Soft link wasn\'t removed in config folder, please do so manually.');
		}
	}
}
echo "SUCCESS\n";
exit(0);