<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2012-2016, Franck Villaume - TrivialDev
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
global $g; // Group object
global $group_id; // id of the group
global $HTML;
global $warning_msg;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerEditFile;

jQuery(document).ready(function() {
	controllerEditFile = new DocManAddFileController({
		fileRow:		jQuery('#uploadnewroweditfile'),
		urlRow:			jQuery('#fileurlroweditfile'),
		pathRow:		jQuery('#pathroweditfile'),
		editRow:		jQuery('#editonlineroweditfile'),
		editNameRow:		jQuery('#editnamerow'),
		buttonFile:		jQuery('#editButtonFile'),
		buttonUrl:		jQuery('#editButtonUrl'),
		buttonManualUpload:	jQuery('#editButtonManualUpload'),
		buttonEditor:		jQuery('#editButtonEditor')
	});
});

//]]>
<?php
echo html_ac(html_ap() - 1);

echo html_ao('div', array('id' => 'editFile'));
echo $HTML->openForm(array('id' => 'editdocdata', 'name' => 'editdocdata', 'method' => 'post', 'enctype' => 'multipart/form-data'));
echo $HTML->listTableTop();
$cells = array();
$cells[] = array(_('Folder that document belongs to')._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('select', array('name' => 'doc_group', 'id' => 'doc_group'), '', false);
$cells[] = array(_('State')._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('select', array('name' => 'stateid', 'id' => 'stateid'), '', false);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
$thArr = array(_('VersionID/Current'), _('Filename'), _('Title'), _('Description'), _('Comment'), _('Author'), _('Last Time'), _('Size'), _('Actions'));
$thClass = array('', '', '', '', '', '', '', '', 'unsortable');
echo $HTML->listTableTop($thArr, array(), 'sortable full', 'sortable_doc_version_table', $thClass);
echo $HTML->listTableBottom();
echo html_e('button', array('id' => 'doc_version_addbutton', 'type' => 'button', 'onclick' => 'javascript:controllerListFile.toggleAddVersionView()'), _('Add new version'));
echo $HTML->listTableTop(array(), array(), 'listing full hide', 'doc_version_edit');
$cells = array();
$cells[] = array(_('Document Title').utils_requiredField()._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('input', array('pattern' => '.{5,}', 'title' => sprintf(_('(at least %s characters)'), 5), 'id' => 'title', 'type' => 'text', 'name' => 'title', 'size' => '40', 'maxlength' => '255'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Description').utils_requiredField()._(':'), 'class' => 'docman_editfile_description');
$cells[][] = html_e('textarea', array('pattern' => '.{10,}', 'title' => _('Editing tips:http,https or ftp: Hyperlinks. [#NNN]: Tracker id NNN. [TNNN]: Task id NNN. [wiki:&lt;pagename&gt;]: Wiki page. [forum:&lt;msg_id&gt;]: Forum post. [DNNN]: Document id NNN.').
										sprintf(_('at least %s characters)'), 10), 'id' => 'description', 'name' => 'description', 'maxlength' => '255', 'rows' => '5', 'cols' => '50'), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Comment')._(':'), 'class' => 'docman_editfile_comment');
$cells[][] = html_e('textarea', array('id' => 'vcomment', 'name' => 'vcomment', 'maxlength' => '255', 'rows' => '5', 'cols' => '50'), '', false);
echo $HTML->multiTableRow(array(), $cells);
if ($g->useDocmanSearch()) {
	$cells = array();
	$cells[] =  array(_('Both fields are used by the document search engine.'), 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
}
$cells = array();
$cells[] = array(_('Current Version')._(':'), 'class' => 'docman_editfile_currentversion');
$cells[][] = html_e('input', array('type' => 'checkbox', 'title' => _('Make this version the current version'), 'id' => 'current_version', 'name' => 'current_version', 'value' => 1));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = _('Type of Document') .utils_requiredField();
$nextcell = html_e('input', array('type' => 'radio', 'id' => 'editButtonFile', 'name' => 'type', 'value' => 'httpupload', 'checked' => 'checked', 'required' => 'required')).html_e('label', array('for' => 'editButtonFile'), _('File')).
		html_e('input', array('type' => 'radio', 'id' => 'editButtonUrl', 'name' => 'type', 'value' => 'pasteurl', 'required' => 'required')).html_e('label', array('for' => 'editButtonUrl'), _('URL'));
if (forge_get_config('use_manual_uploads')) {
	$nextcell .= html_e('input', array('type' => 'radio', 'id' => 'editButtonManualUpload', 'name' => 'type', 'value' => 'manualupload', 'required' => 'required')).html_e('label', array('for' => 'editButtonManualUpload'), _('Already-uploaded file'));
}
if ($g->useCreateOnline()) {
	$nextcell .= html_e('input', array('type' => 'radio', 'id' => 'editButtonEditor', 'name' => 'type', 'value' => 'editor', 'required' => 'required')).html_e('label', array('for' => 'editButtonEditor'), _('Create online'));
}
$cells[][] = $nextcell;
echo $HTML->multiTableRow(array(), $cells);
	if (forge_get_config('use_manual_uploads')) {
		$cells = array();
		$cells[][] = _('File').utils_requiredField();
		$incoming = forge_get_config('groupdir_prefix')."/".$g->getUnixName()."/incoming";
		$manual_files_arr = ls($incoming, true);
		if (count($manual_files_arr)) {
			$cells[][] = html_build_select_box_from_arrays($manual_files_arr, $manual_files_arr, 'manual_path', '').
					html_e('br').
					html_e('span', array(), sprintf(_('Pick a file already uploaded (by SFTP or SCP) to the <a href="%1$s">project\'s incoming directory</a> (%2$s).'),
									'sftp://'.forge_get_config('shell_host').$incoming.'/', $incoming), false);
		} else {
			$cells[][] = html_e('p', array('class' => 'warning'), sprintf(_('You need first to upload file in %s'),$incoming), false);
		}
		echo $HTML->multiTableRow(array('id' => 'pathroweditfile', 'class' => 'hide'), $cells);
	}
if ($g->useCreateOnline()) {
	$cells = array();
	$cells[] = array(_('Edit the contents to your desire or leave them as they are to remain unmodified.').html_e('br').
			html_e('textarea', array('id' => 'defaulteditzone', 'name' => 'details', 'rows' => '15', 'cols' => '100'), '', false).
			html_e('input', array('id' => 'defaulteditfiletype', 'type' => 'hidden', 'name' => 'filetype', 'value' => 'text/plain')).
			html_e('input', array('id' => 'editor', 'type' => 'hidden', 'name' => 'editor', 'value' => 'online')),
			'colspan' => 2);
	echo $HTML->multiTableRow(array('id' => 'editonlineroweditfile', 'class' => 'hide'), $cells);
}

$cells = array();
$cells[] = array(_('Specify an new outside URL where the file will be referenced').utils_requiredField()._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('input', array('id' => 'editFileurl', 'type' => 'url', 'name' => 'file_url', 'size' => '50', 'pattern' => 'ftp://.+|https?://.+'));
echo $HTML->multiTableRow(array('id' => 'fileurlroweditfile', 'class' => 'hide'), $cells);
$cells = array();
$cells[] = array(_('File')._(':'), 'class' => 'docman_editfile_file');
$cells[][] = html_e('input', array('type' => 'file', 'name' => 'uploaded_data')).html_e('br').'('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')';
echo $HTML->multiTableRow(array('id' => 'uploadnewroweditfile', 'class' => 'hide'), $cells);
echo $HTML->listTableBottom();
echo html_e('input', array('type' => 'hidden', 'id' => 'docid', 'name' => 'docid'));
echo html_e('input', array('type' => 'hidden', 'id' => 'edit_version', 'name' => 'edit_version'));
echo html_e('input', array('type' => 'hidden', 'id' => 'new_version', 'name' => 'new_version', 'value' => 0));
echo $HTML->closeForm();
echo html_ac(html_ap() -1);
