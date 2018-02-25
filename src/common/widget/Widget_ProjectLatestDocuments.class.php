<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2012-2013,2016, Franck Villaume - TrivialDev
 * Copyright 2013, French Ministry of National Education
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
 * along with FusionForge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';

/**
 * Widget_ProjectLatestDocuments
 */

class Widget_ProjectLatestDocuments extends Widget {
	var $content;
	function __construct() {
		parent::__construct('projectlatestdocuments');
		$request =& HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$project = $pm->getProject($request->get('group_id'));
		if ($project && $this->canBeUsedByProject($project) && forge_check_perm('docman', $project->getID(), 'read')) {
			$this->content['title'] = _('5 Latest Published Documents');
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getContent() {
		$result = '';

		global $HTML;
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');

		$qpa = db_construct_qpa();
		$qpa = db_construct_qpa($qpa, 'SELECT docid FROM doc_data, doc_groups WHERE doc_data.group_id = $1',
					array($group_id));

		$stateIdDg = 1;
		$stateIdDocuments = array(1);
		if (forge_check_perm('docman', $group_id, 'approve')) {
			$stateIdDg = 5;
			$stateIdDocuments = array(1, 2, 3, 4, 5);
		}
		$df = new DocumentFactory(group_get_object($group_id));
		$df->setDocGroupState($stateIdDg);
		$df->setStateID($stateIdDocuments);
		$df->setLimit(5);
		$df->setOrder(array('updatedate', 'createdate'));
		$df->setSort('DESC');
		$df->getDocuments();

		$keys = array_keys($df->Documents);
		if (!count($keys)) {
			$result .= $HTML->warning_msg(_('This project has not published any documents.'));
		} else {
			html_use_tablesorter();
			$result .= $HTML->getJavascripts();
			$tabletop = array(_('Date'), _('File Type'), _('File Name'), _('Title'), _('Author'), _('Path'));
			$classth = array('', '', '', '', '', '');
			if (session_loggedin()) {
				$tabletop[] = _('Status');
				$classth[] = '';
				$tabletop[] = _('Actions');
				$classth[] = 'unsortable';
			}
			$result .= $HTML->listTableTop($tabletop, array(), 'sortable_widget_docman_listfile full', 'sortable_docman', $classth);

			foreach ($keys as $key) {
				$count = count($df->Documents[$key]);
				for ($i=0; $i < $count; $i++) {
					$doc =& $df->Documents[$key][$i];
					$updatedate = $doc->getUpdated();
					$createdate = $doc->getCreated();
					$filename = $doc->getFileName();
					$filetype = $doc->getFileType();
					$docid = $doc->getID();
					$docgroup = $doc->getDocGroupID();
					$ndg = documentgroup_get_object($docgroup, $group_id);
					switch ($filetype) {
						case "URL": {
							$docurl = util_make_link($filename, html_image($doc->getFileTypeImage(), 22, 22, array('alt'=>$doc->getFileType())), array(), true);
							break;
						}
						default: {
							$docurl = util_make_link('/docman/view.php/'.$group_id.'/'.$docid, html_image($doc->getFileTypeImage(), 22, 22, array('alt'=>$doc->getFileType())));
						}
					}
					$cells = array();
					$cells[][] = date(_('Y-m-d'), ($updatedate >= $createdate) ? $updatedate : $createdate);
					$cells[][] = $docurl;
					$cells[][] = $filename;
					$cells[] = array($doc->getName(), 'title' => $doc->getDescription());
					$cells[][] = util_display_user($doc->getCreatorUserName(), $doc->getCreatorID(), $doc->getCreatorRealName());
					$cells[][] = $ndg->getPath(true, true);
					if (session_loggedin()) {
						$cells[][] = $doc->getStateName();
						$action = '';
						if ($doc->getStateID() != 2) {
							if ($doc->isMonitoredBy(UserManager::instance()->getCurrentUser()->getID())) {
								$option = 'stop';
								$titleMonitor = _('Stop monitoring this document');
								$image = $HTML->getStopMonitoringPic($titleMonitor, $titleMonitor);
							} else {
								$option = 'start';
								$titleMonitor = _('Start monitoring this document');
								$image = $HTML->getStartMonitoringPic($titleMonitor, $titleMonitor);
							}
							$action .= util_make_link('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$docgroup.'&action=monitorfile&option='.$option.'&fileid='.$doc->getID(), $image, array('title' => $titleMonitor));
							if (forge_check_perm('docman', $group_id, 'approve') && !$doc->getLocked()) {
								$action .= util_make_link('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$docgroup.'&action=trashfile&fileid='.$doc->getID(), $HTML->getDeletePic('', _('Move this document to trash')), array('title' => _('Move this document to trash')));
								$action .= util_make_link($doc->getPermalink(), $HTML->getEditFilePic(_('Edit this document')), array('title' => _('Edit this document')));
							}
						}
						$cells[][] = $action;
					}
					$result .= $HTML->multiTableRow(array(), $cells);
				}
			}
			$result .= $HTML->listTableBottom();
		}
		$result .= html_e('div', array('class' => 'underline-link'), util_make_link('/docman/?group_id='.$group_id, _('Browse Documents Manager')));

		return $result;
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function canBeUsedByProject(&$project) {
		return $project->usesDocman();
	}

	function getCategory() {
		return _('Documents Manager');
	}

	function getDescription() {
		return _('List the 5 most recent documents published by team project.');
	}
}
