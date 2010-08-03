<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright (C) 2010 Alcatel-Lucent
 * Copyright 2010, Franck Villaume
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $group_id; // id of the group
global $dirid; // id of doc_group
global $nested_docs; // flat docs array

$DocGroupName = getNameDocGroup($dirid,$group_id);
if (!$DocGroupName) {
	$feedback = $g->getErrorMessage();
	Header('Location: '.util_make_url('/docman/?group_id='.$group_id.'&feedback='.urlencode($feedback)));
	exit;
}
?>

<script language="javascript">
function displaySubGroup() {
	if ( 'none' == document.getElementById('addsubdocgroup').style.display ) {
		document.getElementById('addsubdocgroup').style.display = 'inline';
		document.getElementById('addfile').style.display = 'none';
		document.getElementById('editdocgroup').style.display = 'none';
	} else {
		document.getElementById('addsubdocgroup').style.display = 'none';
	}
}
function displayAddFile() {
	if ( 'none' == document.getElementById('addfile').style.display ) {
		document.getElementById('addfile').style.display = 'inline';
		document.getElementById('addsubdocgroup').style.display = 'none';
		document.getElementById('editdocgroup').style.display = 'none';
	} else {
		document.getElementById('addfile').style.display = 'none';
	}
}
function displayEditDocGroup() {
	if ( 'none' == document.getElementById('editdocgroup').style.display ) {
		document.getElementById('editdocgroup').style.display = 'inline';
		document.getElementById('addsubdocgroup').style.display = 'none';
		document.getElementById('addfile').style.display = 'none';
	} else {
		document.getElementById('editdocgroup').style.display = 'none';
	}
}
function displayEditFile(id) {
	var divid = 'editfile'+id;
	if ( 'none' == document.getElementById(divid).style.display ) {
		document.getElementById(divid).style.display = 'inline';
	} else {
		document.getElementById(divid).style.display = 'none';
	}
}
</script>

<?php
echo '<h3>Directory : <i>'.$DocGroupName.'</i>&nbsp;';
if (forge_check_perm ('docman', $group_id, 'approve')) {
	echo '<a href="#" onclick="javascript:displayEditDocGroup()" >'. html_image('docman/configure-directory.png',22,22,array('alt'=>'edit')). '</a>';
	echo '<a href="#" onclick="javascript:displaySubGroup()" >'. html_image('docman/insert-directory.png',22,22,array('alt'=>'addsubdir')). '</a>';
	echo '<a href="?group_id='.$group_id.'&action=trashdir&dirid='.$dirid.'">'. html_image('docman/trash-empty.png',22,22,array('alt'=>'trashdir')). '</a>';
	if (!isset($nested_docs[$dirid]) && !isset($nested_groups[$dirid]))
		echo '<a href="?group_id='.$group_id.'&action=deldir&dirid='.$dirid.'">'. html_image('docman/delete-directory.png',22,22,array('alt'=>'deldir')). '</a>';
}

echo '<a href="#" onclick="javascript:displayAddFile()" >'. html_image('docman/insert-file.png',22,22,array('alt'=>'addfile')). '</a>';

echo '</h3>';

echo '<div id="editdocgroup" style="display:none">';
include ('docman/views/editdocgroup.php');
echo '</div>';
echo '<div id="addsubdocgroup" style="display:none">';
include ('docman/views/addsubdocgroup.php');
echo '</div>';
echo '<div id="addfile" style="display:none">';
include ('docman/views/addfile.php');
echo '</div>';

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
	echo '<table style="width:100%">';
	echo '<tr>';
	echo '<td>Type</td>'
		.'<td>Filename</td>'
		.'<td>Title</td>'
		.'<td>Description</td>'
		.'<td>Author</td>'
		.'<td>Last time</td>'
		.'<td>Etat</td>'
		.'<td>Size</td>';

	if (forge_check_perm ('docman', $group_id, 'approve'))
		echo '<td>Actions</td>';

	echo '</tr>';
	$time_new = 604800;
	foreach ($nested_docs[$dirid] as $d) {
		echo '<tr>';
		$docurl=util_make_url ('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()));
		echo '<td><a href="'.$docurl.'">';
		switch ($d->getFileType()) {
			case "image/png":
			case "image/jpeg":
				echo html_image('docman/file_type_image.png',22,22,array('alt'=>$d->getFileType()));
				break;
			case "application/pdf":
				echo html_image('docman/file_type_pdf.png',22,22,array('alt'=>$d->getFileType()));
				break;
			default:
				echo html_image('docman/file_type_unknown.png',22,22,array('alt'=>$d->getFileType()));
		}
		echo '</a></td>';
		echo '<td>';
		if (($d->getUpdated() && $time_new > (time() - $d->getUpdated())) || $time_new > (time() - $d->getCreated())) {
			echo html_image('docman/new.png',14,14,array('alt'=>'new'));
		}
		echo '&nbsp;'.$d->getFileName();
		echo '</td>';
		echo '<td>'.$d->getName().'</td>';
		echo '<td>'.$d->getDescription().'</td>';
		echo '<td>'.$d->getCreatorRealName().'</td>';
		echo '<td>';
		if ( $d->getUpdated() ) {
			echo date(_('Y-m-d H:i'),$d->getUpdated());
		} else {
			echo date(_('Y-m-d H:i'),$d->getCreated());
		}
		echo '</td>';
		echo '<td>'.$d->getStateName().'</td>';
		echo '<td>';
		$metric = 'B';
		$size = $d->getFileSize();
		if ($size > 1024 ) {
			$metric = 'KB';
			$size = floor ($size/1024);
			if ($size > 1024 ) {
				$metric = 'MB';
				$size = floor ($size/1024);
			}
		}
		echo $size . $metric;
		echo '</td>';

		if (forge_check_perm ('docman', $group_id, 'approve')) {
			echo '<td>';
			echo '<a href="?group_id='.$group_id.'&action=trashfile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'">'.html_image('docman/trash-empty.png',22,22,array('alt'=>'trashfile')). '</a>';
			echo '<a href="#" onclick="javascript:displayEditFile(\''.$d->getID().'\')" >'.html_image('docman/edit-file.png',22,22,array('alt'=>'editfile')). '</a>';
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '<div >'.html_image('docman/new.png',14,14,array('alt'=>'new')).' : ' . _('Created or updated since less than 7 days') .'</div>';
	include 'docman/views/editfile.php';
}

?>
