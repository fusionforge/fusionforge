<?php
/**
 *
 * This file is part of Fusionforge.
 * Copyright 2011, Franck Villaume - Capgemini
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

/**
 * main cronjob for scmhook plugin
 */

require dirname(__FILE__).'/../../env.inc.php';

require $gfwww.'include/squal_pre.php';
require_once $gfcommon.'include/cron_utils.php';
require_once $gfplugin.'scmhook/common/scmhookPlugin.class.php';

define("CRON_ID", 21);

function logger($level, $line) {
	cron_logger($level, $line, CRON_ID);
}

function tracer(&$output) {
	cron_tracer($output);
}

############
###### START

// get the list of project to be updated
$res = db_query_params('SELECT groups.group_id, groups.scm_box, plugin_scmhook.hooks
			FROM groups, plugin_scmhook
			WHERE groups.status = $1
			AND plugin_scmhook.id_group = groups.group_id
			AND plugin_scmhook.need_update = $2
			AND groups.use_scm = $3
			AND groups.repo_created = $4',
			array('A', 1, 1, 1));

if (! $res) {
	logger("FATAL", "Database Query Failed: " . db_error());
}

$scmhookPlugin = new scmhookPlugin;
while ($row =& db_fetch_array($res)) {
	$group_id = $row['group_id'];
	$scm_box = $row['scm_box'];
	$scmtype = '';
	// find the scm type of the project
	$listScm = $scmhookPlugin->getListLibraryScm();
	$group = &group_get_object($group_id);
	for ($i = 0; $i < count($listScm); $i++) {
		if ($group->usesPlugin($listScm[$i])) {
			$scmtype = $listScm[$i];
			continue;
		}
	}
	$returnvalue = true;
	// call the right cronjob in the library
	switch ($scmtype) {
		case 'scmsvn': {
			logger("INFO", "start updating hooks for project ".$group->getUnixName());
			include $gfplugins.'scmhook/library/'.$scmtype.'/cronjobs/updateScmRepo.php';
			global $svndir_prefix;
			$params = array();
			$params['group_id'] = $group_id;
			$params['hooksString'] = $row['hooks'];
			$params['scm_root'] = $svndir_prefix;

			if (updateScmRepo($params)) {
				$res = db_query_params('UPDATE plugin_scmhook set need_update = $1 where id_group = $2', array(0, $group_id));
				if (!$res) {
					$returnvalue = false;
				}
			}
			break;
		}
		default: {
			logger("WARNING", "No scm plugin found for this project ".$group->getUnixName()." or no cronjobs for thi type");
			$returnvalue = false;
			break;
		}
	}

	if ($returnvalue) {
		logger("INFO", "hooks updated for project ".$group->getUnixName());
	} else {
		logger("ERROR", "Unable to update hooks for project ".$group->getUnixName());
	}
}

?>
