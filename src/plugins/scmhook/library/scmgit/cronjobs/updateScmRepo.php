<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014, Benoit Debaenst - Trivialdev
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

class ScmGitUpdateScmRepo {
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
		$gitdir_root = $params['scm_root'];
		$group = group_get_object($group_id);
		$scmhookPlugin = new scmhookPlugin;
		$hooksAvailable = $scmhookPlugin->getAvailableHooks($group_id);
		$unixname = $group->getUnixName();
		if (is_dir($gitdir_root)) {
			@unlink($gitdir_root.'/hooks/post-receive');
			$hooksPostReceive = array();
			foreach ($hooksAvailable as $hook) {
				switch ($hook->getHookType()) {
					case "post-receive": {
						$hooksPostReceive[] = $hook;
						break;
					}
					default: {
						//byebye hook.... we do not validate you...
						break;
					}
				}
			}

			foreach($hooksPostReceive as $hookPostReceive) {
				if ($hookPostReceive->needCopy()) {
					foreach($hookPostReceive->getFiles() as $hookPostReceiveFile) {
						@unlink($gitdir_root.'/hooks/'.basename($hookPostReceiveFile));
					}
				}
			}

			$newHooks = explode('|', $hooksString);
			if (count($newHooks)) {
				$newHooksPostReceive = array();
				foreach($newHooks as $newHook) {
					foreach($hooksPostReceive as $hookPostReceive) {
						if ($hookPostReceive->getClassname() == $newHook) {
							$newHooksPostReceive[] = $hookPostReceive;
						}
					}
				}
			}

			foreach($newHooksPostReceive as $newHookPostReceive) {
				if ($newHookPostReceive->needCopy()) {
					foreach ($newHookPostReceive->getFiles() as $file) {
						copy($file, $gitdir_root.'/hooks/'.basename($file));
						chmod($gitdir_root.'/hooks/'.basename($file), 0755);
					}
				}
			}

			if (count($newHooksPostReceive)) {
				// prepare the post-receive
				$file = fopen("/tmp/post-receive-$unixname.tmp", "w");
				fwrite($file, file_get_contents(dirname(__FILE__).'/../skel/post-receive/head'));
				$string = '';

				foreach($newHooksPostReceive as $newHookPostReceive) {
					$string .= $newHookPostReceive->getHookCmd()."\n";
				}

				$string .= "\n";
				fwrite($file, $string);
				fclose($file);

				copy('/tmp/post-receive-'.$unixname.'.tmp', $gitdir_root.'/hooks/post-receive');
				chmod($gitdir_root.'/hooks/post-receive', 0755);
				unlink('/tmp/post-receive-'.$unixname.'.tmp');

				if (! preg_grep("/mailinglist/",file($gitdir_root.'/config'))) {
					copy($gitdir_root.'/config',$gitdir_root.'/config.backup');
					$file = fopen("$gitdir_root/config", "a");
					$string = "[hooks]\n";
					$string .= "\tmailinglist = ".$unixname.'-commits@'.forge_get_config('lists_host')."\n";
					$string .= "\temailprefix = \"[".$unixname.'-commits] "'."\n";
					fwrite($file, $string);
					fclose($file);
				}

			} else {
				@unlink($gitdir_root.'/hooks/post-receive');
			}
			return true;
		}
		return false;
	}
}
