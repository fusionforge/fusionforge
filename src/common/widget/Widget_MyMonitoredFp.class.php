<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2014, Franck Villaume - TrivialDev
 *
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
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
/**
* Widget_MyMonitoredFp
*
* FRS packages that are actively monitored
*/
class Widget_MyMonitoredFp extends Widget {
	function Widget_MyMonitoredFp() {
		$this->Widget('mymonitoredfp');
	}
	function getTitle() {
		return _('Monitored File Packages');
	}
	function getContent() {
		global $HTML;
		$html_my_monitored_fp = '';
		$sql = "SELECT groups.group_name,groups.group_id ".
			"FROM groups,filemodule_monitor,frs_package ".
			"WHERE groups.group_id=frs_package.group_id ".
			"AND groups.status = 'A'".
			"AND frs_package.package_id=filemodule_monitor.filemodule_id ".
			"AND filemodule_monitor.user_id=$1";
		$um = UserManager::instance();
		$current_user = $um->getCurrentUser();
		if ($current_user->getStatus()=='S') {
			$projects = $current_user->getProjects();
			$sql .= 'AND groups.group_id IN ('. implode(',', $projects) .') ';
		}
		$sql .= 'GROUP BY groups.group_id ORDER BY groups.group_id ASC LIMIT 100';

		$result = db_query_params($sql,array(user_getid()));
		$rows = db_numrows($result);
		if (!$result || $rows < 1) {
			$html_my_monitored_fp .= $HTML->warning_msg(_('You are not monitoring any files.')).html_e('p', array(), _('If you monitor files, you will be sent new release notices via email, with a link to the new file on our download server.')).html_e('p', array(), _("You can monitor files by visiting a project's “Summary Page” and clicking on the appropriate icon in the files section."));
		} else {
			$html_my_monitored_fp .= $HTML->listTableTop();
			$request =& HTTPRequest::instance();
			for ($j = 0; $j < $rows; $j++) {
				$group_id = db_result($result, $j, 'group_id');

				$sql2="SELECT frs_package.name,filemodule_monitor.filemodule_id ".
					"FROM groups,filemodule_monitor,frs_package ".
					"WHERE groups.group_id=frs_package.group_id ".
					"AND groups.group_id=$1 ".
					"AND frs_package.package_id=filemodule_monitor.filemodule_id ".
					"AND filemodule_monitor.user_id=$2  LIMIT 100";
				$result2 = db_query_params($sql2, array($group_id, user_getid()));
				$rows2 = db_numrows($result2);

				$vItemId = new Valid_UInt('hide_item_id');
				$vItemId->required();
				if($request->valid($vItemId)) {
					$hide_item_id = $request->get('hide_item_id');
				} else {
					$hide_item_id = null;
				}

				$vFrs = new Valid_WhiteList('hide_frs', array(0, 1));
				$vFrs->required();
				if($request->valid($vFrs)) {
					$hide_frs = $request->get('hide_frs');
				} else {
					$hide_frs = null;
				}

				list($hide_now, $count_diff, $hide_url) = my_hide_url('frs', $group_id, $hide_item_id, $rows2, $hide_frs);

				$count_new = max(0, $count_diff);
				$cells = array();
				$cells[] = array($hide_url.util_make_link('/frs/?group_id='.$group_id, db_result($result,$j,'group_name')).my_item_count($rows2,$count_new), 'colspan' => 2);
				$html_hdr = $HTML->multiTableRow(array('class' => 'boxitem'), $cells);
				$html = '';
				for ($i = 0; $i < $rows2; $i++) {
					if (!$hide_now) {
						$cells = array();
						$url = '/frs/?group_id='.$group_id.'&package_id='.db_result($result2,$i,'filemodule_id').'&action=monitor&status=0&ajax=0';
						$title = db_result($result2,$i,'name').' - '._('Stop monitoring this package');
						$package_monitor = util_make_link($url, $HTML->getDeletePic($title, $title, array('onClick' => 'return confirm("'._('Stop monitoring this package?').'")')));
						$cells[] = array('    - '.util_make_link('/frs/?group_id='.$group_id, db_result($result2,$i,'name')), 'width' => '99%');
						$cells[][] = $package_monitor;
						$html .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
					}
				}
				$html_my_monitored_fp .= $html_hdr .$html;
			}
			$html_my_monitored_fp .= $HTML->listTableBottom();
		}
		return $html_my_monitored_fp;
	}

	function getCategory() {
		return _('File Release System');
	}
	function getDescription() {
		return _('List packages that you are currently monitoring, by project.')
		. '<br />'
		. _('To cancel any of the monitored items just click on the trash icon next to the item label.');
	}
	function isAjax() {
		return true;
	}

	function isAvailable() {
		if (!forge_get_config('use_frs')) {
			return false;
		}
		foreach (UserManager::instance()->getCurrentUser()->getGroups(false) as $p) {
			if ($p->usesFRS()) {
				return true;
			}
		}
		return false;
	}
}
