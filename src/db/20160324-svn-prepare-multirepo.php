<?php
/**
 * Move SVN repos to the new structure to support multi SVN repositories
 * Copyright, 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';

$svn_root = forge_get_config('repos_path', 'scmsvn');
if (is_dir($svn_root)) {
	if ($svn_opendir = opendir($svn_root)) {
		while (($svn_repodir = readdir($svn_opendir)) !== false) {
			$keep = true;
			//check if this is a real repo with a project. reponame = unix_group_name
			$group = group_get_object_by_name($svn_repodir);
			if (!$group || !is_object($group) || $group->isError()) {
				$keep = false;
			}
			if ($keep) {
				if (mkdir($svn_root.'/'.$svn_repodir.'.svn')) {
					if (!rename($svn_root.'/'.$svn_repodir, $svn_root.'/'.$svn_repodir.'.svn/'.$svn_repodir)) {
						echo "UNABLE TO MOVE TO FINAL DESTINATION REPO: ".$svn_repodir."\n";
					} else {
						rename($svn_root.'/'.$svn_repodir.'.svn', $svn_root.'/'.$svn_repodir);
					}
				} else {
					echo "UNABLE TO CREATE TARGET DIR FOR REPO: ".$svn_repodir."\n";
				}
			}
		}
	}
}
echo "SUCCESS\n";
