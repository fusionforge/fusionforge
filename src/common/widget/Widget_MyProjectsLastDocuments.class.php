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
require_once('common/docman/DocumentGroup.class.php');

/**
* Widget_MyProjectsLastDocuments
* 
* PROJECT LIST
*/
class Widget_MyProjectsLastDocuments extends Widget {
	function Widget_MyProjectsLastDocuments() {
		$this->Widget('myprojectslastdocuments');
	}

	function getTitle() {
		return _("The 5 Last Documents in My Projects");
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
				$html_hdr = ($i ? '<tr class="boxitem"><td colspan="4">' : '').
					$hide_url.'<a href="/docman/?group_id='.$g->getID().'">'.
					$g->getPublicName().'</a>&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>';

				$html = '';
				if (!$hide_now) {
					$keys = array_keys($df->Documents);
					$j = 0;
					if (!count($keys)) {
						$html .= '<tr><td colspan="4"><div class="warning">'._('No documents').'</div></td></tr>';
					}
					foreach ($keys as $key) {
						$dg = new DocumentGroup($g,$key);
						$html .= '<tr><td colspan="4"><a href="'.util_make_url('/docman/?group_id='.$g->getID().'&view=listfile&dirid='.$key).'">'.$dg->getPath().'</a></td></tr>';
						$j++;
						$count = count($df->Documents[$key]);
						for ($i=0; $i < $count; $i++) {
							$doc =& $df->Documents[$key][$i];
							$html .= '<tr '. $HTML->boxGetAltRowStyle($j) .'>';
							$html .= '<td>&nbsp;</td>';
							switch ($doc->getFileType()) {
								case "URL": {
									$docurl = $doc->getFileName();
									break;
								}
								default: {
									$docurl = util_make_url('/docman/view.php/'.$g->getID().'/'.$doc->getID().'/'.urlencode($doc->getFileName()));
								}
							}
							$html .= '<td><a href="'.$docurl.'">'.$doc->getFilename().'</a></td>';
							$html .= '<td>'.make_user_link($doc->getCreatorUserName(), $doc->getCreatorRealName()).'</td>';
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
		return _("List the last 5 documents (with filename, author and last modification time) published in projects you belong to. Selecting any of these projects brings you to the corresponding Project Document Manager page. The documents will be per directory ordered. Selecting any of directory links will brings you to the corresponding Project Document Manager Listing Directory page");
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
