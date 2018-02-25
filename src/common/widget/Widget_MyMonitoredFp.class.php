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
require_once $gfwww.'include/my_utils.php';
require_once $gfcommon.'include/MonitorElement.class.php';

/**
* Widget_MyMonitoredFp
*
* FRS packages that are actively monitored
*/
class Widget_MyMonitoredFp extends Widget {
	function __construct() {
		parent::__construct('mymonitoredfp');
	}
	function getTitle() {
		return _('Monitored File Packages');
	}
	function getContent() {
		global $HTML;
		$html_my_monitored_fp = '';
		$monitorElementObject = new MonitorElement('frspackage');
		$distinctMonitorGroupIdsArray = $monitorElementObject->getMonitoredDistinctGroupIdsByUserIdInArray(user_getid());
		if (!$distinctMonitorGroupIdsArray || count($distinctMonitorGroupIdsArray) < 1) {
			$html_my_monitored_fp .= $HTML->warning_msg(_('You are not monitoring any files.')).html_e('p', array(), _('If you monitor files, you will be sent new release notices via email, with a link to the new file on our download server.')).html_e('p', array(), _("You can monitor files by visiting a project's “Summary Page” and clicking on the appropriate icon in the files section."));
		} else {
			$validDistinctMonitorGroupIdsArray = array();
			foreach ($distinctMonitorGroupIdsArray as $distinctMonitorGroupId) {
				if (forge_check_perm('frs_admin', $distinctMonitorGroupId, 'read')) {
					$validDistinctMonitorGroupIdsArray[] = $distinctMonitorGroupId;
				} else {
					// Oh ho! we found some monitored elements where user has no read access. Let's clean the situation
					$monitorElementObject->disableMonitoringForGroupIdByUserId($distinctMonitorGroupId, user_getid());
				}
			}
			if (count($validDistinctMonitorGroupIdsArray)) {
				$request =& HTTPRequest::instance();
				$vItemId = new Valid_UInt('hide_item_id');
				$vItemId->required();
				if ($request->valid($vItemId)) {
					$hide_item_id = $request->get('hide_item_id');
				} else {
					$hide_item_id = null;
				}
				$vFrs = new Valid_WhiteList('hide_frs', array(0, 1));
				$vFrs->required();
				if ($request->valid($vFrs)) {
					$hide_frs = $request->get('hide_frs');
				} else {
					$hide_frs = null;
				}
				$setListTableTop = true;
				foreach ($validDistinctMonitorGroupIdsArray as $validDistinctMonitorGroupId) {
					$groupObject = group_get_object($validDistinctMonitorGroupId);
					$monitoredPackageIdsArray = $monitorElementObject->getMonitoredIdsByGroupIdByUserIdInArray($validDistinctMonitorGroupId, user_getid());
					$validMonitoredPackageIds = array();
					foreach ($monitoredPackageIdsArray as $monitoredPackageId) {
						if (forge_check_perm('frs', $monitoredPackageId, 'read')) {
							$validMonitoredPackageIds[] = $monitoredPackageId;
						} else {
							// Oh ho! we found some monitored elements where user has no read access. Let's clean the situation
							$monitorElementObject->disableMonitoringByUserId($monitoredPackageId, user_getid());
						}
					}
					if (count($validMonitoredPackageIds)) {
						if ($setListTableTop) {
							$html_my_monitored_fp .= $HTML->listTableTop();
							$setListTableTop = false;
						}
						list($hide_now, $count_diff, $hide_url) = my_hide_url('frs', $validDistinctMonitorGroupId, $hide_item_id, count($validMonitoredPackageIds), $hide_frs);
						$count_new = max(0, $count_diff);
						$cells = array();
						$cells[] = array($hide_url.util_make_link('/frs/?group_id='.$validDistinctMonitorGroupId, $groupObject->getPublicName()).my_item_count(count($validMonitoredPackageIds),$count_new), 'colspan' => 2);
						$html_hdr = $HTML->multiTableRow(array('class' => 'boxitem'), $cells);
						$html = '';
						if (!$hide_now) {
							foreach ($validMonitoredPackageIds as $key => $validMonitoredPackageId) {
								$frsPackageObject = frspackage_get_object($validMonitoredPackageId);
								$cells = array();
								$url = '/frs/?group_id='.$validDistinctMonitorGroupId.'&package_id='.$validMonitoredPackageId.'&action=monitor&status=0&ajax=0';
								$title = $frsPackageObject->getName().' - '._('Stop monitoring this package');
								$package_monitor = util_make_link($url, $HTML->getDeletePic($title, $title, array('onClick' => 'return confirm("'._('Stop monitoring this package?').'")')));
								$cells[] = array('    - '.util_make_link('/frs/?group_id='.$validDistinctMonitorGroupId, $frsPackageObject->getName()), 'width' => '99%');
								$cells[][] = $package_monitor;
								$html .= $HTML->multiTableRow(array(), $cells);
							}
						}
						$html_my_monitored_fp .= $html_hdr .$html;
					} else {
						$html_my_monitored_fp .= $HTML->warning_msg(_('You are not monitoring any files.')).html_e('p', array(), _('If you monitor files, you will be sent new release notices via email, with a link to the new file on our download server.')).html_e('p', array(), _("You can monitor files by visiting a project's “Summary Page” and clicking on the appropriate icon in the files section."));
					}
					if (!$setListTableTop) {
						$html_my_monitored_fp .= $HTML->listTableBottom();
					}
				}
			} else {
				$html_my_monitored_fp .= $HTML->warning_msg(_('You are not monitoring any files.')).html_e('p', array(), _('If you monitor files, you will be sent new release notices via email, with a link to the new file on our download server.')).html_e('p', array(), _("You can monitor files by visiting a project's “Summary Page” and clicking on the appropriate icon in the files section."));
			}
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

	function getAjaxUrl($owner_id, $owner_type) {
		$request =& HTTPRequest::instance();
		$ajax_url = parent::getAjaxUrl($owner_id, $owner_type);
		if ($request->exist('hide_item_id') || $request->exist('hide_frs')) {
			$ajax_url .= '&hide_item_id='.$request->get('hide_item_id').'&hide_frs='.$request->get('hide_frs');
		}
		return $ajax_url;
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
