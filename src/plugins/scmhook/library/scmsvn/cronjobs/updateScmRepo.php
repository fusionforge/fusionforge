<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
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

/**
 * scmsvn_updateScmRepo - update the scmrepo with the new hooks
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
		foreach($hooksAvailable['pre-commit'] as $hookAvailable) {
			@unlink($svndir_root.'/hooks/'.$hookAvailable);
		}
		foreach($hooksAvailable['post-commit'] as $hookAvailable) {
			@unlink($svndir_root.'/hooks/'.$hookAvailable);
		}

		$newHooks = explode('|', $hooksString);
		foreach($newHooks as $newHook) {
			if (stristr($newHook, 'pre-commit')) {
				$filename = preg_replace('/pre-commit_/','',$newHook);
				copy(dirname(__FILE__).'/../hooks/pre-commit/'.$filename, $svndir_root.'/hooks/'.$filename);
				chmod($svndir_root.'/hooks/'.$filename, 0755);
			}
			if (stristr($newHook, 'post-commit')) {
				$filename = preg_replace('/post-commit_/','',$newHook);
				copy(dirname(__FILE__).'/../hooks/post-commit/'.$filename, $svndir_root.'/hooks/'.$filename);
				chmod($svndir_root.'/hooks/'.$filename, 0755);
			}
		}
		// prepare the pre-commit
		$file = fopen("/tmp/pre-commit-$unixname.tmp", "w");
		fwrite($file, file_get_contents(dirname(__FILE__).'/../skel/pre-commit/pre-commit.head'));
		$loopid = 0;
		$string = '';
		foreach($newHooks as $newHook) {
			if (stristr($newHook, 'pre-commit.')) {
				if ($loopid) {
					//insert && \ between commands
					$string .= ' && ';
				}
				$string .= rtrim(file_get_contents(dirname(__FILE__).'/../skel/pre-commit/'.$newHook));
				$loopid = 1;
			}
		}
		$string .= "\n";
		fwrite($file, $string);
		fclose($file);
		copy('/tmp/pre-commit-'.$unixname.'.tmp', $svndir_root.'/hooks/pre-commit');
		chmod($svndir_root.'/hooks/pre-commit', 0755);
		unlink('/tmp/pre-commit-'.$unixname.'.tmp');

		// prepare the post-commit
		$file = fopen("/tmp/post-commit-$unixname.tmp", "w");
		fwrite($file, file_get_contents(dirname(__FILE__).'/../skel/post-commit/post-commit.head'));
		$loopid = 0;
		$string = '';
		foreach($newHooks as $newHook) {
			if (stristr($newHook, 'post-commit.')) {
				if ($loopid) {
					//insert && \ between commands
					$string .= ' && ';
				}
				$string .= rtrim(file_get_contents(dirname(__FILE__).'/../skel/post-commit/'.$newHook));
				$loopid = 1;
			}
		}
		$string .= "\n";
		fwrite($file, $string);
		fclose($file);
		copy('/tmp/post-commit-'.$unixname.'.tmp', $svndir_root.'/hooks/post-commit');
		chmod($svndir_root.'/hooks/post-commit', 0755);
		unlink('/tmp/post-commit-'.$unixname.'.tmp');
		return true;
	}
	return false;
}

?>
