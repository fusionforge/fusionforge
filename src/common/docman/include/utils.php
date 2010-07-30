<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
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

/* tooling library */

function getNameDocGroup($id,$group) {
	$res = db_query_params ('SELECT groupname FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
							array ($id,$group));
	if (!$res || db_numrows($res) < 1) {
		$this->setError(_('DocumentGroup: Invalid DocumentGroup ID'));
		return false;
	} else {
		return (db_result($res,0,'groupname'));
	}
}

function getStateDocGroup($id,$group) {
	$res = db_query_params ('SELECT stateid FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
							array ($id,$group));
	if (!$res || db_numrows($res) < 1) {
		$this->setError(_('DocumentGroup: Invalid DocumentGroup ID'));
		return false;
	} else {
		return (db_result($res,0,'stateid'));
	}
}

function doc_get_state_box($checkedval='xzxz') {
    $res_states=db_query_params ('select * from doc_states', array());
    echo html_build_select_box ($res_states,'stateid',$checkedval,false);

}

function docman_recursive_display($docgroup) {
	global $nested_groups,$group_id;
	global $idExposeTreeIndex,$dirid,$idhtml;

    if (is_array(@$nested_groups[$docgroup])) {
        foreach ($nested_groups[$docgroup] as $dg) {
            $idhtml++;

            if ($dirid == $dg->getID())
                $idExposeTreeIndex = $idhtml;

            echo "
                ['".'<span class="JSCookTreeFolderClosed"><i><img alt="" src="\' + ctThemeXPBase + \'folder1.gif" /></i></span><span class="JSCookTreeFolderOpen"><i><img alt="" src="\' + ctThemeXPBase + \'folderopen1.gif"></i></span>'."', '".addslashes($dg->getName())."', '?group_id=".$group_id."&view=listfile&dirid=".$dg->getID()."', '', '',";
	            docman_recursive_display($dg->getID());
            echo ",
                    ],";
        }
    }
}

function docman_recursive_stateid($docgroup,$nested_groups,$nested_docs,$stateid=2) {
    if (is_array(@$nested_groups[$docgroup])) {
        foreach ($nested_groups[$docgroup] as $dg) {
		$dg->setStateID($stateid);
        }
    }
	if (isset($nested_docs[$docgroup]) && is_array($nested_docs[$docgroup])) {
		foreach ($nested_docs[$docgroup] as $d) {
			$d->setStateID($stateid);
		}
	}
}

/**
 * docman_display_documents - Recursive function to show the documents inside the groups tree
 */
function docman_display_trash(&$document_factory,$parent_group=0) {
	$nested_groups =& $document_factory->getNested(2);
	$child_count = count($nested_groups["$parent_group"]);
	echo "<ul style='list-style-type: none'>\n";
	for ($i=0; $i < $child_count; $i++) {
		$doc_group =& $nested_groups["$parent_group"][$i];
		echo "<li>".$doc_group->getName()."</li>";
	}
	echo "</ul>";
}

function docman_display_documents(&$nested_groups, &$document_factory, $is_editor, $stateid=0, $parent_group=0) {
	if (!array_key_exists("$parent_group",$nested_groups) || !is_array($nested_groups["$parent_group"])) {
		return;
	}
	
	echo '<script language="javascript">';
	echo 'function EditData(iddiv) {';
	echo '	if ( "none" == document.getElementById(iddiv).style.display ) {';
	echo '		document.getElementById(iddiv).style.display = "inline";';
	echo '	} else {';
	echo '		document.getElementById(iddiv).style.display = "none";';
	echo '	}';
	echo '}';
	echo '</script>';
	echo "<ul style='list-style-type: none'>\n";
	$child_count = count($nested_groups["$parent_group"]);
	
	for ($i=0; $i < $child_count; $i++) {		
		$doc_group =& $nested_groups["$parent_group"][$i];
		
		if ($doc_group->hasDocuments($nested_groups, $document_factory, $stateid)) {
			$icon = 'ofolder15.png';
			echo "<li>".html_image('docman/directory.png',"22","22",array("border"=>"0"))."&nbsp;".$doc_group->getName()."</li>";
			docman_display_documents($nested_groups, $document_factory, $is_editor, $stateid, $doc_group->getID());
		}

		// Display this group's documents
		// Retrieve all the docs from this category			
		if ($stateid) {
			$document_factory->setStateID($stateid);
		}
		$document_factory->setDocGroupID($doc_group->getID());
		$docs = $document_factory->getDocuments();
		if (is_array($docs)) {
			$docs_count = count($docs);
			
			echo "<ul style='list-style-type: none'>";
			for ($j=0; $j < $docs_count; $j++) {
				$tooltip = $docs[$j]->getFileName() . " (" .
							($docs[$j]->getUpdated() ?
							date(_('Y-m-d H:i'), $docs[$j]->getUpdated()) :
							date(_('Y-m-d H:i'),$docs[$j]->getCreated()))  .
							") ";
				if ($docs[$j]->getFileSize() > 1024) {
					$tooltip .= floor($docs[$j]->getFileSize()/1024) . "KB";
				} else {
					$tooltip .= $docs[$j]->getFileSize() . "B";
				}
				$tooltip = htmlspecialchars($tooltip);
				echo '<li>'.  html_image('docman/file_type_unknown.png',"22","22",array("border"=>"0")). 
					$docs[$j]->getName().  ' - ' . $tooltip . '&nbsp;<a href="#" onclick="javascript:EditData(\'editdata'.$docs[$j]->getID().'\')" >'. html_image('docman/edit-file.png',22,22,array('alt'=>'editfile')) .'</a></li>';
				echo "<i>".$docs[$j]->getDescription()."</i><br/>";
				echo '<div id="editdata'.$docs[$j]->getID().'" style="display:none">';
				document_editdata($docs[$j]);
				echo '</div>';
			}
			echo "</ul>";
		}
	}
	echo "</ul>\n";
}

function document_editdata(&$document) {
	global $g,$dirid,$group_id;
	$dgh = new DocumentGroupHTML($g);
        if ($dgh->isError())
                exit_error('Error',$dgh->getErrorMessage());

	$dgf = new DocumentGroupFactory($g);
        if ($dgf->isError())
                exit_error('Error',$dgf->getErrorMessage());


?>
<p>
<?php echo _("<strong>Document Title</strong>:  Refers to the relatively brief title of the document (e.g. How to use the download server)<br /><strong>Description:</strong> A brief description to be placed just under the title.") ?>
</p>
<?php
	if ($g->useDocmanSearch())
		echo '<p>'. _('Both fields are used by document search engine.'). '</p>';
?>

	<form id="editdata<?php echo $document->getID(); ?>" name="editdata<?php echo $document->getID(); ?>" action="?group_id=<?php echo $group_id; ?>&action=editfile&dirid=<?php echo $dirid; ?>" method="post" enctype="multipart/form-data">

<table border="0">
	<tr>
		<td>
			<strong><?php echo _('Document Title') ?>: </strong><?php echo utils_requiredField(); ?> <?php printf(_('(at least %1$s characters)'), 5) ?><br />
			<input type="text" name="title" size="40" maxlength="255" value="<?php echo $document->getName(); ?>" />
			<br />
		</td>
	</tr>

    <tr>
        <td>
        <strong><?php echo _('Description') ?></strong><?php echo utils_requiredField(); ?> <?php printf(_('(at least %1$s characters)'), 10) ?><br />
        <input type="text" name="description" size="50" maxlength="255" value="<?php echo $document->getDescription(); ?>" />
        <br /></td>
    </tr>

    <tr>
        <td>
        <strong><?php echo _('File')?></strong><?php echo utils_requiredField(); ?><br />
        <?php if ($document->isURL()) {
            echo '<a href="'.inputSpecialchars($d->getFileName()).'">[View File URL]</a>';
        } else { ?>
        <a target="_blank" href="../view.php/<?php echo $group_id.'/'.$document->getID().'/'.urlencode($document->getFileName()) ?>"><?php echo $document->getName(); ?></a>
        <?php } ?>
        </td>
    </tr>

<?php

    if ((!$document->isURL()) && ($document->isText())) {
        echo '<tr>
	                <td>
		                ';
	
		echo _('Edit the contents to your desire or leave them as they are to remain unmodified.');
		echo '<textarea name="data" rows="15" cols="100" wrap="soft">'. $document->getFileData()  .'</textarea><br />';
		echo '<input type="hidden" name="filetype" value="text/plain">';
		echo '</td>
		            </tr>';
	}

?>

    <tr>
        <td>
        <strong><?php echo _('Group that document belongs in') ?></strong><br />
        <?php
				$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $document->getDocGroupID());

	     ?></td>
    </tr>

    <tr>
        <td>
        <br /><strong><?php echo _('State') ?>:</strong><br />
        <?php
		     doc_get_state_box($document->getStateID());
        ?></td>
    </tr>
    <tr>
        <td>
        <?php if ($document->isURL()) { ?>
        <strong><?php echo _('Specify an outside URL where the file will be referenced') ?> :</strong><?php echo utils_requiredField(); ?><br />
        <input type="text" name="file_url" size="50" value="<?php echo $document->getFileName() ?>" />
        <?php } else { ?>
        <strong><?php echo _('OPTIONAL: Upload new file') ?></strong><br />
        <input type="file" name="uploaded_data" size="30" /><br/><br />
            <?php
            	if (forge_get_config('use_ftp_uploads')) {
                	echo '<strong>' ;
                	printf(_('OR choose one form FTP %1$s.'), forge_get_config('ftp_upload_host'));
                	echo '</strong><br />' ;
                	$ftp_files_arr=array_merge($arr,ls($upload_dir,true));
                	echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename','');
                	echo '<br /><br />';
            	}
			}
	        ?>
        </td>
    </tr>
    </table>

    <input type="hidden" name="docid" value="<?php echo $document->getID(); ?>" />
    <input type="submit" id="submiteditdata<?php echo $document->getID(); ?>" value="<?php echo _('Submit Edit') ?>" /><br /><br />
    </form>

<?php
}
?>
