<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright (C) 2010 Alcatel-Lucent
 * Copyright 2010, Franck Villaume - Capgemini
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
global $nested_groups; // flat document directories array
global $HTML; // Layout object
global $LUSER; // User object

$DocGroupName = getNameDocGroup($dirid,$group_id);
if (!$DocGroupName) {
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($g->getErrorMessage()));
}
?>

<script language="javascript">
var lockfileid = new Array();
JQuery(window).unload(function(){
    for (var localid in localfileid) {
        JQuery.ajax({async: false, url:"<?php util_make_url('docman') ?>", data: {group_id:<?php echo $group_id ?>,action:'lockfile',lock:0,fileid:localid}});
    }
});

function displaySubGroup() {
	if ( 'none' == document.getElementById('addsubdocgroup').style.display ) {
		document.getElementById('addsubdocgroup').style.display = 'block';
		document.getElementById('addfile').style.display = 'none';
		document.getElementById('editdocgroup').style.display = 'none';
	} else {
		document.getElementById('addsubdocgroup').style.display = 'none';
	}
}
function displayAddFile() {
	if ( 'none' == document.getElementById('addfile').style.display ) {
		document.getElementById('addfile').style.display = 'block';
		document.getElementById('addsubdocgroup').style.display = 'none';
		document.getElementById('editdocgroup').style.display = 'none';
	} else {
		document.getElementById('addfile').style.display = 'none';
	}
}
function displayEditDocGroup() {
	if ( 'none' == document.getElementById('editdocgroup').style.display ) {
		document.getElementById('editdocgroup').style.display = 'block';
		document.getElementById('addsubdocgroup').style.display = 'none';
		document.getElementById('addfile').style.display = 'none';
	} else {
		document.getElementById('editdocgroup').style.display = 'none';
	}
}
function displayEditFile(id) {
	var divid = 'editfile'+id;
	if ( 'none' == document.getElementById(divid).style.display ) {
        lockfileid.push(id);
		document.getElementById(divid).style.display = 'block';
        jQuery.get('<?php util_make_url('docman') ?>',
                    {group_id:<?php echo $group_id ?>,action:'lockfile',lock:1,fileid:id});
	} else {
		document.getElementById(divid).style.display = 'none';
        lockfileid.splice(lockfileid.indexOf(id),1);
        jQuery.get('<?php util_make_url('docman') ?>',
                    {group_id:<?php echo $group_id ?>,action:'lockfile',lock:0,fileid:id});
	}
}
</script>

<?php
echo '<h3 class="docman_h3" >Directory : <i>'.$DocGroupName.'</i>&nbsp;';
if (forge_check_perm ('docman', $group_id, 'approve')) {
	echo '<a href="#" onclick="javascript:displayEditDocGroup()" >'. html_image('docman/configure-directory.png',22,22,array('alt'=>'edit')). '</a>';
	echo '<a href="#" onclick="javascript:displaySubGroup()" >'. html_image('docman/insert-directory.png',22,22,array('alt'=>'addsubdir')). '</a>';
	//echo '<a href="?group_id='.$group_id.'&action=trashdir&dirid='.$dirid.'">'. html_image('docman/trash-empty.png',22,22,array('alt'=>'trashdir')). '</a>';
	if (!isset($nested_docs[$dirid]) && !isset($nested_groups[$dirid]))
		echo '<a href="?group_id='.$group_id.'&action=deldir&dirid='.$dirid.'">'. html_image('docman/delete-directory.png',22,22,array('alt'=>'deldir')). '</a>';
}

echo '<a href="#" onclick="javascript:displayAddFile()" >'. html_image('docman/insert-file.png',22,22,array('alt'=>'addfile')). '</a>';

echo '</h3>';

echo '<div class="docman_div_include" id="editdocgroup" style="display:none;">';
echo '<h4 class="docman_h4">'. _('Edit this directory') .'</h4>';
include ('docman/views/editdocgroup.php');
echo '</div>';
echo '<div class="docman_div_include" id="addsubdocgroup" style="display:none;">';
echo '<h4 class="docman_h4">'. _('Add a new subdirectory') .'</h4>';
include ('docman/views/addsubdocgroup.php');
echo '</div>';
echo '<div class="docman_div_include" id="addfile" style="display:none">';
echo '<h4 class="docman_h4">'. _('Add a new document') .'</h4>';
include ('docman/views/addfile.php');
echo '</div>';

if (isset($nested_docs[$dirid]) && is_array($nested_docs[$dirid])) {
    $tabletop = array('','Filename','Title','Description','Author','Last time','Status','Size');
	if (forge_check_perm ('docman', $group_id, 'approve'))
		$tabletop[] = 'Actions';
    echo '<div class="docmanDiv">';
    echo $HTML->listTableTop($tabletop);
	$time_new = 604800;
    $rowid = 0;
	foreach ($nested_docs[$dirid] as $d) {
		echo '<tr ' . $HTML->boxGetAltRowStyle($rowid).'>';
		switch ($d->getFileType()) {
		case "URL":
			$docurl=$d->getFileName();
			break;
		default:
			$docurl=util_make_url ('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()));
		}
		echo '<td><a href="'.$docurl.'">';
		switch ($d->getFileType()) {
			case "image/png":
			case "image/jpeg":
			case "image/gif":
			case "image/tiff":
				echo html_image('docman/file_type_image.png',22,22,array('alt'=>$d->getFileType()));
				break;
			case "application/pdf":
				echo html_image('docman/file_type_pdf.png',22,22,array('alt'=>$d->getFileType()));
				break;
			case "text/html":
			case "URL":
				echo html_image('docman/file_type_html.png',22,22,array('alt'=>$d->getFileType()));
				break;
			case "text/plain":
				echo html_image('docman/file_type_plain.png',22,22,array('alt'=>$d->getFileType()));
				break;
			case "application/msword":
            case "application/vnd.oasis.opendocument.text":
				echo html_image('docman/file_type_writer.png',22,22,array('alt'=>$d->getFileType()));
				break;
			case "application/vnd.ms-excel":
			case "application/vnd.oasis.opendocument.spreadsheet":
				echo html_image('docman/file_type_spreadsheet.png',22,22,array('alt'=>$d->getFileType()));
				break;
            case "application/vnd.oasis.opendocument.presentation":
            case "application/vnd.ms-powerpoint":
				echo html_image('docman/file_type_presentation.png',22,22,array('alt'=>$d->getFileType()));
				break;
			case "application/zip":
			case "application/x-tar":
            case "application/x-rpm":
				echo html_image('docman/file_type_archive.png',22,22,array('alt'=>$d->getFileType()));
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
		echo '<td>'.make_user_link($d->getCreatorUserName(),$d->getCreatorRealName()).'</td>';
		echo '<td>';
		if ( $d->getUpdated() ) {
			echo date(_('Y-m-d H:i'),$d->getUpdated());
		} else {
			echo date(_('Y-m-d H:i'),$d->getCreated());
		}
		echo '</td>';
        echo '<td>';
        if ($d->getReserved()) {
            echo html_image('docman/document-reserved.png',22,22,array('alt'=>_('Reserved Document')));
        } else {
            echo $d->getStateName().'</td>';
        }
		echo '<td>';
		switch ($d->getFileType()) {
		case "URL":
			echo "--";
			break;
		default:
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
		}

		if (forge_check_perm ('docman', $group_id, 'approve')) {
			echo '<td>';
            if (!$d->getLocked() && !$d->getReserved()) {
			    echo '<a href="?group_id='.$group_id.'&action=trashfile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'">'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Trash this file'))). '</a>';
			    echo '<a href="#" onclick="javascript:displayEditFile(\''.$d->getID().'\')" >'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this file'))). '</a>';
			        echo '<a href="?group_id='.$group_id.'&action=reservefile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'">'.html_image('docman/reserve-document.png',22,22,array('alt'=>_('Reserve this document'))). '</a>';
            } else {
                if ($d->getReservedBy() != $LUSER->getID()) {
                    echo html_image('docman/trash-forbidden.png',22,22,array('alt'=>_('Trash forbidden')));
                    echo html_image('docman/edit-forbidden.png',22,22,array('alt'=>_('Edition forbidden')));
                    if (forge_check_perm ('docman', $group_id, 'admin')) {
                        echo '<a href="?group_id='.$group_id.'&action=enforcereserve&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'">'.html_image('docman/enforce-document.png',22,22,array('alt'=>'Enforce reservation'));
                    } else {
                        echo html_image('docman/document-reserved.png',22,22,array('alt',_('Document reserved by')));
                    }
                } else {
			        echo '<a href="?group_id='.$group_id.'&action=trashfile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'">'.html_image('docman/trash-empty.png',22,22,array('alt'=>_('Trash this file'))). '</a>';
			        echo '<a href="#" onclick="javascript:displayEditFile(\''.$d->getID().'\')" >'.html_image('docman/edit-file.png',22,22,array('alt'=>_('Edit this file'))). '</a>';
			        echo '<a href="?group_id='.$group_id.'&action=releasefile&view=listfile&dirid='.$dirid.'&fileid='.$d->getID().'">'.html_image('docman/release-document.png',22,22,array('alt'=>_('Release this document'))). '</a>';
                }
            }
			echo '</td>';
		}
		echo '</tr>';
        $rowid++;
	}
    echo $HTML->listTableBottom();
    echo '</div>';
	echo '<div class="docmanDiv">'.html_image('docman/new.png',14,14,array('alt'=>'new')).' : ' . _('Created or updated since less than 7 days') .'</div>';
	include 'docman/views/editfile.php';
}

?>
