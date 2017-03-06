<?php
/**
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
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

require_once $gfplugins.'taskboard/common/TaskBoardFactory.class.php';

class TaskBoardFactoryHtml extends TaskBoardFactory {
	function header($params=array()) {
		global $HTML;

		if (!forge_get_config('use_tracker')) {
			exit_disabled();
		}

		$group_id= $this->Group->getID();

		$params['group']=$group_id;
		if (!isset($params['title'])) {
			$params['title']=sprintf(_('Taskboards for %s'), $this->Group->getPublicName());
		}
		$params['toptab']='taskboard';

		$labels = array(_('View Taskboards'));
		$links  = array('plugins/taskboard/?group_id='.$group_id);
		$attr   = array(array('title' => _('Get the list of available taskboards')));
		if (session_loggedin()) {
			if (forge_check_perm('tracker_admin', $group_id)) {
				$labels[] = _('Taskboards Administration');
				$links[]  = 'plugins/taskboard/admin/?group_id='.$group_id;
				$attr[]   = array('title' => _('Global administration for taskboards.'));
			}
		}

		$params['submenu'] = $HTML->subMenu($labels, $links, $attr);

		site_project_header($params);
	}

	function footer($params = array()) {
		site_project_footer($params);
	}
}
