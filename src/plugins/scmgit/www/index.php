<?php
/**
 * scmgit plugin
 *
 * Copyright 2010, Roland Mas <lolando@debian.org>
 * Copyright © 2012
 *	Thorsten Glaser <t.glaser@tarent.de>
 * All rights reserved.
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once '../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';

$plugin = plugin_get_object('scmgit');
$plugin_id = $plugin->getID();

$func = getStringFromRequest('func');

$matches = array();
if (preg_match('!^grouppage/([a-z][-a-z0-9_]+)(/.*)$!', $func, $matches)) {
	$grp = util_ifsetor($matches[1]);
	if ($grp) {
		$grp = group_get_object_by_name($grp);
	}
	if ($grp && is_object($grp) && !$grp->isError()) {
		session_redirect('/projects/' . $grp->getUnixName() . '/');
	}
	exit_error(sprintf(_('Cannot locate group for func=%s'), $func), 'scm');
}

switch ($func) {
case 'request-personal-repo':
	$group_id = getIntFromRequest('group_id');
	if (session_loggedin() && forge_check_perm('scm', $group_id, 'read')) {
		$user = session_get_user(); // get the session user
		$result = db_query_params('SELECT * FROM scm_personal_repos WHERE group_id=$1 AND user_id=$2 AND plugin_id=$3',
					array($group_id,
						$user->getID(),
						$plugin_id));
		if ($result && db_numrows($result) == 1) {
			scm_header(array('title' => _('SCM Repository'), 'group' => $group_id));
			echo _('You have already requested a personal Git repository for this project.  If it does not exist yet, it will be created shortly.');
			scm_footer();
			exit;
		}

		$glist = $user->getGroups();
		foreach ($glist as $g) {
			if ($g->getID() == $group_id) {
				$result = db_query_params('INSERT INTO scm_personal_repos (group_id, user_id, plugin_id) VALUES ($1,$2,$3)',
							array ($group_id,
								$user->getID(),
								$plugin_id));

				scm_header(array('title' => _('SCM Repository'), 'group' => $group_id));
				echo _('You have now requested a personal Git repository for this project.  It will be created shortly.');
				scm_footer();
				exit;
			}
		}
	}
	exit_no_group();
	break;
default:
	exit_missing_param();
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
