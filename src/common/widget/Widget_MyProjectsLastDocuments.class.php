<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2011-2014,2016, Franck Villaume - TrivialDev
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
require_once $gfcommon.'docman/DocumentFactory.class.php';

/**
 * Widget_MyProjectsLastDocuments
 */

class Widget_MyProjectsLastDocuments extends Widget {
	function __construct() {
		$this->Widget('myprojectslastdocuments');
	}

	function getTitle() {
		return _('The 5 Last Documents in My Projects');
	}

	function getContent() {
		$html_my_projects = '';
		global $HTML;
		$user = session_get_user();
		$groups = $user->getGroups();

		if (count ($groups) < 1) {
			$html_my_projects .= $HTML->warning_msg(_("You're not a member of any project"));
		} else {
			sortProjectList($groups);
			$request = HTTPRequest::instance();
			$vItemId = new Valid_UInt('hide_item_id');
			$vItemId->required();
			if($request->valid($vItemId)) {
				$hide_item_id = $request->get('hide_item_id');
			} else {
				$hide_item_id = null;
			}
			$html_my_projects .= $HTML->listTableTop();
			$i = 0;
			foreach ($groups as $g) {
				if ($g->usesDocman()) {
					$i++;

					$vWhiteList = new Valid_WhiteList('hide_dmproj', array(0, 1));
					$vWhiteList->required();
					if($request->valid($vWhiteList)) {
						$hide_docmanproject = $request->get('hide_dmproj');
					} else {
						$hide_docmanproject = null;
					}

					$stateIdDg = 1;
					if (forge_check_perm('docman', $g->getID(), 'approve')) {
						$stateIdDg = 5;
					}
					$df = new DocumentFactory($g);
					$df->setDocGroupState($stateIdDg);
					$df->setLimit(5);
					$df->setOrder(array('updatedate', 'createdate'));
					$df->setSort('DESC');
					$df->getDocuments();

					list($hide_now,$count_diff,$hide_url) = my_hide_url('dmproj', $g->getID(), $hide_item_id, count($df->Documents), $hide_docmanproject);
					$html_hdr = ($i ? '<tr class="boxitem"><td colspan="4">' : '').
						$hide_url.util_make_link('/docman/?group_id='.$g->getID(), $g->getPublicName()).
						'&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>';

					$html = '';
					if (!$hide_now) {
						$keys = array_keys($df->Documents);
						$j = 0;
						if (!count($keys)) {
							$html .= '<tr><td colspan="4">'.$HTML->warning_msg(_('No documents.')).'</td></tr>';
						}
						foreach ($keys as $key) {
							$dg = new DocumentGroup($g, $key);
							$view = ($dg->getState() != 2) ? 'listfile' : 'listtrashfile';
							$html .= '<tr><td colspan="4">'.util_make_link('/docman/?group_id='.$g->getID().'&view='.$view.'&dirid='.$key, $dg->getPath()).'</td></tr>';
							$j++;
							$count = count($df->Documents[$key]);
							for ($i=0; $i < $count; $i++) {
								$doc =& $df->Documents[$key][$i];
								$html .= '<tr '. $HTML->boxGetAltRowStyle($j) .'>';
								switch ($doc->getFileType()) {
									case "URL": {
										$docurl = util_make_link($doc->getFileName(), html_image($doc->getFileTypeImage(), '22', '22', array('alt'=>$doc->getFileType())), array(), true);
										break;
									}
									default: {
										$docurl = util_make_link('/docman/view.php/'.$g->getID().'/'.$doc->getID().'/'.urlencode($doc->getFileName()), html_image($doc->getFileTypeImage(), '22', '22', array('alt'=>$doc->getFileType())));
									}
								}
								$html .= '<td>'.$docurl.'</td>';
								$html .= '<td>'.$doc->getFilename().'</td>';
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
			}
			$html_my_projects .= $HTML->listTableBottom();
		}
		return $html_my_projects;
	}

	function getDescription() {
		return _('List the last 5 documents (with filename, author and last modification time) published in projects you belong to. Selecting any of these projects brings you to the corresponding Project Document Manager page. The documents will be per directory ordered. Selecting any of directory links will brings you to the corresponding Project Document Manager Listing Directory page');
	}

	function getCategory() {
		return _('Documents Manager');
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
