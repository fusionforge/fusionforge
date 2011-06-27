<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright (C) 2010 Alcatel-Lucent
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
global $nested_docs; // flat docs array
global $nested_groups; // flat document directories array
global $HTML; // Layout object
global $LUSER; // User object
global $df;

// we need to know if there is some pending docs for some actions such as delete empty directories
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

$DocGroupName = getNameDocGroup($dirid, $group_id);
if (!$DocGroupName) {
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($g->getErrorMessage()));
}
?>

<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
var controller;

jQuery(document).ready(function() {
	controller = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		tipsyElements:		[
						{selector: '#docman-addnewfile', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-addsubdirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-editdirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-deletedirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-viewfile', options:{gravity: 'nw', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-reserveddocument', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-movetotrash', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-editfile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-releasereservation', options:{gravity: 'ne',delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-reservefile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-monitorfile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}}
					],

		divAddDirectory:	jQuery('#addsubdocgroup'),
		divAddFile:		jQuery('#addfile'),
		divEditDirectory:	jQuery('#editdocgroup'),
		buttonAddDirectory:	jQuery('#docman-addsubdirectory'),
		buttonAddNewFile:	jQuery('#docman-addnewfile'),
		buttonEditDirectory:	jQuery('#docman-editdirectory'),
		docManURL:		'<?php util_make_url("docman") ?>',
		lockIntervalDelay:	60000 //in microsecond and if you change this value, please update the check value 600
	});
});

/* ]]> */</script>

<?php
echo '<h3 class="docman_h3" >Directory : <i>'.$DocGroupName.'</i>&nbsp;';
if (forge_check_perm ('docman', $group_id, 'approve')) {
	echo '<a href="#" id="docman-editdirectory" title="'._('Edit this directory').'">'. html_image('docman/configure-directory.png',22,22,array('alt'=>'edit')). '</a>';
	echo '<a href="#" id="docman-addsubdirectory" title="'._('Add a new subdirectory').'">'. html_image('docman/insert-directory.png',22,22,array('alt'=>'addsubdir')). '</a>';
	// do not uncomment the line : trash directory is not correctly implemented
	//echo '<a href="?group_id='.$group_id.'&amp;action=trashdir&amp;dirid='.$dirid.'">'. html_image('docman/trash-empty.png',22,22,array('alt'=>'trashdir')). '</a>';
	if (!isset($nested_docs[$dirid]) && !isset($nested_groups[$dirid]) && !isset($nested_pending_docs[$dirid]))
		echo '<a href="?group_id='.$group_id.'&amp;action=deldir&amp;dirid='.$dirid.'" id="docman-deletedirectory" title="'._('Permanently delete this directory').'" >'. html_image('docman/delete-directory.png',22,22,array('alt'=>'deldir')). '</a>';
}

if (forge_check_perm ('docman', $group_id, 'submit')) {
	echo '<a href="#" id="docman-addnewfile" title="'. _('Add a new document') . '" >'. html_image('docman/insert-file.png',22,22,array('alt'=>'addfile')). '</a>';
}

echo '</h3>';

echo '<div class="docman_div_include" id="editdocgroup" style="display:none;">';
echo '<h2 class="docman_h2">'. _('Edit this folder') .'</h2>';
include ($gfcommon.'docman/views/editdocgroup.php');
echo '</div>';
echo '<div class="docman_div_include" id="addsubdocgroup" style="display:none;">';
echo '<h2 class="docman_h2">'. _('Add a new folder') .'</h2>';
include ($gfcommon.'docman/views/addsubdocgroup.php');
echo '</div>';
echo '<div class="docman_div_include" id="addfile" style="display:none">';
echo '<h2 class="docman_h2">'. _('Add a new document') .'</h2>';
include ($gfcommon.'docman/views/addfile.php');
echo '</div>';

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$tabletop = array('', _('Filename'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'));
	$classth = array('unsortable', '', '', '', '', '', '', '');
	if (forge_check_perm('docman', $group_id, 'approve'))
		$tabletop[] = _('Actions');
		$classth[] = 'unsortable';
	echo '<div class="docmanDiv">';
	echo $HTML->listTableTop($tabletop, false, 'sortable_docman_listfile', 'sortable', $classth);
	$time_new = 604800;
	foreach ($nested_docs[$dirid] as $d) {
		echo '<tr>';
		switch ($d->getFileType()) {
			case "URL": {
				$docurl = $d->getFileName();
				break;
			}
			default: {
				$docurl = util_make_url('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()));
			}
		}
		echo '<td><a href="'.$docurl.'" class="docman-viewfile" title="'._('View this document').'" >';
		switch ($d->getFileType()) {
			case "image/png":
			case "image/jpeg":
			case "image/gif":
			case "image/tiff":
			case "image/vnd.microsoft.icon":
			case "image/svg+xml": {
				echo html_image('docman/file_type_image.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			case "application/pdf": {
				echo html_image('docman/file_type_pdf.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			case "text/html":
			case "URL": {
				echo html_image('docman/file_type_html.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			case "text/plain": {
				echo html_image('docman/file_type_plain.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			case "application/msword":
			case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
			case "application/vnd.oasis.opendocument.text": {
				echo html_image('docman/file_type_writer.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			case "application/vnd.ms-excel":
			case "application/vnd.oasis.opendocument.spreadsheet":
			case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet": {
				echo html_image('docman/file_type_spreadsheet.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			case "application/vnd.oasis.opendocument.presentation":
			case "application/vnd.ms-office":
			case "application/vnd.ms-powerpoint": {
				echo html_image('docman/file_type_presentation.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			case "application/zip":
			case "application/x-tar":
			case "application/x-rpm": {
				echo html_image('docman/file_type_archive.png', '22', '22', array('alt'=>$d->getFileType()));
				break;
			}
			default: {
				echo html_image('docman/file_type_unknown.png', '22', '22' , array('alt'=>$d->getFileType()));
			}
		}
		echo '</a></td>'."\n";
		echo '<td>';
		if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
			echo html_image('docman/new.png', '14', '14', array('alt'=>'new'));
		}
		echo '&nbsp;'.$d->getFileName();
		echo '</td>';
		echo '<td>'.$d->getName().'</td>';
		echo '<td>'.$d->getDescription().'</td>';
		echo '<td>'.make_user_link($d->getCreatorUserName(), $d->getCreatorRealName()).'</td>';
		echo '<td>';
		if ( $d->getUpdated() ) {
			echo date(_('Y-m-d H:i'), $d->getUpdated());
		} else {
			echo date(_('Y-m-d H:i'), $d->getCreated());
		}
		echo '</td>';
		echo '<td>';
		if ($d->getReserved()) {
			echo html_image('docman/document-reserved.png', '22', '22', array('alt'=>_('Reserved Document'),'title'=>_('Reserved Document'),'class'=>'docman-reserveddocument'));
		} else {
			echo $d->getStateName();
		}
		echo '</td>';
		echo '<td>';
		switch ($d->getFileType()) {
			case "URL": {
				echo "--";
				break;
			}
			default: {
				echo human_readable_bytes($d->getFileSize());
					}
				}
				echo '</td>';

		if (forge_check_perm('docman', $group_id, 'approve')) {
			echo '<td>';
			/* should we steal the lock on file ? */
			if ($d->getLocked()) {
				if ($d->getLockedBy() == $LUSER->getID()) {
					$d->setLock(0);
					/* if you change the 60000 value above, please update here too */
				} elseif ((time() - $d->getLockdate()) > 600) {
					$d->setLock(0);
				}
			}
			if (!$d->getLocked() && !$d->getReserved()) {
				echo '<a href="?group_id='.$group_id.'&amp;action=trashfile&amp;view=listfile&amp;dirid='.$dirid.'&amp;fileid='.$d->getID().'" class="docman-movetotrash" title="'. _('Move this document to trash') .'" >'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move this document to trash'))). '</a>';
				echo '<a href="#" onclick="javascript:controller.toggleEditFileView(\''.$d->getID().'\')" class="docman-editfile" title="'. _('Edit this document') .'" >'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';
				echo '<a href="?group_id='.$group_id.'&amp;action=reservefile&amp;view=listfile&amp;dirid='.$dirid.'&amp;fileid='.$d->getID().'" class="docman-reservefile" title="'. _('Reserve this document for later edition') .'" >'.html_image('docman/reserve-document.png',22,22,array('alt'=>_('Reserve this document for later edition'))). '</a>';
			} else {
				if ($d->getReservedBy() != $LUSER->getID()) {
					if (forge_check_perm('docman', $group_id, 'admin')) {
						echo '<a href="?group_id='.$group_id.'&amp;action=enforcereserve&amp;view=listfile&amp;dirid='.$dirid.'&amp;fileid='.$d->getID().'" class="docman-enforcereservation" title="'. _('Enforce reservation') .'" >'.html_image('docman/enforce-document.png',22,22,array('alt'=>_('Enforce reservation')));
					}
				} else {
					echo '<a href="?group_id='.$group_id.'&amp;action=trashfile&amp;view=listfile&amp;dirid='.$dirid.'&amp;fileid='.$d->getID().'" class="docman-movetotrash" title="'. _('Move this document to trash') .'" >'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move this document to trash'))). '</a>';
					echo '<a href="#" onclick="javascript:controller.toggleEditFileView(\''.$d->getID().'\')" class="docman-editfile" title="'. _('Edit this document') .'" >'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';
					echo '<a href="?group_id='.$group_id.'&amp;action=releasefile&amp;view=listfile&amp;dirid='.$dirid.'&amp;fileid='.$d->getID().'" class="docman-releasereservation" title="'. _('Release reservation') .'" >'.html_image('docman/release-document.png',22,22,array('alt'=>_('Release reservation'))). '</a>';
				}
			}
			if ($d->isMonitoredBy($LUSER->getID())) {
				$option = 'remove';
				$titleMonitor = _('Stop monitoring this document');
			} else {
				$option = 'add';
				$titleMonitor = _('Start monitoring this document');
			}
			echo '<a href="?group_id='.$group_id.'&amp;action=monitorfile&amp;option='.$option.'&amp;view=listfile&amp;dirid='.$dirid.'&amp;fileid='.$d->getID().'" class="docman-monitorfile" title="'.$titleMonitor.'" >'.html_image('docman/monitor-'.$option.'document.png',22,22,array('alt'=>$titleMonitor)). '</a>';
			echo '</td>';
		}
		echo '</tr>'."\n";
	}
	echo $HTML->listTableBottom();
	echo '</div>';
	echo '<div class="docmanDiv">'.html_image('docman/new.png', '14', '14', array('alt'=>'new')).' : ' . _('Created or updated since less than 7 days') .'</div>';
	include ($gfcommon.'docman/views/editfile.php');
}
?>
