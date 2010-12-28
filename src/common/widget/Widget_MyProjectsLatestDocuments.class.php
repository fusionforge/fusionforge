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
require_once('common/docman/DocumentFactory.class.php');

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
		return _("5 Lastest Documents in My Projects");
	}

	function getContent() {
		$html_my_projects = '';
		global $HTML;
		$user = session_get_user();
		$groups = $user->getGroups();
		sortProjectList($groups);
		$request =& HTTPRequest::instance();

		if (count ($groups) < 1) {
			$html_my_projects .= '<div class="warning">'. _("You're not a member of any project") .'</div>';
		} else {
			$html_my_projects .= '<table style="width:100%">';
			$i = 0;
			foreach ($groups as $g) {
				$i++;
				$vItemId = new Valid_UInt('hide_item_id');
				$vItemId->required();
				if($request->valid($vItemId)) {
					$hide_item_id = $request->get('hide_item_id');
				} else {
					$hide_item_id = null;
				}

				$vWhiteList = new Valid_WhiteList('hide_docmanproject', array(0, 1));
				$vWhiteList->required();
				if($request->valid($vWhiteList)) {
					$hide_docmanproject = $request->get('hide_docmanproject');
				} else {
					$hide_docmanproject = null;
				}

				$df = new DocumentFactory($g);
				$df->setLimit(5);
				$df->setOrder(array('updatedate','createdate'));
				$df->setSort('DESC');
				$df->getDocuments();

				list($hide_now,$count_diff,$hide_url) = my_hide_url('docmanproject',$g->getID(),$hide_item_id,count($df->Documents),$hide_docmanproject);
				$html_hdr = ($i ? '<tr class="boxitem"><td colspan="2">' : '').
					$hide_url.'<a href="/docman/?group_id='.$g->getID().'">'.
					$g->getPublicName().'</a>&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>';

				$html = '';
				if (!$hide_now) {
					$keys = array_keys($df->Documents);
					$j = 0;
					foreach ($keys as $key) {
						$j++;
						$count = count($df->Documents[$key]);
						for ($i=0; $i < $count; $i++) {
							$doc =& $df->Documents[$key][$i];
							$html .= '<tr '. $HTML->boxGetAltRowStyle($j) .'>';
							$html .= '<td>'.$doc->getFilename().'</td>';
							if ( $doc->getUpdated() ) {
								$html .= '<td>'.date(_('Y-m-d H:i'), $doc->getUpdated()).'</td>';
							} else {
								$html .= '<td>'.date(_('Y-m-d H:i'), $doc->getCreated()).'</td>';
							}
							$html .= '</tr>';
							$j++;
						}
						$j--;
					}
				}

				$html_my_projects .= $html_hdr.$html;
			}
			$html_my_projects .= '</table>';
		}
		return $html_my_projects;
	}

	function getDescription() {
		return _("List the last 5 documents publish in projects you belong to. Selecting any of these projects brings you to the corresponding Project Document Manager page. The documents will be per directory ordered.");
	}

	function getCategory() {
		return 'Documents-Manager';
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
