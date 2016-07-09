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

class Widget_HomeDetailActivityMostActiveProjectWeek extends Widget {

	var $cached_perms = array();

	function __construct() {
		parent::__construct('homedetailactivitymostactiveprojectweek');
		if (forge_get_config('use_activity')) {
			$this->content['title'] = _('Detailed Activity for the 10 Most Active Projects this Week');
		}
	}

	function getContent() {
		global $HTML;
		$stats = new Stats();
		$result = $stats->getMostActiveStats('week', 0);
		$return = '';
		$count = 1;
		$selected_groups = array();
		while (($row = db_fetch_array($result)) && ($count <= 10)) {
			$group = group_get_object($row['group_id']);
			if (forge_check_perm('project_read', $row['group_id']) && $group->usesActivity()) {
				$selected_groups[] = $row['group_id'];
				$count++;
			}
		}
		$activities = array();
		$begin = (time()-(7*86400));
		$end = time();
		$res = db_query_params('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
					AND group_id = ANY ($3) ORDER BY activity_date DESC',
				array($begin, $end, db_int_array_to_any_clause($selected_groups)));
		if ($res && db_numrows($res) > 0) {
			while ($arr = db_fetch_array($res)) {
				$activities[] = $arr;
			}
		}
		foreach ($selected_groups as $group_id) {
			// If plugins wants to add activities.
			$hookParams['group'] = $group_id;
			$hookParams['results'] = &$activities;
			$hookParams['begin'] = $begin;
			$hookParams['end'] = $end;
			plugin_hook('activity', $hookParams);
		}
		if (count($activities) > 0) {
			$date_format = _('%Y-%m-%d');
			$date_format_js = _('yy-mm-dd');
			usort($activities, 'Widget_HomeDetailActivityMostActiveProjectWeek::date_compare');
			$displayTableTop = 0;
			$last_day = 0;
			$j = 0;
			foreach ($activities as $activity) {
				$docmanerror = 0;
				if (!$this->check_perm_for_activity($activity)) {
					continue;
				}
				if (!$displayTableTop) {
					$theader = array();
					$theader[] = _('Time');
					$theader[] = _('Project');
					$theader[] = _('Activity');
					$theader[] = _('By');

					echo $HTML->listTableTop($theader);
					$displayTableTop = 1;
				}
				switch (@$activity['section']) {
					case 'scm': {
						$icon = html_image('ic/cvs16b.png','','',array('alt'=>_('Source Code')));
						$url = util_make_link('/scm/'.$activity['ref_id'].$activity['subref_id'],_('scm commit')._(': ').$activity['description']);
						break;
					}
					case 'trackeropen': {
						$icon = html_image('ic/tracker20g.png','','',array('alt'=>_('Trackers')));
						$url = util_make_link('/tracker/?func=detail&atid='.$activity['ref_id'].'&aid='.$activity['subref_id'].'&group_id='.$activity['group_id'],_('Tracker Item').' [#'.$activity['subref_id'].'] '.$activity['description'].' '._('Opened'));
						break;
					}
					case 'trackerclose': {
						$icon = html_image('ic/tracker20g.png','','',array('alt'=>_('Trackers')));
						$url = util_make_link('/tracker/?func=detail&atid='.$activity['ref_id'].'&aid='.$activity['subref_id'].'&group_id='.$activity['group_id'],_('Tracker Item').' [#'.$activity['subref_id'].'] '.$activity['description'].' '._('Closed'));
						break;
					}
					case 'frsrelease': {
						$icon = html_image('ic/cvs16b.png','','',array('alt'=>_('Files')));
						$url = util_make_link('/frs/?release_id='.$activity['subref_id'].'&group_id='.$activity['group_id'],_('FRS Release').' '.$activity['description']);
						break;
					}
					case 'forumpost': {
						$icon = html_image('ic/forum20g.png','','',array('alt'=>_('Forum')));
						$url = util_make_link('/forum/message.php?msg_id='.$activity['subref_id'].'&group_id='.$activity['group_id'],_('Forum Post').' '.$activity['description']);
						break;
					}
					case 'news': {
						$icon = html_image('ic/write16w.png','','',array('alt'=>_('News')));
						$url = util_make_link('/forum/forum.php?forum_id='.$activity['subref_id'],_('News').' '.$activity['description']);
						break;
					}
					case 'taskopen':
					case 'taskclose':
					case 'taskdelete': {
						$icon = html_image('ic/taskman20w.png','','',array('alt'=>_('Tasks')));
						$url = util_make_link('/pm/task.php?func=detailtask&project_task_id='.$activity['subref_id'].'&group_id='.$activity['group_id'].'&group_project_id='.$activity['ref_id'],_('Tasks').' '.$activity['description']);
						break;
					}
					case 'docmannew':
					case 'docmanupdate': {
						$document = document_get_object($activity['subref_id'], $activity['group_id']);
						$stateid = $document->getStateID();
						if ($stateid != 1 && !forge_check_perm('docman', $activity['group_id'], 'approve')) {
							$docmanerror = 1;
						}
						$dg = documentgroup_get_object($activity['ref_id'], $activity['group_id']);
						if (!$dg || $dg->isError() || !$dg->getPath(true, false)) {
							$docmanerror = 1;
						}
						$icon = html_image('ic/docman16b.png', '', '', array('alt'=>_('Documents')));
						$url = util_make_link('docman/?group_id='.$activity['group_id'].'&view=listfile&dirid='.$activity['ref_id'],_('Document').' '.$activity['description']);
						break;
					}
					case 'docgroupnew': {
						$dg = documentgroup_get_object($activity['subref_id'], $activity['group_id']);
						if (!$dg || $dg->isError() || !$dg->getPath(true, false)) {
							$docmanerror = 1;
						}
						$icon = html_image('ic/cfolder15.png', '', '', array("alt"=>_('Directory')));
						$url = util_make_link('docman/?group_id='.$activity['group_id'].'&view=listfile&dirid='.$activity['subref_id'],_('Directory').' '.$activity['description']);
						break;
					}
					default: {
						$icon = isset($activity['icon']) ? $activity['icon'] : '';
						$url = '<a href="'.$activity['link'].'">'.$activity['title'].'</a>';
					}
				}
				if ($docmanerror) {
					continue;
				}
				if ($last_day != strftime($date_format, $activity['activity_date'])) {
					$cells = array();
					$cells[] = array(strftime($date_format, $activity['activity_date']), 'colspan' => 4);
					echo $HTML->multiTableRow(array('class' => 'tableheading'), $cells, true);
					$last_day=strftime($date_format, $activity['activity_date']);
				}
				$cells = array();
				$cells[][] = date('H:i:s',$activity['activity_date']);
				$group_object = group_get_object($activity['group_id']);

				$cells[][] = util_make_link_g($group_object->getUnixName(), $activity['group_id'], $group_object->getPublicName());
				$cells[][] = $icon .' '.$url;
				if (isset($activity['user_name']) && $activity['user_name']) {
					$cells[][] = util_display_user($activity['user_name'], $activity['user_id'],$activity['realname']);
				} else {
					$cells[][] = $activity['realname'];
				}
				echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($j++, true)), $cells);
			}
			if ($displayTableTop) {
				echo $HTML->listTableBottom();
			}
		} else {
			echo $HTML->information(_('No activity during the last week'));
		}
		return $return;
	}

	function getTitle() {
		return $this->content['title'];
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function date_compare($a, $b) {
		if ($a['activity_date'] == $b['activity_date']) {
			return 0;
		}
		return ($a['activity_date'] > $b['activity_date']) ? -1 : 1;
	}

	function check_perm_for_activity($arr) {
		$s = $arr['section'];
		$ref = $arr['ref_id'];
		$group_id = $arr['group_id'];

		if (!isset($this->cached_perms[$s][$ref])) {
			switch ($s) {
				case 'scm': {
					$this->cached_perms[$s][$ref] = forge_check_perm('scm', $group_id, 'read');
					break;
				}
				case 'trackeropen':
				case 'trackerclose': {
					$this->cached_perms[$s][$ref] = forge_check_perm('tracker', $ref, 'read');
					break;
				}
				case 'frsrelease': {
					$this->cached_perms[$s][$ref] = forge_check_perm('frs', $ref, 'read');
					break;
				}
				case 'forumpost':
				case 'news': {
					$this->cached_perms[$s][$ref] = forge_check_perm('forum', $ref, 'read');
					break;
				}
				case 'taskopen':
				case 'taskclose':
				case 'taskdelete': {
					$this->cached_perms[$s][$ref] = forge_check_perm('pm', $ref, 'read');
					break;
				}
				case 'docmannew':
				case 'docmanupdate':
				case 'docgroupnew': {
					$this->cached_perms[$s][$ref] = forge_check_perm('docman', $group_id, 'read');
					break;
				}
				default: {
					// Must be a bug somewhere, we're supposed to handle all types
					$this->cached_perms[$s][$ref] = false;
				}
			}
		}
		return $this->cached_perms[$s][$ref];
	}
}
