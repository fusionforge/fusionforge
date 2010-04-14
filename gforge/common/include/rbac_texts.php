<?php
/**
 * FusionForge localisation
 *
 * Copyright 2007-2010, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

/**
 * This file maps symbolic values to localised texts for the role permissions
 */

require_once $gfcommon.'include/PluginManager.class.php';

function setup_rbac_strings () {
	global $rbac_permission_names, $rbac_edit_section_names ;
	$rbac_permission_names = array_replace_recursive ($rbac_permission_names, 
							  array (
		'frspackage0' => _('Private'),
		'frspackage1' => _('Public'),
		'frspackage' => _('File Release System'),
		'projectpublic0' => _('Private'),
		'projectpublic1' => _('Public'),
		'scmpublic0' => _('Private'),
		'scmpublic1' => _('Public (PServer)'),
		'forumpublic0' => _('Private'),
		'forumpublic1' => _('Public'),
		'forumanon0' => _('No Anonymous Posts'),
		'forumanon1' => _('Allow Anonymous Posts'),
		'pmpublic0' => _('Private'),
		'pmpublic1' => _('Public'),
		'trackerpublic0' => _('Private'),
		'trackerpublic1' => _('Public'),
		'trackeranon0' => _('No Anonymous Posts'),
		'trackeranon1' => _('Allow Anonymous Posts'),
		'frs0' => _('Read'),
		'frs1' => _('Write'),
		'scm-1' => _('No Access'),
		'scm0' => _('Read'),
		'scm1' => _('Write'),
		'forum-1' => _('No Access'),
		'forum0' => _('Read'),
		'forum1' => _('Post'),
		'forum2' => _('Admin'),
		'tracker-1' => _('No Access'),
		'tracker0' => _('Read'),
		'tracker1' => _('Tech'),
		'tracker2' => _('Tech & Admin'),
		'tracker3' => _('Admin Only'),
		'pm-1' => _('No Access'),
		'pm0' => _('Read'),
		'pm1' => _('Tech'),
		'pm2' => _('Tech & Admin'),
		'pm3' => _('Admin Only'),
		'docman0' => _('Read/Post'),
		'docman1' => _('Admin'),
		'projectadmin0' => _('None'),
		'projectadminA' => _('Admin'),
		'pmadmin0' => _('None'),
		'pmadmin2' => _('Admin'),
		'forumadmin0' => _('None'),
		'forumadmin2' => _('Admin'),
		'trackeradmin0' => _('None'),
		'trackeradmin2' => _('Admin'),
		'webcal2' => _('See'),
		'webcal1' => _('Modify'),
		'webcal0' => _('No access')
								  )
		);

	$rbac_edit_section_names = array_replace_recursive ($rbac_edit_section_names,
							    array (
		'forum' => _('Forum'),
		'newforum' => _('Default for new forums'),
		'forumpublic' => _('Forum'),
		'forumanon' => _('Anonymous Forum'),
		'forumadmin' => _('Forum Admin'),
		'pm' => _('Tasks'),
		'newpm' => _('Default for new tasks'),
		'pmpublic' => _('Tasks'),
		'pmadmin' => _('Tasks Admin'),
		'projectpublic' => _('Project'),
		'tracker' => _('Tracker'),
		'newtracker' => _('Default for new trackers'),
		'trackerpublic' => _('Tracker'),
		'trackeranon' => _('Anonymous Tracker'),
		'trackeradmin' => _('Tracker Admin'),
		'frs' => _('File Release System'),
		'frspackage' => _('Files'),
		'webcal' => _('Webcal'),
		'projectadmin' => _('Project Admin'),
		'scm' => _('SCM'),
		'scmpublic' => _('SCM'),
		'docman' => _('Documentation Manager'),
								    )
		) ;

	plugin_hook ("role_translate_strings") ;
  }

setup_rbac_strings () ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
