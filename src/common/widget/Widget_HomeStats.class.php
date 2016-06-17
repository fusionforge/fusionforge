<?php
/**
 * Copyright 2016, Franck Villaume - TrivialDev
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
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

class Widget_HomeStats extends Widget {
	function __construct() {
		parent::__construct('homestats');
	}

	function getTitle() {
		return sprintf(_('%s Statistics'), forge_get_config('forge_name'));
	}

	function getContent() {
		global $HTML;
		$return = show_sitestats();
		if (forge_get_config('use_frs')) {
			$return .= $HTML->boxMiddle(_('Top Project Downloads'), 'Top_Projects_Downloads');
			$return .= show_top_downloads();
		}
		if (forge_get_config('use_ratings')) {
			$return .= $HTML->boxMiddle(_('Highest Ranked Users'), 'Highest_Ranked_Users');
			$return .= show_highest_ranked_users();
		}
		$return .= $HTML->boxMiddle(_('Most Active This Week'), 'Most_Active_This_Week');
		$return .= show_highest_ranked_projects();
		$return .= $HTML->boxMiddle(_('Recently Registered Projects'), 'Recently_Registered_Projects');
		$return .= show_newest_projects();
		$params['return'] = &$return;
		plugin_hook_by_reference("widget_homestats", $params);
		return $return;
	}
}
