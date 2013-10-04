<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2011-2013, Franck Villaume - TrivialDev
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
		return _("Monitored Documents");
	}

	function getContent() {
		$html_my_monitored_documents = '';
		$result=db_query_params('select DISTINCT groups.group_name, docdata_vw.group_id from groups, docdata_vw, docdata_monitored_docman where docdata_monitored_docman.doc_id = docdata_vw.docid and groups.group_id = docdata_vw.group_id and docdata_monitored_docman.user_id = $1',array(user_getid()));
		$rows=db_numrows($result);
		if (!$result || $rows < 1) {
			$html_my_monitored_documents .= '<div class="warning">' . _("You are not monitoring any documents.") . '</div><p>' . _("If you monitor documents, you will be sent new update in the form of an email.") . '</p><p>' . _("You can monitor documents by clicking on the appropriate icon action in the directory itself.") . '</p>';
		} else {
			$request =& HTTPRequest::instance();
			$html_my_monitored_documents .= '<table style="width:100%">';
			$vItemId = new Valid_UInt('hide_item_id');
			$vItemId->required();
			if($request->valid($vItemId)) {
				$hide_item_id = $request->get('hide_item_id');
			} else {
				$hide_item_id = null;
			}
			for ($j=0; $j<$rows; $j++) {
				$group_id = db_result($result,$j,'group_id');
				$sql2 = "select docdata_vw.docid, docdata_vw.doc_group, docdata_vw.filename, docdata_vw.filetype, docdata_vw.description from docdata_vw,docdata_monitored_docman where docdata_vw.docid = docdata_monitored_docman.doc_id and docdata_vw.group_id = $1 and docdata_monitored_docman.user_id = $2 limit 100";
				$result2 = db_query_params($sql2,array($group_id,user_getid()));
				$rows2 = db_numrows($result2);

				$vDocument = new Valid_WhiteList('hide_document', array(0, 1));
				$vDocument->required();
				if($request->valid($vDocument)) {
					$hide_document = $request->get('hide_document');
				} else {
					$hide_document = null;
				}

				list($hide_now,$count_diff,$hide_url) = my_hide_url('document',$group_id,$hide_item_id,$rows2,$hide_document);

				$html_hdr = '<tr class="boxitem"><td colspan="2">'.
				$hide_url.'<a href="/docman/?group_id='.$group_id.'">'.
				db_result($result,$j,'group_name').'</a>&nbsp;&nbsp;&nbsp;&nbsp;';

				$html = '';
				$count_new = max(0, $count_diff);
				for ($i = 0; $i < $rows2; $i++) {
					if (!$hide_now) {
						$doc_group = db_result($result2,$i,'doc_group');
						$docid = db_result($result2,$i,'docid');
						$html .= '
							<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td width="99%">'.
							'&nbsp;&nbsp;&nbsp;-&nbsp;<a href="/docman/?group_id='.$group_id.'&amp;view=listfile&amp;dirid='.$doc_group.'">'.
							stripslashes(db_result($result2,$i,'filename')).'</a></td>'.
							'<td align="center"><a href="/docman/?group_id='.$group_id.'&amp;action=monitorfile&amp;option=remove&amp;view=listfile&amp;dirid='.$doc_group.'&amp;fileid='.$docid.'">'.
							'<img src="'.$GLOBALS['HTML']->imgroot.'ic/trash.png" height="16" width="16" '.
							'border="0" alt="'._("Stop Monitoring").'" /></a></td></tr>';
					}
				}

				$html_hdr .= '['.$rows2.($count_new ? ", <b>".sprintf(_('%s new'), $count_new)."</b>]" : ']').'</td></tr>';
				$html_my_monitored_documents .= $html_hdr.$html;
			}
			$html_my_monitored_documents .= '</table>';
		}
		return $html_my_monitored_documents;
	}

	function getCategory() {
		return 'Documents-Manager';
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
	}
}
