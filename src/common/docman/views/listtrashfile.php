<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011-2014, Franck Villaume - TrivialDev
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
global $g; // the Group object

$linkmenu = 'listtrashfile';
$childgroup_id = getIntFromRequest('childgroup_id');
$baseredirecturl = '/docman/?group_id='.$group_id;
$redirecturl = $baseredirecturl.'&view=.'.$linkmenu.'&dirid='.$dirid;
$actionlistfileurl = '?group_id='.$group_id.'&amp;view='.$linkmenu.'&amp;dirid='.$dirid;

if (!forge_check_perm('docman', $group_id, 'approve')) {
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

$dgh = new DocumentGroupHTML($g);
if ($dgh->isError())
	exit_error($dgh->getErrorMessage(), 'docman');

$df->setStateID('2');

$d_arr =& $df->getDocuments();
$linkmenu = 'listtrashfile';

$nested_docs = array();
$DocGroupName = 0;

if ($dirid) {
	$ndg = new DocumentGroup($g, $dirid);
	$DocGroupName = $ndg->getName();
	if (!$DocGroupName) {
		session_redirect($baseredirecturl.'&error_msg='.urlencode($g->getErrorMessage()));
	}
	if ($ndg->getState() != 2) {
		$error_msg = _('Invalid folder');
		session_redirect($baseredirecturl.'&view=listtrashfile&error_msg='.urlencode($error_msg));
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

echo '<div id="rightdiv">';
echo '<div style="padding:5px;"><form id="emptytrash" name="emptytrash" method="post" action="?group_id='.$group_id.'&amp;action=emptytrash" >';
echo '<input id="submitemptytrash" type="submit" value="'. _('Delete permanently all documents and folders with deleted status.') .'" >';
echo '</form></div>';
?>
<script type="text/javascript">//<![CDATA[
var controllerListTrash;

jQuery(document).ready(function() {
	controllerListTrash = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		divEditDirectory:	jQuery('#editdocgroup'),
		buttonEditDirectory:	jQuery('#docman-editdirectory'),
		docManURL:		'<?php util_make_uri("docman") ?>',
		lockIntervalDelay:	60000, //in microsecond and if you change this value, please update the check value 600
		divLeft:		jQuery('#leftdiv'),
		divRight:		jQuery('#rightdiv'),
		divEditFile:		jQuery('#editFile'),
		divEditTitle:		'<?php echo _("Edit document dialog box") ?>',
		enableResize:		true,
		page:			'trashfile'
	});
});
//]]></script>
<?php
if ($DocGroupName) {
	echo '<h3 class="docman_h3" >'._('Document Folder')._(': ').' <i>'.$DocGroupName.'</i>&nbsp;';
	if ($DocGroupName != '.trash') {
		echo '<a href="#" id="docman-editdirectory" class="tabtitle" title="'._('Edit this folder').'" >'. html_image('docman/configure-directory.png',22,22,array('alt'=>'edit')). '</a>';
		echo '<a href="'.$actionlistfileurl.'&amp;action=deldir" id="docman-deletedirectory" title="'._('Delete permanently this folder and his content.').'" >'. html_image('docman/delete-directory.png',22,22,array('alt'=>'deldir')). '</a>';
	}
	echo '</h3>';
	echo '<div class="docman_div_include" id="editdocgroup" style="display:none;">';
	echo '<h4 class="docman_h4">'. _('Edit this folder') .'</h4>';
	include ($gfcommon.'docman/views/editdocgroup.php');
	echo '</div>';
}

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$tabletop = array('<input id="checkallactive" title="'._('Select / Deselect all documents for massaction').'" class="tabtitle-w" type="checkbox" onClick="controllerListTrash.checkAll(\'checkeddocidactive\', \'active\')" />', '', _('File Name'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('Actions'));
	$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', 'unsortable');
	echo '<div class="docmanDiv">';
	echo $HTML->listTableTop($tabletop, false, 'sortable_docman_listfile', 'sortable', $classth);
	$time_new = 604800;
	foreach ($nested_docs[$dirid] as $d) {
		echo '<tr>';
		echo '<td>';
		echo '<input title="'._('Select / Deselect this document for massaction').'" class="checkeddocidactive tabtitle-w" type="checkbox" value="'.$d->getID().'" onClick="controllerListTrash.checkgeneral(\'active\')" />';
		echo '</td>';
		switch ($d->getFileType()) {
			case "URL": {
				$docurl = $d->getFileName();
				$docurltitle = _('Visit this link');
				break;
			}
			default: {
				$docurl = util_make_uri('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()));
				$docurltitle = _('View this document');
			}
		}
		echo '<td><a href="'.$docurl.'" class="tabtitle-nw" title="'.$docurltitle.'" >';
		echo html_image($d->getFileTypeImage(), '22', '22', array('alt'=>$d->getFileType()));;
		echo '</a></td>';
		echo '<td style="word-wrap: break-word; max-width: 250px;" >';
		if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
			$html_image_attr = array();
			$html_image_attr['alt'] = _('new');
			$html_image_attr['class'] = 'docman-newdocument';
			$html_image_attr['title'] = _('Updated since less than 7 days');
			echo html_image('docman/new.png', '14', '14', $html_image_attr);
		}
		echo '&nbsp;'.$d->getFileName();
		echo '</td>';
		echo '<td style="word-wrap: break-word; max-width: 250px;" >'.$d->getName().'</td>';
		echo '<td style="word-wrap: break-word; max-width: 250px;" >'.$d->getDescription().'</td>';
		echo '<td>'.make_user_link($d->getCreatorUserName(), $d->getCreatorRealName()).'</td>';
		if ( $d->getUpdated() ) {
			echo '<td sorttable_customkey="'.$d->getUpdated().'" >';
			echo date(_('Y-m-d H:i'), $d->getUpdated());
		} else {
			echo '<td sorttable_customkey="'.$d->getCreated().'" >';
			echo date(_('Y-m-d H:i'), $d->getCreated());
		}
		echo '</td>';
		echo '<td>';
		echo $d->getStateName().'</td>';
		echo '<td>';
		switch ($d->getFileType()) {
			case "URL": {
				echo "--";
				break;
			}
			default: {
				echo human_readable_bytes($d->getFileSize());
				break;
			}
		}
		echo '</td>';

		echo '<td>';
		$newdgf = new DocumentGroupFactory($d->Group);
		$editfileaction = '?action=editfile&amp;fromview=listfile&amp;dirid='.$d->getDocGroupID();
		if (isset($GLOBALS['childgroup_id']) && $GLOBALS['childgroup_id']) {
			$editfileaction .= '&amp;childgroup_id='.$GLOBALS['childgroup_id'];
		}
		$editfileaction .= '&amp;group_id='.$GLOBALS['group_id'];
		echo '<a class="tabtitle" href="'.$actionlistfileurl.'&amp;action=delfile&amp;fileid='.$d->getID().'" title="'. _('Delete permanently this document.') .'" >'.html_image('docman/delete-directory.png',22,22,array('alt'=>_('Delete permanently this document.'))). '</a>';
		echo '<a class="tabtitle-ne" href="#" onclick="javascript:controllerListTrash.toggleEditFileView({action:\''.$editfileaction.'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json','2').', docgroupDict:'.$dm->getDocGroupList($newdgf->getNested(), 'json').', title:\''.$d->getName().'\', filename:\''.$d->getFilename().'\', description:\''.$d->getDescription().'\', isURL:\''.$d->isURL().'\', isText:\''.$d->isText().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri("docman").'\'})" title="'. _('Edit this document') .'" >'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';
		echo '</td>';
		echo '</tr>'."\n";
	}
	echo $HTML->listTableBottom();
	echo '<p>';
	echo '<span id="massactionactive" style="display: none;" >';
    echo '<span class="tabtitle" id="docman-massactionmessage" title="'. _('Actions availables for selected documents, you need to check at least one document to get actions') . '" >';
    echo _('Mass actions for selected documents:');
    echo '</span>';
	echo '<a class="tabtitle" href="#" onclick="window.location.href=\''.$actionlistfileurl.'&amp;action=delfile&amp;fileid=\'+controllerListTrash.buildUrlByCheckbox(\'active\')" title="'. _('Permanently Delete') .'" >'.html_image('docman/delete-directory.png',22,22,array('alt'=>_('Permanently Delete'))). '</a>';
	echo '<a class="tabtitle" href="#" onclick="window.location.href=\'/docman/view.php/'.$group_id.'/zip/selected/\'+controllerListTrash.buildUrlByCheckbox(\'active\')" title="'. _('Download as a ZIP') . '" >' . html_image('docman/download-directory-zip.png',22,22,array('alt'=>_('Download as a ZIP'))). '</a>';
	echo '</span>';
	echo '</p>';
	echo '</div>';
} else {
	if ($dirid) {
		echo '<p class="information">'._('No documents.').'</p>';
	}
}

echo '</div>';
$foundFiles = 0;
if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	$foundFiles = count($nested_docs[$dirid]);
}
if (forge_check_perm('docman', $g->getID(), 'approve') && $foundFiles) {
	include ($gfcommon.'docman/views/editfile.php');
}
