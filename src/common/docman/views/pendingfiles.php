<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2011, Franck Villaume - Capgemini
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
global $nested_pending_docs;
global $use_tooltips; // enable or not tooltips in docman

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

if (!isset($nested_pending_docs)) {
	echo '<div class="feedback">'._('No pending files.').'</div>';
} else {

?>
<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
var controllerListPending;

jQuery(document).ready(function() {
	controllerListPending = new DocManListFileController({
		groupId:		<?php echo $group_id ?>,
		tipsyElements:		[
						{selector: '.docman-pendingdownloadaszip', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-massactionpendingmessage', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-pendingactivate', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-pendingviewfile', options:{gravity: 'nw', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-pendingeditfile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
					],

		docManURL:		'<?php util_make_uri("docman") ?>',
		lockIntervalDelay:	60000, //in microsecond and if you change this value, please update the check value 600
	});
});
/* ]]> */</script>
<?php
	if (isset($nested_pending_docs[$dirid]) && is_array($nested_pending_docs[$dirid])) {
		echo '<div class="docmanDiv">';
		echo '<h4>'._('Pending files').'</h4>';
		$tabletop = array('<input id="checkallpending" type="checkbox" onchange="controllerListPending.checkAll(\'checkeddocidPending\', \'pending\')" />', '', _('Filename'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('Actions'));
		$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', 'unsortable');
		echo $HTML->listTableTop($tabletop, false, 'sortable_docman_listfile', 'sortable', $classth);
		$time_new = 604800;
		foreach ($nested_pending_docs[$dirid] as $d) {
			echo '<tr>';
			echo '<td>';
			echo '<input type="checkbox" value="'.$d->getID().'" class="checkeddocidPending" onchange="controllerListPending.checkgeneral(\'pending\')" />';
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
			echo $d->getStateName().'</td>';
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

			echo '<td>';
			echo '<a class="docman-pendingeditfile" href="#" onclick="javascript:controllerListPending.toggleEditFileView(\''.$d->getID().'\')" ';
			if ($use_tooltips)
				echo ' title="'. _('Edit this document') .'" ';

			echo '>'.html_image('docman/edit-file.png', 22, 22, array('alt'=>_('Edit this document'))). '</a>';
			echo '</td>';
		}
		echo '</tr>';
		echo $HTML->listTableBottom();
		echo '<p>';
		echo '<span id="docman-massactionpendingmessage"';
		if ($use_tooltips)
			echo ' title="'. _('Actions availables for checked files, you need to check at least one file to get actions') . '" ';

		echo '>';
		echo _('Mass actions for selected pending files:');
		echo '</span>';
		echo '<span id="massactionpending" class="docman-massaction-hide" style="display:none;" >';
		echo '<a class="docman-pendingdownloadaszip" href="#" onclick="window.location.href=\'/docman/view.php/'.$group_id.'/zip/selected/'.$dirid.'/\'+controllerListPending.buildUrlByCheckbox(\'Pending\')" ';
		if ($use_tooltips)
			echo ' title="'. _('Download as a zip') . '" ';

		echo '>' . html_image('docman/download-directory-zip.png', 22, 22, array('alt'=>'Download as Zip')). '</a>';
		echo '<a class="docman-pendingactivate" href="#" onclick="window.location.href=\'?group_id='.$group_id.'&action=validatefile&view=listfile&dirid='.$dirid.'&fileid=\'+controllerListPending.buildUrlByCheckbox(\'Pending\')" ';
		if ($use_tooltips)
			echo ' title="'. _('Activate in this directory') . '" ';

		echo '>' . html_image('docman/validate.png', 22, 22, array('alt'=>'Activate in this directory')). '</a>';
		echo '</span>';
		echo '</p>';
		echo '</div>';
	}
}
?>
