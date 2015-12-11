<?php

/**
 * svntracker -> scmhook commitTracker migration script
 *
 * Copyright 2013, Franck Villaume - TrivialDev
 * Copyright 2015, Benoit Debaenst - TrivialDev
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

$used = false;
$groupnames = db_query_params('SELECT g.group_name FROM plugins p, group_plugin gp, groups g WHERE plugin_name = $1 and gp.group_id = g.group_id and p.plugin_id = gp.plugin_id',
	array('svntracker'));

if ($groupnames) {
	if (db_numrows($groupnames) > 0) {
		$used = true;
	}
}

if ($used) {
	if (!is_readable($gfplugins.'scmhook/library/scmsvn/cronjobs/updateScmRepo.php')) {
		echo "FAILED: scmhook is missing. Please install scmhook plugin and rerun the migration script\n";
		exit(1);
	}
	require_once $gfplugins.'scmhook/library/scmsvn/cronjobs/updateScmRepo.php';
	// is scmhook activated ?
	// no ... -> activate the plugin
	if (!$pm->PluginIsInstalled('scmhook')) {
		$pm->activate('scmhook');
	}

	$pm->LoadPlugin('scmhook');
	$pluginScmHook = $pm->GetPluginObject('scmhook');
	$scmsvncronjob = new ScmSvnUpdateScmRepo();
	$pluginScmHook->install();

	while ($arrGroupNames = db_fetch_array($groupnames)) {
		// -> register scmhook for these groups
		$projectObject = group_get_object_by_publicname($arrGroupNames['group_name']);
		if (!$projectObject->usesTracker()) {
			$projectObject->setUseTracker(true);
		}
		$projectId = $projectObject->getID();
		if (!$projectObject->usesPlugin('scmhook')) {
			$projectObject->setPluginUse('scmhook');
			$pluginScmHook->add($projectId);
		}
		$group = $projectObject; // need to be set due to use of global vars in commitEmail class
		$enabledHooks = $pluginScmHook->getEnabledHooks($projectId);
		// -> add committracker hook
		$params = array();
		$params['group_id'] = $projectId;
		$params['scmsvn_commitTracker'] = 1;
		foreach ($enabledHooks as $enableHook) {
			$params[$enableHook] = 1;
		}
		$pluginScmHook->update($params);
		$hooksArray = $pluginScmHook->getEnabledHooks($projectId);
		$params['hooksString'] = implode('|',$hooksArray);
		$params['scm_root'] = forge_get_config('repos_path', 'scmsvn') . '/' . $projectObject->getUnixName();
		if ($scmsvncronjob->updateScmRepo($params)) {
			db_query_params('UPDATE plugin_scmhook set need_update = $1 where id_group = $2', array(0, $projectId));
		}
		$projectObject->setPluginUse('svntracker', false);
	}
	// -> retrieve old data if any
	$res = db_query_params('insert into plugin_scmhook_scmsvn_committracker_data_artifact (kind, group_artifact_id, project_task_id)
				select kind, group_artifact_id, project_task_id FROM plugin_svntracker_data_artifact', array());
	$res = db_query_params('insert into plugin_scmhook_scmsvn_committracker_data_master (holder_id, svn_date, log_text, file, prev_version, actual_version, author)
				select holder_id, svn_date, log_text, file, prev_version, actual_version, author FROM plugin_svntracker_data_master', array());
}

$pm->deactivate('svntracker');
// Remove the symbolic link made if plugin has a www.
if (is_dir(forge_get_config('plugins_path') . '/svntracker/www')) { // if the plugin has a www dir delete the link to it
	if (file_exists('../www/plugins/svntracker')) {
		$result = unlink('../www/plugins/svntracker');
		if (!$result) {
			$feedback .= "\n"._('Soft link wasn\'t removed in www/plugins folder, please do so manually.');
		}
	}
}

// Remove the symbolic link made if plugin has a config.
if (file_exists(forge_get_config('config_path'). '/plugins/svntracker')) {
	$result = unlink(forge_get_config('config_path'). '/plugins/svntracker'); // the apache group or user should have write perms in forge_get_config('config_path')/plugins folder...
	if (!$result) {
		$feedback .= _('Soft link wasn\'t removed in config folder, please do so manually.');
	}
}
echo "SUCCESS\n";
exit(0);
