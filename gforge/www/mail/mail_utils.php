<?php
/**
 * GForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003 (c) Guillaume Smet
 *
 * @version   $Id$
 *
 */


function mail_header($params) {
	global $Language, $group_id;

	if ($group_id) {
		//required for site_project_header
		$params['group'] = $group_id;
		$params['toptab'] = 'mail';

		$project =& group_get_object($group_id);

		if ($project && is_object($project)) {
			if (!$project->usesMail()) {
				exit_error($Language->getText('general', 'error'), $Language->getText('mail_utils', 'turned_off'));
			}
		}


		site_project_header($params);
		echo '<p><strong><a href="/mail/admin/?group_id='.$group_id.'">'.$Language->getText('mail_utils', 'admin').'</a></strong></p>';
	} else {
		$params['toptab'] = 'mail';
		site_project_header($params);
	}
}

function mail_footer($params) {
	site_project_footer($params);
}

?>