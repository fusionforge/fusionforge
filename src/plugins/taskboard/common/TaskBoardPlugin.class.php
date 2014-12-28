<?php

/*
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

forge_define_config_item('test','taskboard','test value');

class TaskBoardPlugin extends Plugin {
	function TaskBoardPlugin () {
		$this->Plugin() ;
		$this->name = "taskboard" ;
		$this->text = "Task Board" ; // To show in the tabs, use...
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "groupmenu";
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
	}

	function CallHook ($hookname, &$params) {
		if ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's task board administration
			$group_id = $params['group_id'];
			$group = group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo '<p><a href="/plugins/taskboard/admin/index.php?group_id=' . $group->getID() . '">' . _("Task Board Admin") . '</a></p>';
			}
		} elseif ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$group = group_get_object($group_id);
			if (!$group || !is_object($group))
				return;
			if ($group->isError())
				return;
			if (!$group->isProject())
				return;

			if( $group->usesPlugin ( $this->name ) ) {     
				$params['TITLES'][] = _("Task Board");
				$params['DIRS'][] = '/plugins/taskboard/index.php?group_id=' . $group->getID()  ;
		
				if($params['toptab'] == $this->name) {
					$params['selected']=array_search($this->text,$params['TITLES']);
				}
			}
		}
	}

}

