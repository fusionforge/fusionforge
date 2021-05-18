<?php
/**
 * TrackerMonitorPlugin Class
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class TrackerMonitorPlugin extends Plugin {
	function __construct($id=0) {
		parent::__construct($id) ;
		$this->name = "trackermonitor";
		$this->text = _("Tracker monitor status");
		$this->pkg_desc = _("Project administrators can view and edit tracker monitoring status for users");
		$this->_addHook('admin_tracker_add_actions');
		$this->_addHook("groupisactivecheckbox");
		$this->_addHook("groupisactivecheckboxpost");
	}

	function CallHook($hookname, &$params) {
		global $gfplugins;

		if ($hookname == "admin_tracker_add_actions") {
			$group_id = $params['group_id'];
			$project = group_get_object($group_id);
			if (!$project || !is_object($project) || $project->isError() || !$project->usesPlugin($this->name)) {
				return;
			}
			$params['result']['show_monitor'] = array(
				'text' => _('User monitoring status'),
				'description' => _('Show which user has activated monitoring on this tracker.'),
				'page' => $gfplugins.'trackermonitor/include/tracker/views/form-showmonitor.php',
			);
		}
	}
}
