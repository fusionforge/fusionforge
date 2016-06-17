<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2015, Franck Villaume - TrivialDev
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once 'common/include/Codendi_HTMLPurifier.class.php';

/**
 * Widget_ProjectDescription
 */

class Widget_ProjectDescription extends Widget {
	function __construct() {
		parent::__construct('projectdescription');
	}

	public function getTitle() {
		return _('Project description');
	}

	public function getContent() {
		global $HTML;
		$result = '';

		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);

		$pluginManager = plugin_manager_get_object();
		if (! $pluginManager->PluginIsInstalled('blocks') || !plugin_hook ("blocks", 'summary_description')) {
			$project_description = $project->getDescription();
			if ($project_description) {
				// need to use a litteral version for content attribute since nl2br is for HTML
				$result .= "<p>";
				if (forge_get_config('use_project_multimedia') && $project->getLogoImageID()) {
					$result .= '<span><img src="/dbimage.php?id='.$project->getLogoImageID().'" width="40" height="40" /></span>';
				}
				$result .= '<span property="doap:description" content="'. preg_quote($project_description,'"') .'">'
					. nl2br($project_description)
					.'</span></p>';
			} else {
				$result .= $HTML->information(_('This project has not yet submitted a description.'));
			}
		}
		return $result;
	}

	public function canBeUsedByProject(&$project) {
		return true;
	}

	function getDescription() {
		return _('Allow you to view the project description');
	}
}
