<?php
/**
 * Copyright 2016, Franck Villaume - TrivialDev
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
 * Widget_MySystasks
 */

class Widget_MySystasks extends Widget {
	function __construct() {
		parent::__construct('mysystasks');
	}

	function getTitle() {
		return _('System Actions Queue (past day) in My Projects');
	}

	function getContent() {
		$html_my_systasks = '';
		global $HTML;
		$user = session_get_user();
		$groups = $user->getGroups();
		$gids = array();
		foreach($groups as $g)
			$gids[] = $g->getID();
		if (empty($gids)) {
			return $HTML->information(_('Not member of any project'));
		}

		$gids = implode(',', $gids);
		$query = "
			SELECT systask_id, COALESCE(unix_group_name, '-') AS unix_group_name,
			plugin_name, systask_type, systasks.status,
			EXTRACT(epoch FROM requested) AS requested,
			EXTRACT(epoch FROM started) AS started,
			EXTRACT(epoch FROM stopped) AS stopped,
			EXTRACT(epoch FROM started-requested) AS queued,
			EXTRACT(epoch FROM stopped-started) AS run
			FROM systasks LEFT JOIN groups ON (systasks.group_id = groups.group_id)
			LEFT JOIN plugins ON (systasks.plugin_id = plugins.plugin_id)
			WHERE user_id=$1 or systasks.group_id IN ($gids)
			AND requested > NOW() - interval '1 day'
			ORDER BY systask_id";
		$res = db_query_params($query, array($user->getID()));

		if (db_numrows($res) == 0) {
			return $HTML->information(_('No systask executed during last day'));
		}

		$title_arr = array(
			_('Task Id'),
			_('Plugin'),
			_('SysTask Type'),
			_('Project Name'),
			_('Status'),
			_('Requested'),
			_('Started'),
			_('Stopped'),
		);

		$html_my_systasks = $HTML->listTableTop($title_arr);
		for ($i=0; $i<db_numrows($res); $i++) {
			$cells = array();
			$cells[][] = db_result($res,$i,'systask_id');
			$plugin_name = db_result($res,$i,'plugin_name');
			if ($plugin_name == null)
				$cells[][] = 'core';
			else
				$cells[][] = $plugin_name;
			$cells[][] = db_result($res,$i,'systask_type');
			$cells[][] = db_result($res,$i,'unix_group_name');
			$cells[][] = db_result($res,$i,'status');
			$cells[][] = date("H:i:s", db_result($res, $i,'requested'));
			$cells[][] = date("H:i:s", db_result($res, $i,'started'))
				. ' (+' . round(db_result($res, $i,'queued'), 1) . 's)';
			$cells[][] = date("H:i:s", db_result($res, $i,'stopped'))
				. ' (+' . round(db_result($res, $i,'run'), 1) . 's)';
			$html_my_systasks .= $HTML->multiTableRow(array(), $cells);
		}

		$html_my_systasks .= $HTML->listTableBottom();
		return $html_my_systasks;
	}

	function getDescription() {
		return _('List actions performed by Systask system on all your projects during the last day.');
	}
}
