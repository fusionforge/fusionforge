<?php
/**
 * Activity Class
 *
 * Copyright 2017, Franck Villaume - TrivialDev
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class Activity extends FFError {

	//No constructor. Inherit from FFError.
	function check_perm_for_activity($arr, &$cached_perms) {
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
				case 'mediawiki':
					$cached_perms[$s][$ref] = forge_check_perm('plugin_mediawiki_read', $group_id, 'read');
					break;
				default: {
					// Must be a bug somewhere, we're supposed to handle all types
					$cached_perms[$s][$ref] = false;
				}
			}
		}
		return $cached_perms[$s][$ref];
	}

	static function date_compare($a, $b) {
		if ($a['activity_date'] == $b['activity_date']) {
			return 0;
		}
		return ($a['activity_date'] > $b['activity_date']) ? -1 : 1;
	}

	function getDisplayInfo($arr) {
		global $HTML;
		$docmanerror = 0;
		switch (@$arr['section']) {
			case 'scm': {
				$icon = $HTML->getScmPic(_('Source Code'), 'sourcecode');
				$url = util_make_link('/scm/'.$arr['ref_id'].$arr['subref_id'],_('scm commit')._(': ').$arr['description']);
				break;
			}
			case 'trackeropen': {
				$icon = $HTML->getOpenTicketPic(_('Tracker Open'), 'trackeropen');
				$url = util_make_link('/tracker/a_follow.php/'.$arr['subref_id'],_('Tracker Item').' [#'.$arr['subref_id'].'] '.$arr['description'].' '._('Opened'));
				break;
			}
			case 'trackerclose': {
				$icon = $HTML->getClosedTicketPic(_('Tracker Closed'), 'trackerclose');
				$url = util_make_link('/tracker/a_follow.php/'.$arr['subref_id'],_('Tracker Item').' [#'.$arr['subref_id'].'] '.$arr['description'].' '._('Closed'));
				break;
			}
			case 'frsrelease': {
				$icon = $HTML->getPackagePic(_('Files'), 'files');
				$url = util_make_link('/frs/?release_id='.$arr['subref_id'].'&group_id='.$arr['group_id'],_('FRS Release').' '.$arr['description']);
				break;
			}
			case 'forumpost': {
				$icon = $HTML->getForumPic(_('Forum'), 'forum');
				$url = util_make_link('/forum/message.php?msg_id='.$arr['subref_id'].'&group_id='.$arr['group_id'],_('Forum Post').' '.$arr['description']);
				break;
			}
			case 'news': {
				$icon = $HTML->getNewsPic(_('News'), 'news');
				$url = util_make_link('/forum/forum.php?forum_id='.$arr['subref_id'],_('News').' '.$arr['description']);
				break;
			}
			case 'taskopen': {
				$icon = $HTML->getPmPic(_('Open Task'), 'opentask');
				$url = util_make_link('/pm/t_follow.php/'.$arr['subref_id'],_('Tasks').' '.$arr['description']);
				break;
			}
			case 'taskclose': {
				$icon = $HTML->getPmPic(_('Closed Task'), 'closedtask');
				$url = util_make_link('/pm/t_follow.php/'.$arr['subref_id'],_('Tasks').' '.$arr['description']);
				break;
			}

			case 'taskdelete': {
				$icon = $HTML->getPmPic(_('Deleted Task', 'deletedtask'));
				$url = util_make_link('/pm/t_follow.php/'.$arr['subref_id'],_('Tasks').' '.$arr['description']);
				break;
			}
			case 'docmannew':
			case 'docmanupdate': {
				$document = document_get_object($arr['subref_id'], $arr['group_id']);
				$stateid = $document->getStateID();
				if ($stateid != 1 && !forge_check_perm('docman', $arr['group_id'], 'approve')) {
					$docmanerror = 1;
					break;
				}
				$dg = documentgroup_get_object($arr['ref_id'], $arr['group_id']);
				if (!$dg || $dg->isError() || !$dg->getPath(true, false)) {
					$docmanerror = 1;
					break;
				}
				$icon = html_image($document->getFileTypeImage(), 22, 22, array('alt' => $document->getFileType()));
				$url = util_make_link($document->getPermalink(),_('Document').' '.$arr['description']);
				break;
			}
			case 'docgroupnew': {
				$dg = documentgroup_get_object($arr['subref_id'], $arr['group_id']);
				if (!$dg || $dg->isError() || !$dg->getPath(true, false)) {
					$docmanerror = 1;
					break;
				}
				$icon = $HTML->getFolderPic(_('Directory'), 'directory');
				if ($dg->getState() == 2) {
					$view = 'listtrashfile';
				} else {
					$view = 'listfile';
				}
				$url = util_make_link('/docman/?group_id='.$arr['group_id'].'&view='.$view.'&dirid='.$arr['subref_id'],_('Directory').' '.$arr['description']);
				break;
			}
			default: {
				$icon = isset($arr['icon']) ? $arr['icon'] : '';
				$url = '<a href="'.$arr['link'].'">'.$arr['title'].'</a>';
			}
		}
		if ($docmanerror) {
			return false;
		}
		return $icon .' '.$url;
	}

	function getActivitiesForProject($group_id, $begin, $end, $section) {
		$res = db_query_params('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
				AND group_id = $3 AND section = ANY ($4) ORDER BY activity_date DESC',
				array($begin,
					$end,
					$group_id,
					db_string_array_to_any_clause($section)));

		if (db_error()) {
			$this->setError(db_error());
			return false;
		}

		$results = array();
		while ($arr = db_fetch_array($res)) {
			$results[] = $arr;
		}
		return $results;
	}

	function getActivitiesForProjects($selected_groups, $begin, $end) {
		$activities = array();
		$res = db_query_params('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
					AND group_id = ANY ($3) ORDER BY activity_date DESC',
				array($begin, $end, db_int_array_to_any_clause($selected_groups)));
		if ($res && db_numrows($res) > 0) {
			while ($arr = db_fetch_array($res)) {
				$activities[] = $arr;
			}
		}
		return $activities;
	}
}
