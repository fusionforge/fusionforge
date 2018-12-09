<?php
/**
 * FusionForge Followers (monitored users and vice versa) Widget
 *
 * Copyright 2018, Franck Villaume - TrivialDev
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

require_once 'Widget.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';

 class Widget_MyFollowers extends Widget {
	function __construct() {
		parent::__construct('myfollowers');
	}

	function getTitle() {
		return _('Followers');
	}

	function getDescription() {
		return _('List users you are following and users that follows you.');
	}

	function getContent() {
		global $HTML;
		$monitorUser = new MonitorElement('user');
		$monitoredUserIds = $monitorUser->getMonitedByUserIdInArray(user_getid());
		$followerIds = $monitorUser->getMonitorUsersIdsInArray(user_getid());
		if (!(is_array($monitoredUserIds) && count($monitoredUserIds) > 0) && !(is_array($followerIds) && count($followerIds) > 0)) {
			echo $HTML->warning_msg(_('You are not following any user and not followed by any.'));
		} else {
			echo $HTML->listTableTop();
			$cells[][] = _('Following');
			$cells[][] = _('Followers');
			echo $HTML->multiTableRow(array(), $cells);
			$cells = array();
			$cells[][] = count($followerIds);
			$cells[][] = count($monitoredUserIds);
			echo $HTML->multiTableRow(array(), $cells);
			echo $HTML->listTableBottom();
			$groupsArr = array();
			$date_format = _('%Y-%m-%d');
			$ids = array();
			$texts = array();

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

			if (forge_get_config('use_diary')) {
				$ids[]		= 'diaryentry';
				$texts[]	= _('New Diary Entries');
			}
			if (count($followerIds) > 0) {
				$section = $ids;
				$ffactivity = new Activity();
				if (!isset($begin)) {
					$begin = (time() - (30 * 86400));
				}
				if (!isset($end)) {
					$end = time();
				}
				$results = $ffactivity->getActivitiesForUsers($followerIds, $begin, $end, $section);
				if ($results === false) {
					echo $HTML->error_msg(_('Unable to get activities')._(':').$ffactivity->getErrorMessage());
				}
				if (count($results) < 1) {
					echo $HTML->information(_('No Activity Found'));
				} else {
					$cached_perms = array();

					usort($results, 'Activity::date_compare');

					$displayTableTop = 0;
					$j = 0;
					$last_day = 0;
					foreach ($results as $arr) {
						if (!$ffactivity->check_perm_for_activity($arr, $cached_perms)) {
							continue;
						}

						$displayinfo = $ffactivity->getDisplayInfo($arr);
						if (!$displayinfo) {
							continue;
						}

						if (!$displayTableTop) {
							$theader = array();
							$theader[] = _('Time');
							$theader[] = _('Project');
							$theader[] = _('Activity');

							echo $HTML->listTableTop($theader);
							$displayTableTop = 1;
						}

						if ($last_day != strftime($date_format, $arr['activity_date'])) {
							echo '<tr class="tableheading"><td colspan="3">'.strftime($date_format, $arr['activity_date']).'</td></tr>';
							$last_day=strftime($date_format, $arr['activity_date']);
						}
						$cells = array();
						$cells[][] = date('H:i:s',$arr['activity_date']);
						if (isset($arr['group_id']) && $arr['group_id']) {
							if (!isset($groupsArr[$arr['group_id']])) {
								$groupsArr[$arr['group_id']] = group_get_object($arr['group_id']);
							}
							$cells[][] = util_make_link_g($groupsArr[$arr['group_id']]->getUnixName(), $groupsArr[$arr['group_id']]->getID(), $groupsArr[$arr['group_id']]->getPublicName());
						} else {
							$cells[][] = '--';
						}
						$cells[][] = $displayinfo;
						echo $HTML->multiTableRow(array(), $cells);
					}
					if ($displayTableTop) {
						echo $HTML->listTableBottom();
					}
					if (!$displayTableTop) {
						echo $HTML->information(_('No Activity Found'));
					}
				}
			}
		}
	}
}
