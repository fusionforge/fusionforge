<?php
/**
 * Project Admin: Edit Releases of Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $HTML; // html object
global $group_id; // id of group
global $g; // group object

$package_id = getIntFromRequest('package_id');
$release_id = getIntFromRequest('release_id');

if (!$package_id || !$release_id) {
	session_redirect('/frs/?view=admin&group_id='.$group_id);
}

session_require_perm('frs', $package_id, 'file');

//
//  Get the package
//
$frsp = new FRSPackage($g, $package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error(_('Could Not Get FRS Package'), 'frs');
} elseif ($frsp->isError()) {
	exit_error($frsp->getErrorMessage(), 'frs');
}

//
//  Get the release
//
$frsr = new FRSRelease($frsp,$release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('Could Not Get FRS Release'), 'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(), 'frs');
}

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerFRS;

jQuery(document).ready(function() {
	controllerFRS = new FRSController();
});

//]]>
<?php
echo html_ac(html_ap() - 1);
echo html_e('h2', array(), _('Edit Release for the package').' '.$frsp->getName());
/*
 * Show the forms for each part
 */
if (forge_check_perm('frs', $package_id, 'admin')) {
	echo $HTML->openForm(array('enctype' => 'multipart/form-data', 'method' => 'post', 'action' => util_make_uri('/frs/?group_id='.$group_id.'&release_id='.$release_id.'&package_id='.$package_id.'&action=editrelease')));
	echo $HTML->listTableTop();
	$cells = array();
	$cells[][] = '<strong>'._('Release Date')._(':').'</strong>';
	$cells[][] = '<input type="text" name="release_date" value="'.date('Y-m-d H:i',$frsr->getReleaseDate()).'" size="16" maxlength="16" />';
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[][] = '<strong>'._('Release Name').utils_requiredField()._(':').'</strong>';
	$cells[][] = '<input type="text" name="release_name" value="'.$frsr->getName().'" required="required" pattern=".{3,}" title="'._('At least 3 characters').'" />';
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[][] = '<strong>'._('Status')._(':').'</strong>';
	$cells[][] = frs_show_status_popup('status_id',$frsr->getStatus());
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array(_('Edit the Release Notes or Change Log for this release of this package. These changes will apply to all files attached to this release.').'<br/>'.
			_('You can either upload the release notes and change log individually, or paste them in together below.'), 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<strong>'._('Upload Release Notes')._(':').'</strong>'.
			'('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')', 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<input type="file" name="uploaded_notes" size="30" />', 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<strong>'._('Upload Change Log')._(':').'</strong>'.
			'('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')', 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<input type="file" name="uploaded_changes" />', 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<strong>'._('Paste The Notes In')._(':').'</strong><br/>'.
			'<textarea name="release_notes" rows="10" cols="60">'.$frsr->getNotes().'</textarea>', 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<strong>'._('Paste The Change Log In')._(':').'</strong><br/>'.
			'<textarea name="release_changes" rows="10" cols="60">'.$frsr->getChanges().'</textarea>', 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<input type="checkbox" name="preformatted" value="1" '.(($frsr->getPreformatted())?'checked="checked"':'').' />'._('Preserve my pre-formatted text').
			'<p><input type="submit" name="submit" value="'._('Submit/Refresh').'" /></p>', 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	echo $HTML->listTableBottom();
	echo $HTML->closeForm();
}
echo html_e('hr');
echo html_e('h2', array(), _('Add Files To This Release'));
echo html_e('p', array(), _('Now, choose a file to upload into the system.'));

echo $HTML->openForm(array('enctype' => 'multipart/form-data', 'method' => 'post', 'action' => util_make_uri('/frs/?group_id='.$group_id.'&release_id='.$release_id.'&package_id='.$package_id.'&action=addfile')));
echo html_ao('fieldset');
echo html_e('legend', array(), '<strong>'._('File Name').'</strong>');
echo _('Upload a new file')._(': ').'<input type="file" name="userfile" />'.'('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')';
$content = '';
if (forge_get_config('use_ftp_uploads')) {
	include ($gfcommon.'frs/views/useftpuploads.php');
}

if (forge_get_config('use_manual_uploads')) {
	include $gfcommon.'frs/views/usemanualuploads.php';
}
if ($g->usesDocman() && forge_check_perm('docman', $group_id, 'read')) {
	include $gfcommon.'frs/views/docmanfile.php';
}
if (!empty($content)) {
	echo $content;
}
echo html_ac(html_ap() -1);
echo $HTML->listTableTop();
$cells = array();
$cells[][] = '<strong>'._('File Type')._(':').'</strong>';
$cells[][] = frs_show_filetype_popup('type_id');
$cells[][] = '<strong>'._('Processor Type')._(':').'</strong>';
$cells[][] = frs_show_processor_popup('processor_id');
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo html_e('p', array(), '<input type="submit" name="submit" value="'._('Add This File').'" />');
echo $HTML->closeForm();
// Get a list of files associated with this release
$files = $frsr->getFiles();
if(count($files)) {
	echo html_e('hr');
	echo html_e('h2', array(), _('Edit Files In This Release'));
	print(_('Once you have added files to this release you <strong>must</strong> update each of these files with the correct information or they will not appear on your download summary page.')."\n");
	$title_arr[] = html_e('input', array('id' => 'checkallactive', 'type' => 'checkbox', 'title' => _('Select / Deselect all files for massaction'), 'onClick' => 'controllerFRS.checkAll("checkedrelidactive", "active")'));
	$title_arr[] = _('File Name');
	$title_arr[] = _('Processor');
	$title_arr[] = _('File Type');
	$title_arr[] = _('Release');
	$title_arr[] = _('Release Date');
	$title_arr[] = _('Actions');

	echo $HTML->listTableTop($title_arr, array(), '', '', array(), array(), array(array('style' => 'width: 2%'), array('style' => 'width: 30%')));
	echo '<tr><td colspan="7" style="padding:0;">';
	foreach ($files as $key => $file) {
		echo $HTML->openForm(array('action' => util_make_uri('/frs/?group_id='.$group_id.'&release_id='.$release_id.'&package_id='.$package_id.'&file_id='.$file->getID().'&action=editfile'), 'method' => 'post', 'id' => 'fileid'.$file->getID()));
		echo $HTML->listTableTop();
		$cells = array();
		$cells[] = array(html_e('input', array('type' => 'checkbox', 'value' => $file->getID(), 'class' => 'checkedrelidactive', 'title' => _('Select / Deselect this file for massaction'), 'onClick' => 'controllerFRS.checkgeneral("active")')), 'style' => 'width: 2%; padding: 0px;');
		$cells[] = array($file->getName(), 'style' => 'white-space: nowrap; width: 30%');
		$cells[][] = frs_show_processor_popup('processor_id', $file->getProcessorID());
		$cells[][] = frs_show_filetype_popup('type_id', $file->getTypeID());
		$cells[][] = frs_show_release_popup($group_id, $name = 'new_release_id', $release_id);
		$cells[][] = '<input type="text" name="release_time" value="'.date('Y-m-d', $file->getReleaseTime()).'" size="10" maxlength="10" />';
		$deleteUrlAction = util_make_uri('/frs/?action=deletefile&package_id='.$package_id.'&group_id='.$group_id.'&file_id='.$file->getID());
		$cells[][] = '<input type="submit" name="submit" value="'._('Update/Refresh').'" />'.util_make_link('#', $HTML->getDeletePic(_('Delete this file'), _('Delete file')), array('onclick' => 'javascript:controllerFRS.toggleConfirmBox({idconfirmbox: \'confirmbox1\', do: \''._('Delete the file').' '.$file->getName().'\', cancel: \''._('Cancel').'\', height: 150, width: 400, action: \''.$deleteUrlAction.'\'})' ), true);
		echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($key, true)), $cells);
		echo $HTML->listTableBottom();
		echo $HTML->closeForm();
	}
	echo '</td></tr>';
	echo $HTML->listTableBottom();
	$deleteUrlAction = util_make_uri('/frs/?action=deletefile&group_id='.$group_id.'&package_id='.$package_id);
	echo html_ao('p');
	echo html_ao('span', array('id' => 'massactionactive', 'class' => 'hide'));
	echo html_e('span', array('id' => 'frs-massactionmessage', 'title' => _('Actions availables for selected files, you need to check at least one file to get actions')), _('Mass actions for selected files')._(':'), false);
	echo util_make_link('#', $HTML->getDeletePic(_('Delete selected file(s)'), _('Delete files')), array('onclick' => 'javascript:controllerFRS.toggleConfirmBox({idconfirmbox: \'confirmbox1\', do: \''._('Delete selected file(s)').'\', cancel: \''._('Cancel').'\', height: 150, width: 300, action: \''.$deleteUrlAction.'&file_id=\'+controllerFRS.buildUrlByCheckbox("active")})', 'title' => _('Delete selected file(s)')), true);
	echo html_ac(html_ap() - 2);
}

echo $HTML->jQueryUIconfirmBox('confirmbox1', _('Delete file'), _('You are about to delete permanently this file. Are you sure? This action is definitive.'));
echo html_e('p', array(), sprintf(ngettext('There is %s user monitoring this package.', 'There are %s users monitoring this package.', $frsp->getMonitorCount()), $frsp->getMonitorCount()));
