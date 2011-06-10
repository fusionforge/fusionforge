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

/**
 * tooling library
 */

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
	global $idExposeTreeIndex, $dirid, $idhtml, $linkmenu;

	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			$idhtml++;

			if ($dirid == $dg->getID())
				$idExposeTreeIndex = $idhtml;

			echo "
				['".'<span class="JSCookTreeFolderClosed"><i><img alt="" src="\' + ctThemeXPBase + \'folder1.gif" /></i></span><span class="JSCookTreeFolderOpen"><i><img alt="" src="\' + ctThemeXPBase + \'folderopen1.gif" /></i></span>'."', '".addslashes($dg->getName())."', '?group_id=".$group_id."&amp;view=".$linkmenu."&amp;dirid=".$dg->getID()."', '', '',";
					docman_recursive_display($dg->getID());
			echo ",
				],";
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

	<form id="editdata<?php echo $document->getID(); ?>" name="editdata<?php echo $document->getID(); ?>" action="?group_id=<?php echo $group_id; ?>&amp;action=editfile&amp;fromview=<?php echo $fromview; ?>&amp;dirid=<?php echo $dirid; ?>" method="post" enctype="multipart/form-data">

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
						echo '<textarea name="details'.$document->getID().'" rows="15" cols="70">'. $document->getFileData()  .'</textarea><br />';
					}
					echo '<input type="hidden" name="filetype" value="text/html">';
					unset($GLOBALS['editor_was_set_up']);
					break;
				}
				default: {
					echo '<textarea name="details'.$document->getID().'" rows="15" cols="70">'. $document->getFileData()  .'</textarea><br />';
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
