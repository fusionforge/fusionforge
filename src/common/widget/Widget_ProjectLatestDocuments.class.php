<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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

/**
 * Widget_ProjectLatestDocuments
 */

class Widget_ProjectLatestDocuments extends Widget {
	var $content;
	function __construct() {
		$this->Widget('projectlatestdocuments');
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
		global $HTML;
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');

		$qpa = db_construct_qpa();
		$qpa = db_construct_qpa($qpa, 'SELECT docid FROM doc_data WHERE group_id=$1 AND stateid=$2',
					array($group_id, '1'));

		if (session_loggedin() && forge_check_perm('docman', $group_id, 'approve')) {
			$qpa = db_construct_qpa($qpa, ' AND stateid IN ($1, $2, $3, $4)', array('1', '3', '4', '5'));
		}

		$qpa = db_construct_qpa($qpa, ' ORDER BY updatedate,createdate DESC LIMIT 5',array());
		$res_files = db_query_qpa($qpa);

		$rows_files = db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No documents
			echo $HTML->information(_('This project has not published any documents.'));
		} else {
			use_javascript('/js/sortable.js');
			echo $HTML->getJavascripts();
			$tabletop = array(_('Date'), _('File Name'), _('Title'), _('Author'), _('Path'));
			if (session_loggedin()) {
				$tabletop[] = _('Status');
				$tabletop[] = _('Actions');
			}
			echo $HTML->listTableTop($tabletop, false, 'sortable_widget_docman_listfile full', 'sortable');
			for ($f=0; $f < $rows_files; $f++) {
				$documentObject = document_get_object(db_result($res_files, $f, 'docid'));
				$updatedate = $documentObject->getUpdated();
				$createdate = $documentObject->getCreated();
				$realdate = ($updatedate >= $createdate) ? $updatedate : $createdate;
				$filename = $documentObject->getFileName();
				$title = $documentObject->getFileName();
				$realname = $documentObject->getCreatorRealName();
				$user_name = $documentObject->getCreatorUserName();
				$statename = $documentObject->getStateName();
				$filetype = $documentObject->getFileType();
				$docid = $documentObject->getID();
				$docgroup = $documentObject->getDocGroupID();
				$ndg = documentgroup_get_object($docgroup);
				$path = $ndg->getPath(true, true);
				switch ($filetype) {
					case "URL": {
						$docurl = util_make_link($filename, $filename, array(), true);
						break;
					}
					default: {
						$docurl = util_make_link('/docman/view.php/'.$group_id.'/'.$docid.'/'.urlencode($filename), '<strong>'.$filename.'</strong>');
					}
				}
				$cells = array();
				$cells[][] = date(_('Y-m-d'),$realdate);
				$cells[][] = $docurl;
				$cells[][] = $title;
				$cells[][] = make_user_link($user_name, $realname);
				$cells[][] = $path;
				if (session_loggedin()) {
					$cells[][] = $statename;
					if ($documentObject->isMonitoredBy(UserManager::instance()->getCurrentUser()->getID())) {
						$option = 'stop';
						$titleMonitor = _('Stop monitoring this document');
						$image = $HTML->getStopMonitoringPic($titleMonitor, $titleMonitor);
					} else {
						$option = 'start';
						$titleMonitor = _('Start monitoring this document');
						$image = $HTML->getStartMonitoringPic($titleMonitor, $titleMonitor);
					}
					$action = util_make_link('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$docgroup.'&action=monitorfile&option='.$option.'&fileid='.$documentObject->getID(), $image, array('title' => $titleMonitor));
					if (forge_check_perm('docman', $group_id, 'approve') && !$documentObject->getLocked()) {
						$action .= util_make_link('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$docgroup.'&action=trashfile&fileid='.$documentObject->getID(), $HTML->getDeletePic('', _('Move this document to trash')), array('title' => _('Move this document to trash')));
					}
					$cells[][] = $action;
				}

				echo $HTML->multiTableRow(array(), $cells);
			}
			echo $HTML->listTableBottom();
		}
		echo html_e('div', array('class' => 'underline-link'), util_make_link('/docman/?group_id='.$group_id, _('Browse Documents Manager')));
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
