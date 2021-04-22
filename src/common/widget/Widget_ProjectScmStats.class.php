<?php
/**
 * Copyright 2016,2021, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is a part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

/**
 * Widget_ProjectScmStats
 */

class Widget_ProjectScmStats extends Widget {

	var $content = array();

	function __construct() {
		global $project;
		parent::__construct('projectscmstats');
		if ($project && $this->canBeUsedByProject($project) && forge_check_perm('scm', $project->getID(), 'read')) {
			$this->content['title'] = _('Repository History');
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getContent() {
		global $project;
		$project_plugins = $project->getPlugins();
		foreach ($project_plugins as $value) {
			$plugin_object = plugin_get_object($value);
			if (is_object($plugin_object) && $plugin_object->provide('scm')) {
				$html_projectscmstats .= $plugin_object->getStatsBlock($project);
			}
		}
		return $html_projectscmstats;
	}

	function getDescription() {
		return _('Display Repository Statistics history. Number of adds & updates per user since the init of the repository');
	}

	function canBeUsedByProject(&$project) {
		return $project->usesSCM();
	}

	function getCategory() {
		return _('SCM');
	}

	function isAvailable() {
		return isset($this->content['title']);
	}
}
