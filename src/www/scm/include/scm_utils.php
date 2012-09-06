<?php
/**
 * FusionForge SCM Library
 *
 * Copyright 2004-2005 (c) GForge LLC, Tim Perdue
 * Copyright 2010 (c), Franck Villaume - Capgemini
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

function scm_header($params) {
	global $HTML;
	if (!forge_get_config('use_scm')) {
		exit_disabled();
	}

	$project = group_get_object($params['group']);
	if (!$project || !is_object($project)) {
		exit_no_group();
	} elseif ($project->isError()) {
		exit_error($project->getErrorMessage(),'scm');
	}

	if (!$project->usesSCM()) {
		exit_disabled();
	}
	/*
		Show horizontal links
	*/
	if (session_loggedin()) {
		$params['TITLES'][] = _('View Source Code');
		$params['DIRS'][] = '/scm/?group_id='.$params['group'];
		$params['TOOLTIPS'][] = array('title' => _('Online Source code browsing'), 'class' => 'tabtitle');
		$params['TITLES'][] = _('Reporting');
		$params['DIRS'][] = '/scm/reporting/?group_id='.$params['group'];
		$params['TOOLTIPS'][] = array('title' => _('Global statistics on this SCM repository'), 'class' => 'tabtitle');
		$params['TITLES'][] = _('Administration');
		$params['DIRS'][] = '/scm/admin/?group_id='.$params['group'];
		$params['TOOLTIPS'][] = array('title' => _('Administration page : enable / disable options'), 'class' => 'tabtitle');

		if (forge_check_perm('project_admin', $project->getID())) {
			$params['submenu'] = $HTML->subMenu(
				$params['TITLES'],
				$params['DIRS'],
				$params['TOOLTIPS']
				);
		}
	}

	$params['toptab'] = 'scm';
	site_project_header($params);
	echo '<div id="scm" class="scm">';
}

function scm_footer() {
	echo '</div>';
	site_project_footer(array());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
