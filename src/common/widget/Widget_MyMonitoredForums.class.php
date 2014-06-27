<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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
require_once $gfwww.'include/my_utils.php';

/**
 * Widget_MyMonitoredForums
 *
 * Forums that are actively monitored
 */

class Widget_MyMonitoredForums extends Widget {
	function __construct() {
		$this->Widget('mymonitoredforums');
	}

	function getTitle() {
		return _('Monitored Forums');
	}

	function getContent() {
		global $HTML;
		$html_my_monitored_forums = '';
		$sql = "SELECT DISTINCT groups.group_id, groups.group_name,
			forum_group_list.group_forum_id, forum_group_list.forum_name ".
			"FROM groups,forum_group_list,forum_monitored_forums ".
			"WHERE groups.group_id=forum_group_list.group_id ".
			"AND groups.status = 'A' ".
			"AND forum_group_list.group_forum_id=forum_monitored_forums.forum_id ".
			"AND forum_monitored_forums.user_id=$1 ";
		$um = UserManager::instance();
		$current_user = $um->getCurrentUser();
		if ($current_user->getStatus()=='S') {
			$projects = $current_user->getProjects();
			$sql .= "AND groups.group_id IN (". implode(',', $projects) .") ";
		}
		//$sql .= "GROUP BY groups.group_id ORDER BY groups.group_id ASC LIMIT 100";
		$sql .= "ORDER BY groups.group_id ASC LIMIT 100";

		$result = db_query_params($sql, array(user_getid()));
		$glist = array();
		while ($r = db_fetch_array($result)) {
			if (forge_check_perm('project', $r['group_id'], 'read')
					&& forge_check_perm('forum', $r['group_forum_id'], 'read')) {
				$glist[] = serialize(array($r['group_id'], $r['group_name']));
			}
		}
		$glist = array_unique($glist);
		$rows = count($glist);
		if (!$result || $rows < 1) {
			$html_my_monitored_forums .= $HTML->warning_msg(_('You are not monitoring any forums.')).html_e('p', array(), _("If you monitor forums, you will be sent new posts in the form of an email, with a link to the new message.")).html_e('p', array(), _("You can monitor forums by clicking on the appropriate menu item in the discussion forum itself."));
		} else {
			$request =& HTTPRequest::instance();
			$html_my_monitored_forums .= $HTML->listTableTop();
			foreach ($glist as $group) {
				list($group_id, $group_name) = unserialize($group);

				$sql2="SELECT forum_group_list.group_forum_id,forum_group_list.forum_name ".
					"FROM groups,forum_group_list,forum_monitored_forums ".
					"WHERE groups.group_id=forum_group_list.group_id ".
					"AND groups.group_id=$1".
					"AND forum_group_list.group_forum_id=forum_monitored_forums.forum_id ".
					"AND forum_monitored_forums.user_id=$2 LIMIT 100";

				$result2 = db_query_params($sql2, array($group_id, user_getid()));
				$flist = array();
				while ($r = db_fetch_array($result2)) {
					if (forge_check_perm('forum', $r['group_forum_id'], 'read')) {
						$flist[] = $r;
					}
				}

				$rows2 = count($flist);

				$vItemId = new Valid_UInt('hide_item_id');
				$vItemId->required();
				if ($request->valid($vItemId)) {
					$hide_item_id = $request->get('hide_item_id');
				} else {
					$hide_item_id = null;
				}

				$vForum = new Valid_WhiteList('hide_forum', array(0, 1));
				$vForum->required();
				if ($request->valid($vForum)) {
					$hide_forum = $request->get('hide_forum');
				} else {
					$hide_forum = null;
				}

				list($hide_now,$count_diff,$hide_url) = my_hide_url('forum',$group_id,$hide_item_id,$rows2,$hide_forum);
				$count_new = max(0, $count_diff);
				$cells = array();
				$cells[] = array($hide_url.util_make_link('/forum/?group_id='.$group_id, $group_name).'    ['.$rows2.($count_new ? ', '.html_e('b', array(), sprintf(_('%s new'), $count_new)).']' : ']'),
						'colspan' => 2);
				$html_hdr = $HTML->multiTableRow(array('class' => 'boxitem'), $cells);
				$html = '';
				for ($i=0; $i<$rows2; $i++) {
					if (!$hide_now) {
						$group_forum_id = $flist[$i]['group_forum_id'];
						$cells = array();
						$cells[] = array('&nbsp;&nbsp;&nbsp;-&nbsp;'.util_make_link('/forum/forum.php?forum_id='.$group_forum_id, $flist[$i]['forum_name']), 'style' => 'width:99%');
						$cells[] = array(util_make_link('/forum/monitor.php?forum_id='.$group_forum_id.'&group_id='.$group_id.'&stop=1',
										$HTML->getDeletePic(_('Stop Monitoring'), _('Stop Monitoring'), array('onClick' => 'return confirm("'._('Stop monitoring this forum?').'")'))),
								'class' => 'align-center');
						$html .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
					}
				}
				$html_my_monitored_forums .= $html_hdr.$html;
			}
			$html_my_monitored_forums .= $HTML->listTableBottom();
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
		$request =& HTTPRequest::instance();
		$ajax_url = parent::getAjaxUrl($owner_id, $owner_type);
		if ($request->exist('hide_item_id') || $request->exist('hide_forum')) {
			$ajax_url .= '&hide_item_id='.$request->get('hide_item_id').'&hide_forum='.$request->get('hide_forum');
		}
		return $ajax_url;
	}

	function isAvailable() {
		if (!forge_get_config('use_forum')) {
			return false;
		}
		foreach (UserManager::instance()->getCurrentUser()->getGroups(false) as $p) {
			if ($p->usesForum()) {
				return true;
			}
		}
		return false;
	}
}
