<?php
/**
 * Move Hg repos to the new structure to support multi Hg repositories
 * Copyright, 2018, Franck Villaume - TrivialDev
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

$hg_root = forge_get_config('repos_path', 'scmhg');
if (is_dir($hg_root) && ($hg_opendir = opendir($hg_root))) {
	while (($hg_repodir = readdir($hg_opendir)) !== false) {
		$keep = true;
		//check if this is a real repo with a project. reponame = unix_group_name
		$group = group_get_object_by_name($hg_repodir);
		if (!$group || !is_object($group) || $group->isError()) {
			$keep = false;
		}
		if ($keep && is_dir($hg_root.'/'.$hg_repodir.'/.hg')) {
			if (mkdir($hg_root.'/'.$hg_repodir.'/'.$hg_repodir)) {
				if (!rename($hg_root.'/'.$hg_repodir.'/.hg', $hg_root.'/'.$hg_repodir.'/'.$hg_repodir.'/.hg')) {
					echo "UNABLE TO MOVE TO FINAL DESTINATION REPO: ".$hg_repodir."\n";
				}
			} else {
				echo "UNABLE TO CREATE TARGET DIR FOR REPO: ".$hg_repodir."\n";
			}
			if (!is_file("$hg_root/$hg_repodir/config")) {
				$f = fopen("$hg_root/$hg_repodirconfig", 'w');
				$conf = "[paths]\n";
				$conf .= '/ = '.$hg_root.'/'.$hg_repodir.'/*'."\n";
				fwrite($f, $conf);
				fclose($f);
			}
		}
	}
}
echo "SUCCESS\n";
