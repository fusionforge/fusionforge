<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
global $g; //group object
global $group_id; // id of the group
global $dirid; // id of doc_group
global $dgf; // document directory factory of this group
global $dgh; // document directory html
global $nested_docs; // flat docs array
global $nested_pending_docs; // flat pending docs array

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

// include pending files in list for edit.
if (is_array($nested_pending_docs) && array_key_exists($dirid, $nested_pending_docs)) {
	foreach ($nested_pending_docs[$dirid] as $d) {
		$nested_docs[$dirid][] = $d;
	}
}

if (array_key_exists($dirid,$nested_docs) && is_array($nested_docs[$dirid])) {
	foreach ($nested_docs[$dirid] as $d) {

?>
<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
	function doItEditData<?php echo $d->getID(); ?>() {
		document.getElementById('editdata<?php echo $d->getID(); ?>').submit();
		document.getElementById('submiteditdata<?php echo $d->getID(); ?>').disabled = true;
	}
/* ]]> */</script>
<div id="editfile<?php echo $d->getID(); ?>" style="display:none" class="docman_div_include">
<p>
<strong><?php echo _('Document Title:') ?></strong>
<?php echo _('Refers to the relatively brief title of the document (e.g. How to use the download server).') ?>
</p>
<p>
<strong><?php echo _('Description:') ?></strong>
<?php echo _('A brief description to be placed just under the title.') ?>
</p>
<?php
	if ($g->useDocmanSearch())
		echo '<p>'. _('Both fields are used by document search engine.'). '</p>';
?>

	<form id="editdata<?php echo $d->getID(); ?>" name="editdata<?php echo $d->getID(); ?>" action="?group_id=<?php echo $group_id; ?>&amp;action=editfile&amp;fromview=listfile&amp;dirid=<?php echo $dirid; ?>" method="post" enctype="multipart/form-data">

<table border="0">
	<tr>
		<td>
			<strong><?php echo _('Document Title:') ?> </strong><?php echo utils_requiredField(); ?> <?php printf(_('(at least %1$s characters)'), 5) ?><br />
			<input type="text" name="title" size="40" maxlength="255" value="<?php echo $d->getName(); ?>" />
		</td>
	</tr>

	<tr>
		<td>
			<strong><?php echo _('Description:') ?> </strong><?php echo utils_requiredField(); ?> <?php printf(_('(at least %1$s characters)'), 10) ?><br />
			<input type="text" name="description" size="50" maxlength="255" value="<?php echo $d->getDescription(); ?>" />
		</td>
	</tr>

	<tr>
		<td>
			<strong><?php echo _('File')?></strong><?php echo utils_requiredField(); ?><br />
			<?php if ($d->isURL()) {
				echo '<a href="'.inputSpecialchars($d->getFileName()).'">['. _('View File URL') .']</a>';
				} else { ?>
			<a target="_blank" href="view.php/<?php echo $group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()) ?>"><?php echo $d->getFileName(); ?></a>
			<?php } ?>
		</td>
	</tr>

<?php

	if ((!$d->isURL()) && ($d->isText()) && $d->getStateID() != '2') {
		if ($g->useCreateOnline()) {
			echo '<tr>
				<td>';
			echo _('Edit the contents to your desire or leave them as they are to remain unmodified.');
			switch ($d->getFileType()) {
				case "text/html": {
					$GLOBALS['editor_was_set_up']=false;
					$params = array() ;
					/* name must be != data then nothing is displayed */
					$params['name'] = 'details'.$d->getID();
					$params['height'] = "300";
					$params['group'] = $group_id;
					$params['body'] = $d->getFileData();
					plugin_hook("text_editor",$params);
					if (!$GLOBALS['editor_was_set_up']) {
						echo '<textarea name="details'.$d->getID().'" rows="15" cols="70">'. $d->getFileData() .'</textarea><br />';
					}
					unset($GLOBALS['editor_was_set_up']);
					echo '<input type="hidden" name="filetype" value="text/html">';
					break;
				}
				default: {
					echo '<textarea name="details'.$d->getID().'" rows="15" cols="70">'. $d->getFileData() .'</textarea><br />';
					echo '<input type="hidden" name="filetype" value="text/plain">';
				}
			}
			echo '	</td>
			</tr>';
		}
	}
?>
	<tr>
		<td>
			<strong><?php echo _('Folder that document belongs in') ?></strong><br />
			<?php
				if ($d->getStateID() == 2) {
					$newdgf = new DocumentGroupFactory($g);
					$dgh->showSelectNestedGroups($newdgf->getNested(), 'doc_group', false, false);
				} else {
					$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $d->getDocGroupID());
				}
		?></td>
	</tr>
	<tr>
		<td>
			<strong><?php echo _('State') ?>:</strong><br />
			<?php
				if ($d->getStateID() == 2) {
					doc_get_state_box('xzxz', $d->getStateID());
				} else {
					doc_get_state_box('xzxz');
				}
			?>
		</td>
	</tr>
	<tr>
		<td>
		<?php	if ($d->getStateID() != '2') {
				if ($d->isURL()) { ?>
		<strong><?php echo _('Specify an outside URL where the file will be referenced') ?> :</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="file_url" size="50" value="<?php echo $d->getFileName() ?>" />
		<?php 		} else { ?>
		<strong><?php echo _('OPTIONAL: Upload new file') ?></strong><br />
		<input type="file" name="uploaded_data" size="30" />
		<?php
				}
			}
		?>
		</td>
	</tr>
</table>
<input type="hidden" name="docid" value="<?php echo $d->getID(); ?>" />
<input type="button" id="submiteditdata<?php echo $d->getID(); ?>" value="<?php echo _('Submit Edit') ?>" onclick="javascript:doItEditData<?php echo $d->getID(); ?>()" />
</form>
</div>
<?php
	}
}
?>
