<?php
/**
 * Apply permissions to unify ssh and web access
 * Copyright (C) 2015  Inria (Sylvain Beucler)
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
require_once $gfcommon.'include/system/pgsql.class.php';

$verbose = (count($argv) > 1 and $argv[1] == '--verbose');

// Get the name of each group + the gid
// (avoids querying nss for all groups, which sometimes fails due to
// transient sql/network error or deleted projects)
$res = db_query_params("SELECT name, group_id, gid, perm_val AS anon FROM nss_groups
  LEFT JOIN pfo_role_setting ON (nss_groups.group_id = pfo_role_setting.ref_id
                                 AND pfo_role_setting.role_id=$1 AND pfo_role_setting.section_name=$2)
  WHERE gid < $3
  ORDER BY name", array(1, 'scm', 20000));

// Store everything in RAM to avoid a 3h-long SQL connection
while ($row = db_fetch_array($res))
    $groups[] = $row;

$svnroot = forge_get_config('repos_path', 'scmsvn');
$gitroot = forge_get_config('repos_path', 'scmgit');
foreach ($groups as $group) {
    $gname = $group['name'];
    $gid_ro = $group['group_id'] + $SYS->GID_ADD_SCMRO;
    $gid_rw = $group['group_id'] + $SYS->GID_ADD_SCMRW;
    if ($verbose) print "$gname\n";
    
    $repo = "$svnroot/$gname";
    if (is_dir($repo)) {
        chmod($repo, $group['anon'] ? 02755 : 02750);
        system("chown -Rh root:{$gid_rw} $repo");
        system("chown  -h root:{$gid_ro} $repo");
        system("find $repo/* -type d -print0 | xargs -r -0 chmod 2775");
        system("chmod -R g+rwX,o+rX-w $repo/*");
    }
    $repo = '/nonexistent';  // for safety

    $projroot = "$gitroot/$gname";
    if (is_dir("$projroot")) {
        chmod($projroot, $group['anon'] ? 02755 : 02750);

        if (is_dir("$projroot/users")) {
            chmod("$projroot/users", 00755);
            foreach (glob("$projroot/users/*") as $userrepo) {
				if (is_dir($userrepo)) {
					$matches = preg_match(":/users/([^/]+)/:", $userrepo);
					$user = $matches[1];
					system("chown -hR $user:root $userrepo");
					system("chmod -R g+rX-sw,o+rX-w $userrepo");
				}
            }
        }

        system("chown  -h root:{$gid_ro} $projroot");
        system("chown -Rh root:{$gid_rw} $projroot/*.git");
        system("find $projroot/*.git -type d -print0 | xargs -r -0 chmod 2775");
        system("chmod -R g+rwX,o+rX-w $projroot/*.git");
    }
}

echo "SUCCESS\n";
