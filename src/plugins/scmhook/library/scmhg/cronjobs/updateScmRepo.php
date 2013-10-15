<?php
/**
 * Copyright 2012, Franck Villaume - TrivialDev
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
 * you need to implement only function updateScmRepo($params)
 * $params is an array containing :
 *	$params['group_id'] = $group_id
 *	$params['hooksString'] = list of hooks to be deploy, separator is |
 *	$params['scm_root'] = directory containing the scm repository
 */

class ScmHgUpdateScmRepo {
	/**
	 * updateScmRepo - update the scmrepo with the new hooks
	 *
	 * @param  Array   $params the complete array description
	 * @return boolean    success or not
	*/
	function updateScmRepo($params) {
		$group_id = $params['group_id'];
		$hooksString = $params['hooksString'];
		$scmdir_root = $params['scm_root'];
		$group = group_get_object($group_id);
		$scmhookPlugin = new scmhookPlugin;
		$hooksAvailable = $scmhookPlugin->getAvailableHooks($group_id);
		$unixname = $group->getUnixName();
		if (is_dir($scmdir_root)) {
			$hooksServePushPullBundle = array();
			foreach ($hooksAvailable as $hook) {
				switch ($hook->getHookType()) {
					case "serve-push-pull-bundle": {
						$hooksServePushPullBundle[] = $hook;
						break;
					}
					default: {
						//byebye hook.... we do not know you...
						break;
					}
				}
			}
			//first we disable all hooks
			foreach($hooksServePushPullBundle as $hookServePushPullBundle) {
				$hookServePushPullBundle->disable($group);
			}
			//now we enable new available hooks
			$newHooks = explode('|', $hooksString);
			if (count($newHooks)) {
				$newHooksServePushPullBundle = array();
				foreach($newHooks as $newHook) {
					foreach($hooksServePushPullBundle as $hookServePushPullBundle) {
						if ($hookServePushPullBundle->getClassname() == $newHook) {
							$newHooksServePushPullBundle[] = $hookServePushPullBundle;
						}
					}
				}
			}
			if (isset($newHooksServePushPullBundle) && count($newHooksServePushPullBundle)) {
				foreach($newHooksServePushPullBundle as $newHookServePushPullBundle) {
					$newHookServePushPullBundle->enable($group);
				}
			}
			return true;
		}
		return false;
	}
}
