<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
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

require_once('Widget.class.php');

/**
* Widget_ProjectLatestDocuments
* 
*/
class Widget_ProjectLatestDocuments extends Widget {
	var $content;
	function Widget_ProjectLatestDocuments() {
		$this->Widget('projectlatestdocuments');
		$request =& HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$project = $pm->getProject($request->get('group_id'));
		if ($project && $this->canBeUsedByProject($project)) {
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
		$qpa = db_construct_qpa($qpa, 'SELECT filename, title, updatedate, createdate, realname, state_name
						FROM docdata_vw
						WHERE group_id=$1
						AND stateid=$2',
					array($group_id, '1'));

		if (session_loggedin() && (user_ismember($group_id) || user_ismember(1,'A'))) {
			$qpa = db_construct_qpa($qpa, ' OR stateid=$1 OR stateid=$2 OR stateid=$3', array('3','4','5'));
		}

		$qpa = db_construct_qpa($qpa, ' ORDER BY updatedate,createdate DESC LIMIT 5',array());
		$res_files = db_query_qpa($qpa);

		$rows_files = db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No documents
			echo '<div class="warning">'._('This Project Has Not Published Any Documents').'</div>';
		} else {
			$tabletop = array(_('Date'), _('Filename'), _('Title'), _('Author'));
			if (session_loggedin() && (user_ismember($group_id) || user_ismember(1,'A'))) {
				$tabletop[] = _('Status');
			}
			echo $HTML->listTableTop($tabletop, false, 'sortable_widget_docman_listfile', 'sortable');
			for ($f=0; $f<$rows_files; $f++) {
				$updatedate = db_result($res_files, $f, 'updatedate');
				$createdate = db_result($res_files, $f, 'createdate');
				$realdate = ($updatedate >= $createdate) ? $updatedate : $createdate;
				$displaydate = getdate($realdate);
				$filename = db_result($res_files,$f,'filename');
				$title = db_result($res_files,$f,'title');
				$realname = db_result($res_files,$f,'realname');
				$statename = db_result($res_files,$f,'state_name');
				echo '
					<tr class="align-center">
						<td>'
							. $displaydate["month"] . ' ' . $displaydate["mday"] . ', ' . $displaydate["year"] .
						'</td>
						<td>
							<strong>' . $filename . '</strong>
						</td>
						<td>'
							.$title.'
						</td>
						<td >'
							. $realname .
						'</td>';
				if (session_loggedin() && (user_ismember($group_id) || user_ismember(1,'A'))) {
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
		return 'Documents-Manager';
	}

	function getDescription() {
		return _(' List the 5 most recent documents published by team project.');
	}

}

?>
