<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
global $g; // group object
global $group_id; // id of the group
global $dirid; //id of the doc_group
global $dm; // the Document Manager object
global $HTML; // Layout object
global $warning_msg;
global $error_msg;
global $childgroup_id;
global $stateidArr;

$actionurl = '/docman/?group_id='.$group_id.'&action=addfile&dirid='.$dirid;
$redirecturl = '/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid;
// plugin projects-hierarchy support
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$actionurl .= '&childgroup_id='.$childgroup_id;
	$redirecturl .= '&childgroup_id='.$childgroup_id;
}

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError())
	exit_error($dgf->getErrorMessage(), 'docman');

if (!forge_check_perm('docman', $group_id, 'submit')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($redirecturl);
}
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
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

//]]>
<?php
echo html_ac(html_ap() - 1);
echo html_ao('div', array('class' => 'docmanDivIncluded'));
if ($dgf->getNested($stateidArr) == NULL) {
	$dg = new DocumentGroup($g);

	if ($dg->isError()) {
		$error_msg = $dg->getErrorMessage();
		session_redirect('/docman/?group_id='.$group_id);
	}

	if ($dg->create(_('Uncategorized Submissions'))) {
		session_redirect('/docman/?group_id='.$group_id.'&view=additem');
	}

	echo $HTML->warning_msg(_('You MUST first create at least one folder to store your document.'));
} else {
	/* display the add new documentation form */
	echo $HTML->openForm(array('name' => 'adddata', 'action' => util_make_uri($actionurl), 'method' => 'post', 'enctype' => 'multipart/form-data'));
	echo $HTML->listTableTop(array(), array(), 'infotable');
	$cells = array();
	$cells[][] = _('Document Title').utils_requiredField();
	$cells[][] = html_e('input', array('pattern' => '.{5,}', 'placeholder' => _('Document Title').' '.sprintf(_('(at least %s characters)'), 5), 'title' => _('Document Title')._(': ')._('Refers to the relatively brief title of the document (e.g. How to use the download server).'), 'type' => 'text', 'name' => 'title', 'size' => 40, 'maxlength' => 255, 'required' => 'required'));
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[][] = _('Description') .utils_requiredField();
	$cells[][] = html_e('textarea', array('pattern' => '.{10,}', 'placeholder' => _('Description').' '.sprintf(_('(at least %s characters)'), 10), 'title' => _('Editing tips:http,https or ftp: Hyperlinks. [#NNN]: Tracker id NNN. [TNNN]: Task id NNN. [wiki:&lt;pagename&gt;]: Wiki page. [forum:&lt;msg_id&gt;]: Forum post. [DNNN]: Document id NNN.'), 'name' => 'description', 'rows' => 5, 'cols' => 50, 'maxlength' => 255, 'required' => 'required'), '', false);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[][] = _('Comment');
	$cells[][] = html_e('textarea', array('placeholder' => _('Add free comment'), 'name' => 'vcomment', 'rows' => 5, 'cols' => 50, 'maxlength' => 255), '', false);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[][] = _('Type of Document') .utils_requiredField();
	$nextcell = html_e('input', array('type' => 'radio', 'id' => 'buttonFile', 'name' => 'type', 'value' => 'httpupload', 'checked' => 'checked', 'required' => 'required')).html_e('span', array(), _('File'), false).
			html_e('input', array('type' => 'radio', 'id' => 'buttonUrl', 'name' => 'type', 'value' => 'pasteurl', 'required' => 'required')).html_e('span', array(), _('URL'), false);
	if (forge_get_config('use_manual_uploads')) {
		$nextcell .= html_e('input', array('type' => 'radio', 'id' => 'buttonManualUpload', 'name' => 'type', 'value' => 'manualupload', 'required' => 'required')).html_e('span', array(), _('Already-uploaded file'), false);
	}
	if ($g->useCreateOnline()) {
		$nextcell .= html_e('input', array('type' => 'radio', 'id' => 'buttonEditor', 'name' => 'type', 'value' => 'editor', 'required' => 'required')).html_e('span', array(), _('Create online'), false);
	}
	$cells[][] = $nextcell;
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[][] = _('Upload File').utils_requiredField();
	$cells[][] = html_e('input', array('type' => 'file', 'required' => 'required', 'name' => 'uploaded_data')).
			html_e('span', array(), '('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')', false);
	echo $HTML->multiTableRow(array('id' => 'filerow'), $cells);
	$cells = array();
	$cells[][] = _('URL').utils_requiredField();
	$cells[][] = html_e('input', array('type' => 'url', 'name' => 'file_url', 'size' => '30', 'placeholder' => _('Enter a valid URL'), 'pattern' => 'ftp://.+|https?://.+'));
	echo $HTML->multiTableRow(array('id' => 'urlrow', 'class' => 'hide'), $cells);
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
		echo $HTML->multiTableRow(array('id' => 'pathrow', 'class' => 'hide'), $cells);
	}
	$cells = array();
	$cells[][] = _('File Name').utils_requiredField();
	$cells[][] = html_e('input', array('type' => 'text', 'name' => 'name', 'size' => '30'));
	echo $HTML->multiTableRow(array('id' => 'editnamerow', 'class' => 'hide'), $cells);
	echo html_ao('tr', array('id' => 'editrow', 'class' => 'hide'));
	echo html_ao('td', array('colspan' => '2'));
	$GLOBALS['editor_was_set_up'] = false;
	$params = array() ;
	/* name must be details !!! if name = data then nothing is displayed */
	$params['name'] = 'details';
	$params['height'] = "300";
	$params['body'] = "";
	$params['group'] = $group_id;
	plugin_hook("text_editor", $params);
	if (!$GLOBALS['editor_was_set_up']) {
		echo html_e('textarea', array('name' => 'details', 'rows' => 5, 'cols' => 80), '', false);
	}
	unset($GLOBALS['editor_was_set_up']);
	echo html_ac(html_ap() - 2);
	if ($dirid) {
		echo html_ao('tr');
		echo html_ao('td', array('colspan' => 2));
		echo html_e('input', array('type' => 'hidden', 'name' => 'doc_group', 'value' => $dirid));
		echo html_ac(html_ap() - 2);
	} else {
		$cells = array();
		$cells[][] = _('Documents folder that document belongs in');
		$cells[][] = $dm->showSelectNestedGroups($dgf->getNested($stateidArr), 'doc_group', false, $dirid);
		echo $HTML->multiTableRow(array(), $cells);
	}
	if (forge_check_perm('docman', $group_id, 'approve')) {
		$cells = array();
		$cells[][] = _('Status of that document');
		$cells[][] = doc_get_state_box('xzxz', array(2)); /** no direct deleted status */
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo $HTML->addRequiredFieldsInfoBox();
	if ($g->useDocmanSearch()) {
		echo html_e('p', array(), _('Both fields Title & Description are used by the document search engine.'), false);
	}
	echo html_e('div', array('class' => 'docmanSubmitDiv'), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit Information'))));
	echo $HTML->closeForm();
}
echo html_ac(html_ap() - 1);
