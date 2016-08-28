<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2015-2016, Franck Villaume - TrivialDev
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

forge_define_config_item('test', 'taskboard', 'test value');

class TaskBoardPlugin extends Plugin {
	function __construct() {
		parent::__construct();
		$this->name = 'taskboard';
		$this->text = 'Task Board'; // To show in the tabs, use...
		$this->pkg_desc =
_('Agile TaskBoard: Supports Scrum and Kanban methodologies.');
		$this->hooks[] = 'project_admin_plugins'; // to show up in the admin page fro group
		$this->hooks[] = 'groupmenu';
		$this->hooks[] = 'groupisactivecheckbox'; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = 'groupisactivecheckboxpost'; //
	}

	function CallHook($hookname, &$params) {
		if ($hookname == 'project_admin_plugins') {
			// this displays the link in the project admin options page to it's task board administration
			$group_id = $params['group_id'];
			$group = group_get_object($group_id);
			if ( $group->usesPlugin($this->name)) {
				echo html_e('p', array(), util_make_uri('/plugins/'.$this->name.'/admin/index.php?group_id='.$group_id, _('Task Board Admin')));
			}
		} elseif ($hookname == 'groupmenu') {
			$group_id = $params['group'];
			$group = group_get_object($group_id);
			if (!$group || !is_object($group))
				return;
			if ($group->isError())
				return;
			if (!$group->isProject())
				return;

			if($group->usesPlugin($this->name)) {
				$params['TITLES'][] = $this->text;
				$params['DIRS'][] = util_make_uri('/plugins/'.$this->name.'/index.php?group_id='.$group_id);
				$params['TOOLTIPS'][] = _('Agile Scrum and Kanban display of existing artifacts.');
				if (session_loggedin()) {
					$user = session_get_user();
					$userperm = $group->getPermission();
					if ($userperm->isAdmin()) {
						$params['ADMIN'][] = util_make_uri('/plugins/'.$this->name.'/admin/?&group_id='.$group_id);
					}
				}
				if(isset($params['toptab'])){
					if($params['toptab'] == $this->name) {
						$params['selected'] = array_search($this->text, $params['TITLES']);
					}
				}
			}
		}
	}
}
