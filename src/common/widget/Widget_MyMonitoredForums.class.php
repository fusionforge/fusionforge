<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012-2015,2021, Franck Villaume - TrivialDev
 * Copyright (C) 2014 Alain Peyrat - Alcatel-Lucent
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
require_once $gfcommon.'include/my_utils.php';
require_once $gfcommon.'include/MonitorElement.class.php';

/**
 * Widget_MyMonitoredForums
 *
 * Forums that are actively monitored
 */

class Widget_MyMonitoredForums extends Widget {
	function __construct() {
		parent::__construct('mymonitoredforums');
	}

	function getTitle() {
		return _('Monitored Forums');
	}

	function getContent() {
		global $HTML;
		$html_my_monitored_forums = '';
		$monitorElementObject = new MonitorElement('forum');
		$distinctMonitorGroupIdsArray = $monitorElementObject->getMonitoredDistinctGroupIdsByUserIdInArray(user_getid());
		if (!$distinctMonitorGroupIdsArray || count($distinctMonitorGroupIdsArray) < 1) {
			$html_my_monitored_forums .= $HTML->warning_msg(_('You are not monitoring any forums.')).html_e('p', array(), _("If you monitor forums, you will be sent new posts in the form of an email, with a link to the new message.")).html_e('p', array(), _("You can monitor forums by clicking on the appropriate menu item in the discussion forum itself."));
		} else {
			$validDistinctMonitorGroupIdsArray = array();
			foreach ($distinctMonitorGroupIdsArray as $distinctMonitorGroupId) {
				if (forge_check_perm('project_read', $distinctMonitorGroupId)) {
					$validDistinctMonitorGroupIdsArray[] = $distinctMonitorGroupId;
				} else {
					// Oh ho! we found some monitored elements where user has no read access. Let's clean the situation
					$monitorElementObject->disableMonitoringForGroupIdByUserId($distinctMonitorGroupId, user_getid());
				}
			}
			if (count($validDistinctMonitorGroupIdsArray)) {
				$hide_item_id = getIntFromRequest('hide_item_id', 0);
				$hide_forum = getIntFromRequest('hide_forum', 0);
				$setListTableTop = true;
				foreach ($validDistinctMonitorGroupIdsArray as $validDistinctMonitorGroupId) {
					$groupObject = group_get_object($validDistinctMonitorGroupId);
					$monitoredForumIdsArray = $monitorElementObject->getMonitoredIdsByGroupIdByUserIdInArray($validDistinctMonitorGroupId, user_getid());
					$validMonitoredForumIds = array();
					foreach ($monitoredForumIdsArray as $monitoredForumId) {
						if (forge_check_perm('forum', $monitoredForumId, 'read')) {
							$validMonitoredForumIds[] = $monitoredForumId;
						} else {
							// Oh ho! we found some monitored elements where user has no read access. Let's clean the situation
							$monitorElementObject->disableMonitoringByUserId($monitoredForumId, user_getid());
						}
					}
					if (count($validMonitoredForumIds)) {
						if ($setListTableTop) {
							$html_my_monitored_forums .= $HTML->listTableTop();
							$setListTableTop = false;
						}

						list($hide_now, $count_diff, $hide_url) = my_hide_url('forum', $validDistinctMonitorGroupId, $hide_item_id, count($validMonitoredForumIds), $hide_forum);
						$count_new = max(0, $count_diff);
						$cells = array();
						$cells[] = array($hide_url.util_make_link('/forum/?group_id='.$validDistinctMonitorGroupId, $groupObject->getPublicName()).'    ['.count($validMonitoredForumIds).($count_new ? ', '.html_e('b', array(), sprintf(_('%s new'), $count_new)).']' : ']'), 'colspan' => 2);
						$html_hdr = $HTML->multiTableRow(array('class' => 'boxitem'), $cells);
						$html = '';
						if (!$hide_now) {
							foreach ($validMonitoredForumIds as $validMonitoredForumId) {
								$forumObject = forum_get_object($validMonitoredForumId);
								$cells = array();
								$cells[] = array('&nbsp;&nbsp;&nbsp;-&nbsp;'.util_make_link('/forum/forum.php?forum_id='.$validMonitoredForumId, $forumObject->getName()), 'style' => 'width:99%');
								$cells[] = array(util_make_link('/forum/monitor.php?forum_id='.$validMonitoredForumId.'&group_id='.$groupObject->getID().'&stop=1',
										$HTML->getDeletePic(_('Stop monitoring'), _('Stop monitoring'), array('onClick' => 'return confirm("'._('Stop monitoring this forum?').'")'))),
										'class' => 'align-center');
								$html .= $HTML->multiTableRow(array(), $cells);
							}
						}
						$html_my_monitored_forums .= $html_hdr.$html;
					} else {
						$html_my_monitored_forums .= $HTML->warning_msg(_('You are not monitoring any forums.')).html_e('p', array(), _("If you monitor forums, you will be sent new posts in the form of an email, with a link to the new message.")).html_e('p', array(), _("You can monitor forums by clicking on the appropriate menu item in the discussion forum itself."));
					}
					if (!$setListTableTop) {
						$html_my_monitored_forums .= $HTML->listTableBottom();
						$html_my_monitored_forums .= html_e('p', array(), _('Detailed page about monitored forums')._(': ').util_make_link('/forum/myforums.php', _('Here')));
					}
				}
			} else {
				$html_my_monitored_forums .= $HTML->warning_msg(_('You are not monitoring any forums.')).html_e('p', array(), _("If you monitor forums, you will be sent new posts in the form of an email, with a link to the new message.")).html_e('p', array(), _("You can monitor forums by clicking on the appropriate menu item in the discussion forum itself."));
			}
		}
		return $html_my_monitored_forums;
	}

	function getCategory() {
		return _('Forums');
	}

	function getDescription() {
		return _("List forums that you are currently monitoring, by project.")
             . "<br />"
             . _("To cancel any of the monitored items just click on the trash icon next to the item label.");
	}

	function isAjax() {
		return true;
	}

	function getAjaxUrl($owner_id, $owner_type) {
		$ajax_url = parent::getAjaxUrl($owner_id, $owner_type);
		if (existInRequest('hide_item_id') || existInRequest('hide_forum')) {
			$ajax_url .= '&hide_item_id='.getIntFromRequest('hide_item_id').'&hide_forum='.getIntFromRequest('hide_forum');
		}
		return $ajax_url;
	}

	function isAvailable() {
		if (!forge_get_config('use_forum')) {
			return false;
		}
		foreach (session_get_user()->getGroups(false) as $p) {
			if ($p->usesForum()) {
				return true;
			}
		}
		return false;
	}
}
