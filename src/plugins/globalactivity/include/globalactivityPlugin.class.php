<?php

/**
 * globalactivityPlugin Class
 *
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

class globalactivityPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "globalactivity";
		$this->text = "Global Activity"; // To show in the tabs, use...
	}

	function CallHook ($hookname, &$params) {
	}

	public function getData($begin,$end,$show,&$ids,&$texts) {
		if ($begin > $end) {
			$tmp = $end;
			$end = $begin;
			$begin = $tmp;
			$tmp = $rendered_end;
			$rendered_end = $rendered_begin;
			$rendered_begin = $tmp;
		}
		
		if (forge_get_config('use_forum')) {
			$ids[]		= 'forumpost';
			$texts[]	= _('Forum Post');
		}

		if (forge_get_config('use_tracker')) {
			$ids[]		= 'trackeropen';
			$texts[]	= _('Tracker Opened');
			$ids[]		= 'trackerclose';
			$texts[]	= _('Tracker Closed');
		}

		if (forge_get_config('use_news')) {
			$ids[]		= 'news';
			$texts[]	= _('News');
		}

		if (forge_get_config('use_pm')) {
			$ids[]		= 'taskopen';
			$texts[]	= _('Tasks Opened');
			$ids[]		= 'taskclose';
			$texts[]	= _('Tasks Closed');
			$ids[]		= 'taskdelete';
			$texts[]	= _('Tasks Deleted');
		}

		if (forge_get_config('use_frs')) {
			$ids[]		= 'frsrelease';
			$texts[]	= _('FRS Release');
		}

		if (forge_get_config('use_docman')) {
			$ids[]		= 'docmannew';
			$texts[]	= _('New Documents');
			$ids[]		= 'docmanupdate';
			$texts[]	= _('Updated Documents');
			$ids[]		= 'docgroupnew';
			$texts[]	= _('New Directories');
		}

		if (count($show) < 1) {
			$section = $ids;
		} else {
			$section = $show;
		}

		function date_compare($a, $b) {
			if ($a['activity_date'] == $b['activity_date']) {
				return 0;
			}
			return ($a['activity_date'] > $b['activity_date']) ? -1 : 1;
		}

		global $cached_perms;
		$cached_perms = array();
		function check_perm_for_activity($arr) {
			global $cached_perms;
			$s = $arr['section'];
			$ref = $arr['ref_id'];
			$group_id = $arr['group_id'];

			if (!isset($cached_perms[$s][$ref])) {
				switch ($s) {
					case 'scm': {
						$cached_perms[$s][$ref] = forge_check_perm('scm', $group_id, 'read');
						break;
					}
					case 'trackeropen':
					case 'trackerclose': {
						$cached_perms[$s][$ref] = forge_check_perm('tracker', $ref, 'read');
						break;
					}
					case 'frsrelease': {
						$cached_perms[$s][$ref] = forge_check_perm('frs', $ref, 'read');
						break;
					}
					case 'forumpost':
					case 'news': {
						$cached_perms[$s][$ref] = forge_check_perm('forum', $ref, 'read');
						break;
					}
					case 'taskopen':
					case 'taskclose':
					case 'taskdelete': {
						$cached_perms[$s][$ref] = forge_check_perm('pm', $ref, 'read');
						break;
					}
					case 'docmannew':
					case 'docmanupdate':
					case 'docgroupnew': {
						$cached_perms[$s][$ref] = forge_check_perm('docman', $group_id, 'read');
						break;
					}
					default: {
						// Must be a bug somewhere, we're supposed to handle all types
						$cached_perms[$s][$ref] = false;
					}
				}
			}
			return $cached_perms[$s][$ref];
		}

		$res = db_query_params('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
			AND section = ANY ($3) ORDER BY activity_date DESC',
							   array($begin,
									 $end,
									 db_string_array_to_any_clause($section)));

		if (db_error()) {
			exit_error(db_error(), 'home');
		}

		$results = array();
		while ($arr = db_fetch_array($res)) {
			$group_id = $arr['group_id'];
			if (!forge_check_perm('project_read', $group_id)) {
				continue;
			}
			if (!check_perm_for_activity($arr)) {
				continue;
			}
			$results[] = $arr;
		}

		$res = db_query_params('SELECT group_id FROM groups WHERE status=$1',
							   array('A'));

		if (db_error()) {
			exit_error(db_error(), 'home');
		}

		// If plugins wants to add activities.
		while ($arr = db_fetch_array($res)) {
			if (!forge_check_perm('project_read', $group_id)) {
				continue;
			}
			$group_id = $arr['group_id'];
			$hookParams['group'] = $group_id;
			$hookParams['results'] = &$results;
			$hookParams['show'] = &$show;
			$hookParams['begin'] = $begin;
			$hookParams['end'] = $end;
			$hookParams['ids'] = &$ids;
			$hookParams['texts'] = &$texts;
			plugin_hook("activity", $hookParams);
		}

		if (count($show) < 1) {
			$show = $ids;
		}

		foreach ($show as $showthis) {
			if (array_search($showthis, $ids) === false) {
				exit_error(_('Invalid Data Passed to query'), 'home');
			}
		}

		$res2 = array();
		foreach ($results as $arr) {
			$group_id = $arr['group_id'];
			if (!forge_check_perm('project_read', $group_id)) {
				continue;
			}
			if (!check_perm_for_activity($arr)) {
				continue;
			}
			$res2[] = $arr;
		}

		usort($res2, 'date_compare');

		return $res2;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
