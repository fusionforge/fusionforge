<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

class ScmSvnUpdateScmRepo {
	function __construct() {
		return true;
	}

	/**
	* updateScmRepo - update the scmrepo with the new hooks
	*
	* @params	Array	the complete array description
	* @return	boolean	success or not
	*/
	function updateScmRepo($params) {
		$group_id = $params['group_id'];
		$hooksString = $params['hooksString'];
		$svndir_root = $params['scm_root'];
		$group = group_get_object($group_id);
		$scmhookPlugin = new scmhookPlugin;
		$hooksAvailable = $scmhookPlugin->getAvailableHooks($group_id);
		$unixname = $group->getUnixName();

		if (is_dir($svndir_root)) {
			@unlink($svndir_root.'/hooks/pre-commit');
			@unlink($svndir_root.'/hooks/post-commit');
			$hooksPreCommit = array();
			$hooksPreRevPropChange = array();
			$hooksPostCommit = array();
			foreach ($hooksAvailable as $hook) {
				switch ($hook->getHookType()) {
					case "pre-commit": {
						$hooksPreCommit[] = $hook;
						break;
					}
					case "pre-revprop-change": {
						$hooksPreRevPropChange[] = $hook;
						break;
					}
					case "post-commit": {
						$hooksPostCommit[] = $hook;
						break;
					}
					default: {
						//byebye hook.... we do not validate you...
						break;
					}
				}
			}

			foreach($hooksPreCommit as $hookPreCommit) {
				if ($hookPreCommit->needCopy()) {
					foreach($hookPreCommit->getFiles() as $hookPreCommitFile) {
						@unlink($svndir_root.'/hooks/'.basename($hookPreCommitFile));
					}
				}
			}

			foreach($hooksPreRevPropChange as $hook) {
				if ($hook->needCopy()) {
					foreach($hook->getFiles() as $file) {
						@unlink($svndir_root.'/hooks/'.basename($file));
					}
				}
			}

			foreach($hooksPostCommit as $hookPostCommit) {
				if ($hookPostCommit->needCopy()) {
					foreach($hookPostCommit->getFiles() as $hookPostCommitFile) {
						@unlink($svndir_root.'/hooks/'.basename($hookPostCommitFile));
					}
				}
			}

			$newHooks = explode('|', $hooksString);
			if (count($newHooks)) {
				$newHooksPreCommit = array();
				$newHooksPreRevPropChange = array();
				$newHooksPostCommit = array();
				foreach($newHooks as $newHook) {
					foreach($hooksPreCommit as $hookPreCommit) {
						if ($hookPreCommit->getClassname() == $newHook) {
							$newHooksPreCommit[] = $hookPreCommit;
						}
					}
					foreach($hooksPreRevPropChange as $hook) {
						if ($hook->getClassname() == $newHook) {
							$newHooksPreRevPropChange[] = $hook;
						}
					}
					foreach($hooksPostCommit as $hookPostCommit) {
						if ($hookPostCommit->getClassname() == $newHook) {
							$newHooksPostCommit[] = $hookPostCommit;
						}
					}
				}
			}

			foreach($newHooksPreCommit as $newHookPreCommit) {
				if ($newHookPreCommit->needCopy()) {
					foreach ($newHookPreCommit->getFiles() as $file) {
						copy($file, $svndir_root.'/hooks/'.basename($file));
						chmod($svndir_root.'/hooks/'.basename($file), 0755);
					}
				}
			}

			foreach($hooksPreRevPropChange as $newHook) {
				if ($newHook->needCopy()) {
					foreach ($newHook->getFiles() as $file) {
						copy($file, $svndir_root.'/hooks/'.basename($file));
						chmod($svndir_root.'/hooks/'.basename($file), 0755);
					}
				}
			}

			foreach($newHooksPostCommit as $newHookPostCommit) {
				if ($newHookPostCommit->needCopy()) {
					foreach ($newHookPostCommit->getFiles() as $file) {
						copy($file, $svndir_root.'/hooks/'.basename($file));
						chmod($svndir_root.'/hooks/'.basename($file), 0755);
					}
				}
			}

			if (count($newHooksPreCommit)) {
				// prepare the pre-commit
				$file = fopen("/tmp/pre-commit-$unixname.tmp", "w");
				fwrite($file, file_get_contents(dirname(__FILE__).'/../skel/pre-commit/head'));
				$string = '';
				foreach($newHooksPreCommit as $newHookPreCommit) {
					$string .= $newHookPreCommit->getHookCmd()."\n";
				}
				$string .= "\n";
				fwrite($file, $string);
				fclose($file);
				copy('/tmp/pre-commit-'.$unixname.'.tmp', $svndir_root.'/hooks/pre-commit');
				chmod($svndir_root.'/hooks/pre-commit', 0755);
				unlink('/tmp/pre-commit-'.$unixname.'.tmp');
			} else {
				@unlink($svndir_root.'/hooks/pre-commit');
			}

			if (count($newHooksPreRevPropChange)) {
				// prepare the pre-revprop-change
				$file = fopen("/tmp/pre-revprop-change-$unixname.tmp", "w");
				fwrite($file, file_get_contents(dirname(__FILE__).'/../skel/pre-revprop-change/head'));
				$string = '';
				foreach($newHooksPreRevPropChange as $hook) {
					$string .= $hook->getHookCmd()."\n";
				}
				$string .= "\n";
				fwrite($file, $string);
				fclose($file);
				copy('/tmp/pre-revprop-change-'.$unixname.'.tmp', $svndir_root.'/hooks/pre-revprop-change');
				chmod($svndir_root.'/hooks/pre-revprop-change', 0755);
				unlink('/tmp/pre-revprop-change-'.$unixname.'.tmp');
			} else {
				@unlink($svndir_root.'/hooks/pre-revprop-change');
			}

			if (count($newHooksPostCommit)) {
				// prepare the post-commit
				$file = fopen("/tmp/post-commit-$unixname.tmp", "w");
				fwrite($file, file_get_contents(dirname(__FILE__).'/../skel/post-commit/head'));
				$string = '';
				foreach($newHooksPostCommit as $newHookPostCommit) {
					$string .= $newHookPostCommit->getHookCmd()."\n";
				}
				$string .= "\n";
				fwrite($file, $string);
				fclose($file);
				copy('/tmp/post-commit-'.$unixname.'.tmp', $svndir_root.'/hooks/post-commit');
				chmod($svndir_root.'/hooks/post-commit', 0755);
				unlink('/tmp/post-commit-'.$unixname.'.tmp');
			}
			return true;
		}
		return false;
	}
}
