#!/usr/bin/php
<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012-2013, Franck Villaume - TrivialDev
 * Copyright 2013, Benoit Debaenst - TrivialDev
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

/**
 * main cronjob for scmhook plugin
 */

require dirname(__FILE__).'/../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';
require_once $gfplugins.'scmhook/common/scmhookPlugin.class.php';

// if you want debug output, uncomment the verbose variable.
//$verbose = true;

############
###### START

$res = db_query_params('SELECT systask_id, group_id, user_name FROM systasks
	JOIN users ON (systasks.user_id = users.user_id)
	WHERE systasks.status=$1 AND systask_type=$2',
	array('WIP', 'SCMHOOK_UPDATE'));
while ($task = db_fetch_array($res)) {
	$group_id = $task['group_id'];
	$user_name = $task['user_name'];
	// Run as the requesting user to avoid symlinks attacks
	if (util_sudo_effective_user($user_name, 'install_hooks', array($group_id)) == false) {
		cron_debug("ERROR scmhook: couldn't run install_hooks as user $user_name");
	}
}

$group = null;  // pass info to library/ scripts
function install_hooks($params) {
	global $group, $gfplugins;

	$group_id = $params[0];
	// get the list of project to be updated
	$res = db_query_params('SELECT groups.group_id, groups.scm_box, plugin_scmhook.hooks
			FROM groups, plugin_scmhook
			WHERE groups.status = $1
			AND plugin_scmhook.id_group = groups.group_id
			AND plugin_scmhook.need_update = $2
			AND groups.use_scm = $3
            AND group_id = $4',
		array('A', 1, 1, $group_id));

	if (! $res) {
		cron_debug("FATAL Database Query Failed: " . db_error());
	}

	$scmhookPlugin = new scmhookPlugin;
	while ($row = db_fetch_array($res)) {
		$group_id = $row['group_id'];
		$scm_box = $row['scm_box'];
		$scmtype = '';
		// find the scm type of the project
		$listScm = $scmhookPlugin->getListLibraryScm();
		$group = group_get_object($group_id);
		for ($i = 0; $i < count($listScm); $i++) {
			if ($group->usesPlugin($listScm[$i])) {
				$scmtype = $listScm[$i];
				continue;
			}
		}
		$returnvalue = true;
		// call the right cronjob in the library
		switch ($scmtype) {
		case 'scmsvn':
			cron_debug("INFO start updating hooks for project ".$group->getUnixName());
			require_once $gfplugins.'scmhook/library/'.$scmtype.'/cronjobs/updateScmRepo.php';
			$scmsvncronjob = new ScmSvnUpdateScmRepo();
			$params = array();
			$params['group_id'] = $group_id;
			$params['hooksString'] = $row['hooks'];
			$params['scm_root'] = forge_get_config('repos_path', 'scmsvn') . '/' . $group->getUnixName();
			if ($scmsvncronjob->updateScmRepo($params)) {
				$res_update = db_query_params('UPDATE plugin_scmhook set need_update = $1 where id_group = $2', array(0, $group_id));
				if (!$res_update) {
					$returnvalue = false;
				}
			}
			break;

		case 'scmhg':
			cron_debug("INFO start updating hooks for project ".$group->getUnixName());
			require_once $gfplugins.'scmhook/library/'.$scmtype.'/cronjobs/updateScmRepo.php';
			$scmhgcronjob = new ScmHgUpdateScmRepo();
			$params = array();
			$params['group_id'] = $group_id;
			$params['hooksString'] = $row['hooks'];
			$params['scm_root'] = forge_get_config('repos_path', 'scmhg') . '/' . $group->getUnixName();
			if ($scmhgcronjob->updateScmRepo($params)) {
				$res_update = db_query_params('UPDATE plugin_scmhook set need_update = $1 where id_group = $2', array(0, $group_id));
				if (!$res_update) {
					$returnvalue = false;
				}
			}
			break;

		case 'scmgit':
			cron_debug("INFO start updating hooks for project ".$group->getUnixName());
			require_once $gfplugins.'scmhook/library/'.$scmtype.'/cronjobs/updateScmRepo.php';
			$scmgitcronjob = new ScmGitUpdateScmRepo();
			$params = array();
			$params['group_id'] = $group_id;
			$params['hooksString'] = $row['hooks'];
			$params['scm_root'] = forge_get_config('repos_path', 'scmgit') . '/' . $group->getUnixName() . '/' . $group->getUnixName() . '.git' ;
			if ($scmgitcronjob->updateScmRepo($params)) {
				$res_update = db_query_params('UPDATE plugin_scmhook set need_update = $1 where id_group = $2', array(0, $group_id));
				if (!$res_update) {
					$returnvalue = false;
				}
			}
			break;

		default:
			cron_debug("WARNING No scm plugin found for this project ".$group->getUnixName()." or no cronjobs for this type");
			$returnvalue = false;
			break;
		}

		if ($returnvalue) {
			cron_debug("INFO hooks updated for project ".$group->getUnixName());
		} else {
			cron_debug("ERROR Unable to update hooks for project ".$group->getUnixName());
		}
	}
}

cron_debug("INFO end of updateScmRepo main cronjob");

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
