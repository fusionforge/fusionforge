<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
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
global $actionlistfileurl; // built action url from listfile.php (handle the hierarchy)

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect($redirecturl.'&warning_msg='.urlencode($return_msg));
}

if (!isset($nested_pending_docs)) {
	echo '<p class="information">'._('No pending documents.').'</p>';
} else {

?>
<script language="JavaScript" type="text/javascript">//<![CDATA[
var controllerListPending;

jQuery(document).ready(function() {
	controllerListPending = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		docManURL:		'<?php util_make_uri("docman") ?>',
		lockIntervalDelay:	60000, //in microsecond and if you change this value, please update the check value 600
		divEditFile:		jQuery('#editFile'),
		divEditTitle:		'<?php echo _("Edit document dialog box") ?>',
	});
});
//]]></script>
<?php
	if (isset($nested_pending_docs[$dirid]) && is_array($nested_pending_docs[$dirid])) {
		echo '<div class="docmanDiv">';
		echo '<h4>'._('Pending files').'</h4>';
		$tabletop = array('<input id="checkallpending" type="checkbox" onchange="controllerListPending.checkAll(\'checkeddocidpending\', \'pending\')" />', '', _('Filename'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('Actions'));
		$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', 'unsortable');
		echo $HTML->listTableTop($tabletop, false, 'sortable_docman_listfile', 'sortable', $classth);
		$time_new = 604800;
		foreach ($nested_pending_docs[$dirid] as $d) {
			echo '<tr>';
			echo '<td>';
			echo '<input type="checkbox" value="'.$d->getID().'" class="checkeddocidpending" onchange="controllerListPending.checkgeneral(\'pending\')" />';
			echo '</td>';
			switch ($d->getFileType()) {
				case "URL": {
					$docurl = $d->getFileName();
					$docurltitle = _('Visit this link');
					break;
				}
				default: {
					$docurl = util_make_uri('/docman/view.php/'.$g->getID().'/'.$d->getID().'/'.urlencode($d->getFileName()));
					$docurltitle = _('View this document');
				}
			}
			echo '<td><a href="'.$docurl.'" class="tabtitle-nw" title="'.$docurltitle.'" >';
			echo html_image($d->getFileTypeImage(), '22', '22', array('alt'=>$d->getFileType()));;
			echo '</a></td>';
			echo '<td>';
			if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
				$html_image_attr = array();
				$html_image_attr['alt'] = _('new');
				$html_image_attr['class'] = 'docman-newdocument';
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
			$editfileaction = '?action=editfile&amp;fromview=listfile&amp;dirid='.$d->getDocGroupID();
			if (isset($GLOBALS['childgroup_id']) && $GLOBALS['childgroup_id']) {
				$editfileaction .= '&amp;childgroup_id='.$GLOBALS['childgroup_id'];
			}
			$editfileaction .= '&amp;group_id='.$GLOBALS['group_id'];
			echo '<a class="tabtitle-ne" href="#" onclick="javascript:controllerListPending.toggleEditFileView({action:\''.$editfileaction.'\', lockIntervalDelay: 60000, childGroupId: '.util_ifsetor($childgroup_id, 0).' ,id:'.$d->getID().', groupId:'.$d->Group->getID().', docgroupId:'.$d->getDocGroupID().', statusId:'.$d->getStateID().', statusDict:'.$dm->getStatusNameList('json').', docgroupDict:'.$dm->getDocGroupList($nested_groups, 'json').', title:\''.htmlspecialchars($d->getName()).'\', filename:\''.$d->getFilename().'\', description:\''.htmlspecialchars($d->getDescription()).'\', isURL:\''.$d->isURL().'\', isText:\''.$d->isText().'\', useCreateOnline:'.$d->Group->useCreateOnline().', docManURL:\''.util_make_uri("docman").'\'})" title="'. _('Edit this document') .'" >'.html_image('docman/edit-file.png', 22, 22, array('alt'=>_('Edit this document'))). '</a>';
			echo '</td>';
			echo '</tr>';
		}
		echo $HTML->listTableBottom();
		echo '<p>';
		echo '<span id="docman-massactionpendingmessage" class="tabtitle-nw" title="'. _('Actions availables for checked files, you need to check at least one file to get actions') . '">';
		echo _('Mass actions for selected pending files:');
		echo '</span>';
		echo '<span id="massactionpending" class="docman-massaction-hide" style="display:none;" >';
		echo '<a class="tabtitle" href="#" onclick="window.location.href=\'/docman/view.php/'.$g->getID().'/zip/selected/'.$dirid.'/\'+controllerListPending.buildUrlByCheckbox(\'pending\')" title="'. _('Download as a zip') . '" >' . html_image('docman/download-directory-zip.png', 22, 22, array('alt'=>'Download as Zip')). '</a>';
		echo '<a class="tabtitle" href="#" onclick="window.location.href=\''.$actionlistfileurl.'&action=validatefile&fileid=\'+controllerListPending.buildUrlByCheckbox(\'pending\')" title="'. _('Activate in this directory') . '" >' . html_image('docman/validate.png', 22, 22, array('alt'=>'Activate in this directory')). '</a>';
		echo '</span>';
		echo '</p>';
		echo '</div>';
	}
}
