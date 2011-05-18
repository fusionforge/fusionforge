<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright (C) 2010 Alcatel-Lucent
 * Copyright 2010-2011, Franck Villaume - Capgemini

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
global $dgf; // document group factory
global $HTML; // Layout object
global $u; // User object
global $use_tooltips; // enable or not tooltips in docman
global $g; // the Group object

if (!forge_check_perm('docman', $group_id, 'read')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

$df->setDocGroupID($dirid);

/**
 * var must be named d_arr & nested_groups
 * because used by tree.php
 */
$d_arr =& $df->getDocuments();
$nested_groups = $dgf->getNested();
$linkmenu = 'listfile';

$nested_docs = array();
$DocGroupName = 0;

if ($dirid) {
	$ndg = new DocumentGroup($g,$dirid);
	$DocGroupName = $ndg->getName();
	if (!$DocGroupName) {
		session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($g->getErrorMessage()));
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

?>

<script type="text/javascript">
var controllerListFile;

jQuery(document).ready(function() {
	controllerListFile = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		tipsyElements:		[
						{selector: '#docman-additem', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-editdirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-deletedirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-trashdirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-downloadaszip', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-viewfile', options:{gravity: 'nw', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-reserveddocument', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-movetotrash', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-editfile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-releasereservation', options:{gravity: 'ne',delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-reservefile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-monitorfile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-newdocument', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}}
					],
		divAddItem:		jQuery('#additem'),
		divEditDirectory:	jQuery('#editdocgroup'),
		buttonAddItem:		jQuery('#docman-additem'),
		buttonEditDirectory:	jQuery('#docman-editdirectory'),
		docManURL:		'<?php util_make_uri("docman") ?>',
		lockIntervalDelay:	60000, //in microsecond and if you change this value, please update the check value 600
		divLeft:		jQuery('#left'),
		divHandle:		jQuery('#handle'),
		divRight:		jQuery('#right'),
		divsEdit:		'<?php if (isset($nested_docs[$dirid])) { echo $nested_docs[$dirid]; } ?>',
	});
});

</script>

<?php
echo '<div id="left" style="float:left; width:17%; min-width: 50px;">';
include ($gfcommon.'docman/views/tree.php');
echo '</div>';
echo '<div id="handle" style="float:left; height:100px; margin:3px; width:3px; background: #000; cursor:e-resize;"></div>';
echo '<div id="right" style="float:left; width: 80%; overflow: auto; max-width: 90%;">';
if ($DocGroupName) {
	echo '<h3 class="docman_h3" >Directory : <i>'.$DocGroupName.'</i>&nbsp;';
	if (forge_check_perm('docman', $group_id, 'approve')) {
		echo '<a href="#" id="docman-editdirectory" ';
		if ($use_tooltips)
			echo 'title="'._('Edit this directory').'"';

		echo ' >'. html_image('docman/configure-directory.png',22,22,array('alt'=>'edit')). '</a>';
		echo '<a href="?group_id='.$group_id.'&action=trashdir&dirid='.$dirid.'" id="docman-trashdirectory" ';
		if ($use_tooltips)
			echo 'title="'._('Move this directory and his content to trash').'" ';
		echo '>'. html_image('docman/trash-empty.png',22,22,array('alt'=>'trashdir')). '</a>';
		if (!isset($nested_docs[$dirid]) && !isset($nested_groups[$dirid])) {
			echo '<a href="?group_id='.$group_id.'&action=deldir&dirid='.$dirid.'" id="docman-deletedirectory" ';
			if ($use_tooltips)
				echo ' title="'._('Permanently delete this directory').'" ';

			echo '>'. html_image('docman/delete-directory.png',22,22,array('alt'=>'deldir')). '</a>';
		}
	}

	if (forge_check_perm('docman', $group_id, 'submit')) {
		echo '<a href="#" id="docman-additem" ';
		if ($use_tooltips)
			echo 'title="'. _('Add a new item in this directory') . '" ';

		echo '>'. html_image('docman/insert-directory.png',22,22,array('alt'=>'additem')). '</a>';
	}

	echo '<a href="/docman/view.php/'.$group_id.'/zip/full/'.$dirid.'" ';
	if ($use_tooltips)
		echo 'class="docman-downloadaszip" title="'. _('Download this directory as a zip') . '" ';

	echo '>' . html_image('docman/download-directory-zip.png',22,22,array('alt'=>'downloadaszip')). '</a>';

	echo '</h3>';

	if (forge_check_perm('docman', $group_id, 'approve')) {
		echo '<div class="docman_div_include" id="editdocgroup" style="display:none;">';
		echo '<h4 class="docman_h4">'. _('Edit this directory') .'</h4>';
		include ($gfcommon.'docman/views/editdocgroup.php');
		echo '</div>';
	}
	if (forge_check_perm('docman', $group_id, 'submit')) {
		echo '<div class="docman_div_include" id="additem" style="display:none">';
		echo '<h4 class="docman_h4">'. _('Add a new item') .'</h4>';
		include ($gfcommon.'docman/views/additem.php');
		echo '</div>';
	}
}

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$tabletop = array('<input id="checkall" type="checkbox" onchange="controllerListFile.checkAll()" />', '', _('Filename'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'));
	$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '');
	if (forge_check_perm('docman', $group_id, 'approve')) {
		$tabletop[] = _('Actions');
		$classth[] = 'unsortable';
	}
	echo '<div class="docmanDiv">';
	echo $HTML->listTableTop($tabletop, false, 'sortable_docman_listfile', 'sortable', $classth);
	$time_new = 604800;
	foreach ($nested_docs[$dirid] as $d) {
		echo '<tr>';
		echo '<td>';
		if (!$d->getLocked() && !$d->getReserved()) {
			echo '<input type="checkbox" value="'.$d->getID().'" id="checkeddocid" class="checkeddocid" onchange="controllerListFile.checkgeneral()" />';
		} else {
			if (session_loggedin() && ($d->getReservedBy() != $u->getID())) {
				echo '<input type="checkbox" name="disabled" disabled="disabled"';
			} else {
				echo '<input type="checkbox" value="'.$d->getID().'" id="checkeddocid" class="checkeddocid" onchange="controllerListFile.checkgeneral()" />';
			}
		}
		echo '</td>';
		switch ($d->getFileType()) {
			case "URL": {
				$docurl = $d->getFileName();
				break;
			}
			default: {
				$docurl = util_make_uri('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()));
			}
		}
		echo '<td><a href="'.$docurl.'" class="docman-viewfile"';
		if ($use_tooltips)
			echo ' title="'._('View this document').'"';

		echo ' >';
		echo html_image($d->getFileTypeImage(), '22', '22', array('alt'=>$d->getFileType()));;
		echo '</a></td>';
		echo '<td>';
		if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
			$html_image_attr = array();
			$html_image_attr['alt'] = _('new');
			$html_image_attr['class'] = 'docman-newdocument';
			if ($use_tooltips)
				$html_image_attr['title'] = _('Created or updated since less than 7 days');
			echo html_image('docman/new.png', '14', '14', $html_image_attr);
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
			$html_image_attr = array();
			$html_image_attr['alt'] = _('Reserved Document');
			$html_image_attr['class'] = 'docman-reserveddocument';
			if ($use_tooltips)
				$html_image_attr['title'] = _('Reserved Document');

			echo html_image('docman/document-reserved.png', '22', '22', $html_image_attr);
		} else {
			echo $d->getStateName().'</td>';
		}
		echo '<td>';
		switch ($d->getFileType()) {
			case "URL": {
				echo "--";
				break;
			}
			default: {
				$metric = 'B';
				$size = $d->getFileSize();
				if ($size > 1024 ) {
					$metric = 'KB';
					$size = floor($size/1024);
					if ($size > 1024 ) {
						$metric = 'MB';
						$size = floor($size/1024);
					}
				}
				echo $size . $metric;
				echo '</td>';
			}
		}

		if (forge_check_perm('docman', $group_id, 'approve')) {
			echo '<td>';
			/* should we steal the lock on file ? */
			if ($d->getLocked()) {
				if ($d->getLockedBy() == $u->getID()) {
					$d->setLock(0);
					/* if you change the 60000 value above, please update here too */
				} elseif ((time() - $d->getLockdate()) > 600) {
					$d->setLock(0);
				}
			}
			if (!$d->getLocked() && !$d->getReserved()) {
				echo '<a class="docman-movetotrash" href="?group_id='.$group_id.'&action=trashfile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'" ';
				if ($use_tooltips)
					echo ' title="'. _('Move this document to trash') .'"';

				echo ' >'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move to trash this document'))). '</a>';

				echo '<a class="docman-editfile" href="#" onclick="javascript:controllerListFile.toggleEditFileView(\''.$d->getID().'\')" ';
				if ($use_tooltips)
					echo ' title="'. _('Edit this document') .'" ';

				echo '>'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';

				echo '<a class="docman-reservefile" href="?group_id='.$group_id.'&action=reservefile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'" ';
				if ($use_tooltips)
					echo ' title="'. _('Reserve this document for later edition') .'" ';

				echo '>'.html_image('docman/reserve-document.png',22,22,array('alt'=>_('Reserve this document'))). '</a>';
			} else {
				if ($d->getReservedBy() != $u->getID()) {
					if (forge_check_perm('docman', $group_id, 'admin')) {
						echo '<a class="docman-enforcereservation" href="?group_id='.$group_id.'&action=enforcereserve&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'" ';
						if ($use_tooltips)
							echo ' title="'. _('Enforce reservation') .'" ';

						echo '>'.html_image('docman/enforce-document.png',22,22,array('alt'=>_('Enforce reservation')));
					}
				} else {
					echo '<a class="docman-movetotrash" href="?group_id='.$group_id.'&action=trashfile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'" ';
					if ($use_tooltips)
						echo ' title="'. _('Move this document to trash') .'" ';

					echo '>'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move to trash this document'))). '</a>';
					echo '<a class="docman-editfile" href="#" onclick="javascript:controllerListFile.toggleEditFileView(\''.$d->getID().'\')" ';
					if ($use_tooltips)
						echo ' title="'. _('Edit this document') .'" ';

					echo '>'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';
					echo '<a class="docman-releasereservation" href="?group_id='.$group_id.'&action=releasefile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'" ';
					if ($use_tooltips)
						echo ' title="'. _('Release reservation') .'" ';

					echo '>'.html_image('docman/release-document.png',22,22,array('alt'=>_('Release reservation'))). '</a>';
				}
			}
			if ($d->isMonitoredBy($u->getID())) {
				$option = 'remove';
				$titleMonitor = _('Stop monitoring this document');
			} else {
				$option = 'add';
				$titleMonitor = _('Start monitoring this document');
			}
			echo '<a class="docman-monitorfile" href="?group_id='.$group_id.'&action=monitorfile&option='.$option.'&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'" ';
			if ($use_tooltips)
				echo ' title="'.$titleMonitor.'" ';

			echo '>'.html_image('docman/monitor-'.$option.'document.png',22,22,array('alt'=>$titleMonitor)). '</a>';
			echo '</td>';
		}
		echo '</tr>';
	}
	echo $HTML->listTableBottom();
	echo '</div>';
	echo '<div class="docmanDiv"><p>';
	echo _('Mass Actions for selected files:');
	if (forge_check_perm('docman', $group_id, 'approve')) {
		echo '<a class="docman-movetotrash" href="#" onClick="window.location.href=\'?group_id='.$group_id.'&action=trashfile&view=listfile&dirid='.$dirid.'&fileid=\'+controllerListFile.buildUrlByCheckbox()" ';
		if ($use_tooltips)
			echo ' title="'. _('Move to trash') .'" ';

		echo '>'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Move to trash'))). '</a>';
		echo '<a class="docman-reservefile" href="#" onClick="window.location.href=\'?group_id='.$group_id.'&action=reservefile&view=listfile&dirid='.$dirid.'&fileid=\'+controllerListFile.buildUrlByCheckbox()" ';
		if ($use_tooltips)
			echo ' title="'. _('Reserve for later edition') .'" ';

		echo '>'.html_image('docman/reserve-document.png',22,22,array('alt'=>_('Reserve'))). '</a>';
		echo '<a class="docman-releasereservation" href="#" onClick="window.location.href=\'?group_id='.$group_id.'&action=releasefile&view=listfile&dirid='.$dirid.'&fileid=\'+controllerListFile.buildUrlByCheckbox()" ';
		if ($use_tooltips)
			echo ' title="'. _('Release reservation') .'" ';

		echo '>'.html_image('docman/release-document.png',22,22,array('alt'=>_('Release reservation'))). '</a>';
		echo '<a class="docman-monitorfile" href="#" onClick="window.location.href=\'?group_id='.$group_id.'&action=monitorfile&option=add&view=listfile&dirid='.$dirid.'&fileid=\'+controllerListFile.buildUrlByCheckbox()" ';
		if ($use_tooltips)
			echo ' title="'. _('Start monitoring') .'" ';

		echo '>'.html_image('docman/monitor-adddocument.png',22,22,array('alt'=>_('Start monitoring'))). '</a>';
		echo '<a class="docman-monitorfile" href="#" onClick="window.location.href=\'?group_id='.$group_id.'&action=monitorfile&option=remove&view=listfile&dirid='.$dirid.'&fileid=\'+controllerListFile.buildUrlByCheckbox()" ';
		if ($use_tooltips)
			echo ' title="'. _('Stop monitoring') .'" ';

		echo '>'.html_image('docman/monitor-removedocument.png',22,22,array('alt'=>_('Stop monitoring'))). '</a>';
	}
	echo '<a class="docman-downloadaszip" href="#" onClick="window.location.href=\'/docman/view.php/'.$group_id.'/zip/selected/\'+controllerListFile.buildUrlByCheckbox()" ';
	if ($use_tooltips)
		echo ' title="'. _('Download as a zip') . '" ';

	echo '>' . html_image('docman/download-directory-zip.png',22,22,array('alt'=>'Download as Zip')). '</a>';
	echo '</p></div>';
	if (forge_check_perm('docman', $group_id, 'approve')) {
		include ($gfcommon.'docman/views/editfile.php');
	}
} else {
	echo '<p class="warning">'._('No documents to display').'</p>';
}
echo '</div>';
echo '<div style="clear:both"; />';
?>