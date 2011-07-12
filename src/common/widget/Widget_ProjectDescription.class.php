<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');
require_once('common/include/Codendi_HTMLPurifier.class.php');

/**
* Widget_ProjectDescription
*
*/
class Widget_ProjectDescription extends Widget {
    public function __construct() {
        $this->Widget('projectdescription');
    }
    public function getTitle() {
        return _('Project description');
    }
    public function getContent() {
        $request =& HTTPRequest::instance();
        $group_id = $request->get('group_id');
        $pm = ProjectManager::instance();
        $project = $pm->getProject($group_id);
        $hp = Codendi_HTMLPurifier::instance();

	$pluginManager = plugin_manager_get_object();
		if (! $pluginManager->PluginIsInstalled('blocks') || !plugin_hook ("blocks", 'summary_description')) {
		$project_description = $project->getDescription();
		if ($project_description) {
			// need to use a litteral version for content attribute since nl2br is for HTML
			print "<p>"
				.'<span property="doap:description" content="'. preg_quote($project_description,'"') .'">'
				. nl2br($project_description)
				.'</span></p>';
		} else {
			print "<p>" . _('This project has not yet submitted a description.') . '</p>';
		}
	}




    }
    public function canBeUsedByProject(&$project) {
	    return true;
    }
    function getDescription() {
	    return _('Allow you to view the project description');
    }
}

?>
