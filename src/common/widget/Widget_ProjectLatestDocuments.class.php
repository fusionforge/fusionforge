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
		$qpa = db_construct_qpa($qpa, 'SELECT filename, title, updatedate, createdate, realname, user_name, state_name, filetype, docid, doc_group
						FROM docdata_vw
						WHERE group_id=$1
						AND state_id=$2',
					array($group_id,'1'));

		if (session_loggedin() && (user_ismember($group_id) ||
		    forge_check_global_perm('forge_admin'))) {
			$qpa = db_construct_qpa($qpa, ' AND stateid IN ($1, $2, $3, $4)', array('1','3','4','5'));
		} else {
			$qpa = db_construct_qpa($qpa, ' AND stateid=$1', array('1'));
		}

		$qpa = db_construct_qpa($qpa, ' ORDER BY updatedate,createdate DESC LIMIT 5',array());
		$res_files = db_query_qpa($qpa);

		$rows_files = db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No documents
			echo '<div class="warning">'._('This Project Has Not Published Any Documents').'</div>';
		} else {
			$tabletop = array(_('Date'), _('File Name'), _('Title'), _('Author'), _('Path'));
			if (session_loggedin() && (user_ismember($group_id) ||
			    forge_check_global_perm('forge_admin'))) {
				$tabletop[] = _('Status');
			}
			echo $HTML->listTableTop($tabletop, false, 'sortable_widget_docman_listfile', 'sortable');
			for ($f=0; $f<$rows_files; $f++) {
				$updatedate = db_result($res_files, $f, 'updatedate');
				$createdate = db_result($res_files, $f, 'createdate');
				$realdate = ($updatedate >= $createdate) ? $updatedate : $createdate;
				$filename = db_result($res_files,$f,'filename');
				$title = db_result($res_files,$f,'title');
				$realname = db_result($res_files,$f,'realname');
				$user_name = db_result($res_files,$f,'user_name');
				$statename = db_result($res_files,$f,'state_name');
				$filetype = db_result($res_files,$f,'filetype');
				$docid = db_result($res_files,$f,'docid');
				$docgroup = db_result($res_files,$f,'doc_group');
				$ndg = new DocumentGroup(group_get_object($group_id), $docgroup);
				$path = $ndg->getPath(true, true);
				switch ($filetype) {
					case "URL": {
						$docurl = $filename;
						break;
					}
					default: {
						$docurl = util_make_url('/docman/view.php/'.$group_id.'/'.$docid.'/'.urlencode($filename));
					}
				}
				echo '
					<tr '. $HTML->boxGetAltRowStyle($f+1) .'>
						<td>'
							. date(_('Y-m-d'),$realdate) .
						'</td>
						<td>
							<a href="'.$docurl.'" ><strong>' . $filename . '</strong></a>
						</td>
						<td>'
							.$title.'
						</td>
						<td >'
							. make_user_link($user_name, $realname) .
						'</td>
						<td>'
							. $path .
						'</td>';
				if (session_loggedin() && (user_ismember($group_id) ||
				    forge_check_global_perm('forge_admin'))) {
					echo	'<td>'
							. $statename .
						'</td>';
				}
				echo	'</tr>';
			}
			echo $HTML->listTableBottom();
		}
		echo '<div class="underline-link">' . util_make_link('/docman/?group_id='.$group_id, _('Browse Documents Manager')) . '</div>';
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
