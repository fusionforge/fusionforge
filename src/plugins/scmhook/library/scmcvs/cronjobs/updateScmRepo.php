<?php
/**
 * Copyright (C) 2014 Philipp Keidel - EDAG Engineering AG
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

class ScmCvsUpdateScmRepo {
	/**
	 * updateScmRepo - update the scmrepo with the new hooks
	 *
	 * @params	Array	the complete array description
	 * @return	boolean	success or not
	 */
	function updateScmRepo($params) {
		$group_id = $params['group_id'];
		$hooksString = $params['hooksString'];
		$cvsdir_root = $params['scm_root'];
		$group = group_get_object($group_id);
		$scmhookPlugin = new scmhookPlugin;
		$hooksAvailable = $scmhookPlugin->getAvailableHooks($group_id);
		$unixname = $group->getUnixName();

		if (is_dir($cvsdir_root)) {
			@unlink($cvsdir_root.'/hooks/pre-commit');
			@unlink($cvsdir_root.'/hooks/post-commit');
			$hooksPostCommit = array();
			foreach ($hooksAvailable as $hook) {
				switch ($hook->getHookType()) {
					case 'post-commit': {
						$hooksPostCommit[] = $hook;
						break;
					}
					default: {
						//byebye hook.... we do not validate you...
						break;
					}
				}
			}
			foreach($hooksPostCommit as $hookPostCommit) {
				if ($hookPostCommit->needCopy()) {
					foreach($hookPostCommit->getFiles() as $hookPostCommitFile) {
						@unlink($cvsdir_root.'/hooks/'.basename($hookPostCommitFile));
					}
				}
			}

			$newHooks = explode('|', $hooksString);
			if (count($newHooks)) {
				$newHooksPostCommit = array();
				foreach($newHooks as $newHook) {
					foreach($hooksPostCommit as $hookPostCommit) {
						if ($hookPostCommit->getClassname() == $newHook) {
							$newHooksPostCommit[] = $hookPostCommit;
						}
					}
				}
			}
			foreach($newHooksPostCommit as $newHookPostCommit) {
				if ($newHookPostCommit->needCopy()) {
					foreach ($newHookPostCommit->getFiles() as $file) {
						copy($file, $cvsdir_root.'/hooks/'.basename($file));
						chmod($cvsdir_root.'/hooks/'.basename($file), 0755);
					}
				}
			}

			$loginfo = "$cvsdir_root/CVSROOT/loginfo";
			if (count($newHooksPostCommit)) {
				// Befehl in /CVSROOT/loginfo eintragen
				$content = file_get_contents($loginfo);
				$add     = '';
				foreach($newHooksPostCommit as $newHookPostCommit) {
					// Wenn der Befehl noch nicht vorkommt, dann hinzufügen
					if(strpos($content, $newHookPostCommit->getHookCmd()) === false) {
						$add .= $newHookPostCommit->getHookCmd()."\n";
					}
				}
				file_put_contents($loginfo, trim($content.$add));
			} else 	{
				// Befehl aus /CVSROOT/loginfo entfernen
				$content = file_get_contents($loginfo);
				file_put_contents($loginfo, "");
				$oldLines = explode("\n", $content);
				foreach($oldLines as $line) {
					if(substr($line, 0, 1) == "#" || strpos($line, "cvs_wrapper.php") === false) {
						file_put_contents($loginfo, "$line\n", FILE_APPEND);
					}
				}
			}
			return true;
		}
		return false;
	}
}
