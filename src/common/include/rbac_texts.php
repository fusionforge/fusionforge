<?php
/**
 * FusionForge localisation
 *
 * Copyright 2007-2010, Roland Mas
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
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
 * This file maps symbolic values to localised texts for the role permissions
 */

require_once $gfcommon.'include/PluginManager.class.php';
require_once $gfcommon.'include/utils.php';

/**
 * Maps symbolic values to localised texts for the role permissions
 */
function setup_rbac_strings () {
	global $rbac_permission_names, $rbac_edit_section_names ;

	if (!isset ($rbac_permission_names)) {
		$rbac_permission_names = array () ;
	}
	if (!isset ($rbac_edit_section_names)) {
		$rbac_edit_section_names = array () ;
	}

	$rbac_permission_names = array_replace_recursive ($rbac_permission_names,
							  array (
								  'forge_admin0' => _('No administrative access'),
								  'forge_admin1' => _('Forge administration'),
								  'approve_projects0' => _('No Access'),
								  'approve_projects1' => _('Approve projects'),
								  'approve_news0' => _('No Access'),
								  'approve_news1' => _('Approve news'),
								  'forge_stats0' => _('No Access'),
								  'forge_stats1' => _('Read access'),
								  'forge_stats2' => _('Admin forge stats'),

								  'project_read0' => _('Hidden'),
								  'project_read1' => _('Visible'),
								  'project_admin0' => _('No administrative access'),
								  'project_admin1' => _('Project administration'),

								  'tracker_admin0' => _('No administrative access'),
								  'tracker_admin1' => _('Trackers Administration'),
								  'pm_admin0' => _('No administrative access'),
								  'pm_admin1' => _('Task managers administration'),
								  'forum_admin0' => _('No administrative access'),
								  'forum_admin1' => _('Forums administration'),

								  'tracker0' => _('No Access'),
								  'tracker1' => _('Read only'),
								  'tracker2' => _('Technician (no read access, no submitting)'),
								  'tracker3' => _('Technician (no submitting)'),
								  'tracker4' => _('Manager (no read access, no submitting)'),
								  'tracker5' => _('Manager (no submitting)'),
								  'tracker6' => _('Tech & manager (no read access, no submitting)'),
								  'tracker7' => _('Tech & manager (no submitting)'),
								  'tracker8' => _('Submit only'),
								  'tracker9' => _('Read & submit'),
								  'tracker10' => _('Technician (no read access)'),
								  'tracker11' => _('Technician'),
								  'tracker12' => _('Manager (no read access)'),
								  'tracker13' => _('Manager'),
								  'tracker14' => _('Tech & manager (no read access)'),
								  'tracker15' => _('Tech & manager'),
								  'tracker16' => _('No access + Vote'),
								  'tracker17' => _('Read only + Vote'),
								  'tracker18' => _('Technician (no read access, no submitting) + Vote'),
								  'tracker19' => _('Technician (no submitting) + Vote'),
								  'tracker20' => _('Manager (no read access, no submitting) + Vote'),
								  'tracker21' => _('Manager (no submitting) + Vote'),
								  'tracker22' => _('Tech & manager (no read access, no submitting) + Vote'),
								  'tracker23' => _('Tech & manager (no submitting) + Vote'),
								  'tracker24' => _('Submit only + Vote'),
								  'tracker25' => _('Read & submit + Vote'),
								  'tracker26' => _('Technician (no read access) + Vote'),
								  'tracker27' => _('Technician + Vote'),
								  'tracker28' => _('Manager (no read access) + Vote'),
								  'tracker29' => _('Manager + Vote'),
								  'tracker30' => _('Tech & manager (no read access) + Vote'),
								  'tracker31' => _('Tech & manager + Vote'),
								  'pm0' => _('No Access'),
								  'pm1' => _('Read only'),
								  'pm2' => _('Technician (no read access)'),
								  'pm3' => _('Technician'),
								  'pm4' => _('Manager (no read access)'),
								  'pm5' => _('Manager'),
								  'pm6' => _('Tech & manager (no read access)'),
								  'pm7' => _('Tech & manager'),
								  'forum0' => _('No Access'),
								  'forum1' => _('Read only'),
								  'forum2' => _('Moderated post'),
								  'forum3' => _('Unmoderated post'),
								  'forum4' => _('Moderation'),

								  'new_tracker0' => _('No Access'),
								  'new_tracker1' => _('Read only'),
								  'new_tracker2' => _('Technician (no read access, no submitting)'),
								  'new_tracker3' => _('Technician (no submitting)'),
								  'new_tracker4' => _('Manager (no read access, no submitting)'),
								  'new_tracker5' => _('Manager (no submitting)'),
								  'new_tracker6' => _('Tech & manager (no read access, no submitting)'),
								  'new_tracker7' => _('Tech & manager (no submitting)'),
								  'new_tracker8' => _('Submit only'),
								  'new_tracker9' => _('Read & submit'),
								  'new_tracker10' => _('Technician (no read access)'),
								  'new_tracker11' => _('Technician'),
								  'new_tracker12' => _('Manager (no read access)'),
								  'new_tracker13' => _('Manager'),
								  'new_tracker14' => _('Tech & manager (no read access)'),
								  'new_tracker15' => _('Tech & manager'),
								  'new_tracker16' => _('No access + Vote'),
								  'new_tracker17' => _('Read only + Vote'),
								  'new_tracker18' => _('Technician (no read access, no submitting) + Vote'),
								  'new_tracker19' => _('Technician (no submitting) + Vote'),
								  'new_tracker20' => _('Manager (no read access, no submitting) + Vote'),
								  'new_tracker21' => _('Manager (no submitting) + Vote'),
								  'new_tracker22' => _('Tech & manager (no read access, no submitting) + Vote'),
								  'new_tracker23' => _('Tech & manager (no submitting) + Vote'),
								  'new_tracker24' => _('Submit only + Vote'),
								  'new_tracker25' => _('Read & submit + Vote'),
								  'new_tracker26' => _('Technician (no read access) + Vote'),
								  'new_tracker27' => _('Technician + Vote'),
								  'new_tracker28' => _('Manager (no read access) + Vote'),
								  'new_tracker29' => _('Manager + Vote'),
								  'new_tracker30' => _('Tech & manager (no read access) + Vote'),
								  'new_tracker31' => _('Tech & manager + Vote'),
								  'new_pm0' => _('No Access'),
								  'new_pm1' => _('Read only'),
								  'new_pm2' => _('Technician (no read access)'),
								  'new_pm3' => _('Technician'),
								  'new_pm4' => _('Manager (no read access)'),
								  'new_pm5' => _('Manager'),
								  'new_pm6' => _('Tech & manager (no read access)'),
								  'new_pm7' => _('Tech & manager'),
								  'new_forum0' => _('No Access'),
								  'new_forum1' => _('Read only'),
								  'new_forum2' => _('Moderated post'),
								  'new_forum3' => _('Unmoderated post'),
								  'new_forum4' => _('Moderation'),

								  'scm0' => _('No Access'),
								  'scm1' => _('Read only'),
								  'scm2' => _('Commit access'),
								  'docman0' => _('No Access'),
								  'docman1' => _('Read only'),
								  'docman2' => _('Submit documents'),
								  'docman3' => _('Approve documents'),
								  'docman4' => _('Doc manager administration'),
								  'frs0' => _('No Access'),
								  'frs1' => _('View public packages only'),
								  'frs2' => _('View all packages'),
								  'frs3' => _('Publish files'),
								  )
		);

	$rbac_edit_section_names = array_replace_recursive ($rbac_edit_section_names,
							    array (
								    'forge_admin' => _('Forge administration'),
								    'approve_projects' => _('Approve projects'),
								    'approve_news' => _('Approve news'),
								    'forge_stats' => _('Forge statistics'),

								    'project_read' => _('Project visibility'),
								    'project_admin' => _('Project administration'),

								    'tracker_admin' => _('Trackers administration'),
								    'pm_admin' => _('Task managers administration'),
								    'forum_admin' => _('Forums administration'),

								    'tracker' => _('Tracker'),
								    'pm' => _('Tasks'),
								    'forum' => _('Forum'),

								    'new_tracker' => _('Default for new trackers'),
								    'new_pm' => _('Default for new task managers'),
								    'new_forum' => _('Default for new forums'),

								    'scm' => _('SCM'),
								    'docman' => _('Documentation manager'),
								    'frs' => _('Files'),

								    )
		) ;
	plugin_hook ("role_translate_strings") ;
}

setup_rbac_strings () ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
