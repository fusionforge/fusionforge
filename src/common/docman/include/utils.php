<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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

/**
 * tooling library
 */

function getNameDocGroup($id, $group) {
	$group_object = group_get_object($group);
	$res = db_query_params('SELECT groupname FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
				array($id, $group));
	if (!$res || db_numrows($res) < 1) {
		$group_object->setError('DocumentGroup::'. _('Invalid DocumentGroup ID'));
		return false;
	} else {
		return (db_result($res, 0, 'groupname'));
	}
}

function getStateDocGroup($id, $group) {
	$group_object = group_get_object($group);
	$res = db_query_params('SELECT stateid FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
				array($id, $group));
	if (!$res || db_numrows($res) < 1) {
		$group_object->setError('DocumentGroup:: '. _('Invalid DocumentGroup ID'));
		return false;
	} else {
		return (db_result($res, 0, 'stateid'));
	}
}

function doc_get_state_box($checkedval = 'xzxz') {
	$res_states = db_query_params('select * from doc_states', array());
	echo html_build_select_box($res_states, 'stateid', $checkedval, false);
}

/**
 * docman_recursive_display - Recursive function to show the documents inside the groups tree : javascript enabled function
 *
 * @param	int	doc_group_id
 */
function docman_recursive_display($docgroup) {
	global $nested_groups, $group_id;
	global $idExposeTreeIndex, $dirid,$idhtml;

	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			$idhtml++;

			if ($dg->getState() != 2) {
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
}

/**
 * docman_fill_zip - Recursive function to add docgroup and documents inside zip for backup
 *
 * @param	$object	zip
 * @param	$array	nested groups
 * @param	$object	documentfactory
 * @param	$int	documentgroup id : default value = 0
 * @param	$string	documentgroup parent name : default value = empty
 * @return	boolean	success or not
 * @access	public
 */
function docman_fill_zip($zip, $nested_groups, $document_factory, $docgroup = 0, $parent_docname = '') {
	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			if (!$zip->addEmptyDir($parent_docname.'/'.$dg->getName()))
				return false;

			$document_factory->setDocGroupID($dg->getID());
			$docs = $document_factory->getDocuments(1);	// no caching
			if (is_array($docs) && count($docs) > 0) {	// this group has documents
				foreach ($docs as $doc) {
					if ( !$zip->addFromString($parent_docname.'/'.$dg->getName().'/'.$doc->getFileName(),$doc->getFileData()))
						return false;
				}
			}
			if (!docman_fill_zip($zip, $nested_groups, $document_factory, $dg->getID(), $parent_docname.'/'.$dg->getName())) {
				return false;
			}
		}
	}
	return true;
}

function docman_recursive_stateid($docgroup, $nested_groups, $nested_docs, $stateid = 2) {
	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			$dg->setStateID($stateid);
		}
	}
	if (isset($nested_docs[$docgroup]) && is_array($nested_docs[$docgroup])) {
		foreach ($nested_docs[$docgroup] as $d) {
			$d->setState($stateid);
		}
	}
}

/**
 * docman_display_trash - function to show the documents inside the groups tree with specific status : 2 = deleted
 *@todo: remove css code
 */
function docman_display_trash(&$document_factory, $parent_group = 0) {
	$nested_groups =& $document_factory->getNested(2);
	$child_count = count($nested_groups["$parent_group"]);
	echo "<ul style='list-style-type: none'>\n";
	for ($i=0; $i < $child_count; $i++) {
		$doc_group =& $nested_groups["$parent_group"][$i];
		echo "<li>".$doc_group->getName()."</li>";
	}
	echo "</ul>";
}

/**
 * docman_display_documents - Recursive function to show the documents inside the groups tree
 * @todo : remove the css code
 * @todo : use the javascript controler
 * @todo : use jquery
 */
function docman_display_documents(&$nested_groups, &$document_factory, $is_editor, $stateid = 0, $parent_group = 0) {
	global $group_id;
	if (!array_key_exists("$parent_group", $nested_groups) || !is_array($nested_groups["$parent_group"])) {
		return;
	}
	
	echo '<script type="text/javascript">';
	echo 'var lockInterval = new Array();';
	echo 'function EditData(iddiv) {';
	echo '	if ( "none" == document.getElementById(\'editdata\'+iddiv).style.display ) {';
	echo '		document.getElementById(\'editdata\'+iddiv).style.display = "block";';
	echo '		jQuery.get(\''. util_make_uri('docman/') .'\',{group_id:'. $group_id.',action:\'lockfile\',lock:1,fileid:iddiv});';
	echo '		lockInterval[iddiv] = setInterval("jQuery.get(\''. util_make_uri('docman') .'\',{group_id:'. $group_id .',action:\'lockfile\',lock:1,fileid:"+iddiv+"})",60000);';
	echo '	} else {';
	echo '		document.getElementById(\'editdata\'+iddiv).style.display = "none";';
	echo '		jQuery.get(\''. util_make_uri('docman/') .'\',{group_id:'. $group_id .',action:\'lockfile\',lock:0,fileid:iddiv});';
	echo '		clearInterval(lockInterval[iddiv]);';
	echo '	}';
	echo '}';
	echo '</script>';
	echo '<ul style="list-style-type: none">';
	$child_count = count($nested_groups["$parent_group"]);
	
	for ($i=0; $i < $child_count; $i++) {
		$doc_group =& $nested_groups["$parent_group"][$i];
		
		if ($doc_group->hasDocuments($nested_groups, $document_factory, $stateid)) {
			$icon = 'ofolder15.png';
			echo '<li>'.html_image('docman/directory.png', '22', '22', array('border'=>'0'))."&nbsp;".$doc_group->getName()."</li>";
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
							date(_('Y-m-d H:i'),$docs[$j]->getCreated())) .
							") ";
				if ($docs[$j]->getFileSize() > 1024) {
					$tooltip .= floor($docs[$j]->getFileSize()/1024) . "KB";
				} else {
					$tooltip .= $docs[$j]->getFileSize() . "B";
				}
				$tooltip = htmlspecialchars($tooltip);
				echo '<li>'.  html_image('docman/file_type_unknown.png', '22', '22', array("border"=>"0")). 
					$docs[$j]->getName(). ' - ' . $tooltip . '&nbsp;<a href="#" onclick="javascript:EditData(\''.$docs[$j]->getID().'\')" >'. html_image('docman/edit-file.png', '22', '22', array('alt'=>'editfile')) .'</a></li>';
				echo "<i>".$docs[$j]->getDescription()."</i><br/>";
				echo '<div class="docman_div_include" id="editdata'.$docs[$j]->getID().'" style="display:none">';
				document_editdata($docs[$j]);
				echo '</div>';
			}
			echo "</ul>";
		}
	}
	echo "</ul>";
}

/**
 * @todo - remove the css code
 */
function document_editdata(&$document) {
	global $g, $dirid, $group_id;
	$dgh = new DocumentGroupHTML($g);
	if ($dgh->isError())
		exit_error($dgh->getErrorMessage(), 'docman');

	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError())
		exit_error($dgf->getErrorMessage(), 'docman');

	switch ($document->getStatename()) {
		case "pending": {
			$fromview = "listpendingfile";
			break;
		}
		case "deleted": {
			$fromview = "listtrashfile";
			break;
		}
		default: {
			$fromview = "listfile";
			break;
		}
	}
?>
<div class="docmanDivIncluded">
<p><strong>
<?php echo _('Document Title:') ?>
</strong>
<?php echo _('Refers to the relatively brief title of the document (e.g. How to use the download server).') ?>
</p><p><strong>
<?php echo _('Description:') ?>
</strong>
<?php echo _('A brief description to be placed just under the title.') ?>
</p>
<?php
	if ($g->useDocmanSearch())
		echo '<p>'. _('Both fields are used by document search engine.'). '</p>';
?>

	<form id="editdata<?php echo $document->getID(); ?>" name="editdata<?php echo $document->getID(); ?>" action="?group_id=<?php echo $group_id; ?>&action=editfile&fromview=<?php echo $fromview; ?>&dirid=<?php echo $dirid; ?>" method="post" enctype="multipart/form-data">

<table>
	<tr>
		<td style="text-align:right;"> <strong><?php echo _('Document Title') ?></strong><?php echo utils_requiredField(); ?>
	</td>
	<td>
			<input type="text" name="title" size="40" maxlength="255" value="<?php echo $document->getName(); ?>" />
		<?php printf(_('(at least %1$s characters)'), 5) ?>
		</td>
	</tr>
	<tr>
		<td style="text-align:right;">
		<strong><?php echo _('Description') ?></strong><?php echo utils_requiredField(); ?>
		</td>
		<td>
			<input type="text" name="description" size="50" maxlength="255" value="<?php echo $document->getDescription(); ?>" />
			<?php printf(_('(at least %1$s characters)'), 10) ?>
		</td>
	</tr>

	<tr>
		<td style="text-align:right;">
			<strong><?php echo _('File')?></strong><?php echo utils_requiredField(); ?>
		</td>
		<td>
			<?php if ($document->isURL()) {
					echo '<a href="'.inputSpecialchars($d->getFileName()).'">[View File URL]</a>';
				} else { ?>
					<a target="_blank" href="/docman/view.php/<?php echo $group_id.'/'.$document->getID().'/'.urlencode($document->getFileName()) ?>"><?php echo $document->getName(); ?></a>
			<?php } ?>
		</td>
	</tr>

<?php

	if ((!$document->isURL()) && ($document->isText())) {
		if ($g->useCreateOnline()) {
		echo '<tr>
			<td colspan="2">';
			echo _('Edit the contents to your desire or leave them as they are to remain unmodified.');
			switch ($document->getFileType()) {
				case "text/html": {
					$GLOBALS['editor_was_set_up']=false;
					$params = array() ;
					/* warning name must be unique */
					$params['name'] = 'details'.$document->getID();
					$params['width'] = "800";
					$params['height'] = "300";
					$params['body'] = $d->getFileData();
					$params['group'] = $group_id;
					plugin_hook("text_editor",$params);
					if (!$GLOBALS['editor_was_set_up']) {
						echo '<textarea name="details'.$document->getID().'" rows="15" cols="70" wrap="soft">'. $document->getFileData()  .'</textarea><br />';
					}
					echo '<input type="hidden" name="filetype" value="text/html">';
					unset($GLOBALS['editor_was_set_up']);
					break;
				}
				default: {
					echo '<textarea name="details'.$document->getID().'" rows="15" cols="70" wrap="soft">'. $document->getFileData()  .'</textarea><br />';
					echo '<input type="hidden" name="filetype" value="text/plain">';
				}
			}

			echo '	</td>
			</tr>';
		}
	}
?>
	<tr>
		<td style="text-align:right;">
			<strong><?php echo _('Directory that document belongs in') ?></strong>
		</td>
		<td>
			<?php $dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $document->getDocGroupID()); ?>
		</td>
	</tr>
	<tr>
		<td style="text-align:right;">
			<strong><?php echo _('State') ?></strong>
		</td>
		<td>
			<?php doc_get_state_box($document->getStateID()); ?>
		</td>
	</tr>
	<tr>
		<td style="text-align:right;">
			<?php if ($document->isURL()) { ?>
			<strong><?php echo _('Specify an outside URL where the file will be referenced') ?> :</strong><?php echo utils_requiredField(); ?>
		</td>
		<td>
			<input type="text" name="file_url" size="50" value="<?php echo $document->getFileName() ?>" />
			<?php } else { ?>
			<strong><?php echo _('OPTIONAL: Upload new file') ?></strong>
		</td>
		<td>
			<input type="file" name="uploaded_data" size="30" />
			<?php
			}
			?>
		</td>
	</tr>
</table>

<input type="hidden" name="docid" value="<?php echo $document->getID(); ?>" />
<input type="submit" id="submiteditdata<?php echo $document->getID(); ?>" value="<?php echo _('Submit Edit') ?>" /><br /><br />
</form>
</div>
<?php
}
?>
