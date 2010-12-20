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
			$this->content['title'] = _('Latest Published Documents');
		}
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getContent() {
		$request =& HTTPRequest::instance();
		$pm = ProjectManager::instance();
		$group_id = $request->get('group_id');
		$project = $pm->getProject($group_id);
		$unix_group_name = $project->getUnixName();
		echo '
			<table summary="Latest publish documents" class="width-100p100">
				<tr class="table-header">
					<th class="align-left" scope="col">
						'._('Filename').'
					</th>
					<th scope="col">
						'._('Title').'
					</th>
					<th scope="col">
						'._('Description').'
					</th>
					<th scope="col">
						'._('Date').'
					</th>
					<th scope="col">
						'._('Author').'
					</th>
				</tr>';

		$res_files = db_query_params('SELECT filename, title, description, updatedate, realname
						FROM docdata_vw
						WHERE group_id=$1
						AND stateid=$2
						ORDER BY filename,updatedate DESC',
					array ($group_id,'2'));

		$rows_files = db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No documents
			echo '<tr><td colspan="6"><strong>'._('This Project Has Not Published Any Documents').'</strong></td></tr>';
		} else {
			for ($f=0; $f<$rows_files; $f++) {
				$updatedate = getdate(db_result($res_files, $f, 'updatedate'));
				$filename = db_result($res_files,$f,'filename');
				$title = db_result($res_files,$f,'title');
				$description = db_result($res_files,$f,'description');
				$realname = db_result($res_files,$f,'realname');
				echo '
					<tr class="align-center">
						<td class="align-left">
							<strong>' . $filename . '</strong>
						</td>
						<td>'
							.$title.'
						</td>
						<td>'
							. $description .
						'</td>
						<td>'
							. $updatedate["month"] . ' ' . $updatedate["mday"] . ', ' . $updatedate["year"] .
						'</td>
						<td >'
							. $realname .
						'</td>
					</tr>';
			}
		}
		echo '</table>';
		echo '<div class="underline-link">' . util_make_link('/docman/?group_id='.$group_id, _('Browse Documents Project')) . '</div>';
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function canBeUsedByProject(&$project) {
		return $project->usesDocman();
	}

	function getCategory() {
		return 'Document Manager';
	}

	function getDescription() {
		return _(' List the most recent documents published by team project.');
	}

}

?>
