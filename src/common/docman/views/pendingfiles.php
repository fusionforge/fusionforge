<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012-2015, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $group_id; // id of the group
global $g; // the group object
global $dirid; // id of doc_group
global $HTML; // Layout object
global $nested_pending_docs;
global $nested_groups;
global $redirecturl; // built url from listfile.php (handle the hierarchy)
global $warning_msg;
global $childgroup_id;

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect($redirecturl);
}

if (!isset($nested_pending_docs)) {
	echo $HTML->information(_('No pending documents.'));
} else {
	echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerListPending;

jQuery(document).ready(function() {
	controllerListPending = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		docManURL:		'<?php echo util_make_uri('/docman') ?>',
		lockIntervalDelay:	60000, //in microsecond and if you change this value, please update the check value 600
		divEditFile:		jQuery('#editFile'),
		divEditTitle:		'<?php echo _("Edit document dialog box") ?>'
	});
});
//]]>
<?php
	echo html_ac(html_ap() - 1);
	if (isset($nested_pending_docs[$dirid]) && is_array($nested_pending_docs[$dirid])) {
		echo html_ao('div', array('class' => 'docmanDiv'));
		echo html_e('h4', array('class' => 'docman_h4'), _('Pending files'), false);
		$tabletop = array(html_e('input', array('id' => 'checkallpending', 'type' => 'checkbox', 'onClick' => 'controllerListPending.checkAll("checkeddocidpending", "pending")')), '', 'ID', _('File Name'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('View'), _('Actions'));
		$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', '', '', 'unsortable');
		echo $HTML->listTableTop($tabletop, array(), 'sortable_docman_listfile', 'sortable', $classth);
		$time_new = 604800;
		foreach ($nested_pending_docs[$dirid] as $d) {
			$cells = array();
			$cells[][] = html_e('input', array('type' => 'checkbox', 'value' => $d->getID(), 'class' => 'checkeddocidpending', 'title' => _('Select / Deselect this document for massaction'), 'onClick' => 'controllerListPending.checkgeneral("pending")'));
			switch ($d->getFileType()) {
				case 'URL': {
					$cells[][] = util_make_link($d->getFileName(), html_image($d->getFileTypeImage(), 22, 22, array('alt'=>$d->getFileType())), array('title' => _('Visit this link')), true);
					break;
				}
				default: {
					$cells[][] = util_make_link('/docman/view.php/'.$g->getID().'/'.$d->getID().'/'.urlencode($d->getFileName()), html_image($d->getFileTypeImage(), 20, 20, array('alt'=>$d->getFileType())), array('title' => _('View this document')));
				}
			}
			$cells[][] = 'D'.$d->getID();
			$nextcell = '';
			if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
				$nextcell.= $HTML->getNewPic(_('Created or updated since less than 7 days'), 'new', array('class' => 'docman-newdocument')).'&nbsp;';
			}
			$cells[] = array($nextcell.$d->getFileName(), 'style' => 'word-wrap: break-word; max-width: 250px;');
			$cells[] = array($d->getName(), 'style' => 'word-wrap: break-word; max-width: 250px;');
			$cells[] = array($d->getDescription(), 'style' => 'word-wrap: break-word; max-width: 250px;');
			$cells[][] = util_display_user($d->getCreatorUserName(), $d->getCreatorID(), $d->getCreatorRealName());
			if ( $d->getUpdated() ) {
				$cells[] = array(date(_('Y-m-d H:i'), $d->getUpdated()), 'content' => $d->getUpdated());
			} else {
				$cells[] = array(date(_('Y-m-d H:i'), $d->getCreated()), 'content' => $d->getCreated());
			}
			$cells[][] =$d->getStateName();
			switch ($d->getFileType()) {
				case 'URL': {
					$cells[][] = '--';
					break;
				}
				default: {
					$cells[][] = human_readable_bytes($d->getFileSize());
					break;
				}
			}
			$cells[][] = $d->getDownload();
			$editfileaction = '/docman/?action=editfile&fromview=listfile&dirid='.$d->getDocGroupID();
			if ($childgroup_id) {
				$editfileaction .= '&childgroup_id='.$childgroup_id;
			}
			$editfileaction .= '&group_id='.$group_id;
			$cells[][] = util_make_link('#', $HTML->getEditFilePic($edittitle, 'editdocument'), array('onclick' => 'javascript:controllerListPending.toggleEditFileView({action:\''.util_make_uri($editfileaction).'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json').', docgroupDict:'.$dm->getDocGroupList($nested_groups, 'json').', title:\''.addslashes($d->getName()).'\', filename:\''.addslashes($d->getFileName()).'\', description:\''.addslashes($d->getDescription()).'\', isURL:\''.$d->isURL().'\', isText:\''.$d->isText().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri("docman").'\'})', 'title' => _('Edit this document')), true).
					util_make_link('#', html_image('docman/validate.png', 22, 22, array('alt' => _('Activate in this folder'))), array('onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=validatefile&fileid='.$d->getID()).'\'', 'title' => _('Activate in this folder')), true);
			echo $HTML->multiTableRow(array(), $cells);
		}
		echo $HTML->listTableBottom();
		echo html_ao('p');
		echo html_ao('span', array('id' => 'massactionpending', 'class' => 'hide'));
		echo html_e('span', array('id' => 'docman-massactionpendingmessage', 'title' => _('Actions availables for selected documents, you need to check at least one document to get actions')), _('Mass actions for selected pending documents:'), false);
		echo util_make_link('#', html_image('docman/download-directory-zip.png', 22, 22, array('alt'=>_('Download as a ZIP'))), array('onclick' => 'window.location.href=\''.util_make_uri('/docman/view.php/'.$g->getID().'/zip/selected/'.$dirid.'/\'+controllerListPending.buildUrlByCheckbox("pending")'), 'title' => _('Download as a ZIP')), true);
		echo util_make_link('#', html_image('docman/validate.png', 22, 22, array('alt' => _('Activate in this folder'))), array('onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=validatefile&fileid=\'+controllerListPending.buildUrlByCheckbox("pending")'), 'title' => _('Activate in this folder')), true);
		echo html_ac(html_ap() - 3);
	}
}
