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
$redirecturl = $baseredirecturl.'&view=listfile&dirid='.$dirid;
$actionlistfileurl = '?group_id='.$group_id.'&amp;view=listfile&amp;dirid='.$dirid;
if (!forge_check_perm('docman', $group_id, 'read')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect($baseredirecturl.'&warning_msg='.urlencode($return_msg));
}

echo '<div id="leftdiv">';
include ($gfcommon.'docman/views/tree.php');
echo '</div>';

// plugin projects-hierarchy
$childgroup_id = getIntFromRequest('childgroup_id');
if ($childgroup_id) {
	if (!forge_check_perm('docman', $childgroup_id, 'read')) {
		$return_msg= _('Document Manager Access Denied');
		session_redirect($baseredirecturl.'&warning_msg='.urlencode($return_msg));
	}
	$redirecturl .= '&childgroup_id='.$childgroup_id;
	$actionlistfileurl .= '&amp;childgroup_id='.$childgroup_id;
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

echo '<div id="rightdiv">';
?>

<script type="text/javascript">//<![CDATA[
var controllerListFile;

jQuery(document).ready(function() {
	controllerListFile = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		divAddItem:		jQuery('#additem'),
		divEditDirectory:	jQuery('#editdocgroup'),
		buttonAddItem:		jQuery('#docman-additem'),
		buttonEditDirectory:	jQuery('#docman-editdirectory'),
		docManURL:		'<?php util_make_uri("docman") ?>',
		divLeft:		jQuery('#leftdiv'),
		divRight:		jQuery('#rightdiv'),
		childGroupId:		<?php echo util_ifsetor($childgroup_id, 0) ?>,
		divEditFile:		jQuery('#editFile'),
		divEditTitle:		'<?php echo _("Edit document dialog box") ?>',
		enableResize:		true
	});
});

//]]></script>

<?php
if ($DocGroupName) {
	$headerPath = '<h2>';
	if ($childgroup_id) {
		$headerPath .= _('Subproject')._(': ').util_make_link('/docman/?group_id='.$g->getID(),$g->getPublicName()).' ';
	}
	$headerPath .= _('Path')._(': ').'<i>'.$dgpath.'</i></h2>';
	echo $headerPath;
	echo '<h3 class="docman_h3" >'._('Document Folder')._(': ').html_e('i',array(),$DocGroupName, false).'&nbsp;';
	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
		echo util_make_link('#', html_image('docman/configure-directory.png', 22, 22, array('alt' => 'edit')), array('class' => 'tabtitle', 'id' => 'docman-editdirectory', 'title' => _('Edit this folder')), true);
		echo util_make_link($actionlistfileurl.'&action=trashdir', html_image('docman/trash-empty.png', 22, 22, array('alt' => 'trashdir')), array('class' => 'tabtitle', 'id' => 'docman-trashdirectory', 'title' => _('Move this folder and his content to trash')));
		if (!isset($nested_docs[$dirid]) && !isset($nested_groups[$dirid]) && !isset($nested_pending_docs[$dirid])) {
			echo util_make_link($actionlistfileurl.'&action=deldir', html_image('docman/delete-directory.png', 22, 22, array('alt' => 'deldir')), array('class' => 'tabtitle', 'id' => 'docman-deletedirectory', 'title' => _('Permanently delete this folder')));
		}
	}

	if (forge_check_perm('docman', $group_id, 'submit')) {
		echo util_make_link('#', html_image('docman/insert-directory.png', 22, 22, array('alt' => 'additem')), array('class' => 'tabtitle', 'id' => 'docman-additem', 'title' => _('Add a new item in this folder')), true);
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
		echo util_make_link($actionlistfileurl.'&action=monitordirectory&option='.$option.'&directoryid='.$ndg->getID(), html_image('docman/monitor-'.$option.'document.png',22,22,array('alt'=>$titleMonitor)), array('class' => 'tabtitle-ne', 'title' => $titleMonitor));
	}
	echo '</h3>';

	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
		echo '<div class="docman_div_include" id="editdocgroup" style="display:none;">';
		echo html_e('h4', array('class' => 'docman_h4'), _('Edit this folder'), false);
		include ($gfcommon.'docman/views/editdocgroup.php');
		echo '</div>';
	}
	if (forge_check_perm('docman', $ndg->Group->getID(), 'submit')) {
		echo '<div class="docman_div_include" id="additem" style="display:none">';
		include ($gfcommon.'docman/views/additem.php');
		echo '</div>';
	}
}

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$tabletop = array('<input id="checkallactive" type="checkbox" title="'._('Select / Deselect all documents for massaction').'" class="tabtitle-w" onchange="controllerListFile.checkAll(\'checkeddocidactive\', \'active\')" />', '', _('File Name'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('View'));
	$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', '');
	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
		$tabletop[] = _('Actions');
		$classth[] = 'unsortable';
	}
	echo '<div class="docmanDiv">';
	echo $HTML->listTableTop($tabletop, array(), 'sortable_docman_listfile', 'sortable', $classth);
	$time_new = 604800;
	foreach ($nested_docs[$dirid] as $d) {
		$cells = array();
		if (!$d->getLocked() && !$d->getReserved()) {
			$cells[][] = '<input type="checkbox" value="'.$d->getID().'" class="checkeddocidactive tabtitle-w" title="'._('Select / Deselect this document for massaction').'" onchange="controllerListFile.checkgeneral(\'active\')" />';
		} else {
			if (session_loggedin() && ($d->getReservedBy() != $u->getID())) {
				$cells[][] = '<input type="checkbox" name="disabled" disabled="disabled" />';
			} else {
				$cells[][] = '<input type="checkbox" value="'.$d->getID().'" class="checkeddocidactive tabtitle-w" title="'._('Select / Deselect this document for massaction').'"" onchange="controllerListFile.checkgeneral(\'active\')" />';
			}
		}
		switch ($d->getFileType()) {
			case "URL": {
				$docurl = $d->getFileName();
				$docurltitle = _('Visit this link');
				break;
			}
			default: {
				$docurl = '/docman/view.php/'.$d->Group->getID().'/'.$d->getID().'/'.urlencode($d->getFileName());
				$docurltitle = _('View this document');
			}
		}
		$cells[][] =  util_make_link($docurl, html_image($d->getFileTypeImage(), '22', '22', array('alt' => $d->getFileType())), array('class' => 'tabtitle-nw', 'title' => $docurltitle));
		$nextcell = '';
		if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
			$html_image_attr = array();
			$html_image_attr['alt'] = _('new');
			$html_image_attr['class'] = 'tabtitle-ne';
			$html_image_attr['title'] = _('Created or updated since less than 7 days');
			$nextcell = html_image('docman/new.png', '14', '14', $html_image_attr);
		}
		$cells[][] =  $nextcell.'&nbsp;'.$d->getFileName();
		$cells[][] =  $d->getName();
		$cells[][] =  $d->getDescription();
		$cells[][] =  make_user_link($d->getCreatorUserName(), $d->getCreatorRealName());
		if ( $d->getUpdated() ) {
			$cells[][] = date(_('Y-m-d H:i'), $d->getUpdated());
		} else {
			$cells[][] = date(_('Y-m-d H:i'), $d->getCreated());
		}
		$nextcell = '';
		if ($d->getReserved()) {
			$html_image_attr = array();
			$html_image_attr['alt'] = _('Reserved Document');
			$html_image_attr['class'] = 'tabtitle';
			$html_image_attr['title'] = _('Reserved Document');
			$nextcell = html_image('docman/document-reserved.png', '22', '22', $html_image_attr);
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
			case "URL": {
				$cells[][] = "--";
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
			/* should we steal the lock on file ? */
			if ($d->getLocked()) {
				if ($d->getLockedBy() == $u->getID()) {
					$d->setLock(0);
				/* if you change the 60000 value below, please update here too */
				} elseif ((time() - $d->getLockdate()) > 600) {
					$d->setLock(0);
				}
			}
			$editfileaction = '?action=editfile&amp;fromview=listfile&amp;dirid='.$d->getDocGroupID();
			if (isset($GLOBALS['childgroup_id']) && $GLOBALS['childgroup_id']) {
				$editfileaction .= '&amp;childgroup_id='.$GLOBALS['childgroup_id'];
			}
			$editfileaction .= '&amp;group_id='.$GLOBALS['group_id'];
			if (!$d->getLocked() && !$d->getReserved()) {
				$nextcell .= '<a class="tabtitle-ne" href="'.$actionlistfileurl.'&amp;action=trashfile&fileid='.$d->getID().'" title="'. _('Move this document to trash') .'" >'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move this document to trash'))). '</a>';
				$nextcell .= '<a class="tabtitle-ne" href="#" onclick="javascript:controllerListFile.toggleEditFileView({action:\''.$editfileaction.'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json').', docgroupDict:'.$dm->getDocGroupList($nested_groups, 'json').', title:\''.addslashes($d->getName()).'\', filename:\''.$d->getFilename().'\', description:\''.addslashes($d->getDescription()).'\', isURL:\''.$d->isURL().'\', isText:\''.$d->isText().'\', isHtml:\''.$d->isHtml().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri("docman").'\'})" title="'. _('Edit this document') .'" >'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';
				if (session_loggedin()) {
					$nextcell .= '<a class="tabtitle-ne" href="'.$actionlistfileurl.'&amp;action=reservefile&amp;fileid='.$d->getID().'" title="'. _('Reserve this document for later edition') .'" >'.html_image('docman/reserve-document.png',22,22,array('alt'=>_('Reserve this document'))). '</a>';
				}
			} else {
				if (session_loggedin() && $d->getReservedBy() != $u->getID()) {
					if (forge_check_perm('docman', $ndg->Group->getID(), 'admin')) {
						$nextcell .= '<a class="tabtitle-ne" href="'.$actionlistfileurl.'&amp;action=enforcereserve&amp;fileid='.$d->getID().'" title="'. _('Enforce reservation') .'" >'.html_image('docman/enforce-document.png',22,22,array('alt'=>_('Enforce reservation'))).'</a>';
					}
				} else {
					$nextcell .= '<a class="tabtitle-ne" href="'.$actionlistfileurl.'&amp;action=trashfile&amp;fileid='.$d->getID().'" title="'. _('Move this document to trash') .'" >'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move this document to trash'))). '</a>';
					$nextcell .= '<a class="tabtitle-ne" href="#" onclick="javascript:controllerListFile.toggleEditFileView({action:\''.$editfileaction.'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json').', docgroupDict:'.$dm->getDocGroupList($nested_groups, 'json').', title:\''.addslashes($d->getName()).'\', filename:\''.$d->getFilename().'\', description:\''.addslashes($d->getDescription()).'\', isURL:\''.$d->isURL().'\', isText:\''.$d->isText().'\', isHtml:\''.$d->isHtml().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri("docman").'\'})" title="'. _('Edit this document') .'" >'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';
					$nextcell .= '<a class="tabtitle-ne" href="'.$actionlistfileurl.'&amp;action=releasefile&amp;fileid='.$d->getID().'" title="'. _('Release reservation') .'" >'.html_image('docman/release-document.png',22,22,array('alt'=>_('Release reservation'))). '</a>';
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
				$nextcell .= '<a class="tabtitle-ne" href="'.$actionlistfileurl.'&amp;action=monitorfile&amp;option='.$option.'&amp;fileid='.$d->getID().'" title="'.$titleMonitor.'" >'.html_image('docman/monitor-'.$option.'document.png',22,22,array('alt'=>$titleMonitor)). '</a>';
			}
			$cells[][] = $nextcell;
		}
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo '<p>';
	echo '<span id="massactionactive" style="display: none;" >';
	echo '<span class="tabtitle" id="docman-massactionmessage" title="'. _('Actions availables for selected documents, you need to check at least one document to get actions') . '" >';
	echo _('Mass actions for selected documents:');
	echo '</span>';
	if (forge_check_perm('docman', $ndg->Group->getID(), 'approve')) {
		echo '<a class="tabtitle-ne" href="#" onclick="window.location.href=\'?group_id='.$group_id.'&amp;action=trashfile&amp;view=listfile&amp;dirid='.$dirid.'&amp;fileid=\'+controllerListFile.buildUrlByCheckbox(\'active\')" title="'. _('Move to trash') .'" >'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move to trash'))). '</a>';
		if (session_loggedin()) {
			echo '<a class="tabtitle-ne" href="#" onclick="window.location.href=\''.$actionlistfileurl.'&amp;action=reservefile&amp;fileid=\'+controllerListFile.buildUrlByCheckbox(\'active\')" title="'. _('Reserve for later edition') .'" >'.html_image('docman/reserve-document.png',22,22,array('alt'=>_('Reserve'))). '</a>';
			echo '<a class="tabtitle-ne" href="#" onclick="window.location.href=\''.$actionlistfileurl.'&amp;action=releasefile&amp;fileid=\'+controllerListFile.buildUrlByCheckbox(\'active\')" title="'. _('Release reservation') .'">'.html_image('docman/release-document.png',22,22,array('alt'=>_('Release reservation'))). '</a>';
			echo '<a class="tabtitle-ne" href="#" onclick="window.location.href=\''.$actionlistfileurl.'&amp;action=monitorfile&amp;option=add&amp;fileid=\'+controllerListFile.buildUrlByCheckbox(\'active\')" title="'. _('Monitor') .'" >'.html_image('docman/monitor-adddocument.png',22,22,array('alt'=>_('Monitor'))). '</a>';
			echo '<a class="tabtitle-ne" href="#" onclick="window.location.href=\''.$actionlistfileurl.'&amp;action=monitorfile&amp;option=remove&amp;fileid=\'+controllerListFile.buildUrlByCheckbox(\'active\')" title="'. _('Stop Monitoring') .'" >'.html_image('docman/monitor-removedocument.png',22,22,array('alt'=>_('Stop Monitoring'))). '</a>';
		}
	}
	echo '<a class="tabtitle" href="#" onclick="window.location.href=\'/docman/view.php/'.$group_id.'/zip/selected/'.$dirid.'/\'+controllerListFile.buildUrlByCheckbox(\'active\')" title="'. _('Download as a ZIP') . '" >' . html_image('docman/download-directory-zip.png',22,22,array('alt'=>'Download as Zip')). '</a>';
	echo '</span>';
	echo '</p>';
	echo '</div>';
} else {
	if ($dirid) {
		echo '<p class="information">'._('No documents.').'</p>';
	}
}
if (forge_check_perm('docman', $group_id, 'approve') && $DocGroupName) {
	include ($gfcommon.'docman/views/pendingfiles.php');
}
if (forge_check_perm('docman', $g->getID(), 'approve')) {
	include ($gfcommon.'docman/views/editfile.php');
}

include ($gfcommon.'docman/views/help.php');
echo '</div>';
