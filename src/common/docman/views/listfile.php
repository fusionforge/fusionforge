<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright (C) 2010 Alcatel-Lucent
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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
global $dirid; // id of doc_group
global $HTML; // Layout object
global $u; // User object
global $g; // the Group object
global $dm; // the docman manager

$linkmenu = 'listfile';
$baseredirecturl = '/docman/?group_id='.$group_id;
$redirecturl = $baseredirecturl.'&view='.$linkmenu.'&dirid='.$dirid;
if (!forge_check_perm('docman', $group_id, 'read')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect($baseredirecturl.'&warning_msg='.urlencode($return_msg));
}

echo html_ao('div', array('id' => 'leftdiv'));
include ($gfcommon.'docman/views/tree.php');
echo html_ac(html_ap() - 1);

// plugin projects-hierarchy
$childgroup_id = getIntFromRequest('childgroup_id');
if ($childgroup_id) {
	if (!forge_check_perm('docman', $childgroup_id, 'read')) {
		$return_msg= _('Document Manager Access Denied');
		session_redirect($baseredirecturl.'&warning_msg='.urlencode($return_msg));
	}
	$redirecturl .= '&childgroup_id='.$childgroup_id;
	$g = group_get_object($childgroup_id);
}

$df = new DocumentFactory($g);
if ($df->isError())
	exit_error($df->getErrorMessage(), 'docman');

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError())
	exit_error($dgf->getErrorMessage(), 'docman');

$df->setDocGroupID($dirid);

$df->setStateID('1');
$d_arr_active =& $df->getDocuments();
if ($d_arr_active != NULL)
	$d_arr = $d_arr_active;

$df->setStateID('4');
$d_arr_hidden =& $df->getDocuments();
if ($d_arr != NULL && $d_arr_hidden != NULL) {
	$d_arr = array_merge($d_arr, $d_arr_hidden);
} elseif ($d_arr_hidden != NULL) {
	$d_arr = $d_arr_hidden;
}

$df->setStateID('5');
$d_arr_private =& $df->getDocuments();
if ($d_arr != NULL && $d_arr_private != NULL) {
	$d_arr = array_merge($d_arr, $d_arr_private);
} elseif ($d_arr_private != NULL) {
	$d_arr = $d_arr_private;
}

$nested_groups = $dgf->getNested();

$nested_docs = array();
$DocGroupName = 0;

if ($dirid) {
	$ndg = new DocumentGroup($g, $dirid);
	$DocGroupName = $ndg->getName();
	$dgpath = $ndg->getPath(true, false);
	if (!$DocGroupName) {
		session_redirect($baseredirecturl.'&error_msg='.urlencode($g->getErrorMessage()));
	}
	if ($ndg->getState() != 1) {
		$error_msg = _('Invalid folder');
		session_redirect($baseredirecturl.'&view=listfile&error_msg='.urlencode($error_msg));
	}
}

if ($d_arr != NULL ) {
	if (!$d_arr || count($d_arr) > 0) {
		// Get the document groups info
		//put the doc objects into an array keyed off the docgroup
		foreach ($d_arr as $doc) {
			$nested_docs[$doc->getDocGroupID()][] = $doc;
		}
	}
}

$df->setStateID('3');
$d_pending_arr =& $df->getDocuments();

if ($d_pending_arr != NULL ) {
	if (!$d_pending_arr || count($d_pending_arr) > 0) {
		// Get the document groups info
		//put the doc objects into an array keyed off the docgroup
		foreach ($d_pending_arr as $doc) {
			$nested_pending_docs[$doc->getDocGroupID()][] = $doc;
		}
	}
}

echo html_ao('div', array('id' => 'rightdiv'));
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerListFile;

jQuery(document).ready(function() {
	controllerListFile = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		divAddItem:		jQuery('#additem'),
		divEditDirectory:	jQuery('#editdocgroup'),
		divMoveFile:		jQuery('#movefile'),
		buttonAddItem:		jQuery('#docman-additem'),
		buttonEditDirectory:	jQuery('#docman-editdirectory'),
		docManURL:		'<?php echo util_make_uri('/docman') ?>',
		divLeft:		jQuery('#leftdiv'),
		divRight:		jQuery('#rightdiv'),
		childGroupId:		<?php echo util_ifsetor($childgroup_id, 0) ?>,
		divEditFile:		jQuery('#editFile'),
		divEditTitle:		'<?php echo _('Edit document dialog box') ?>',
		enableResize:		true,
		page:			'listfile'
	});
});

//]]>
<?php
echo html_ac(html_ap() - 1);
if ($DocGroupName) {
	$headerPath = '';
	if ($childgroup_id) {
		$headerPath .= _('Subproject')._(': ').util_make_link('/docman/?group_id='.$g->getID(), $g->getPublicName()).' ';
	}
	$headerPath .= _('Path')._(': ').html_e('i', array(), $dgpath, false);
	echo html_e('h2', array(), $headerPath, false);
	echo html_ao('h3', array('class' => 'docman_h3'));
	echo html_e('span', array(), _('Document Folder')._(': ').html_e('i', array(), $DocGroupName, false).'&nbsp;', false);
	/* should we steal the lock on file ? */
	if ($ndg->getLocked()) {
		if ($ndg->getLockedBy() == $u->getID()) {
			$ndg->setLock(0);
		/* if you change the 60000 value below, please update here too */
		} elseif ((time() - $ndg->getLockdate()) > 600) {
			$ndg->setLock(0);
		}
	}
	if (!$ndg->getLocked()) {
		if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
			echo html_e('input', array('type' => 'hidden', 'id' => 'doc_group_id', 'value' => $ndg->getID()));
			echo util_make_link('#', html_image('docman/configure-directory.png', 22, 22, array('alt' => 'edit')), array('class' => 'tabtitle', 'id' => 'docman-editdirectory', 'title' => _('Edit this folder'), 'onclick' => 'javascript:controllerListFile.toggleEditDirectoryView({lockIntervalDelay: 60000, doc_group:'.$ndg->getID().'})' ), true);
			echo util_make_link($redirecturl.'&action=trashdir', html_image('docman/trash-empty.png', 22, 22, array('alt' => 'trashdir')), array('class' => 'tabtitle', 'id' => 'docman-trashdirectory', 'title' => _('Move this folder and his content to trash')));
			if (!isset($nested_docs[$dirid]) && !isset($nested_groups[$dirid]) && !isset($nested_pending_docs[$dirid])) {
				echo util_make_link($redirecturl.'&action=deldir', html_image('docman/delete-directory.png', 22, 22, array('alt' => 'deldir')), array('class' => 'tabtitle', 'id' => 'docman-deletedirectory', 'title' => _('Permanently delete this folder')));
			}
		}

		if (forge_check_perm('docman', $group_id, 'submit')) {
			echo util_make_link('#', html_image('docman/insert-directory.png', 22, 22, array('alt' => 'additem')), array('class' => 'tabtitle', 'id' => 'docman-additem', 'title' => _('Add a new item in this folder')), true);
		}
	}

	$numFiles = $ndg->getNumberOfDocuments(1);
	if (forge_check_perm('docman', $group_id, 'approve'))
		$numPendingFiles = $ndg->getNumberOfDocuments(3);
	if ($numFiles || (isset($numPendingFiles) && $numPendingFiles))
		echo util_make_link('/docman/view.php/'.$ndg->Group->getID().'/zip/full/'.$dirid, html_image('docman/download-directory-zip.png',22,22,array('alt'=>'downloadaszip')), array('class' => 'tabtitle', 'title' => _('Download this folder as a ZIP')));

	if (session_loggedin()) {
		if ($ndg->isMonitoredBy($u->getID())) {
			$option = 'remove';
			$titleMonitor = _('Stop monitoring this folder');
		} else {
			$option = 'add';
			$titleMonitor = _('Start monitoring this folder');
		}
		echo util_make_link($redirecturl.'&action=monitordirectory&option='.$option.'&directoryid='.$ndg->getID(), html_image('docman/monitor-'.$option.'document.png',22,22,array('alt'=>$titleMonitor)), array('class' => 'tabtitle-ne', 'title' => $titleMonitor));
	}
	echo html_ac(html_ap() - 1);

	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
		echo html_ao('div', array('class' => 'docman_div_include hide', 'id' => 'editdocgroup'));
		echo html_e('h4', array('class' => 'docman_h4'), _('Edit this folder'), false);
		include ($gfcommon.'docman/views/editdocgroup.php');
		echo html_ac(html_ap() - 1);
	}
	if (forge_check_perm('docman', $ndg->Group->getID(), 'submit')) {
		echo html_ao('div', array('class' => 'docman_div_include hide', 'id' => 'additem'));
		include ($gfcommon.'docman/views/additem.php');
		echo html_ac(html_ap() - 1);
	}
}

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$tabletop = array(html_e('input', array('id' => 'checkallactive', 'type' => 'checkbox', 'title' => _('Select / Deselect all documents for massaction'), 'class' => 'tabtitle-w', 'onchange' => 'controllerListFile.checkAll("checkeddocidactive", "active")')), '', _('File Name'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('View'));
	$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', '');
	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
		$tabletop[] = _('Actions');
		$classth[] = 'unsortable';
	}
	echo html_ao('div', array('class' => 'docmanDiv'));
	echo $HTML->listTableTop($tabletop, array(), 'sortable_docman_listfile', 'sortable', $classth);
	$time_new = 604800;
	foreach ($nested_docs[$dirid] as $d) {
		$cells = array();
		/* should we steal the lock on file ? */
		if ($d->getLocked()) {
			if ($d->getLockedBy() == $u->getID()) {
				$d->setLock(0);
			/* if you change the 60000 value below, please update here too */
			} elseif ((time() - $d->getLockdate()) > 600) {
				$d->setLock(0);
			}
		}
		if (!$d->getLocked() && !$d->getReserved()) {
			$cells[][] = html_e('input', array('type' => 'checkbox', 'value' => $d->getID(), 'class' => 'checkeddocidactive tabtitle-w', 'title' => _('Select / Deselect this document for massaction'), 'onchange' => 'controllerListFile.checkgeneral("active")'));
		} else {
			if (session_loggedin() && ($d->getReservedBy() != $u->getID())) {
				$cells[][] = html_e('input', array('type' => 'checkbox', 'name' => 'disabled', 'disabled' => 'disabled'));
			} else {
				$cells[][] = html_e('input', array('type' => 'checkbox', 'value' => $d->getID(), 'class' => 'checkeddocidactive tabtitle-w', 'title' => _('Select / Deselect this document for massaction'), 'onchange' => 'controllerListFile.checkgeneral("active")'));
			}
		}
		switch ($d->getFileType()) {
			case 'URL': {
				$cells[][] =  util_make_link($d->getFileName(), html_image($d->getFileTypeImage(), '22', '22', array('alt' => $d->getFileType())), array('class' => 'tabtitle-nw', 'title' => _('Visit this link')), true);
				break;
			}
			default: {
				$cells[][] =  util_make_link('/docman/view.php/'.$d->Group->getID().'/'.$d->getID().'/'.urlencode($d->getFileName()), html_image($d->getFileTypeImage(), '22', '22', array('alt' => $d->getFileType())), array('class' => 'tabtitle-nw', 'title' => _('View this document')));
			}
		}
		$nextcell = '';
		if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
			$nextcell = html_image('docman/new.png', '14', '14', array('alt' => _('new'), 'class' => 'tabtitle-ne', 'title' => _('Created or updated since less than 7 days'))).'&nbsp;';
		}
		$cells[] = array($nextcell.$d->getFileName(), 'style' => 'word-wrap: break-word; max-width: 250px;');
		$cells[] = array($d->getName(), 'style' => 'word-wrap: break-word; max-width: 250px;');
		$cells[] = array($d->getDescription(), 'style' => 'word-wrap: break-word; max-width: 250px;');
		$cells[][] =  make_user_link($d->getCreatorUserName(), $d->getCreatorRealName());
		if ($d->getUpdated()) {
			$cells[] = array(date(_('Y-m-d H:i'), $d->getUpdated()), 'sorttable_customkey' => $d->getUpdated());
		} else {
			$cells[] = array(date(_('Y-m-d H:i'), $d->getCreated()), 'sorttable_customkey' => $d->getCreated());
		}
		$nextcell = '';
		if ($d->getReserved()) {
			$nextcell = html_image('docman/document-reserved.png', '22', '22', array('alt' => _('Reserved Document'), 'class' => 'tabtitle', 'title' => _('Reserved Document')));
			$reserved_by = $d->getReservedBy();
			if ($reserved_by) {
				$user = user_get_object($reserved_by);
				if (is_object($user)) {
					$cells[][] = $nextcell.' '._('by').' '.util_make_link_u($user->getUnixName(), $user->getID(), $user->getRealName());
				}
			}
		} else {
			$cells[][] = $d->getStateName();
		}
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

		if (forge_check_perm('docman', $group_id, 'approve')) {
			$nextcell = '';
			$editfileaction = '/docman/?action=editfile&fromview=listfile&dirid='.$d->getDocGroupID();
			if (isset($GLOBALS['childgroup_id']) && $GLOBALS['childgroup_id']) {
				$editfileaction .= '&childgroup_id='.$GLOBALS['childgroup_id'];
			}
			$editfileaction .= '&group_id='.$GLOBALS['group_id'];
			if (!$d->getLocked() && !$d->getReserved()) {
				$nextcell .= util_make_link($redirecturl.'&action=trashfile&fileid='.$d->getID(), html_image('docman/trash-empty.png', 22, 22, array('alt' => _('Move this document to trash'))), array('class' => 'tabtitle-ne', 'title' => _('Move this document to trash')));
				$nextcell .= util_make_link('#', html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))), array('onclick' => 'javascript:controllerListFile.toggleEditFileView({action:\''.util_make_uri($editfileaction).'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json').', docgroupDict:'.$dm->getDocGroupList($nested_groups, 'json').', title:\''.addslashes($d->getName()).'\', filename:\''.$d->getFilename().'\', description:\''.addslashes($d->getDescription()).'\', isURL:\''.$d->isURL().'\', isText:\''.$d->isText().'\', isHtml:\''.$d->isHtml().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri('/docman').'\'})', 'class' => 'tabtitle-ne', 'title' => _('Edit this document')), true);
				if (session_loggedin()) {
					$nextcell .= util_make_link($redirecturl.'&action=reservefile&fileid='.$d->getID(), html_image('docman/reserve-document.png', 22, 22, array('alt' => _('Reserve this document'))), array('class' => 'tabtitle-ne', 'title' => _('Reserve this document for later edition')));
				}
			} else {
				if (session_loggedin() && $d->getReservedBy() != $u->getID()) {
					if (forge_check_perm('docman', $ndg->Group->getID(), 'admin')) {
						$nextcell .= util_make_link($redirecturl.'&action=enforcereserve&fileid='.$d->getID(), html_image('docman/enforce-document.png',22,22,array('alt'=>_('Enforce reservation'))), array('class' => 'tabtitle-ne', 'title' => _('Enforce reservation')));
					}
				} else {
					$nextcell .= util_make_link($redirecturl.'&action=trashfile&fileid='.$d->getID(), html_image('docman/trash-empty.png', 22, 22, array('alt' => _('Move this document to trash'))), array('class' => 'tabtitle-ne', 'title' => _('Move this document to trash')));
					$nextcell .= util_make_link('#', html_image('docman/edit-file.png', 22 ,22, array('alt' => _('Edit this document'))), array('onclick' => 'javascript:controllerListFile.toggleEditFileView({action:\''.util_make_uri($editfileaction).'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json').', docgroupDict:'.$dm->getDocGroupList($nested_groups, 'json').', title:\''.addslashes($d->getName()).'\', filename:\''.$d->getFilename().'\', description:\''.addslashes($d->getDescription()).'\', isURL:\''.$d->isURL().'\', isText:\''.$d->isText().'\', isHtml:\''.$d->isHtml().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri('/docman').'\'})', 'class' => 'tabtitle-ne', 'title' => _('Edit this document')), true);
					$nextcell .= util_make_link($redirecturl.'&action=releasefile&fileid='.$d->getID(), html_image('docman/release-document.png', 22, 22, array('alt' => _('Release reservation'))), array('class' => 'tabtitle-ne', 'title' => _('Release reservation')));
				}
			}
			if (session_loggedin()) {
				if ($d->isMonitoredBy($u->getID())) {
					$option = 'remove';
					$titleMonitor = _('Stop monitoring this document');
				} else {
					$option = 'add';
					$titleMonitor = _('Start monitoring this document');
				}
				$nextcell .= util_make_link($redirecturl.'&action=monitorfile&option='.$option.'&fileid='.$d->getID(), html_image('docman/monitor-'.$option.'document.png', 22, 22, array('alt' => $titleMonitor)), array('class' => 'tabtitle-ne', 'title' => $titleMonitor));
			}
			$cells[][] = $nextcell;
		}
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo html_ao('p');
	echo html_ao('span', array('id' => 'massactionactive', 'class' => 'hide'));
	echo html_e('span', array('class' => 'tabtitle', 'id' => 'docman-massactionmessage', 'title' => _('Actions availables for selected documents, you need to check at least one document to get actions')), _('Mass actions for selected documents:'), false);
	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
		echo util_make_link('#', html_image('docman/trash-empty.png', 22, 22, array('alt' => _('Move to trash'))), array('onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=trashfile&fileid=\'+controllerListFile.buildUrlByCheckbox("active")'), 'class' => 'tabtitle-ne', 'title' => _('Move to trash')), true);
		if (session_loggedin()) {
			echo util_make_link('#', html_image('docman/reserve-document.png', 22, 22, array('alt' => _('Reserve'))), array('onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=reservefile&fileid=\'+controllerListFile.buildUrlByCheckbox("active")'), 'class' => 'tabtitle-ne', 'title' => _('Reserve for later edition')), true);
			echo util_make_link('#', html_image('docman/release-document.png', 22, 22, array('alt' => _('Release reservation'))) , array('class' => 'tabtitle-ne', 'onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=releasefile&fileid=\'+controllerListFile.buildUrlByCheckbox("active")'), 'title' => _('Release reservation')), true);
			echo util_make_link('#', html_image('docman/monitor-adddocument.png', 22, 22, array('alt' => _('Monitor'))), array('class' => 'tabtitle-ne', 'onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=monitorfile&option=add&fileid=\'+controllerListFile.buildUrlByCheckbox("active")'), 'title' => _('Monitor')), true);
			echo util_make_link('#', html_image('docman/monitor-removedocument.png', 22, 22, array('alt' => _('Stop Monitoring'))), array('class' => 'tabtitle-ne', 'onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=monitorfile&option=remove&fileid=\'+controllerListFile.buildUrlByCheckbox("active")'), 'title' => _('Stop Monitoring')), true);
			echo util_make_link('#', html_image('docman/move-document.png', 22, 22, array('alt' => _('Move files to another folder'))), array('class' => 'tabtitle-ne', 'onclick' => 'javascript:controllerListFile.toggleMoveFileView({})', 'title' => _('Move files to another folder')), true);
		}
	}
	echo util_make_link('#', html_image('docman/download-directory-zip.png', 22, 22, array('alt' => _('Download as a ZIP'))) , array('class' => 'tabtitle', 'onclick' => 'window.location.href=\''.util_make_uri('/docman/view.php/'.$group_id.'/zip/selected/'.$dirid.'/\'+controllerListFile.buildUrlByCheckbox("active")'), 'title' => _('Download as a ZIP')), true);
	echo html_ac(html_ap() - 3);
	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve') && session_loggedin()) {
		echo html_ao('div', array('class' => 'docman_div_include hide', 'id' => 'movefile'));
		include ($gfcommon.'docman/views/movefile.php');
		echo html_ac(html_ap() - 1);
	}
} else {
	if ($dirid) {
		echo $HTML->information(_('No documents.'));
	}
}
if (forge_check_perm('docman', $group_id, 'approve') && $DocGroupName) {
	include ($gfcommon.'docman/views/pendingfiles.php');
}
$foundFiles = 0;
if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$foundFiles = count($nested_docs[$dirid]);
} elseif (isset($nested_pending_docs)) {
	$foundFiles .= count($nested_pending_docs);
}
if (forge_check_perm('docman', $g->getID(), 'approve') && $foundFiles) {
	include ($gfcommon.'docman/views/editfile.php');
}

include ($gfcommon.'docman/views/help.php');
echo html_ac(html_ap() - 1);
