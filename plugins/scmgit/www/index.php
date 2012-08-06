<?php

/*
 * scmgit plugin
 *
 * Copyright 2010, Roland Mas <lolando@debian.org>
 */

require_once ('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';

$func = getStringFromRequest ('func') ;
switch ($func) {
case 'request-personal-repo':
	$group_id = getIntFromRequest ('group_id') ;
	session_require_perm ('scm', $group_id, 'write') ;
	$user = session_get_user(); // get the session user
	$result = db_query_params ('SELECT * FROM plugin_scmgit_personal_repos p WHERE p.group_id=$1 AND p.user_id=$2',
				   array ($group_id,
					  $user->getID())) ;
	if ($result && db_numrows ($result) == 1) {
		scm_header (array ('title' => _('SCM Repository'), 'group' => $group_id)) ;
		echo _('You have already requested a personal Git repository for this project.  If it does not exist yet, it will be created shortly.') ;
		scm_footer();
		exit;
	}

	$glist = $user->getGroups() ;
	foreach ($glist as $g) {
		if ($g->getID() == $group_id) {
			$result = db_query_params ('INSERT INTO plugin_scmgit_personal_repos (group_id, user_id) VALUES ($1,$2)',
						   array ($group_id,
							  $user->getID())) ;

			scm_header (array ('title' => _('SCM Repository'), 'group' => $group_id)) ;
			echo _('You have now requested a personal Git repository for this project.  If will be created shortly.') ;
			scm_footer() ;
			exit ;
		}
	}
	exit_no_group () ;
	break;
default:
	exit_missing_param () ;
}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
