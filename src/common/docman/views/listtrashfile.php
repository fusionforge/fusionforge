<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011-2016, Franck Villaume - TrivialDev
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
global $LUSER; // User object
global $g; // the Group object
global $dm; // the docman manager
global $warning_msg;
global $start; // use to set the offset

$linkmenu = 'listtrashfile';
$childgroup_id = getIntFromRequest('childgroup_id');
$baseredirecturl = '/docman/?group_id='.$group_id;
$redirecturl = $baseredirecturl.'&view='.$linkmenu.'&dirid='.$dirid;

echo html_ao('div', array('id' => 'leftdiv'));
include ($gfcommon.'docman/views/tree.php');
echo html_ac(html_ap() - 1);

// plugin projects-hierarchy
if ($childgroup_id) {
	$redirecturl .= '&childgroup_id='.$childgroup_id;
	$g = group_get_object($childgroup_id);
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect($baseredirecturl);
}

$df = new DocumentFactory($g);
if ($df->isError())
	exit_error($df->getErrorMessage(), 'docman');

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError())
	exit_error($dgf->getErrorMessage(), 'docman');

// deleted state id
$df->setStateID(array(2));
$df->setDocGroupState(2);

$d_arr = $df->getDocuments();

$nested_docs = array();
$DocGroupName = 0;
$dgpath = '';
if ($dirid) {
	$ndg = documentgroup_get_object($dirid, $g->getID());
	$DocGroupName = $ndg->getName();
	$dgpath = $ndg->getPath(true, false);
	if (!$DocGroupName) {
		$error_msg = $g->getErrorMessage();
		session_redirect($baseredirecturl);
	}
	if ($ndg->getState() != 2) {
		$error_msg = _('Invalid folder');
		session_redirect($baseredirecturl.'&view='.$linkmenu);
	}
}

if (is_array($d_arr) && count($d_arr) > 0) {
	// Get the document groups info
	//put the doc objects into an array keyed off the docgroup
	foreach ($d_arr as $doc) {
		$nested_docs[$doc->getDocGroupID()][] = $doc;
	}
}

echo html_ao('div', array('id' => 'rightdiv'));
echo html_ao('div', array('style' => 'padding:5px'));
echo $HTML->openForm(array('id' => 'emptytrash', 'name' => 'emptytrash', 'method' => 'post', 'action' => '/docman/?group_id='.$group_id.'&action=emptytrash'));
echo html_e('input', array('id' => 'submitemptytrash', 'type' => 'submit', 'value' => _('Delete permanently all documents and folders with deleted status.')));
echo $HTML->closeForm();
echo html_ac(html_ap() - 1);
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerListFile;

jQuery(document).ready(function() {
	controllerListFile = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		divEditDirectory:	jQuery('#editdocgroup'),
		buttonEditDirectory:	jQuery('#docman-editdirectory'),
		docManURL:		'<?php echo util_make_uri('/docman') ?>',
		lockIntervalDelay:	60000, //in microsecond and if you change this value, please update the check value 600
		childGroupId:           <?php echo util_ifsetor($childgroup_id, 0) ?>,
		divLeft:		jQuery('#leftdiv'),
		divRight:		jQuery('#rightdiv'),
		divEditFile:		jQuery('#editFile'),
		divEditTitle:		'<?php echo _("Edit document dialog box") ?>',
		enableResize:		true,
		page:			'trashfile',
		docgroupId:		<?php echo $dirid ?>,
		lockIntervalDelay:	60000,
		tableAddVersion:	jQuery('#doc_version_edit')
	});
});
//]]>
<?php
echo html_ac(html_ap() - 1);

if ($DocGroupName) {
	$headerPath = '';
	if ($childgroup_id) {
		$headerPath .= _('Subproject')._(': ').util_make_link('/docman/?group_id='.$g->getID(), $g->getPublicName()).'::';
	}
	$generalpath = $dgpath.'/'.$DocGroupName;
	$generalpath = preg_replace('/\/\//','/', $generalpath);
	$headerPath .= html_e('i', array(), $generalpath, false).'&nbsp;';
	echo html_e('h2', array('class' => 'docman_h2'), $headerPath, false);
	if ($DocGroupName != '.trash') {
		echo util_make_link('#', $HTML->getConfigurePic(_('Edit this folder'), 'edit'), array('id' => 'docman-editdirectory', 'onclick' => 'javascript:controllerListFile.toggleEditDirectoryView()'), true);
		echo util_make_link($redirecturl.'&action=deldir', $HTML->getRemovePic(_('Delete permanently this folder and his content.'), 'deldir'), array('id' => 'docman-deletedirectory'));
	}

	echo html_ao('div', array('class' => 'docman_div_include hide', 'id' => 'editdocgroup'));
	echo html_e('h3', array('class' => 'docman_h3'), _('Edit this folder'), false);
	include ($gfcommon.'docman/views/editdocgroup.php');
	echo html_ac(html_ap() - 1);
}

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$tabletop = array(html_e('input', array('id' => 'checkallactive', 'title' => _('Select / Deselect all documents for massaction'), 'type' => 'checkbox', 'onClick' => 'controllerListFile.checkAll("checkeddocidactive", "active")')), '', _('File Name'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('Actions'));
	$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', 'unsortable');
	echo html_ao('div', array('class' => 'docmanDiv'));
	echo $HTML->listTableTop($tabletop, array(), 'sortable', 'sortable_docman_listfile', $classth);
	$time_new = 604800;
	foreach ($nested_docs[$dirid] as $d) {
		$cells = array();
		$cells[][] = html_e('input', array('type' => 'checkbox', 'class' => 'checkeddocidactive', 'value' => $d->getID(), 'title' => _('Select / Deselect this document for massaction'), 'onClick' => 'controllerListFile.checkgeneral("active")'));
		switch ($d->getFileType()) {
			case 'URL': {
				$cells[][] = util_make_link($d->getFileName(), html_image($d->getFileTypeImage(), 22, 22, array('alt' => $d->getFileType())), array('title' => _('Visit this link')));
				break;
			}
			default: {
				$cells[][] = util_make_link('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()), html_image($d->getFileTypeImage(), 20, 20, array('alt' => $d->getFileType())), array('title' => _('View this document')));
			}
		}
		$nextcell ='';
		if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
			$nextcell.= $HTML->getNewPic(_('Created or updated since less than 7 days'), 'new', array('class' => 'docman-newdocument')).'&nbsp;';
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
		$cells[][] = $d->getStateName();
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
		$newdgf = new DocumentGroupFactory($d->Group);
		$editfileaction = '/docman/?action=editfile&fromview=listfile&dirid='.$d->getDocGroupID();
		if (isset($GLOBALS['childgroup_id']) && $GLOBALS['childgroup_id']) {
			$editfileaction .= '&childgroup_id='.$GLOBALS['childgroup_id'];
		}
		$editfileaction .= '&group_id='.$GLOBALS['group_id'];
		$nextcell = '';
		$nextcell .= util_make_link($redirecturl.'&action=delfile&fileid='.$d->getID(), $HTML->getRemovePic(_('Delete permanently this document.'), 'delfile'));
		$nextcell .= util_make_link('#', $HTML->getEditFilePic(_('Edit this document'), 'editdocument'), array('onclick' => 'javascript:controllerListFile.toggleEditFileView({action:\''.util_make_uri($editfileaction).'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json','2').', docgroupDict:'.$dm->getDocGroupList($newdgf->getNested(array(1, 5)), 'json').', isText:\''.$d->isText().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri("docman").'\'})', 'title' => _('Edit this document')), true);
		$cells[][] = $nextcell;
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo html_ao('p');
	echo html_ao('span', array('id' => 'massactionactive', 'class' => 'hide'));
	echo html_e('span', array('id' => 'docman-massactionmessage', 'title' => _('Actions availables for selected documents, you need to check at least one document to get actions')), _('Mass actions for selected documents:'), false);
	echo util_make_link('#', $HTML->getRemovePic(_('Permanently Delete'), 'del'), array('onclick' => 'window.location.href=\''.util_make_uri($redirecturl.'&action=delfile&fileid=\'+controllerListFile.buildUrlByCheckbox("active")')), true);
	echo util_make_link('#', html_image('docman/download-directory-zip.png', 22, 22, array('alt' => _('Download as a ZIP'))), array('onclick' => 'window.location.href=\''.util_make_uri('/docman/view.php/'.$group_id.'/zip/selected/\'+controllerListFile.buildUrlByCheckbox("active")'), 'title' => _('Download as a ZIP')), true);
	echo html_ac(html_ap() - 3);
} else {
	if ($dirid) {
		echo $HTML->information(_('No documents.'));
	}
}

echo html_ac(html_ap() -1);
$foundFiles = 0;
if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$foundFiles = count($nested_docs[$dirid]);
}
if (forge_check_perm('docman', $g->getID(), 'approve') && $foundFiles) {
	include ($gfcommon.'docman/views/editfile.php');
}
