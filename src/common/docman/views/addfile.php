<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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
global $g; // group object
global $group_id; // id of the group
global $dirid; //id of the doc_group

// plugin projects-hierarchy
$actionurl = '?group_id='.$group_id.'&amp;action=addfile&amp;dirid='.$dirid;
$redirecturl = '/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid;
if (isset($childgroup_id) && $childgroup_id) {
	$g = group_get_object($childgroup_id);
	$actionurl .= '&amp;childgroup_id='.$childgroup_id;
	$redirecturl .= '&amp;childgroup_id='.$childgroup_id;
}

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError())
	exit_error($dgf->getErrorMessage(), 'docman');

$dgh = new DocumentGroupHTML($g);
if ($dgh->isError())
	exit_error($dgh->getErrorMessage(), 'docman');

if (!forge_check_perm('docman', $group_id, 'submit')) {
	$return_msg = _('Document Manager Action Denied.');
	session_redirect($redirecturl.'&warning_msg='.urlencode($return_msg));
}
?>

<script type="text/javascript">//<![CDATA[
var controllerAddFile;

jQuery(document).ready(function() {
	controllerAddFile = new DocManAddFileController({
		fileRow:		jQuery('#filerow'),
		urlRow:			jQuery('#urlrow'),
		pathRow:		jQuery('#pathrow'),
		editRow:		jQuery('#editrow'),
		editNameRow:		jQuery('#editnamerow'),
		buttonFile:		jQuery('#buttonFile'),
		buttonUrl:		jQuery('#buttonUrl'),
		buttonManualUpload:	jQuery('#buttonManualUpload'),
		buttonEditor:		jQuery('#buttonEditor')
	});
});

//]]></script>
<?php
echo '<div class="docmanDivIncluded">';
if ($dgf->getNested() == NULL) {
	$dg = new DocumentGroup($g);

	if ($dg->isError())
		session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($dg->getErrorMessage()));

	if ($dg->create('Uncategorized Submissions')) {
		session_redirect('/docman/?group_id='.$group_id.'&view=additem');
	}

	echo '<div class="warning">'. _('You MUST first create at least one folder to store your document.') .'</div>';
} else {
	/* display the add new documentation form */
	echo '<p><strong>'. _('Document Title')._(': ').'</strong> '. _('Refers to the relatively brief title of the document (e.g. How to use the download server).'). '</p>';
	echo '<p><strong>'. _('Description')._(': ').'</strong> '. _('A brief description to be placed just under the title.') .'</p>';
	if ($g->useDocmanSearch())
		echo '<p>'._('Both fields are used by the document search engine.').'</p>';

	echo '<form name="adddata" action="'.$actionurl.'" method="post" enctype="multipart/form-data">';
	echo '<table class="infotable">
				<tr>
					<td>
						'. _('Document Title').utils_requiredField()
					.'</td><td>'
					.'<input pattern=".{5,}" placeholder="'._('Document Title').'" title="'.sprintf(_('(at least %s characters)'), 5).'" type="text" name="title" size="40" maxlength="255" required="required" />&nbsp;'
					.sprintf(_('(at least %s characters)'), 5)
					.'</td>
				</tr>
				<tr>
					<td>
						'. _('Description') .utils_requiredField()
				 	.'</td><td>'
						.'<input pattern=".{10,}" placeholder="'._('Description').'" title="'.sprintf(_('(at least %s characters)'), 10).'" type="text" name="description" size="50" maxlength="255" required="required" />&nbsp;'
						.sprintf(_('(at least %s characters)'), 10)
					.'</td>
				</tr>
				<tr>
					<td>
						'. _('Type of Document') .utils_requiredField()
					.'</td><td>
					<input type="radio" id="buttonFile" name="type" value="httpupload" checked="checked" required="required" />'. _('File') .
					'<input type="radio" id="buttonUrl" name="type" value="pasteurl" required="required" />'. _('URL');
	if (forge_get_config('use_manual_uploads')) {
					echo '<input type="radio" id="buttonManualUpload" name="type" value="manualupload" required="required" />'. _('Already-uploaded file');
	}
	if ($g->useCreateOnline()) {
					echo '<input type="radio" id="buttonEditor" name="type" value="editor" required="required" />'. _('Create online');
	}
	echo '				</td>
				</tr>
				<tr id="filerow">
					<td>
						'. _('Upload File') .utils_requiredField()
					.'</td><td>'
						.'<input type="file" required="required" name="uploaded_data" />'.sprintf(_('(max upload size: %s)'),human_readable_bytes(util_get_maxuploadfilesize())).'
					</td>
				</tr>
				<tr id="urlrow" style="display:none">
					<td>
						'. _('URL') . utils_requiredField()
					.'</td><td>'
						.'<input type="url" name="file_url" size="30" placeholder="'._('Enter a valid URL').'" pattern="ftp://.+|https?://.+" />
					</td>
				</tr>';
	if (forge_get_config('use_manual_uploads')) {
		echo '		<tr id="pathrow" style="display:none">
					<td>
						'. _('File') . utils_requiredField() . '</td><td>';
		$incoming = forge_get_config('groupdir_prefix')."/".$g->getUnixName()."/incoming";
		$manual_files_arr = ls($incoming, true);
		if (count($manual_files_arr)) {
			echo html_build_select_box_from_arrays($manual_files_arr, $manual_files_arr, 'manual_path', '');
			echo '		<br />';
			printf(_('Pick a file already uploaded (by SFTP or SCP) to the <a href="%2$s">project\'s incoming directory</a> (%1$s).'),
			$incoming, "sftp://" . forge_get_config('shell_host') . $incoming . "/");
			echo '
						</td>
					</tr>';
		} else {
			echo '		<p class="warning">';
				printf(_('You need first to upload file in %s'),$incoming);
			echo '		</p>';
			echo '			</td>
					</tr>';
		}
	}
	echo '			<tr id="editnamerow" style="display:none">
					<td>
						'. _('File Name') . utils_requiredField()
					.'</td><td>'
						.'<input type="text" name="name" size="30" />
					</td>
				</tr>
				<tr id="editrow" style="display:none">
					<td colspan="2">';
	$GLOBALS['editor_was_set_up'] = false;
	$params = array() ;
	/* name must be details !!! if name = data then nothing is displayed */
	$params['name'] = 'details';
	$params['height'] = "300";
	$params['body'] = "";
	$params['group'] = $group_id;
	plugin_hook("text_editor", $params);
	if (!$GLOBALS['editor_was_set_up']) {
		echo '<textarea name="details" rows="5" cols="80"></textarea>';
	}
	unset($GLOBALS['editor_was_set_up']);
	echo '
					</td>
				</tr>';
	if ($dirid) {
		echo '		<tr><td colspan="2"><input type="hidden" name="doc_group" value="'.$dirid.'"></td></tr>';
	} else {
		echo '
				<tr>
					<td>
						'. _('Documents folder that document belongs in').'
					</td><td>';
		$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $dirid);
		echo '
					</td>
				</tr>';
	}
	if (forge_check_perm('docman', $group_id, 'approve')) {
		echo '
				<tr>
					<td>
						'. _('Status of that document').'
					</td><td>';
		doc_get_state_box('xzxz', 2); /**no direct deleted status */
		echo '
					</td>
				</tr>';
	}
	echo '	</table>';
	echo '<span>'.utils_requiredField() .' '. _('Mandatory fields').'</span>';
	echo '	<div class="docmanSubmitDiv">
			<input type="submit" name="submit" value="'. _('Submit Information'). '" />
		</div>
		</form>';
}

echo '</div>';
