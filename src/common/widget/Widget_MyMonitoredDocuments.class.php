<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2011-2014, Franck Villaume - TrivialDev
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
require_once $gfwww.'include/my_utils.php';
require_once $gfcommon.'include/MonitorElement.class.php';

/**
 * Widget_MyMonitoredDocuments
 *
 * Documents that are actively monitored
 */

class Widget_MyMonitoredDocuments extends Widget {
	function __construct() {
		$this->Widget('mymonitoreddocuments');
	}

	function getTitle() {
		return _('Monitored Documents');
	}

	function getContent() {
		global $HTML;
		$html_my_monitored_documents = '';
		$monitorElementObject = new MonitorElement('docdata');
		$distinctMonitorGroupIdsArray = $monitorElementObject->getMonitoredDistinctGroupIdsByUserIdInArray(user_getid());
		if (!$distinctMonitorGroupIdsArray || count($distinctMonitorGroupIdsArray) < 1) {
			$html_my_monitored_documents .= $HTML->warning_msg(_('You are not monitoring any documents.')).html_e('p', array(), _("If you monitor documents, you will be sent new update in the form of an email.")).html_e('p', array(), _("You can monitor documents by clicking on the appropriate icon action in the directory itself."));
		} else {
			$validDistinctMonitorGroupIdsArray = array();
			foreach ($distinctMonitorGroupIdsArray as $distinctMonitorGroupId) {
				if (forge_check_perm('docman', $distinctMonitorGroupId, 'read')) {
					$validDistinctMonitorGroupIdsArray[] = $distinctMonitorGroupId;
				} else {
					// Oh ho! we found some monitored documents where user has no read access. Let's clean the situation
					$monitorElementObject->disableMonitoringForGroupIdByUserId($distinctMonitorGroupId, user_getid());
				}
			}
			if (count($validDistinctMonitorGroupIdsArray)) {
				$request =& HTTPRequest::instance();
				$html_my_monitored_documents .= $HTML->listTableTop();
				$vItemId = new Valid_UInt('hide_item_id');
				$vItemId->required();
				if($request->valid($vItemId)) {
					$hide_item_id = $request->get('hide_item_id');
				} else {
					$hide_item_id = null;
				}
				$vDocument = new Valid_WhiteList('hide_document', array(0, 1));
				$vDocument->required();
				if($request->valid($vDocument)) {
					$hide_document = $request->get('hide_document');
				} else {
					$hide_document = null;
				}
				foreach ($distinctMonitorGroupIdsArray as $distinctMonitorGroupId) {
					$groupObject = group_get_object($distinctMonitorGroupId);
					$monitorElementIds = $monitorElementObject->getMonitoredIdsByGroupIdByUserIdInArray($distinctMonitorGroupId, user_getid());

					list($hide_now, $count_diff, $hide_url) = my_hide_url('document', $distinctMonitorGroupId, $hide_item_id, count($monitorElementIds), $hide_document);
					$count_new = max(0, $count_diff);
					$cells = array();
					$cells[] = array($hide_url.util_make_link('/docman/?group_id='.$distinctMonitorGroupId, $groupObject->getPublicName()).'&nbsp;&nbsp;&nbsp;&nbsp;'.
							'['.count($monitorElementIds).($count_new ? ', '.html_e('b', array(), sprintf(_('%s new'), $count_new)).']' : ']'), 'colspan' => 2);
					$html_hdr = $HTML->multiTableRow(array('class' => 'boxitem'), $cells);
					$html = '';
					if (!$hide_now) {
						foreach ($monitorElementIds as $key => $monitorElementId) {
							$documentObject = document_get_object($monitorElementId);
							$cells = array();
							$cells[] = array('&nbsp;&nbsp;&nbsp;-&nbsp;'.util_make_link('/docman/?group_id='.$distinctMonitorGroupId.'&view=listfile&dirid='.$documentObject->getDocGroupID(), stripslashes($documentObject->getFileName())), 'style' => 'width:99%');
							$cells[] = array(util_make_link('/docman/?group_id='.$distinctMonitorGroupId.'&action=monitorfile&option=stop&view=listfile&dirid='.$documentObject->getDocGroupID().'&fileid='.$documentObject->getID(),
									$HTML->getDeletePic(_('Stop Monitoring'), _('Stop Monitoring'), array('onClick' => 'return confirm("'._('Stop monitoring this document?').'")'))),
									'class' => 'align-center');
							$html .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($key, true)), $cells);

						}
					}


					$html_my_monitored_documents .= $html_hdr.$html;
				}
				$html_my_monitored_documents .= $HTML->listTableBottom();
			} else {
				$html_my_monitored_documents .= $HTML->warning_msg(_('You are not monitoring any documents.')).html_e('p', array(), _("If you monitor documents, you will be sent new update in the form of an email.")).html_e('p', array(), _("You can monitor documents by clicking on the appropriate icon action in the directory itself."));
			}
		}
		return $html_my_monitored_documents;
	}

	function getCategory() {
		return _('Documents Manager');
	}

	function getDescription() {
		return _("List documents that you are currently monitoring, by project.")
				. '<br />'
				. _("To cancel any of the monitored items just click on the trash icon next to the item label.");
	}

	function isAvailable() {
		if (!forge_get_config('use_docman')) {
			return false;
		}
		foreach (UserManager::instance()->getCurrentUser()->getGroups(false) as $p) {
			if ($p->usesDocman()) {
				return true;
			}
		}
		return false;
	}
}
