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
require_once 'www/my/my_utils.php';

/**
* Widget_MyMonitoredDocuments
* 
* Documents that are actively monitored
*/
class Widget_MyMonitoredDocuments extends Widget {
    function Widget_MyMonitoredDocuments() {
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
            $html_my_monitored_documents .= '<p><b>' . _("You are not monitoring any documents.") . '</b></p><p>' . _("If you monitor documents, you will be sent new update in the form of an email.") . '</p><p>' . _("You can monitor documents by clicking on the appropriate icon action in the directory itself.") . '</p>';
        } else {
            $request =& HTTPRequest::instance();
            $html_my_monitored_documents .= '<table style="width:100%">';
            for ($j=0; $j<$rows; $j++) {
                $group_id = db_result($result,$j,'group_id');
        
				$sql2 = "select docdata_vw.docid, docdata_vw.doc_group, docdata_vw.filename, docdata_vw.filetype, docdata_vw.description from docdata_vw,docdata_monitored_docman where docdata_vw.docid = docdata_monitored_docman.doc_id and docdata_vw.group_id = $1 and docdata_monitored_docman.user_id = $2 limit 100";
                $result2 = db_query_params($sql2,array($group_id,user_getid()));
                $rows2 = db_numrows($result2);

                $vItemId = new Valid_UInt('hide_item_id');
                $vItemId->required();
                if($request->valid($vItemId)) {
                    $hide_item_id = $request->get('hide_item_id');
                } else {
                    $hide_item_id = null;
                }

                $vDocument = new Valid_WhiteList('hide_document', array(0, 1));
		$vDocument->required();
		if($request->valid($vDocument)) {
			$hide_document = $request->get('hide_document');
		} else {
			$hide_document = null;
		}

		list($hide_now,$count_diff,$hide_url) = my_hide_url('document',$group_id,$hide_item_id,$rows,$hide_document);

		$html_hdr = ($j ? '<tr class="boxitem"><td colspan="2">' : '').
			$hide_url.'<A HREF="/docman/?group_id='.$group_id.'">'.
			db_result($result,$j,'group_name').'</A>&nbsp;&nbsp;&nbsp;&nbsp;';

		$html = '';
		$count_new = max(0, $count_diff);
		for ($i=0; $i<$rows2; $i++) {

			if (!$hide_now) {
				if ($i % 2 == 0) {
					$class="bgcolor-white";
				}
				else {
					$class="bgcolor-grey";
				}



				$doc_group = db_result($result2,$i,'doc_group');
				$docid = db_result($result2,$i,'docid');

				$html .= '
					<TR class="'. $class .'"><TD WIDTH="99%">'.
					'&nbsp;&nbsp;&nbsp;-&nbsp;<A HREF="/docman/?group_id='.$group_id.'&view=listfile&dirid='.$doc_group.'">'.
					stripslashes(db_result($result2,$i,'filename')).'</A></TD>'.
					'<TD ALIGN="center"><A HREF="/docman/?group_id='.$group_id.'&action=monitorfile&option=remove&view=listfile&dirid='.$doc_group.'&fileid='.$docid.'">'.
					'<IMG SRC="'.$GLOBALS['HTML']->imgroot.'ic/trash.png" HEIGHT="16" WIDTH="16" '.
					'BORDER=0 ALT="'._("STOP MONITORING").'"></A></TD></TR>';
			}
		}

		$html_hdr .= '['.$rows2.($count_new ? ", <b>".sprintf(_('%s new'), array($count_new))."</b>]" : ']').'</td></tr>';
		$html_my_monitored_documents .= $html_hdr.$html;
	    }
	    $html_my_monitored_documents .= '</table>';
	}
	return $html_my_monitored_documents;
    }

    function getCategory() {
	    return 'Documents';
    }
    function getDescription() {
	    return _("List documents that you are currently monitoring, by project.<br />To cancel any of the monitored items just click on the trash icon next to the item label.");
    }
    function isAjax() {
	    return true;
    }
    function getAjaxUrl($owner_id, $owner_type) {
        $request =& HTTPRequest::instance();
        $ajax_url = parent::getAjaxUrl($owner_id, $owner_type);
        if ($request->exist('hide_item_id') || $request->exist('hide_forum')) {
            $ajax_url .= '&hide_item_id=' . $request->get('hide_item_id') . '&hide_forum=' . $request->get('hide_forum');
        }
        return $ajax_url;
    }

}
?>
