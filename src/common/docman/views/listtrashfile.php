<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
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
global $df; // document factory
global $dgf; // document group factory
global $group_id; // id of the group
global $dirid; // id of doc_group
global $g; // the Group object

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

$df->setStateID('2');

/**
 * var must be named d_arr & nested_groups
 * because used by tree.php
 */
$d_arr =& $df->getDocuments();
$nested_groups =& $dgf->getNested('2');
$linkmenu = 'listtrashfile';

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

// $nested_groups has a system directory : .trash => so count < 2
if ((!$d_arr || count($d_arr) < 1) && (!$nested_groups || count($nested_groups) < 2)) {
	echo '<div class="warning">'._('Trash is empty').'</div>';
} else {

?>
<script type="text/javascript">
var controllerListTrash;

jQuery(document).ready(function() {
	controllerListTrash = new DocManListFileController({
		groupId:			<?php echo $group_id ?>,
		tipsyElements:		[
						{selector: '#docman-editdirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-delete', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '#docman-trashdirectory', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-downloadaszip', options:{delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-viewfile', options:{gravity: 'nw', delayIn: 500, delayOut: 0, fade: true}},
						{selector: '.docman-editfile', options:{gravity: 'ne', delayIn: 500, delayOut: 0, fade: true}},
						],

		divEditDirectory:		jQuery('#editdocgroup'),
		buttonEditDirectory:	jQuery('#docman-editdirectory'),
		docManURL:		'<?php util_make_uri("docman") ?>',
		lockIntervalDelay:	60000, //in microsecond and if you change this value, please update the check value 600
		divLeft:			jQuery('#left'),
		divHandle:			jQuery('#handle'),
		divRight:			jQuery('#right'),
	});
});
</script>
<?php
	echo '<div style="padding:5px;"><form id="emptytrash" name="emptytrash" method="post" action="?group_id='.$group_id.'&action=emptytrash" >';
	echo '<input id="submitemptytrash" type="submit" value="'. _('Delete permanently all documents with deleted status.') .'" >';
	echo '</form></div>';

	echo '<div id="left" style="float:left; width:17%; min-width: 50px;">';
	include ($gfcommon.'docman/views/tree.php');
	echo '</div>';
	echo '<div id="handle" style="float:left; height:100px; margin:3px; width:3px; background: #000; cursor:e-resize;"></div>';
	echo '<div id="right" style="float:left; width: 80%; overflow: auto; max-width: 90%;">';
	if ($DocGroupName) {
		echo '<h3 class="docman_h3" >Directory : <i>'.$DocGroupName.'</i>&nbsp;';
		if ($DocGroupName != '.trash') {
			echo '<a href="#" id="docman-editdirectory" ';
			if ($use_tooltips)
				echo 'title="'._('Edit this directory').'"';

			echo ' >'. html_image('docman/configure-directory.png',22,22,array('alt'=>'edit')). '</a>';
			echo '<a href="?group_id='.$group_id.'&action=deldir&dirid='.$dirid.'" id="docman-deletedirectory" ';
			if ($use_tooltips)
				echo ' title="'._('Delete permanently this directory and his content.').'" ';

			echo '>'. html_image('docman/delete-directory.png',22,22,array('alt'=>'deldir')). '</a>';
		}

		echo '</h3>';

		echo '<div class="docman_div_include" id="editdocgroup" style="display:none;">';
		echo '<h4 class="docman_h4">'. _('Edit this directory') .'</h4>';
		include ($gfcommon.'docman/views/editdocgroup.php');
		echo '</div>';
	}

	if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
		$tabletop = array('<input id="checkall" type="checkbox" onchange="controllerListTrash.checkAll()" />', '', _('Filename'), _('Title'), _('Description'), _('Author'), _('Last time'), _('Status'), _('Size'), _('Actions'));
		$classth = array('unsortable', 'unsortable', '', '', '', '', '', '', '', 'unsortable');
		echo '<div class="docmanDiv">';
		echo $HTML->listTableTop($tabletop, false, 'sortable_docman_listfile', 'sortable', $classth);
		$time_new = 604800;
		foreach ($nested_docs[$dirid] as $d) {
			echo '<tr>';
			echo '<td>';
			echo '<input type="checkbox" value="'.$d->getID().'" id="checkeddocid" class="checkeddocid" onchange="controllerListTrash.checkgeneral()" />';
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
			echo '<a class="docman-delete" href="?group_id='.$group_id.'&action=deletefile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'" ';
			if ($use_tooltips)
				echo ' title="'. _('Delete permanently this document.') .'"';

			echo ' >'.html_image('docman/delete-directory.png',22,22,array('alt'=>_('Delete permanently this document.'))). '</a>';

			echo '<a class="docman-editfile" href="#" onclick="javascript:controllerListTrash.toggleEditFileView(\''.$d->getID().'\')" ';
			if ($use_tooltips)
				echo ' title="'. _('Edit this document') .'" ';

			echo '>'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this document'))). '</a>';
			echo '</td>';
		}
		echo '</tr>';
		echo $HTML->listTableBottom();
		echo '</div>';
		echo '<div class="docmanDiv"><p>';
		echo _('Mass Actions for selected files:');
		echo '<a class="docman-delete" href="#" onClick="window.location.href=\'?group_id='.$group_id.'&action=delfile&view=listtrashfile&dirid='.$dirid.'&fileid=\'+controllerListTrash.buildUrlByCheckbox()" ';
		if ($use_tooltips)
			echo ' title="'. _('Delete permanently.') .'" ';

		echo '>'.html_image('docman/delete-directory.png',22,22,array('alt'=>_('Delete permanently.'))). '</a>';
		echo '<a class="docman-downloadaszip" href="#" onClick="window.location.href=\'/docman/view.php/'.$group_id.'/zip/selected/\'+controllerListTrash.buildUrlByCheckbox()" ';
		if ($use_tooltips)
			echo ' title="'. _('Download as a zip') . '" ';

		echo '>' . html_image('docman/download-directory-zip.png',22,22,array('alt'=>'Download as Zip')). '</a>';
		echo '</p></div>';
		include ($gfcommon.'docman/views/editfile.php');
	} else {
		echo '<p class="warning">'._('No documents to display').'</p>';
	}

	echo '</div>';
	echo '<div style="clear:both"; />';
}
?>
