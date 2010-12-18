<?php
/**
 * projects_hierarchyPlugin Class
 *
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

class projects_hierarchyPlugin extends Plugin {
	function projects_hierarchyPlugin() {
		$this->Plugin();
		$this->name = "projects_hierarchy";
		$this->text = _('Project Hierarchy'); // To show in the tabs, use...
		$this->_addHook("groupisactivecheckbox"); // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost"); //
		$this->_addHook("admin_project_link");
		$this->_addHook("project_home_link"); // to see father and sons in project home
		$this->_addHook("tree"); // to see the tree of projects
		$this->_addHook("delete_link"); // to delete link
	}

	function CallHook ($hookname, &$params) {
		global $G_SESSION, $HTML;
		$returned = false;
		switch($hookname) {
			case "tree": {
				header('Location: ../plugins/projects_hierarchy/softwaremap.php');
				break;
			}
			case "project_home_link": {
				$group_id = $params;
				$project = group_get_object($group_id);
				if ($project->usesPlugin($this->name)) {
					include($this->name.'/view/'.$hookname.'.php');
				}
				$returned = true;
				break;
			}
			case "admin_project_link": {
				global $gfplugins;
				require_once $gfplugins.'projects_hierarchy/www/hierarchy_utils.php';
				$group_id = $params;
				$project = group_get_object($group_id);
				if ($project->usesPlugin($this->name)) {
					include($this->name.'/view/'.$hookname.'.php');
				}
				$returned = true;
				break;
			}
			case "delete_link": {
				$res_son = db_query_params('DELETE FROM plugin_projects_hierarchy WHERE project_id = $1 OR sub_project_id = $2 ',
							array($params, $params));
				$returned = true;
				break;
			}
		}
		return $returned;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
