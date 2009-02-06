<?php
/**
 * GForge SCM Library
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2005-04-16
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function scm_header($params) {
	global $HTML, $sys_use_scm;
	if (!$sys_use_scm) {
		exit_disabled();
	}

	$project =& group_get_object($params['group']);
	if (!$project || !is_object($project)) {
		exit_error('Error','Could Not Get Project');
	} elseif ($project->isError()) {
		exit_error('Error',$project->getErrorMessage());
	}

	if (!$project->usesSCM()) {
		exit_error('Error',_('Error - This project has turned off SCM.'));
	}
	site_project_header(array('title'=>_('SCM Repository'),'group'=>$params['group'],'toptab'=>'scm',));
	/*
		Show horizontal links
	*/
	if (session_loggedin()) {
		$perm =& $project->getPermission(session_get_user());
		if ($perm && is_object($perm) && !$perm->isError() && $perm->isAdmin()) {
				echo $HTML->subMenu(
				array(
					_('SCM'),
					_('Admin'),
					_('Reporting')
				),
				array(
					'/scm/?group_id='.$params['group'],
					'/scm/admin/?group_id='.$params['group'],
					'/scm/reporting/?group_id='.$params['group']
				)
			);
		}
	}
	echo '<div style="width:99%">';
}

function scm_footer() {
	echo '</div>';
	site_project_footer(array());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
