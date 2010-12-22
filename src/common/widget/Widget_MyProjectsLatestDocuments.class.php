<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');

/**
* Widget_MyProjectsLatestDocuments
* 
* PROJECT LIST
*/
class Widget_MyProjectsLatestDocuments extends Widget {
	function Widget_MyProjectsLatestDocuments() {
		$this->Widget('myprojectslastestdocuments');
	}

	function getTitle() {
		return _("Lastest Documents in My Projects");
	}

	function getContent() {
		$html_my_projects = '';
		global $HTML;
		$user = session_get_user();
		$groups = $user->getGroups();
		sortProjectList($groups);

		if (count ($groups) < 1) {
			$html_my_projects .= '<div class="warning">'. _("You're not a member of any project") .'</div>';
		} else {
			$html_my_projects .= '<table style="width:100%">';
			$i = 0;
			foreach ($groups as $g) {
				$i++;
				$html_my_projects .= '
					<TR '. $HTML->boxGetAltRowStyle($i) .'"><TD WIDTH="99%">'.
					'<A href="/docman/?group_id='. $g->getID() .'/">'.
					$g->getPublicName().'</A></td></tr>';
			}
			$html_my_projects .= '</table>';
		}
		return $html_my_projects;
	}

	function getDescription() {
		return _("List the documents publish in projects you belong to during the 5 last days. Selecting any of these projects brings you to the corresponding Project Document Manager page.");
	}

	function getCategory() {
		return 'Documents-Manager';
	}

	function isAjax() {
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
